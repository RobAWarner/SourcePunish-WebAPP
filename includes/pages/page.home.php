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

/* BBCode Test */
/*$X = ParseText('[center][url=http://cake.com][img]http://monsterprojects.org/images/logo.png[/img][/url][br][s]cake[/s][/center]', true, true);
echo $X;*/

$Intro = GetCustomPage('home_intro');
$GLOBALS['theme']->AddContent($Intro['title'], $Intro['content']);
unset($Intro);

/* Recent Punishments */
$PunishQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PREFIX.'punishments ORDER BY Punish_Time DESC LIMIT 25');
$Rows = array();
while($PunishRow = $GLOBALS['sql']->FetchArray($PunishQuery)) {
    $Rows[] = $PunishRow;
}
$GLOBALS['sql']->Free($PunishQuery);
if(count($Rows) == 0) {
    $Content = ParseText('#TRANS_2011');
} else {
    $Content = $GLOBALS['theme']->BuildPunishTable($Rows);
}
unset($Rows);
$GLOBALS['theme']->AddContent(ucwords(ParseText('#TRANS_1156')), $Content);
?>