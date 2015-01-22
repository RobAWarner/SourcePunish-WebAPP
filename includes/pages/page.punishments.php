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

/* Add page title */
$GLOBALS['theme']->AddTitle($GLOBALS['trans'][1002]);

/* Check the page number if it exists */
if(isset($_GET['p']) && CheckVar($_GET['p'], SP_VAR_INT))
    $CurrentPage = (int)$GLOBALS['sql']->Escape($_GET['p']);
else {
    if(isset($_GET['p']))
        Redirect('^punishments');
    else
        $CurrentPage = 1;
}

/* Pagination variable */
$PerPage = 40;
if(isset($GLOBALS['settings']['punish_perpage']) && CheckVar($GLOBALS['settings']['punish_perpage'], SP_VAR_INT))
    $PagePage = (int)$GLOBALS['settings']['punish_perpage'];
$TotalPages = ceil($GLOBALS['varcache']['punishcount']/$PerPage);
if($CurrentPage > $TotalPages) 
    Redirect('^punishments');
$Paginate_Limit = intval(($CurrentPage - 1) * $PerPage);

/* Fetch all punishments */
$PunishQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PUNISHMENTS.' ORDER BY Punish_Time DESC LIMIT '.$Paginate_Limit.', '.$PerPage);
$Rows = array();
while($PunishRow = $GLOBALS['sql']->FetchArray($PunishQuery)) {
    $Rows[] = $PunishRow;
}
$GLOBALS['sql']->Free($PunishQuery);

/* Check if any punishment exist */
if(count($Rows) == 0)
    $Content = $GLOBALS['trans'][2011];
else
    $Content = SP_BuildPunishTable($Rows);
unset($Rows);

/* Add main content to page */
$GLOBALS['theme']->AddContent(ucwords(sprintf($GLOBALS['trans'][1157], number_format($CurrentPage), number_format($TotalPages))), $Content);
/* Add pagination links to page */
$GLOBALS['theme']->AddContent('', $GLOBALS['theme']->Paginate($TotalPages, $CurrentPage, ParseURL('^punishments').'&amp;p='));
?>