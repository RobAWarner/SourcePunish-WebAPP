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

$SearchTypes = array('playername', 'playerid', 'playerip', 'player64', 'adminname', 'adminid', 'punishtype', 'authtype', 'reason', 'length', 'active', 'server', 'removed', 'datey', 'datem', 'dated');

if($_GET['q'] == 'searchme') {
    if(!USER_LOGGEDIN)
        Redirect('^search');
    else
        Redirect('^search&player64='.USER_AUTH);
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
                    if(!$GLOBALS['steam']->Valid64($_GET[$Type])) {
                        $Rebuild = true;
                        continue 2;
                    }
                    $SteamID = $GLOBALS['steam']->Steam64ToID($_GET[$Type]);
                    if($SteamID === FALSE) {
                        $Rebuild = true;
                        continue 2;
                    }
                    $Queries[] = 'Punish_Player_ID=\''.$GLOBALS['sql']->Escape($SteamID).'\'';
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
                    if((!IsNum($_GET[$Type]) && $_GET[$Type] != -1) || $_GET[$Type] < -1){
                        $Rebuild = true;
                        continue 2;
                    }
                    $Queries[] = 'Punish_Length=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
                case 'server':
                    if(!IsNum($_GET[$Type]) || $_GET[$Type] < 0){
                        $Rebuild = true;
                        continue 2;
                    }
                    $Queries[] = 'Punish_Server_ID=\''.$GLOBALS['sql']->Escape($_GET[$Type]).'\'';
                break;
                case 'active':
                    if(!IsNum($_GET[$Type]) || $_GET[$Type] > 1 || $_GET[$Type] < 0){
                        $Rebuild = true;
                        continue 2;
                    }
                    $Queries[] = 'UnPunish=\'0\' AND Punish_Time+(Punish_Length*60) > '.time().'';
                break;
                case 'removed':
                    if(!IsNum($_GET[$Type]) || $_GET[$Type] > 1 || $_GET[$Type] < 0){
                        $Rebuild = true;
                        continue 2;
                    }
                    if($_GET[$Type] == 1)
                        $Queries[] = 'UnPunish=\'1\'';
                    else
                        $Queries[] = 'UnPunish=\'0\'';
                break;
                case 'datey':
                    if(!IsNum($_GET[$Type]) || $_GET[$Type] > (int)date('Y') || $_GET[$Type] < 0){
                        $Rebuild = true;
                        continue 2;
                    }
                break;
                case 'datem':
                    if(!IsNum($_GET[$Type]) || $_GET[$Type] > 12 || $_GET[$Type] < 0 || !isset($_GET['dated']) || !isset($_GET['datey'])){
                        $Rebuild = true;
                        continue 2;
                    }
                break;
                case 'dated':
                    if(!IsNum($_GET[$Type]) || $_GET[$Type] > 31 || $_GET[$Type] < 0 || !isset($_GET['datem']) || !isset($_GET['datey'])){
                        $Rebuild = true;
                        continue 2;
                    }
                break;
            }
            $Criteria[$Type] = $_GET[$Type];
        }
    }
    if(isset($Criteria['datey'])) {
        if(isset($Criteria['datem']) && isset($Criteria['dated'])) {
            $DateStart = mktime(0,0,0,$Criteria['datem'],$Criteria['dated'],$Criteria['datey']);
            $DateEnd = mktime(0,0,0,$Criteria['datem'],$Criteria['dated']+1,$Criteria['datey']);
        } else {
            $DateStart = mktime(0,0,0,0,0,$Criteria['datey']);
            $DateEnd = mktime(0,0,0,0,0,$Criteria['datey']+1);
        }
        if($DateStart !== FALSE && $DateEnd !== FALSE)
            $Queries[] = '(Punish_Time < '.$DateEnd.' AND Punish_Time > '.$DateStart.')';
    }
    if($Rebuild) {
        if(!empty($Criteria))
            Redirect('^search&'.http_build_query($Criteria));
        else
            Redirect('^search');
    }
    unset($Rebuild);
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
    //die(print('<pre>'.print_r($QueryString, true).'</pre>'));

/* Build Form */
$Form = '<form name="search-punish" id="form-search" action="'.ParseURL('^search').'" method="get">';
    $Form .= '<input name="q" type="hidden" value="search" />';
    $Form .= 'Player Name: <input name="playername" type="text" /><br />';
    $Form .= 'Player ID: <input name="playerid" type="text" /><br />';
    $Form .= 'Player Steam 64: <input name="player64" type="text" /><br />';
    $Form .= 'Admin Name: <input name="adminname" type="text" /><br />';
    $Form .= 'Admin ID: <input name="adminid" type="text" /><br />';
    $Form .= 'Auth Type: <input name="authtype" type="text" /><br />';
    $Form .= 'Punish Type: <input name="punishtype" type="text" /><br />';
    $Form .= 'Punish Reason: <input name="reason" type="text" /><br />';
    $Form .= 'Punish Length: <input name="length" type="text" /><br />';
    $Form .= 'Punish Active: <input name="active" type="text" /><br />';
    $Form .= 'Punish Removed: <input name="removed" type="text" /><br />';
    $Form .= 'Server ID: <input name="server" type="text" /><br />';
    $Form .= 'Date: DD<input name="dated" type="text" />/MM<input name="datem" type="text" />/YYYY<input name="datey" type="text" /><br />';
    $Form .= '<br /><input value="Search..." type="submit" />';
$Form .= '</form>';
$GLOBALS['theme']->AddContent('Search Form', $Form);
/* Get punishments matching criteria */
if($QueryString != '') {
    if(isset($_GET['p']) && $_GET['p'] != '' && filter_var($_GET['p'], FILTER_VALIDATE_INT) !== false && $_GET['p'] > 0)
        $CurrentPage = intval($GLOBALS['sql']->Escape($_GET['p']));
    else {
        if(isset($_GET['p']))
            Redirect('^search');
        else
            $CurrentPage = 1;
    }
    $PerPage = 40;
    $TotalPages = $GLOBALS['sql']->Query_FetchArray('SELECT count(*) AS prows FROM '.SQL_PUNISHMENTS.$QueryString);
    $TotalPages = ceil((int)$TotalPages['prows']/$PerPage);
    
    if($CurrentPage > $TotalPages) {
        $GLOBALS['theme']->AddContent('Search Results', ParseText('#TRANS_2011'));
    } else {
        //Redirect('^search');
        $Paginate_Limit = intval(($CurrentPage - 1) * $PerPage);

        $PunishQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PUNISHMENTS.$QueryString.' ORDER BY Punish_Time DESC LIMIT '.$Paginate_Limit.', '.$PerPage);
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
        $GLOBALS['theme']->AddContent('', $GLOBALS['theme']->Paginate($TotalPages, $CurrentPage, ParseURL('^search&'.http_build_query($Criteria)).'&p='));
    }
}
?>