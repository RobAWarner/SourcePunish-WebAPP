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

$Theme->Title_Add($Trans->t('title.punishments'));

/* Check the page number if it exists */
if(isset($_GET['p']) && CheckVar($_GET['p'], SP_VAR_INT))
    $CurrentPage = (int)$SQL->Escape($_GET['p']);
else {
    if(isset($_GET['p']))
        Redirect('^punishments');
    else
        $CurrentPage = 1;
}

/* Pagination variable */
$PerPage = 40;
if(isset($Settings['punish_perpage']) && CheckVar($Settings['punish_perpage'], SP_VAR_INT))
    $PagePage = (int)$Settings['punish_perpage'];

/* Get record count */
$TotalRecords = $SQL->Query_FetchArray('SELECT count(1) as total FROM '.SQL_PUNISHMENTS);
$TotalPages = ceil($TotalRecords['total'] / $PerPage);
unset($TotalRecords);

/* Is the current page valid? */
if($CurrentPage > $TotalPages) 
    Redirect('^punishments');
$Paginate_Limit = intval(($CurrentPage - 1) * $PerPage);

/* Fetch all punishments */
$PunishQuery = $SQL->Query('SELECT * FROM '.SQL_PUNISHMENTS.' ORDER BY Punish_Time DESC LIMIT '.$SQL->Escape($Paginate_Limit).', '.$SQL->Escape($PerPage));
unset($PerPage, $Paginate_Limit);

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
    $Theme->Content_Add($Theme->Render('content', array('attrs'=>array(), 'title'=>$Trans->t('title.punishments'), 'text'=>$PunishTable)));
    unset($PunishTable);

    /* Include file for 'Paginate' function */
    SP_Require(DIR_INCLUDE.'inc.paginate.php');

    /* Render the punishment table */
    $Pagination = $Theme->Render('paginate', Paginate($TotalPages, $CurrentPage, ParseURL('^punishments', array('p'=>''))));

    /* Add to the page */
    $Theme->Content_Add($Theme->Render('content', array('text'=>$Pagination)));
    unset($Pagination);
}
unset($CurrentPage, $TotalPages);

?>