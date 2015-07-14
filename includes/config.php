<?php
/*--------------------------------------------------------+
| SourcePunish WebApp                                     |
| Copyright (C) 2015 https://sourcepunish.net             |
+---------------------------------------------------------+
| This program is free software and is released under     |
| the terms of the GNU Affero General Public License      |
| version 3 as published by the Free Software Foundation. |
| You can redistribute it and/or modify it under the      |
| terms of this license, which is included with this      |
| software as agpl-3.0.txt or viewable at                 |
| http://www.gnu.org/licenses/agpl-3.0.html               |
+--------------------------------------------------------*/

if(!defined('SP_LOADED')) die('Access Denied!');

/*{{CONFIG_EXPLAIN}}*/

$GLOBALS['config']['system']['phperrors'] = false;
$GLOBALS['config']['system']['path_html'] = '';

$GLOBALS['config']['sql']['host'] = 'localhost';
$GLOBALS['config']['sql']['username'] = 'user';
$GLOBALS['config']['sql']['password'] = 'password';
$GLOBALS['config']['sql']['database'] = 'somedb';
$GLOBALS['config']['sql']['prefix'] = 'sp_';
$GLOBALS['config']['site']['email'] = 'support@somesite.net';
?>