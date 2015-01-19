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
if(!defined('IN_SP')) die('Access Denied!');

/* Get site introduction */
$Intro = SP_GetCustomPage('home_intro');
$GLOBALS['theme']->AddContent($Intro['title'], $Intro['content']);
unset($Intro);

/* Recent punishments */
$PunishQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PUNISHMENTS.' ORDER BY Punish_Time DESC LIMIT 25');
$Rows = array();
while($PunishRow = $GLOBALS['sql']->FetchArray($PunishQuery)) {
    $Rows[] = $PunishRow;
}
$GLOBALS['sql']->Free($PunishQuery);

/* Chec if any punishments exist */
if(count($Rows) == 0)
    $Content = $GLOBALS['trans'][2011];
else
    $Content = SP_BuildPunishTable($Rows);
unset($Rows);

/* Add main content to page */
$GLOBALS['theme']->AddContent(ucwords($GLOBALS['trans'][1156]), $Content);
?>