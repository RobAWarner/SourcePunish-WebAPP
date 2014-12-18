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

/* TODO
    - Config for server cache time?
    - Move special chars to core?
    - Remove print code
*/

/* Set content type */
header('Content-Type: application/json');

/* Load & Initiate Cache Class */
if(!isset($GLOBALS['cache'])) {
    require_once(DIR_INCLUDE.'class.cache.php');
    $GLOBALS['cache'] = new Cache(DIR_CACHE);
    PrintDebug('Cache Class Loaded');
}

$ServerCache = 'serverlist';
$ServerCacheTime = 120;

if(isset($GLOBALS['settings']['server_cachetime']) && IsNum(ceil((int)$GLOBALS['settings']['server_cachetime'])) && ceil((int)$GLOBALS['settings']['server_cachetime']) >= 0)
    $ServerCacheTime = ceil((int)$GLOBALS['settings']['server_cachetime']);

$Refresh = true;

if($GLOBALS['cache']->Valid($ServerCache, $ServerCacheTime)) {
    $ServerList = $GLOBALS['cache']->Read($ServerCache, false);
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
            $IP = $Row['Server_IP'];
            $Port = 27015;
            /* Get port number */
            if(strpos($IP, ':') !== false) {
                $IPTMP = explode(':', $IP, 2);
                $IP = $IPTMP[0];
                if(!IsNum((int)$IPTMP[1]))
                    continue;
                $Port = (int)$IPTMP[1];
            }
            $ServerList[$Row['Server_ID']] = array('ip'=>$IP, 'port'=>$Port);
        }
    }
    $GLOBALS['sql']->Free($ServerListQuery);

    require_once(DIR_INCLUDE.'class.serverquery.php');
    $ServerQuery = new ServerQuery();

    function SpecialChars($Input) {
        $Return = '';
        if(is_array($Input)) {
            foreach($Input as $Key => $Value) {
                $Return[$Key] = SpecialChars($Value);
            }
        } else
            $Return = htmlspecialchars($Input);
        return $Return;
    }

    $AjaxList = array('success'=>true, 'servers'=>array());
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
    $Written = $GLOBALS['cache']->Write($ServerCache, $Json, false);
    if(isset($_GET['pretty']))
        die(print('<pre>'.print_r($AjaxList, true).'</pre>'));
    else
        die($Json);
}
die('{"success":false}');
?>