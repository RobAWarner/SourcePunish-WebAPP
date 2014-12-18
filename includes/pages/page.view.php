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
    - GeoIP?
    - Search Player
    - Stats?
*/


$GLOBALS['theme']->AddTitle('Punishment Information');

/* Check that the ID variable is set and valid */
if(isset($_GET['id']) && $_GET['id'] != '' && IsNum($_GET['id']) && $_GET['id'] > 0)
    $ID = intval($GLOBALS['sql']->Escape($_GET['id']));
else 
    Redirect();

/* Fetch punishment record from DB */
$PunishmentQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PUNISHMENTS.' WHERE Punish_ID=\''.$ID.'\' LIMIT 1');

/* Check there was a matching record */
if($GLOBALS['sql']->Rows($PunishmentQuery) != 1)
    Redirect();

/* Store the record in an array and free the result */
$Punishment = $GLOBALS['sql']->FetchArray($PunishmentQuery);
unset($Punishment['Punish_ID']);
$GLOBALS['sql']->Free($PunishmentQuery);

/* Action Buttons */
$Buttons = '';
if(USER_LOGGEDIN) {
    if($GLOBALS['auth']->GetUserID() == $Punishment['Punish_Player_ID'])
        $Buttons .= '<a href="'.ParseURL('^appeal&id='.$ID).'" title="'.$GLOBALS['trans'][1163].'">'.$GLOBALS['trans'][1136].'</a>';
    if(USER_ADMIN) {
        if($Buttons != '')
            $Buttons .= ' | ';
        $Buttons .= '<a href="'.ParseURL('^admin&a=edit&id='.$ID).'" title="'.$GLOBALS['trans'][1164].'">'.$GLOBALS['trans'][1137].'</a>';
    }
}
if($Buttons != '')
    $Buttons = '<span class="action-buttons">'.$Buttons.'</span>';

/* Create table for player information */
$PlayerInfo = array('class'=>'table-view table-view-player', 'rows'=>array());
$PlayerInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1123]), array('content'=>htmlspecialchars($Punishment['Punish_Player_Name'])))); unset($Punishment['Punish_Player_Name']);
$PlayerInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1124]), array('content'=>htmlspecialchars($Punishment['Punish_Player_ID'])))); unset($Punishment['Punish_Player_Name']);
/* Check if Punish_Player_ID is a valid SteamID and create a profile link */
if($GLOBALS['steam']->ValidID($Punishment['Punish_Player_ID'])) {
    if(IS32BIT)
        $ProfileID = $GLOBALS['steam']->SteamIDTo64($Punishment['Punish_Player_ID']);
    else
        $ProfileID = $GLOBALS['steam']->SteamIDTo64($Punishment['Punish_Player_ID']);
    if($ProfileID !== false)
        $PlayerInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1127]), array('content'=>'<a href="http://steamcommunity.com/profiles/'.$ProfileID.'" title="'.sprintf($GLOBALS['trans'][3002], 'http://steamcommunity.com/profiles/'.$ProfileID).'" target="_blank">http://steamcommunity.com/profiles/'.$ProfileID.'</a>')));  
    unset($ProfileID);
}
unset($Punishment['Punish_Player_ID']);
/* Should we show IP information? */
if(isset($GLOBALS['settings']['punish_showips_all']) && isset($GLOBALS['settings']['punish_showips_flags'])) {
    if($GLOBALS['settings']['punish_showips_all'] == true || (($GLOBALS['settings']['punish_showips_flags'] == '*' || $GLOBALS['auth']->HasAdminFlag($GLOBALS['settings']['punish_showips_flags'])) && USER_ADMIN)) {
        if($Punishment['Punish_Player_IP'] != '')
            $PlayerInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1126]), array('content'=>htmlspecialchars($Punishment['Punish_Player_IP']))));
    }
}
unset($Punishment['Punish_Player_IP']);
/* A few statistics on player punishments */

/* Build player information table and add it to the page */
$PlayerInfoTable = $GLOBALS['theme']->BuildTable($PlayerInfo);
unset($PlayerInfo);
$GLOBALS['theme']->AddContent($GLOBALS['trans'][1120].$Buttons, $PlayerInfoTable);
unset($PlayerInfoTable);

/* Create table for punishment information */
$PunishInfo = array('class'=>'table-view table-view-server', 'rows'=>array());
$PunishInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1125]), array('content'=>htmlspecialchars(date(DATE_FORMAT, $Punishment['Punish_Time']).sprintf(ParseText(' (#TRANS_3003)'), ucwords(PrintTimeDiff(TimeDiff(time()-$Punishment['Punish_Time']), -1)))))));
$PunishInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1103]), array('content'=>htmlspecialchars(ucwords($Punishment['Punish_Type'])))));
$PunishInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1104]), array('content'=>htmlspecialchars($Punishment['Punish_Reason'])))); unset($Punishment['Punish_Reason']);
/* Work out punishment status */
if($Punishment['UnPunish'] == 1) {
    $Status = $GLOBALS['trans'][1140];
    $Class = 'removed';
} else if(($Punishment['Punish_Time']+($Punishment['Punish_Length']*60) < time()) && $Punishment['Punish_Length'] > 0) {
    $Status = $GLOBALS['trans'][1139];
    $Class = 'expired';
} else if(($Punishment['Punish_Length'] == 0 && $Punishment['Punish_Type'] == 'kick') || $Punishment['Punish_Length'] == -1) {
    $Status = '';
    $Class = 'notapplicable';
} else if($Punishment['Punish_Length'] == 0) {
    $Status = '';
    $Class = 'permanent';
} else {
    $Status = $GLOBALS['trans'][1158];
    $Class = 'active';
}
$PunishInfo['rows'][] = array('class'=>$Class, 'cols'=>array(array('content'=>$GLOBALS['trans'][1105]), array('content'=>htmlspecialchars(PunishTime(($Punishment['Punish_Type']=='kick')?-1:$Punishment['Punish_Length'], 0).($Status!=''?' ('.$Status.')':'')))));
unset($Punishment['Punish_Type'], $Status, $Class);
/* Work out expiry date */
if($Punishment['Punish_Length'] > 0)
    $PunishInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1130]), array('content'=>htmlspecialchars(date(DATE_FORMAT, ($Punishment['Punish_Time']+($Punishment['Punish_Length']*60))))))); unset($Punishment['Punish_Length'], $Punishment['Punish_Time']);
unset($Punishment['Punish_Length'], $Punishment['Punish_Time']);
/* Get server info */
$ServerInfo = GetServerInfo($Punishment['Punish_Server_ID']); unset($Punishment['Punish_Server_ID']);
$PunishInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1101]), array('content'=>htmlspecialchars($ServerInfo['name']).'&nbsp;&nbsp;<img alt="'.$ServerInfo['mod']['short'].'" title="'.$ServerInfo['mod']['name'].'" src="'.HTML_IMAGES_GAMES.$ServerInfo['mod']['image'].'" />')));
/* Work out what servers/mods is the punishment applicable on */
if($Punishment['Punish_All_Mods'] == 1)
    $Applicable = $GLOBALS['trans'][1162];
else if($Punishment['Punish_All_Servers'] == 1)
    $Applicable = sprintf($GLOBALS['trans'][1161], $ServerInfo['mod']['name']);
else
    $Applicable = $GLOBALS['trans'][1160];
unset($ServerInfo);
$PunishInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1159]), array('content'=>htmlspecialchars($Applicable))));
unset($Applicable, $Punishment['Punish_All_Mods'], $Punishment['Punish_All_Servers']);
$PunishInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1129]), array('content'=>htmlspecialchars(ucwords($Punishment['Punish_Auth_Type']))))); unset($Punishment['Punish_Auth_Type']);

/* Build punishment information table and add it to the page */
$PunishInfoTable = $GLOBALS['theme']->BuildTable($PunishInfo);
unset($PunishInfo);
$GLOBALS['theme']->AddContent($GLOBALS['trans'][1121], $PunishInfoTable);
unset($PunishInfoTable);

/* Create table for admin information */
$AdminInfo = array('class'=>'table-view table-view-admin', 'rows'=>array());
$AdminInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1123]), array('content'=>htmlspecialchars($Punishment['Punish_Admin_Name'])))); unset($Punishment['Punish_Admin_Name']);
$AdminInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1124]), array('content'=>htmlspecialchars($Punishment['Punish_Admin_ID'])))); unset($Punishment['Punish_Admin_ID']);
/* Build admin information table and add it to the page */
$AdminInfoTable = $GLOBALS['theme']->BuildTable($AdminInfo);
unset($AdminInfo);
$GLOBALS['theme']->AddContent($GLOBALS['trans'][1122], $AdminInfoTable);
unset($AdminInfoTable);

/* Create table for removal information */
if($Punishment['UnPunish'] == 1) {
    $RemoverInfo = array('class'=>'table-view table-view-removal', 'rows'=>array());
    $RemoverInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1125]), array('content'=>htmlspecialchars(date(DATE_FORMAT, $Punishment['UnPunish_Time']).sprintf(ParseText(' (#TRANS_3003)'), ucwords(PrintTimeDiff(TimeDiff(time()-$Punishment['UnPunish_Time']), -1))))))); unset($Punishment['UnPunish_Time']);
    $RemoverInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1104]), array('content'=>htmlspecialchars($Punishment['UnPunish_Reason'])))); unset($Punishment['UnPunish_Reason']);
    $RemoverInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1123]), array('content'=>htmlspecialchars($Punishment['UnPunish_Admin_Name'])))); unset($Punishment['UnPunish_Admin_Name']);
    $RemoverInfo['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1124]), array('content'=>htmlspecialchars($Punishment['UnPunish_Admin_ID'])))); unset($Punishment['UnPunish_Admin_ID']);
    /* Build removal information table and add it to the page */
    $RemoverInfoTable = $GLOBALS['theme']->BuildTable($RemoverInfo);
    unset($RemoverInfo);
    $GLOBALS['theme']->AddContent($GLOBALS['trans'][1131], $RemoverInfoTable);
    unset($RemoverInfoTable);
}
unset($Punishment);
?>