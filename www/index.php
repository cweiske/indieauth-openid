<?php
/**
 *
 * @link http://indiewebcamp.com/login-brainstorming
 * @link https://indieauth.com/developers
 */
//require_once __DIR__ . '/../src/init.php';
require_once 'OpenID/RelyingParty.php';
require_once 'OpenID/Message.php';
require_once 'OpenID/Exception.php';
require_once 'Net/URL2.php';

function loadDb()
{
    $db = new PDO('sqlite:' . __DIR__ . '/../data/tokens.sq3');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS authtokens(
code TEXT,
me TEXT,
redirect_uri TEXT,
client_id TEXT,
state TEXT,
created DATE
)");
    //clean old tokens
    $stmt = $db->prepare('DELETE FROM authtokens WHERE created < :created');
    $stmt->execute(array(':created' => date('c', time() - 60)));

    return $db;
}

function create_token($me, $redirect_uri, $client_id, $state)
{
    $code = base64_encode(openssl_random_pseudo_bytes(32));
    $db = loadDb();
    $db->prepare(
        'INSERT INTO authtokens (code, me, redirect_uri, client_id, state, created)'
        . ' VALUES(:code, :me, :redirect_uri, :client_id, :state, :created)'
    )->execute(
        array(
            ':code' => $code,
            ':me' => $me,
            ':redirect_uri' => $redirect_uri,
            ':client_id' => $client_id,
            ':state' => (string) $state,
            ':created' => date('c')
        )
    );
    return $code;
}

function validate_token($code, $redirect_uri, $client_id, $state)
{
    $db = loadDb();
    $stmt = $db->prepare(
        'SELECT me FROM authtokens WHERE'
        . ' code = :code'
        . ' AND redirect_uri = :redirect_uri'
        . ' AND client_id = :client_id'
        . ' AND state = :state'
        . ' AND created >= :created'
    );
    $stmt->execute(
        array(
            ':code'         => $code,
            ':redirect_uri' => $redirect_uri,
            ':client_id'    => $client_id,
            ':state'        => (string) $state,
            ':created'      => date('c', time() - 60)
        )
    );
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare('DELETE FROM authtokens WHERE code = :code');
    $stmt->execute(array(':code' => $code));

    if ($row === false) {
        return false;
    }
    return $row['me'];
}

function error($msg)
{
    header('HTTP/1.0 400 Bad Request');
    echo $msg . "\n";
    exit(1);
}

function verifyUrlParameter($givenParams, $paramName)
{
    if (!isset($givenParams[$paramName])) {
        error('"' . $paramName . '" parameter missing');
    }
    $url = parse_url($givenParams[$paramName]);
    if (!isset($url['scheme'])) {
        error('Invalid URL in "' . $paramName . '" parameter: scheme missing');
    }
    if (!isset($url['host'])) {
        error('Invalid URL in "' . $paramName . '" parameter: host missing');
    }

    return $givenParams[$paramName];
}

function getBaseUrl()
{
    if (!isset($_SERVER['REQUEST_SCHEME'])) {
        $_SERVER['REQUEST_SCHEME'] = 'http';
    }
    $file = preg_replace('/#.*$/', '', $_SERVER['REQUEST_URI']);
    if ($file == '') {
        $file = ' /';
    } else if (substr($file, -1) != '/') {
        $file = dirname($file);
    }
    return $_SERVER['REQUEST_SCHEME'] . '://'
        . $_SERVER['HTTP_HOST']
        . $file;
}

session_start();
$returnTo = getBaseUrl();
$realm    = getBaseUrl();

if (isset($_GET['openid_mode']) && $_GET['openid_mode'] != '') {
    //verify openid response
    if (!count($_POST)) {
        list(, $queryString) = explode('?', $_SERVER['REQUEST_URI']);
    } else {
        $queryString = file_get_contents('php://input');
    }

    $message = new \OpenID_Message($queryString, \OpenID_Message::FORMAT_HTTP);
    $id      = $message->get('openid.claimed_id');
    try {
        $o = new \OpenID_RelyingParty($returnTo, $realm, $_SESSION['me']);
        $result = $o->verify(new \Net_URL2($returnTo . '?' . $queryString), $message);

        if ($result->success()) {
            $token = create_token(
                $_SESSION['me'], $_SESSION['redirect_uri'],
                $_SESSION['client_id'], $_SESSION['state']
            );
            //redirect to indieauth
            $url = new Net_URL2($_SESSION['redirect_uri']);
            $url->setQueryVariable('code', $token);
            $url->setQueryVariable('me', $_SESSION['me']);
            $url->setQueryVariable('state', $_SESSION['state']);
            header('Location: ' . $url->getURL());
            exit();
        } else {
            error('Error logging in: ' . $result->getAssertionMethod());
        }
    } catch (OpenID_Exception $e) {
        error('Error logging in: ' . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $me           = verifyUrlParameter($_GET, 'me');
    $redirect_uri = verifyUrlParameter($_GET, 'redirect_uri');
    $client_id    = verifyUrlParameter($_GET, 'client_id');
    $state        = null;
    if (isset($_GET['state'])) {
        $state = $_GET['state'];
    }
    //FIXME: support "response_type"?

    $_SESSION['me']           = $me;
    $_SESSION['redirect_uri'] = $redirect_uri;
    $_SESSION['client_id']    = $client_id;
    $_SESSION['state']        = $state;

    try {
        $o = new \OpenID_RelyingParty($returnTo, $realm, $me);
        $authRequest = $o->prepare();
        $url = $authRequest->getAuthorizeURL();
        header("Location: $url");
        exit(0);
    } catch (OpenID_Exception $e) {
        error('OpenID error: ' . $e->getMessage());
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $redirect_uri = verifyUrlParameter($_POST, 'redirect_uri');
    $client_id    = verifyUrlParameter($_POST, 'client_id');
    $state        = null;
    if (isset($_GET['state'])) {
        $state = $_GET['state'];
    }
    if (!isset($_POST['code'])) {
        error('"code" parameter missing');
    }
    $token = $_POST['code'];

    $me = validate_token($token, $redirect_uri, $client_id, $state);
    if ($me === false) {
        header('HTTP/1.0 400 Bad Request');
        echo "Validating token failed\n";
        exit(1);
    }
    header('Content-type: application/x-www-form-urlencoded');
    echo 'me=' . urlencode($me);
}
?>
