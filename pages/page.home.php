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

global $Settings, $SQL, $Trans, $Theme;

/* Get site introduction */
$Intro = SP_GetCustomPage('home_intro');
$Theme->Content_Add($Theme->Render('content', array('attrs'=>array(), 'title'=>SpecialChars($Intro['title']), 'text'=>$Intro['text'])));
unset($Intro);

/* Fetch punishments */
$PunishQuery = $SQL->Query('SELECT * FROM '.SQL_PUNISHMENTS.' ORDER BY Punish_Time DESC LIMIT 25');

$PunishRows = array();
while($PunishRow = $SQL->FetchArray($PunishQuery)) {
    $PunishRows[] = $PunishRow;
}
$SQL->Free($PunishQuery);

/* Were any records returned */
if(count($PunishRows) == 0) {
    unset($PunishRows);
    $Theme->Content_Add($Theme->Render('alert', array('attrs'=>array(), 'type'=>'danger', 'text'=>$Trans->t('alert.nopunishments'))), true);
} else {
    /* Include file for 'Punish_Table' function */
    SP_Require(DIR_INCLUDE.'inc.punish-table.php');

    /* Render the punishment table */
    $PunishTable = $Theme->Render('table.punish.list', Punish_Table($PunishRows, array('id'=>'tables-punish-list', 'class'=>array('table', 'table-punish'))));
    unset($PunishRows);

    /* Add to the page */
    $Theme->Content_Add($Theme->Render('content', array('attrs'=>array(), 'title'=>$Trans->t('title.recent'), 'text'=>$PunishTable)));
    unset($PunishTable);
}
?>