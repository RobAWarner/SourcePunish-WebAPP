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
$GLOBALS['theme']->AddTitle($GLOBALS['trans'][1004]);

/* Valid search types */
$SearchTypes = array('playername', 'playerid', 'playerip', 'player64', 'adminname', 'adminid', 'punishtype', 'authtype', 'reason', 'length', 'active', 'server', 'removed', 'removername', 'removerid', 'removalreason', 'datey', 'datem', 'dated');

/* Redirect for search on self */
if($_GET['q'] == 'searchme') {
    if(!USER_LOGGEDIN)
        Redirect('^search');
    else
        Redirect('^search&player64='.$GLOBALS['auth']->GetUser64().'#search-results');
}
$Criteria = array();
$Queries = array();
$Rebuild = false;
/* Check/Validate Input */
foreach($SearchTypes as $Type) {
    if(isset($_GET[$Type])) {
        switch($Type) {
            case 'playername':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Player_Name LIKE \'%'.$GLOBALS['sql']->Escape($_GET[$Type]).'%\'';
                break;
            case 'playerid':
                if(!$GLOBALS['steam']->ValidID($_GET[$Type])) {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Player_ID=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
            case 'playerip':
                if(!IsValidIP($_GET[$Type])) {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Player_IP=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
            case 'player64':
                $ID = $_GET[$Type];
                if(!$GLOBALS['steam']->Valid64($ID)) {
                    $Rebuild = true;
                    continue 2;
                }
                $SteamID = $GLOBALS['steam']->Steam64ToID($ID);
                $SteamIDNew = $GLOBALS['steam']->Steam64ToID($ID, true);
                if($SteamID === FALSE || $SteamIDNew == FALSE) {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = '(Punish_Player_ID=\''.$GLOBALS['sql']->Escape($SteamID).'\' OR Punish_Player_ID=\''.$GLOBALS['sql']->Escape($SteamIDNew).'\')';
                break;
            case 'adminname':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Admin_Name LIKE \'%'.$GLOBALS['sql']->Escape($_GET[$Type]).'%\'';
                break;
            case 'adminid':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Admin_ID=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
            case 'punishtype':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Type=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
            case 'authtype':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Auth_Type=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
            case 'reason':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Reason LIKE \'%'.$GLOBALS['sql']->Escape($_GET[$Type]).'%\'';
                break;
            case 'length':
                if((!CheckVar($_GET[$Type], SP_VAR_INT) && $_GET[$Type] != -1) || $_GET[$Type] < -1){
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Length=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
            case 'server':
                if(!CheckVar($_GET[$Type], SP_VAR_INT) || $_GET[$Type] < 0){
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'Punish_Server_ID=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
            case 'active':
                if(!CheckVar($_GET[$Type], SP_VAR_INT) || $_GET[$Type] > 1 || $_GET[$Type] < 0){
                    $Rebuild = true;
                    continue 2;
                }
                if($_GET[$Type] == 1)
                    $Queries[] = 'UnPunish=\'0\' AND Punish_Time+(Punish_Length*60) > '.time().'';
                else
                    $Queries[] = 'Punish_Time+(Punish_Length*60) < '.time().'';
                break;
            case 'removed':
                if(!CheckVar($_GET[$Type], SP_VAR_INT) || $_GET[$Type] > 1 || $_GET[$Type] < 0){
                    $Rebuild = true;
                    continue 2;
                }
                if($_GET[$Type] == 1)
                    $Queries[] = 'UnPunish=\'1\'';
                else
                    $Queries[] = 'UnPunish=\'0\'';
                break;
            case 'removername':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'UnPunish_Admin_Name LIKE \'%'.$GLOBALS['sql']->Escape($_GET[$Type]).'%\'';
                break;
            case 'removerid':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'UnPunish_Admin_ID=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
            case 'removalreason':
                if($_GET[$Type] == '') {
                    $Rebuild = true;
                    continue 2;
                }
                $Queries[] = 'UnPunish_Reason LIKE \'%'.$GLOBALS['sql']->Escape($_GET[$Type]).'%\'';
                break;
            case 'datey':
                if(!CheckVar($_GET[$Type], SP_VAR_INT) || $_GET[$Type] > (int)date('Y') || $_GET[$Type] < 0){
                    $Rebuild = true;
                    continue 2;
                }
                break;
            case 'datem':
                if(!CheckVar($_GET[$Type], SP_VAR_INT) || $_GET[$Type] > 12 || $_GET[$Type] < 0 || !isset($_GET['datey'])){
                    $Rebuild = true;
                    continue 2;
                }
                break;
            case 'dated':
                if(!CheckVar($_GET[$Type], SP_VAR_INT) || $_GET[$Type] > 31 || $_GET[$Type] < 0 || !isset($_GET['datem']) || !isset($_GET['datey'])){
                    $Rebuild = true;
                    continue 2;
                }
                break;
        }
        $Criteria[$Type] = $_GET[$Type];
    }
}

/* If searching a date, create the timestamps needed */
if(isset($Criteria['datey'])) {
    if(isset($Criteria['datem']) && isset($Criteria['dated'])) {
        $DateStart = mktime(0,0,0,$Criteria['datem'],$Criteria['dated'],$Criteria['datey']);
        $DateEnd = mktime(0,0,0,$Criteria['datem'],$Criteria['dated']+1,$Criteria['datey']);
    } else if(isset($Criteria['datem'])) {
        $DateStart = mktime(0,0,0,$Criteria['datem'],1,$Criteria['datey']);
        $DateEnd = mktime(0,0,0,$Criteria['datem']+1,0,$Criteria['datey']);
    } else {
        $DateStart = mktime(0,0,0,1,1,$Criteria['datey']);
        $DateEnd = mktime(0,0,0,1,1,$Criteria['datey']+1);
    }
    if($DateStart !== FALSE && $DateEnd !== FALSE)
        $Queries[] = '(Punish_Time <= '.(int)$DateEnd.' AND Punish_Time >= '.(int)$DateStart.')';
}

/* Do we need to redirect to correct invalid inputs? */
if($Rebuild) {
    if(!empty($Criteria))
        Redirect('^search&'.http_build_query($Criteria).'#search-results');
    else
        Redirect('^search');
}
unset($Rebuild);

/* Build the SQL 'WHERE' clause */
$QueryString = '';
if(!empty($Queries)) {
    foreach($Queries as $Key => $Query) {
        if($QueryString == '')
            $QueryString .= ' WHERE ';
        $QueryString .= $Query;
        if($Key < (count($Queries)-1))
            $QueryString .= ' AND ';
    }
}
unset($Queries);

/* Build the search form in a table */
/* Build server list */
$ServerListQuery = $GLOBALS['sql']->Query('SELECT Server_ID FROM '.SQL_SERVERS);
$Servers = array();
while($Row = $GLOBALS['sql']->FetchArray($ServerListQuery)) {
    $Server = SP_GetServerInfo($Row['Server_ID']);
    $Servers[$Server['mod']['name']][$Row['Server_ID']] = $Server['name'];
}
$GLOBALS['sql']->Free($ServerListQuery);
$ServerList = '<option'.(!isset($Criteria['server'])?' selected="selected"':'').' value="">-</option>';
foreach($Servers as $Mod => $List) {
    $ServerList .= '<optgroup label="'.SpecialChars($Mod).'">';
    foreach($List as $ID => $Server) {
        $ServerList .= '<option '.((isset($Criteria['server']) && $Criteria['server'] == $ID)?' selected="selected"':'').'value="'.$ID.'">'.SpecialChars($Server).'</option>';
    }
    $ServerList .= '</optgroup>';
}
unset($Servers);
/* Build date selectors */
$FirstYear = $GLOBALS['sql']->Query_FetchArray('SELECT YEAR(FROM_UNIXTIME(Punish_Time)) as Punish_Year FROM '.SQL_PUNISHMENTS.' ORDER BY Punish_Time ASC LIMIT 1');
$DateYList = '';
if($FirstYear['Punish_Year'] > 0) {
    $DateYList = '<option'.(!isset($Criteria['datey'])?' selected="selected"':'').' value="">-</option>';
    for($i = (int)date('Y', time()); $i >= $FirstYear['Punish_Year'];$i--) {
        $DateYList .= '<option '.((isset($Criteria['datey']) && $Criteria['datey'] == $i)?' selected="selected"':'').'value="'.$i.'">'.SpecialChars($i).'</option>';
    }
}
unset($FirstYear);
$DateMList = '<option'.(!isset($Criteria['datem'])?' selected="selected"':'').' value="">-</option>';
for($i = 1;$i <= 12;$i++) {
    $DateMList .= '<option '.((isset($Criteria['datem']) && $Criteria['datem'] == $i)?' selected="selected"':'').'value="'.$i.'">'.SpecialChars(date('F', mktime(0, 0, 0, $i, 10))).'</option>';
}
$DateDList = '<option'.(!isset($Criteria['dated'])?' selected="selected"':'').' value="">-</option>';
for($i = 1;$i <= 32;$i++) {
    $DateDList .= '<option '.((isset($Criteria['dated']) && $Criteria['dated'] == $i)?' selected="selected"':'').'value="'.$i.'">'.SpecialChars($i).'</option>';
}

/* Create main table */
$Table = array('class'=>'table-search',
'rows'=>array(
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1400]), array('content'=>'<input name="playername" type="text"'.(isset($Criteria['playername'])?' value="'.$Criteria['playername'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1401]), array('content'=>'<input name="playerid" type="text" placeholder="'.sprintf($GLOBALS['trans'][3008], 'STEAM_0:1:12345678').'"'.(isset($Criteria['playerid'])?' value="'.$Criteria['playerid'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1402]), array('content'=>'<input name="player64" type="text" placeholder="'.sprintf($GLOBALS['trans'][3008], '76561191234567890').'"'.(isset($Criteria['player64'])?' value="'.$Criteria['player64'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1403]), array('content'=>'<input name="adminname" type="text"'.(isset($Criteria['adminname'])?' value="'.$Criteria['adminname'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1404]), array('content'=>'<input name="adminid" type="text" placeholder="'.sprintf($GLOBALS['trans'][3008], 'STEAM_0:1:87654321').'"'.(isset($Criteria['adminid'])?' value="'.$Criteria['adminid'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1405]), array('content'=>'<input name="authtype" type="text" placeholder="'.sprintf($GLOBALS['trans'][3008], 'Steam').'"'.(isset($Criteria['authtype'])?' value="'.$Criteria['authtype'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1406]), array('content'=>'<input name="punishtype" type="text" placeholder="'.sprintf($GLOBALS['trans'][3008], 'Ban').'"'.(isset($Criteria['punishtype'])?' value="'.$Criteria['punishtype'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1407]), array('content'=>'<input name="reason" type="text"'.(isset($Criteria['reason'])?' value="'.$Criteria['reason'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1408].' ('.$GLOBALS['trans'][1153].')'), array('content'=>'<input name="length" type="text" placeholder="'.sprintf($GLOBALS['trans'][3008], '1880').'"'.(isset($Criteria['length'])?' value="'.$Criteria['length'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1409]), array('content'=>'<select name="active"><option value="">-</option><option value="1"'.((isset($Criteria['active']) && $Criteria['active'] == 1)?' selected="selected"':'').'>Yes</option><option value="0"'.((isset($Criteria['active']) && $Criteria['active'] == 0)?' selected="selected"':'').'>No</option></select>'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1411]), array('content'=>'<select name="server">'.$ServerList.'</select>'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1410]), array('content'=>'<select name="removed"><option value="">-</option><option value="1"'.((isset($Criteria['removed']) && $Criteria['removed'] == 1)?' selected="selected"':'').'>Yes</option><option value="0"'.((isset($Criteria['removed']) && $Criteria['removed'] == 0)?' selected="selected"':'').'>No</option></select>'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1418]), array('content'=>'<input name="removername" type="text"'.(isset($Criteria['removername'])?' value="'.$Criteria['removername'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1419]), array('content'=>'<input name="removerid" type="text" placeholder="'.sprintf($GLOBALS['trans'][3008], 'STEAM_0:1:12345678').'"'.(isset($Criteria['removerid'])?' value="'.$Criteria['removerid'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1420]), array('content'=>'<input name="removalreason" type="text"'.(isset($Criteria['removalreason'])?' value="'.$Criteria['removalreason'].'"':'').' />'))),
    array('cols'=>array(array('content'=>$GLOBALS['trans'][1412]), array('content'=>'<select name="dated" title="'.$GLOBALS['trans'][1413].'">'.$DateDList.'</select> / <select name="datem" title="'.$GLOBALS['trans'][1414].'">'.$DateMList.'</select> / '.(($DateYList != '')?'<select name="datey" title="'.$GLOBALS['trans'][1415].'">'.$DateYList.'</select>':'<input class="small" name="datey" maxlength="4" type="text" placeholder="'.$GLOBALS['trans'][1417].'" title="'.$GLOBALS['trans'][1415].'"'.(isset($Criteria['datey'])?' value="'.$Criteria['datey'].'"':'').' />')))),
    array('cols'=>array(array('content'=>''), array('content'=>'<input value="Search..." type="submit" />')))
));

/* Create the form */
$Form = '<form name="search-punish" id="form-search" action="'.ParseURL('^search').'#search-results" method="get">';
$Form .= '<input name="q" type="hidden" value="search" />';
$Form .= $GLOBALS['theme']->BuildTable($Table);
$Form .= '</form>';

/* Add the finished form to the page */
$GLOBALS['theme']->AddContent('Search Form', $Form, '', 'search-form');

/* Get punishments matching criteria */
if($QueryString != '') {
    /* Check page number if it exists */
    if(isset($_GET['p']) && CheckVar($_GET['p'], SP_VAR_INT))
        $CurrentPage = intval($GLOBALS['sql']->Escape($_GET['p']));
    else {
        if(isset($_GET['p']))
            Redirect('^search');
        else
            $CurrentPage = 1;
    }
    $PerPage = 40;
    if(isset($GLOBALS['settings']['punish_perpage']) && CheckVar($GLOBALS['settings']['punish_perpage'], SP_VAR_INT))
        $PagePage = (int)$GLOBALS['settings']['punish_perpage'];
    $TotalPages = $GLOBALS['sql']->Query_FetchArray('SELECT count(*) AS prows FROM '.SQL_PUNISHMENTS.$QueryString);
    $TotalPages = ceil((int)$TotalPages['prows']/$PerPage);
    
    if($CurrentPage > $TotalPages) {
        $GLOBALS['theme']->AddContent('Search Results', '<div class="message error">'.$GLOBALS['trans'][2011].'</div>', '', 'search-results');
    } else {
        $PaginateLimit = intval(($CurrentPage - 1) * $PerPage);

        /* Fetch punishments matching criteria */ 
        $PunishQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PUNISHMENTS.$QueryString.' ORDER BY Punish_Time DESC LIMIT '.$PaginateLimit.', '.$PerPage);
        $Rows = array();
        while($PunishRow = $GLOBALS['sql']->FetchArray($PunishQuery)) {
            $Rows[] = $PunishRow;
        }
        $GLOBALS['sql']->Free($PunishQuery);

        /* Check if any punishments matching the criteria exist */
        if(count($Rows) == 0) {
            $Content = '<div class="message error">'.$GLOBALS['trans'][2011].'</div>';
        } else {
            $Content = SP_BuildPunishTable($Rows);
        }
        unset($Rows);

        /* Add main content to page */
        $GLOBALS['theme']->AddContent(ucwords(sprintf($GLOBALS['trans'][1157], number_format($CurrentPage), number_format($TotalPages))), $Content, '', 'search-results');
        /* Add pagination links to page */
        $GLOBALS['theme']->AddContent('', $GLOBALS['theme']->Paginate($TotalPages, $CurrentPage, ParseURL('^search&'.http_build_query($Criteria)).'&p='));
    }
}
?>