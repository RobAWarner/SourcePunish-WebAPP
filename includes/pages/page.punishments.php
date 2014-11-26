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

if(isset($_GET['p']) && $_GET['p'] != '' && filter_var($_GET['p'], FILTER_VALIDATE_INT) !== false && $_GET['p'] > 0)
    $CurrentPage = intval($GLOBALS['sql']->Escape($_GET['p']));
else {
    if(isset($_GET['p']))
        Redirect('^punishments');
    else
        $CurrentPage = 1;
}
$PerPage = 40;
$TotalPages = ceil($GLOBALS['varcache']['punishcount']/$PerPage);
if($CurrentPage > $TotalPages) 
    Redirect('^punishments');
$Paginate_Limit = intval(($CurrentPage - 1) * $PerPage);

$PunishQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PREFIX.'punishments ORDER BY Punish_Time DESC LIMIT '.$Paginate_Limit.', '.$PerPage);
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
$GLOBALS['theme']->AddContent(ucwords(sprintf(ParseText('#TRANS_1157'), number_format($CurrentPage), number_format($TotalPages))), $Content);
$GLOBALS['theme']->AddContent('', $GLOBALS['theme']->Paginate($TotalPages, $CurrentPage, ParseURL('^punishments').'&p='));
?>