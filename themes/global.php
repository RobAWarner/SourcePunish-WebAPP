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

$GLOBALS['theme']->AddStyle(HTML_CSS.'base.css');
$GLOBALS['theme']->AddScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
$GLOBALS['theme']->AddScript(HTML_SCRIPTS.'global.js');
$GLOBALS['theme']->AddScript(HTML_SCRIPTS.'tooltips.js');
?>