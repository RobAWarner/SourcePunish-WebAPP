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
    - GeoIP?
    - Search by server
    - Stats?
    - Total players row?
    - Update server host as fetched by ajax?
*/

/* Check if an ID variable is set and valid */
if(isset($_GET['id']) && $_GET['id'] != '' && IsNum($_GET['id']) && $_GET['id'] > 0) {
    $ID = intval($GLOBALS['sql']->Escape($_GET['id']));
    $ServerInfo = SP_GetServerInfo($ID, false);
    if($ServerInfo === false)
        Redirect('^servers');
    else {
        $GLOBALS['theme']->AddTitle($GLOBALS['trans'][1007]);
        $GLOBALS['theme']->AddTitle($ServerInfo['name']);
        $GLOBALS['theme']->AddScript(HTML_SCRIPTS.'serverplayers.js');
        $GLOBALS['theme']->AddContent(substr($ServerInfo['name'], 0, 60).' - '.$GLOBALS['trans'][1210], '<div id="player-list-container" data-sid="'.$ID.'"></div>');
    }
} else {   
    $GLOBALS['theme']->AddTitle($GLOBALS['trans'][1007]);
    $GLOBALS['theme']->AddScript(HTML_SCRIPTS.'serverinfo.js');

    /* Server List */
    $ServerListQuery = $GLOBALS['sql']->Query('SELECT Server_ID from '.SQL_PREFIX.'servers ORDER BY Server_Mod ASC');
    $Servers = array();
    while($Row = $GLOBALS['sql']->FetchArray($ServerListQuery)) {
        $Server = SP_GetServerInfo($Row['Server_ID']);
        $Servers[$Row['Server_ID']] = $Server;
    }
    $GLOBALS['sql']->Free($ServerListQuery);

    $ServerTable = array('headings'=>array(), 'rows'=>array(), 'class'=>'table-servers table-servers-update');
    $ServerTable['headings'] = array(
        array('content'=>$GLOBALS['trans'][1206], 'class'=>'col-loc'),
        array('content'=>$GLOBALS['trans'][1205], 'class'=>'col-vac'),
        array('content'=>$GLOBALS['trans'][1200], 'class'=>'col-mod'),
        array('content'=>$GLOBALS['trans'][1202], 'class'=>'col-name'),
        array('content'=>$GLOBALS['trans'][1201], 'class'=>'col-address'),
        array('content'=>$GLOBALS['trans'][1203], 'class'=>'col-players'),
        array('content'=>$GLOBALS['trans'][1204], 'class'=>'col-map'),
    );
    foreach($Servers as $ID=>$Server) {
        $IP = SP_GetAddressFromString($Server['ip']);
        $GeoIP = SP_GeoIPCountry($IP['address']);
        $ServerTable['rows'][] = array('custom'=>'data-sid="'.$ID.'"', 'cols'=>array(array('content'=>($GeoIP!==false?'<img alt="'.$GeoIP['country_code'].'" title="'.$GeoIP['country'].'" src="'.$GeoIP['country_flag'].'" />':'')), array('content'=>'<span class="s-info-vac"><img alt="VAC" title="Valve Anti-Cheat Secure" src="'.HTML_IMAGES.'vac.png" /></span>'), array('content'=>'<span class="s-info-mod"><img alt="'.$Server['mod']['short'].'" title="'.$Server['mod']['name'].'" src="'.HTML_IMAGES_GAMES.$Server['mod']['image'].'" /></span>'), array('content'=>'<span class="s-info-name">'.htmlspecialchars($Server['name']).'</span>'), array('content'=>'<a href="steam://connect/'.$Server['host'].'" title="'.$GLOBALS['trans'][1208].'">'.htmlspecialchars($Server['host']).'</a>'), array('content'=>'<span class="s-info-players">-</span>'), array('content'=>'<span class="s-info-map">-</span>')));
    }
    unset($Servers);
    $ServerTable = $GLOBALS['theme']->BuildTable($ServerTable);
    $GLOBALS['theme']->AddContent($GLOBALS['trans'][1209], $ServerTable);
}
?>