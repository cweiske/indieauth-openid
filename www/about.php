<?php
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
    $prot = 'https';
} else {
    $prot = 'http';
}
$epUrl = $prot . '://' . $_SERVER['HTTP_HOST'] . '/';
if (Phar::running()) {
    $epUrl .= ltrim($_SERVER['SCRIPT_NAME'], '/') . '/';
}
$hepUrl = htmlspecialchars($epUrl);

?>
<?xml version="1.0" encoding="utf-8"?>
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <title>IndieAuth-OpenID proxy</title>
  <style type="text/css">
body {
    max-width: 80ex;
    margin-left: auto;
    margin-right: auto;
}
pre {
    margin-left: 2ex;
    background-color: #DDD;
    padding: 1ex;
    border-radius: 1ex;
}
tt {
    background-color: #DDD;
    padding: 0.2ex 0.5ex;
}
  </style>

 </head>
 <body>
  <h1>IndieAuth to OpenID proxy</h1>
  <p>
   This software make it possible to use your OpenID to login
   to IndieAuth-enabled websites.
  </p>


  <h2 id="setup">Setup</h2>
  <p>
   On your home page, add the following code to the
   HTML <tt>&lt;head&gt;</tt> section:
  </p>
  <pre>&lt;link rel="authorization_endpoint" href="<?php echo $hepUrl; ?>" /&gt;</pre>
  <p>
   Now simply enter your homepage URL in the IndieAuth login field
   and press return.
   You'll be redirected to your OpenID provider and see its login prompt.
  </p>

  
  <h2 id="source">About indieauth-openid</h2>
  <p>
   <em>indieauth-openid</em> was written by
   <a href="http://cweiske.de/">Christian Weiske</a>
   and is licensed under the
   <a href="http://www.gnu.org/licenses/agpl.html">AGPL v3 or later</a>.
  </p>
  <p>
   You can get the source code from
   <a href="http://git.cweiske.de/indieauth-openid.git">git.cweiske.de/indieauth-openid.git</a>
   or the
   <a href="https://github.com/cweiske/indieauth-openid">mirror on Github</a>.
  </p>

 </body>
</html>
