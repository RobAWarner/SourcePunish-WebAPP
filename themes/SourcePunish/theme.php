<?php
/*--------------------------------------------------------+
| SourcePunish WebApp                                     |
| Copyright (C) https://sourcepunish.net                  |
+---------------------------------------------------------+
| This program is free software and is released under     |
| the terms of the GNU Affero General Public License      |
| version 3 as published by the Free Software Foundation. |
| You can redistribute it and/or modify it under the      |
| terms of this license, which is included with this      |
| software as agpl-3.0.txt or viewable at                 |
| http://www.gnu.org/licenses/agpl-3.0.html               |
+--------------------------------------------------------*/
if(!defined('IN_SP')) die('Access Denied!');

/* Theme Info */
$GLOBALS['themeinfo']['name'] = 'SourcePunish';
$GLOBALS['themeinfo']['author'] = 'SourcePunish.net';
$GLOBALS['themeinfo']['version'] = '0.5';
$GLOBALS['themeinfo']['subthemes']['enabled'] = true;
$GLOBALS['themeinfo']['subthemes']['default'] = 'Blue';
$GLOBALS['themeinfo']['subthemes']['required'] = true;

/* Theme Functions */
$GLOBALS['theme']->AddStyle(HTML_THEME_PATH.'styles.css');
$GLOBALS['theme']->AddScript('//html5shiv.googlecode.com/svn/trunk/html5.js', 'if lt IE 9');


?>