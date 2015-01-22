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
+---------------------------------------------------------+
| This product includes GeoLite data created by MaxMind   |
| available from http://www.maxmind.com                   |
+--------------------------------------------------------*/
if(!defined('IN_SP')) die('Access Denied!');

/* TODO
    - Search Player and/or all info? E.G. seach punish type, length etc
    - Stats?
    - Authorisation for unpunish and remove and edit?
    - Obfuscate ID if auth type is IP?
*/

if(isset($_GET['a'])) {
    if($_GET['a'] == 'edit' && !USER_ADMIN)
        Redirect();
}

/* Add page title */
$GLOBALS['theme']->AddTitle($GLOBALS['trans'][1121]);

/* Check that the ID variable is set and valid */
if(isset($_GET['id']) && CheckVar($_GET['id'], SP_VAR_INT))
    $PunishID = $GLOBALS['sql']->Escape($_GET['id']);
else 
    Redirect();

/* Fetch punishment record from DB */
$PunishmentQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PUNISHMENTS.' WHERE Punish_ID=\''.$PunishID.'\' LIMIT 1');

/* Check there was a matching record */
if($GLOBALS['sql']->Rows($PunishmentQuery) != 1)
    Redirect();

/* Store the record in an array and free the result */
$Punishment = $GLOBALS['sql']->FetchArray($PunishmentQuery);
$GLOBALS['sql']->Free($PunishmentQuery);

/* Add formatted data */
    /* Remove unneeded data */
    unset($Punishment['Punish_ID']);
    if($Punishment['UnPunish'] == 0)
        unset($Punishment['UnPunish_Admin_Name'], $Punishment['UnPunish_Admin_ID'], $Punishment['UnPunish_Time'], $Punishment['UnPunish_Reason']);
        
    /* Fix for legacy issue */
    if($Punishment['Punish_Type'] == 'kick')
        $Punishment['Punish_Length'] = -1;

    /* Work out punishment status */
    if($Punishment['UnPunish'] == 1) {
        $Punishment['Punish_Status_Extra'] = ucfirst($GLOBALS['trans'][1140]);
        $Punishment['Punish_Status'] = 'removed';
    } else if(($Punishment['Punish_Time']+($Punishment['Punish_Length']*60) < time()) && $Punishment['Punish_Length'] > 0) {
        $Punishment['Punish_Status_Extra'] = ucfirst($GLOBALS['trans'][1139]);
        $Punishment['Punish_Status'] = 'expired';
    } else if(($Punishment['Punish_Length'] == 0 && $Punishment['Punish_Type'] == 'kick') || $Punishment['Punish_Length'] == -1) {
        $Punishment['Punish_Status'] = 'notapplicable';
    } else if($Punishment['Punish_Length'] == 0) {
        $Punishment['Punish_Status'] = 'permanent';
    } else {
        $Punishment['Punish_Status_Extra'] = ucfirst($GLOBALS['trans'][1158]);
        $Punishment['Punish_Status'] = 'active';
    }
    
    /* Format punishment length */
    $Punishment['Punish_Length_Formatted'] = SP_LengthString($Punishment['Punish_Length'], 0);
    
    /* Format punishment time */
    $Punishment['Punish_Time_Formatted'] = date(DATE_FORMAT, $Punishment['Punish_Time']).sprintf(' ('.$GLOBALS['trans'][3003].')', SP_PrintTimeDiff(SP_TimeDiff(time()-$Punishment['Punish_Time']), -1));
    
    /* Format unpunish time */
    if($Punishment['UnPunish'] == 1)
        $Punishment['UnPunish_Time_Formatted'] = date(DATE_FORMAT, $Punishment['UnPunish_Time']).sprintf(' ('.$GLOBALS['trans'][3003].')', SP_PrintTimeDiff(SP_TimeDiff(time()-$Punishment['UnPunish_Time']), -1));

    /* Format expiry date */
    if($Punishment['Punish_Length'] > 0) {
        $Punishment['Punish_Expiry_Formatted'] = date(DATE_FORMAT, ($Punishment['Punish_Time']+($Punishment['Punish_Length']*60)));
        if($Punishment['Punish_Status'] == 'active')
            $Punishment['Punish_Expiry_Formatted'] .= sprintf(' ('.$GLOBALS['trans'][3012].')', SP_PrintTimeDiff(SP_TimeDiff(($Punishment['Punish_Time'] + ($Punishment['Punish_Length']*60))-time()), -1));
    }

    /* Convert Player ID if it's a SteamID */
    if(isset($Punishment['Punish_Player_ID']) && $GLOBALS['steam']->ValidID($Punishment['Punish_Player_ID'])) {
        $Punishment['Punish_Player_SteamID64'] = $GLOBALS['steam']->SteamIDTo64($Punishment['Punish_Player_ID']);
        if($Punishment['Punish_Player_SteamID64'] === false)
            unset($Punishment['Punish_Player_SteamID64']);
        else {
            $Punishment['Punish_Player_SteamID'] = $GLOBALS['steam']->Steam64ToID($Punishment['Punish_Player_SteamID64'], false);
            if($Punishment['Punish_Player_SteamID'] === false)
                unset($Punishment['Punish_Player_SteamID']);
            $Punishment['Punish_Player_SteamID3'] = $GLOBALS['steam']->Steam64ToID($Punishment['Punish_Player_SteamID64'], true);
            if($Punishment['Punish_Player_SteamID3'] === false)
                unset($Punishment['Punish_Player_SteamID3']);
        }
    }

    /* Convert Admin ID if it's a SteamID */
    if(isset($Punishment['Punish_Admin_ID']) && $GLOBALS['steam']->ValidID($Punishment['Punish_Admin_ID'])) {
        $Punishment['Punish_Admin_SteamID64'] = $GLOBALS['steam']->SteamIDTo64($Punishment['Punish_Admin_ID']);
        if($Punishment['Punish_Admin_SteamID64'] === false)
            unset($Punishment['Punish_Admin_SteamID64']);
    }

    /* Convert Remover ID if it's a SteamID */
    if(isset($Punishment['UnPunish_Admin_ID']) && $GLOBALS['steam']->ValidID($Punishment['UnPunish_Admin_ID'])) {
        $Punishment['UnPunish_Admin_SteamID64'] = $GLOBALS['steam']->SteamIDTo64($Punishment['UnPunish_Admin_ID']);
        if($Punishment['UnPunish_Admin_SteamID64'] === false)
            unset($Punishment['UnPunish_Admin_SteamID64']);
    }

    /* GeoIP data */
    if(isset($Punishment['Punish_Player_IP'])) {
        $GeoIPQuery = SP_GeoIPCountry($Punishment['Punish_Player_IP']);
        if($GeoIPQuery !== false) {
            $Punishment['Punish_Player_Country'] = $GeoIPQuery['country'];
            $Punishment['Punish_Player_Country_Code'] = $GeoIPQuery['country_code'];
            $Punishment['Punish_Player_Country_Flag'] = '<img src="'.$GeoIPQuery['country_flag'].'" title="'.$GeoIPQuery['country'].'" alt="'.$GeoIPQuery['country_code'].'" />';
        }
        unset($GeoIPQuery);
    }

    /* Previous punishments */
    $PreviousOrQuery = '';
    if(isset($Punishment['Punish_Player_SteamID'], $Punishment['Punish_Player_ID']) && $Punishment['Punish_Player_ID'] != $Punishment['Punish_Player_SteamID'])
        $PreviousOrQuery .= ' OR Punish_Player_ID=\''.$GLOBALS['sql']->Escape($Punishment['Punish_Player_SteamID']).'\'';
    if(isset($Punishment['Punish_Player_SteamID3'], $Punishment['Punish_Player_ID']) && $Punishment['Punish_Player_ID'] != $Punishment['Punish_Player_SteamID3'])
        $PreviousOrQuery .= ' OR Punish_Player_ID=\''.$GLOBALS['sql']->Escape($Punishment['Punish_Player_SteamID3']).'\'';

    $PreviousIPQuery = '';
    if(isset($Punishment['Punish_Player_IP']))
        $PreviousIPQuery = ', SUM(Punish_Player_IP=\''.$GLOBALS['sql']->Escape($Punishment['Punish_Player_IP']).'\' AND Punish_Player_IP!=\'\') AS Count_Player_IP';
    $PreviousPunishments = $GLOBALS['sql']->Query_FetchArray('SELECT SUM(Punish_Player_ID!=\'\' AND (Punish_Player_ID=\''.$GLOBALS['sql']->Escape($Punishment['Punish_Player_ID']).'\''.$PreviousOrQuery.')) AS Count_Player_ID'.$PreviousIPQuery.' FROM '.SQL_PUNISHMENTS);
    unset($PreviousOrQuery, $PreviousIPQuery);

    if(isset($PreviousPunishments['Count_Player_ID']))
        $Punishment['Punish_Player_Previous_ID'] = $PreviousPunishments['Count_Player_ID']; 
    if(isset($PreviousPunishments['Count_Player_IP']))
        $Punishment['Punish_Player_Previous_IP'] = $PreviousPunishments['Count_Player_IP'];
    unset($PreviousPunishments);

    /* Server info */
    $ServerInfo = SP_GetServerInfo($Punishment['Punish_Server_ID']);
    $Punishment['Punish_Server_Mod'] = $ServerInfo['mod']['name'];
    $Punishment['Punish_Server_Formatted'] = '<img alt="'.$ServerInfo['mod']['short'].'" title="'.$ServerInfo['mod']['name'].'" src="'.HTML_IMAGES_GAMES.$ServerInfo['mod']['image'].'" />&nbsp;&nbsp;'.SpecialChars($ServerInfo['name']);
    if($Punishment['Punish_All_Mods'] == 1)
        $Punishment['Punish_Server_Applicable'] = $GLOBALS['trans'][1162];
    else if($Punishment['Punish_All_Servers'] == 1)
        $Punishment['Punish_Server_Applicable'] = sprintf($GLOBALS['trans'][1161], $ServerInfo['mod']['name']);
    else
        $Punishment['Punish_Server_Applicable'] = $GLOBALS['trans'][1160];
    unset($ServerInfo);

    /* Can we see IP address? */
    if(isset($GLOBALS['settings']['punish_showips_all']) && isset($GLOBALS['settings']['punish_showips_flags'])) {
        if(($GLOBALS['settings']['punish_showips_all'] == false && !USER_ADMIN) ||(USER_ADMIN && !($GLOBALS['settings']['punish_showips_flags'] == '*' || $GLOBALS['auth']->HasAdminFlag($GLOBALS['settings']['punish_showips_flags']))))
            unset($Punishment['Punish_Player_IP']);
        else {
            if($Punishment['Punish_Player_IP'] == '')
                unset($Punishment['Punish_Player_IP']);
        }
    } else
        unset($Punishment['Punish_Player_IP']);

/* Check actions */
$ActionEdit = false;

if(isset($_GET['a'])) {
    if($_GET['a'] == 'edit' && USER_ADMIN) {
        /* Check if form was submitted */
        if(isset($_POST['submitted'], $_GET['submitted'])) {
            /* Ensure we are editing the correct record */
            if(!isset($_POST['originid']) || $_POST['originid'] !== $PunishID)
                Redirect();

            /* Check and validate input */
            if(isset($_POST['Punish_Type']))
                $InputType = ParseUserInput($_POST['Punish_Type'], 16, SP_INPUT_ESCAPE);
            if(isset($_POST['Punish_Reason']))
                $InputReason = ParseUserInput($_POST['Punish_Reason'], 100, SP_INPUT_ESCAPE);
            if(isset($_POST['Punish_Length']) && CheckVar($_POST['Punish_Length'], SP_VAR_INT))
                $InputLength = $GLOBALS['sql']->Escape($_POST['Punish_Length']);
            if(isset($_POST['Punish_Server_ID']) && CheckVar($_POST['Punish_Server_ID'], SP_VAR_INT)) {
                $InputServer = $GLOBALS['sql']->Escape($_POST['Punish_Server_ID']);
                if($GLOBALS['sql']->Query_Rows('SELECT 1 FROM '.SQL_SERVERS.' WHERE Server_ID=\''.$InputServer.'\' LIMIT 1') != 1)
                    unset($InputServer);
            }
            if(isset($_POST['Punish_Server_Applicable']) && ($_POST['Punish_Server_Applicable'] == 'all' || $_POST['Punish_Server_Applicable'] == 'this' || $_POST['Punish_Server_Applicable'] == 'mod')) {
                $InputApplicable = array();
                switch($_POST['Punish_Server_Applicable']) {
                    case 'all': $InputApplicable['Punish_All_Mods'] = 1; $InputApplicable['Punish_All_Servers'] = 0; break;
                    case 'mod': $InputApplicable['Punish_All_Mods'] = 0; $InputApplicable['Punish_All_Servers'] = 1; break;
                    case 'this': $InputApplicable['Punish_All_Mods'] = 0; $InputApplicable['Punish_All_Servers'] = 0; break;
                }
                if(empty($InputApplicable))
                    unset($InputApplicable);
            }
            if(isset($_POST['Punish_Auth_Type']))
                $InputAuth = ParseUserInput($_POST['Punish_Auth_Type'], 16, SP_INPUT_ESCAPE);

            /* Build the 'SET' part of the MySQL update string */
            $QueryString = (isset($InputType)?' Punish_Type=\''.$InputType.'\',':'').(isset($InputReason)?' Punish_Reason=\''.$InputReason.'\',':'').(isset($InputLength)?' Punish_Length=\''.$InputLength.'\',':'').(isset($InputServer)?' Punish_Server_ID=\''.$InputServer.'\',':'').(isset($InputApplicable['Punish_All_Mods'])?' Punish_All_Mods=\''.$InputApplicable['Punish_All_Mods'].'\',':'').(isset($InputApplicable['Punish_All_Servers'])?' Punish_All_Servers=\''.$InputApplicable['Punish_All_Servers'].'\',':'').(isset($InputAuth)?' Punish_Auth_Type=\''.$InputAuth.'\',':'');
            if(substr($QueryString, -1) == ',')
                $QueryString = substr($QueryString, 0, -1);

            /* Update punishment record */
            $GLOBALS['sql']->Query('UPDATE '.SQL_PUNISHMENTS.' SET '.$QueryString.' WHERE Punish_ID=\''.$PunishID.'\' LIMIT 1');
            Redirect('^view&id='.$PunishID);
        } else {
            /* We are editing this punishment */
            $ActionEdit = true;

            /* Build a list of servers */
            $ServerListQuery = $GLOBALS['sql']->Query('SELECT Server_ID FROM '.SQL_SERVERS);
            $Server = array();
            while($Row = $GLOBALS['sql']->FetchArray($ServerListQuery)) {
                $Server = SP_GetServerInfo($Row['Server_ID']);
                $Servers[$Server['mod']['name']][$Row['Server_ID']] = $Server['name'];
            }
            $GLOBALS['sql']->Free($ServerListQuery);
            $EditServerList = '';
            foreach($Servers as $Mod => $List) {
                $EditServerList .= '<optgroup label="'.SpecialChars($Mod).'">';
                foreach($List as $ID => $Server) {
                    $EditServerList .= '<option '.(($Punishment['Punish_Server_ID'] == $ID)?' selected="selected"':'').'value="'.$ID.'">'.SpecialChars($Server).'</option>';
                }
                $EditServerList .= '</optgroup>';
            }
            unset($Servers);

            /* Build the radio buttons for applicable servers */
            $EditServerApplicable = '<input type="radio" name="Punish_Server_Applicable" value="all"'.($Punishment['Punish_All_Mods']==1?' checked="checked"':'').'>'.$GLOBALS['trans'][1162];
            $EditServerApplicable .= '&nbsp;&nbsp;<input type="radio" name="Punish_Server_Applicable" value="mod"'.($Punishment['Punish_All_Servers']==1?' checked="checked"':'').'>'.sprintf($GLOBALS['trans'][1161], $Punishment['Punish_Server_Mod']);
            $EditServerApplicable .= '&nbsp;&nbsp;<input type="radio" name="Punish_Server_Applicable" value="this"'.(($Punishment['Punish_All_Mods']==0 && $Punishment['Punish_All_Servers']==0)?' checked="checked"':'').'>'.$GLOBALS['trans'][1160];
        }
    } else if(($_GET['a'] == 'unpunish' || $_GET['a'] == 'reinstate') && USER_ADMIN) {
        if(isset($_POST['confirmed']) && $_POST['confirmed'] == 1) {
            /* Ensure we are editing the correct record */
            if(!isset($_POST['originid']) || $_POST['originid'] !== $PunishID)
                Redirect();

            if($_GET['a'] == 'unpunish') {
                if(!isset($_POST['UnPunish_Reason']) || $_POST['UnPunish_Reason'] == '')
                    Redirect('^view&amp;id='.$PunishID.'&amp;a='.$_GET['a']);

                $InputReason = ParseUserInput($_POST['UnPunish_Reason'], 100, SP_INPUT_ESCAPE);
                $AdminID = $GLOBALS['steam']->Steam64ToID($GLOBALS['auth']->GetUser64());
                if($AdminID === false)
                    $AdminID = '-';
                $AdminID = $GLOBALS['sql']->Escape($AdminID);
                $AdminName =$GLOBALS['auth']->GetName();
                if($AdminName === false)
                    $AdminName = '-';
                $AdminName = $GLOBALS['sql']->Escape($AdminName);
                $AdminInfo = ', UnPunish_Time=\''.time().'\', UnPunish_Reason=\''.$InputReason.'\', UnPunish_Admin_ID=\''.$AdminID.'\', UnPunish_Admin_Name=\''.$AdminName.'\'';
            }

            $GLOBALS['sql']->Query('UPDATE '.SQL_PUNISHMENTS.' SET UnPunish=\''.(($_GET['a'] == 'unpunish')?1:0).'\''.(isset($AdminInfo)?$AdminInfo:'').' WHERE Punish_ID=\''.$PunishID.'\' LIMIT 1');
            Redirect('^view&id='.$PunishID);
        } else {
            $BuildForm = '<div class="center message error">'.($_GET['a'] == 'unpunish'?$GLOBALS['trans'][1169]:$GLOBALS['trans'][1170]).'</div><br />';
            $BuildForm .= '<form name="form-punish-edit" action="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a='.$_GET['a'].'&amp;submitted=true').'" method="post" enctype="multipart/form-data">';
            $BuildForm .= '<input type="hidden" name="originid" value="'.$PunishID.'" />';
            $BuildForm .= '<input type="hidden" name="confirmed" value="1" />';
            if($_GET['a'] == 'unpunish')
                $BuildForm .= $GLOBALS['trans'][1133].':&nbsp;<input type="text" name="UnPunish_Reason" required autofocus />';
            $BuildForm .= '<div class="center"><input name="submitted" type="submit" value="'.$GLOBALS['trans'][3015].'" />&nbsp;<a href="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a=edit').'" class="button">'.$GLOBALS['trans'][3011].'</a></div>';
            $BuildForm .= '</form>';
            $GLOBALS['theme']->AddContent($GLOBALS['trans'][3014], $BuildForm);
            unset($BuildForm);
        }
    } else if($_GET['a'] == 'delete' && USER_SUPERADMIN) {
        if(isset($_POST['confirmed']) && $_POST['confirmed'] == 1) {
            /* Ensure we are editing the correct record */
            if(!isset($_POST['originid']) || $_POST['originid'] !== $PunishID)
                Redirect();

            $GLOBALS['sql']->Query('DELETE FROM '.SQL_PUNISHMENTS.' WHERE Punish_ID=\''.$PunishID.'\' LIMIT 1');
            Redirect();
        } else {
            $BuildForm = '<div class="center message error">'.$GLOBALS['trans'][1171].'</div><br />';
            $BuildForm .= '<form name="form-punish-edit" action="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a=delete&amp;submitted=true').'" method="post" enctype="multipart/form-data">';
            $BuildForm .= '<input type="hidden" name="originid" value="'.$PunishID.'" />';
            $BuildForm .= '<input type="hidden" name="confirmed" value="1" />';
            $BuildForm .= '<div class="center"><input name="submitted" type="submit" value="'.$GLOBALS['trans'][3015].'" />&nbsp;<a href="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a=edit').'" class="button">'.$GLOBALS['trans'][3011].'</a></div>';
            $BuildForm .= '</form>';
            $GLOBALS['theme']->AddContent($GLOBALS['trans'][3014], $BuildForm);
            unset($BuildForm);
        }
    } else {
        /* Unknown action set */
        Redirect('^view&id='.$PunishID);
    }
}

/* Build final tables */
    $Page = '';

    /* Action Buttons */
    $Buttons = '';
    if(USER_LOGGEDIN && !$ActionEdit) {
        if(isset($Punishment['Punish_Player_SteamID64']) && $GLOBALS['auth']->GetUser64() == $Punishment['Punish_Player_SteamID64'] && !isset($_GET['a']))
            $Buttons .= '<a href="'.ParseURL('^appeal&amp;id='.$PunishID).'" title="'.$GLOBALS['trans'][1163].'">'.$GLOBALS['trans'][1136].'</a>';
        if(USER_ADMIN) {
            if($Buttons != '')
                $Buttons .= ' | ';
            $Buttons .= '<a href="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a=edit').'" title="'.$GLOBALS['trans'][1164].'">'.$GLOBALS['trans'][1137].'</a>';
        }
    } else if($ActionEdit) {
        $Buttons .= '<a href="'.ParseURL('^view&amp;id='.$PunishID).'" title="'.$GLOBALS['trans'][3011].'">'.$GLOBALS['trans'][3011].'</a>';
    }
    if($Buttons != '')
        $Buttons = '<span class="action-buttons">'.$Buttons.'</span>';

    /* Player info table */
    $PlayerInfoTable = array('class'=>'table-view table-view-player table-format-data', 'rows'=>array());
    $PlayerInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1123]), array('content'=>(isset($Punishment['Punish_Player_Country_Flag'])?$Punishment['Punish_Player_Country_Flag'].'&nbsp;&nbsp;':'').SpecialChars($Punishment['Punish_Player_Name']))));
    $PlayerInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1124]), array('content'=>SpecialChars($Punishment['Punish_Player_ID']))));
    if(isset($Punishment['Punish_Player_SteamID64']))
        $PlayerInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1127]), array('content'=>'<a href="'.$GLOBALS['steam']->GetProfileURL($Punishment['Punish_Player_SteamID64']).'" title="'.sprintf($GLOBALS['trans'][3002], $GLOBALS['trans'][1127]).'" target="_blank">'.$GLOBALS['steam']->GetProfileURL($Punishment['Punish_Player_SteamID64']).'</a>')));
    if(isset($Punishment['Punish_Player_IP']))
        $PlayerInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1126]), array('content'=>SpecialChars($Punishment['Punish_Player_IP']))));
    if(isset($Punishment['Punish_Player_Previous_ID']) || isset($Punishment['Punish_Player_Previous_IP']))
        $PlayerInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1165]), array('content'=>(isset($Punishment['Punish_Player_Previous_ID'])?$GLOBALS['trans'][1124].': '.$Punishment['Punish_Player_Previous_ID']:'').(isset($Punishment['Punish_Player_Previous_ID'], $Punishment['Punish_Player_Previous_IP'])?' / ':'').(isset($Punishment['Punish_Player_Previous_IP'])?$GLOBALS['trans'][1126].': '.$Punishment['Punish_Player_Previous_IP']:''))));

    /* Build player info table */
    $Page .= $GLOBALS['theme']->FormatContent($GLOBALS['trans'][1120].$Buttons, $GLOBALS['theme']->BuildTable($PlayerInfoTable));
    unset($PlayerInfoTable);
    
    /* Punishment info table */
    $PunishInfoTable = array('class'=>'table-view table-view-punishment table-format-data', 'rows'=>array());
    $PunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1125]), array('content'=>SpecialChars($Punishment['Punish_Time_Formatted']))));
    $PunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1103]), array('content'=>$ActionEdit?'<input type="text" name="Punish_Type" value="'.$Punishment['Punish_Type'].'" required />':SpecialChars(ucwords($Punishment['Punish_Type'])))));
    $PunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1104]), array('content'=>$ActionEdit?'<input type="text" name="Punish_Reason" value="'.$Punishment['Punish_Reason'].'" required />':SpecialChars($Punishment['Punish_Reason']))));
    $PunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1105]), array('class'=>$Punishment['Punish_Status'], 'content'=>($ActionEdit && $Punishment['Punish_Length'] >= 0)?'<input type="text" name="Punish_Length" value="'.$Punishment['Punish_Length'].'" required />':SpecialChars($Punishment['Punish_Length_Formatted'].(isset($Punishment['Punish_Status_Extra'])?' ('.$Punishment['Punish_Status_Extra'].')':'')))));
    if(isset($Punishment['Punish_Expiry_Formatted']))
        $PunishInfoTable['rows'][] = array('cols'=>array(array('content'=>($Punishment['Punish_Status']=='expired'?ucfirst($GLOBALS['trans'][1139]):$GLOBALS['trans'][1130])), array('content'=>SpecialChars($Punishment['Punish_Expiry_Formatted']))));
    
    $PunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1101]), array('content'=>($ActionEdit && isset($EditServerList))?'<select name="Punish_Server_ID">'.$EditServerList.'</select>':$Punishment['Punish_Server_Formatted'])));
    unset($EditServerList);
    
    $PunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1159]), array('content'=>$ActionEdit?$EditServerApplicable:$Punishment['Punish_Server_Applicable'])));
    unset($EditServerApplicable);
    
    $PunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1129]), array('content'=>$ActionEdit?'<input type="text" name="Punish_Auth_Type" value="'.$Punishment['Punish_Auth_Type'].'" required />':SpecialChars(ucwords($Punishment['Punish_Auth_Type'])))));
    
    /* Build punishment info table */
    $Page .= $GLOBALS['theme']->FormatContent($GLOBALS['trans'][1121], $GLOBALS['theme']->BuildTable($PunishInfoTable));
    unset($PunishInfoTable);
    
    /* Admin info table */
    $AdminInfoTable = array('class'=>'table-view table-view-admin table-format-data', 'rows'=>array());
    $AdminInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1123]), array('content'=>SpecialChars($Punishment['Punish_Admin_Name']))));
    $AdminInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1124]), array('content'=>SpecialChars($Punishment['Punish_Admin_ID']))));
    if(isset($Punishment['Punish_Admin_SteamID64']))
        $AdminInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1127]), array('content'=>'<a href="'.$GLOBALS['steam']->GetProfileURL($Punishment['Punish_Admin_SteamID64']).'" title="'.sprintf($GLOBALS['trans'][3002], $GLOBALS['trans'][1127]).'" target="_blank">'.$GLOBALS['steam']->GetProfileURL($Punishment['Punish_Admin_SteamID64']).'</a>')));
        
    /* Build admin info table */
    $Page .= $GLOBALS['theme']->FormatContent($GLOBALS['trans'][1122], $GLOBALS['theme']->BuildTable($AdminInfoTable));
    unset($AdminInfoTable);
    
    /* Remover info table */
    if($Punishment['UnPunish'] == 1) {
        $UnpunishInfoTable = array('class'=>'table-view table-view-remover table-format-data', 'rows'=>array());
        $UnpunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1125]), array('content'=>SpecialChars($Punishment['UnPunish_Time_Formatted']))));
        $UnpunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1104]), array('content'=>$ActionEdit?'<input type="text" name="UnPunish_Reason" value="'.$Punishment['UnPunish_Reason'].'" required />':SpecialChars($Punishment['UnPunish_Reason']))));
        $UnpunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1123]), array('content'=>SpecialChars($Punishment['UnPunish_Admin_Name']))));
        $UnpunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1124]), array('content'=>SpecialChars($Punishment['UnPunish_Admin_ID']))));
        if(isset($Punishment['UnPunish_Admin_SteamID64']))
            $UnpunishInfoTable['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1127]), array('content'=>'<a href="'.$GLOBALS['steam']->GetProfileURL($Punishment['UnPunish_Admin_SteamID64']).'" title="'.sprintf($GLOBALS['trans'][3002], $GLOBALS['trans'][1127]).'" target="_blank">'.$GLOBALS['steam']->GetProfileURL($Punishment['UnPunish_Admin_SteamID64']).'</a>')));
            
        /* Build admin info table */
        $Page .= $GLOBALS['theme']->FormatContent($GLOBALS['trans'][1131], $GLOBALS['theme']->BuildTable($UnpunishInfoTable));
        unset($UnpunishInfoTable);
    }

    /* Add form if we are editing */
    if($ActionEdit) {
        $Page = '<form name="form-punish-edit" action="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a=edit&amp;submitted=true').'" method="post" enctype="multipart/form-data"><input type="hidden" name="originid" value="'.$PunishID.'" />'.$Page;
        $Page .= '<div class="center"><input name="submitted" type="submit" value="'.$GLOBALS['trans'][3013].'" />&nbsp;'.($Punishment['UnPunish'] == 0?'<a href="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a=unpunish').'" class="button">'.$GLOBALS['trans'][1167].'</a>':'<a href="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a=reinstate').'" class="button">'.$GLOBALS['trans'][1168].'</a>').(USER_SUPERADMIN?'&nbsp;<a href="'.ParseURL('^view&amp;id='.$PunishID.'&amp;a=delete').'" class="button">'.$GLOBALS['trans'][1166].'</a>':'').'&nbsp;<a href="'.ParseURL('^view&amp;id='.$PunishID).'" class="button">'.$GLOBALS['trans'][3011].'</a></div></form><br />';
    }

    unset($Punishment);

    /* Add final content to the page */
    $GLOBALS['theme']->AddContentRaw($Page);
    unset($Page);
?>