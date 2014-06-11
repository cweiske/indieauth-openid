*************************
IndieAuth to OpenID proxy
*************************

Proxies IndieAuth authorization requests to one's OpenID server.

=====
Setup
=====

1. Setup your webserver: make ``www/`` the root (document) directory of the
   new virtual host
2. Make ``data/`` world-writable (or at least writable by the web server)
3. Modify your website and add the following to its ``<head>``::

     <link rel="authorization_endpoint" href="https://indieauth-openid.example.org/" />


============
Dependencies
============

* PDO::sqlite3
* PEAR Libraries:

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
