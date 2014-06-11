*************************
IndieAuth to OpenID proxy
*************************

Proxies IndieAuth__ authorization requests to one's OpenID__ server.

__ http://indiewebcamp.com/IndieAuth
__ http://openid.net/

=====
Setup
=====

1. Setup your webserver: make ``www/`` the root (document) directory of the
   new virtual host
2. Make ``data/`` world-writable (or at least writable by the web server)
3. Make sure your website can be used as OpenID identifier
4. Modify your website and add the following to its ``<head>``::

     <link rel="authorization_endpoint" href="http://indieauth-openid.example.org/" />


============
Dependencies
============

* PHP 5.3+
* PDO with sqlite3 driver
* PEAR libraries:

  * Net_URL2
  * OpenID


=======
License
=======
``indieauth-openid`` is licensed under the `AGPL v3`__ or later.

__ http://www.gnu.org/licenses/agpl.html


======
Author
======
Written by Christian Weiske, cweiske@cweiske.de
