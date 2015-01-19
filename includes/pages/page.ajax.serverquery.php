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

/* TODO
    - Config for server cache time?
    - Move special chars to core?
    - Remove print code
*/

/* Set content type */
header('Content-Type: application/json');

$QueryTypes = array('serverlist', 'playerlist', 'rules');

$Type = 'serverlist';
if(isset($_GET['type']) && $_GET['type'] != '') {
    if(in_array($_GET['type'], $QueryTypes))
        $Type = $_GET['type'];
}

/* Load & Initiate Cache Class */
if(!isset($GLOBALS['cache'])) {
    require_once(DIR_INCLUDE.'class.cache.php');
    $GLOBALS['cache'] = new Cache(DIR_CACHE);
    PrintDebug('Cache Class Loaded');
}

if($Type == 'serverlist') {
    $ServerCacheTime = 120;
    if(isset($GLOBALS['settings']['server_cachetime']) && intval($GLOBALS['settings']['server_cachetime']) >= 0)
        $ServerCacheTime = intval($GLOBALS['settings']['server_cachetime']);
    $Refresh = true; //
    if($GLOBALS['cache']->Valid('serverlist', $ServerCacheTime)) {
        $ServerList = $GLOBALS['cache']->Read('serverlist', false);
        if($ServerList !== false) {
            $Refresh = false;
            die($ServerList);
        }
    }
    if($Refresh) {
        $ServerListQuery = $GLOBALS['sql']->Query('SELECT Server_ID, Server_IP FROM '.SQL_PREFIX.'servers ORDER BY Server_Mod');
        $ServerList = array();
        if($GLOBALS['sql']->Rows($ServerListQuery) > 0) {
            while($Row = $GLOBALS['sql']->FetchArray($ServerListQuery)) {
                $IP = SP_GetAddressFromString($Row['Server_IP']);
                $ServerList[$Row['Server_ID']] = array('ip'=>$IP['address'], 'port'=>$IP['port']);
            }
        }
        $GLOBALS['sql']->Free($ServerListQuery);

        require_once(DIR_INCLUDE.'class.serverquery.php');
        $ServerQuery = new ServerQuery();

        $AjaxList = array('success'=>true, 'time'=>time(), 'servers'=>array());
        foreach($ServerList as $ID => $Info) {
            $ServerQuery->Connect($Info['ip'], $Info['port']);
            $Server = $ServerQuery->GetServerInfo();
            if($Server !== false && !empty($Server)) {
                foreach($Server as $Key => $Value) {
                    $Server[$Key] = SpecialChars($Value);
                }
                $AjaxList['servers'][$ID] = $Server;
            }
            $ServerQuery->Disconnect();
        }
        unset($ServerList);

        $Json = $GLOBALS['cache']->Encode($AjaxList);
        if($Json === false)
            die('{"success":false}');
        $GLOBALS['cache']->Write('serverlist', $Json, false);
        die($Json);
    }
} else if($Type = 'playerlist') {
    if(isset($_GET['id']) && $_GET['id'] != '' && IsNum($_GET['id']) && $_GET['id'] > 0) {
        $ID = intval($GLOBALS['sql']->Escape($_GET['id']));
        $ServerInfo = SP_GetServerInfo($ID, false);
        if($ServerInfo !== false) {
            require_once(DIR_INCLUDE.'class.serverquery.php');
            $ServerQuery = new ServerQuery();

            $IP = SP_GetAddressFromString($ServerInfo['ip']);
            if(empty($IP['address']))
                die('{"success":false}');
            $ServerQuery->Connect($IP['address'], $IP['port']);
            $GetPlayers = $ServerQuery->GetPlayers();
            if($GetPlayers === false)
                die('{"success":false}');
            $AjaxList = array('success'=>true, 'time'=>time(), 'players'=>'');
            $Table = array('headings'=>array(), 'rows'=>array(), 'class'=>'table-server-players');
            $Table['headings'] = array(
                array('content'=>$GLOBALS['trans'][1211], 'class'=>'col-name'),
                array('content'=>$GLOBALS['trans'][1212], 'class'=>'col-score'),
                array('content'=>$GLOBALS['trans'][1213], 'class'=>'col-time')
            );
            foreach($GetPlayers as $Player) {
                $Table['rows'][] = array('cols'=>array(array('content'=>SpecialChars($Player['name'])), array('content'=>SpecialChars($Player['score'])), array('content'=>SpecialChars(SP_PrintTimeDiff(SP_TimeDiff($Player['time']))))));
            }
            $AjaxList['players'] = $GLOBALS['theme']->BuildTable($Table);
            $Json = $GLOBALS['cache']->Encode($AjaxList);
            if($Json === false)
                die('{"success":false}');
            die($Json);
        }
    }  
}
die('{"success":false}');
?>