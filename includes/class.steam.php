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
    - Completely validate SteamID conversions
*/
class Steam {
    public $ProfilesURL = 'https://steamcommunity.com/profiles/';

    public function Valid64($Steam64) {
        PrintDebug('Called Steam->Valid64 with \''.$Steam64.'\'', 2);
        if(preg_match('/^([0-9]{17,19})+$/', $Steam64) && bccomp($Steam64, '76561197960265728') >= 0 && bccomp('9223372036854775807', $Steam64) >= 0)
            return true;
        else
            return false;
    }
    public function ValidID($SteamID, $Type = 0) {
        PrintDebug('Called Steam->ValidID with \''.$SteamID.'\' AND \''.$Type.'\'', 2);
        /* Should we allow these since they can be assigned to clients? */
        if($SteamID == 'BOT' || $SteamID == 'UNKNOWN' || $SteamID == 'STEAM_ID_PENDING')
            return false;
        if($Type == 0 || $Type == 1) {
            if(preg_match('/^STEAM_[0-5]:([0-8]):([0-9]{1,19})$/i', $SteamID, $Matches)) {
                if(count($Matches) == 3 && (int)$Matches[2] > 0)
                    return true;
            }
        }
        if($Type == 0 || $Type == 2) {
            if(preg_match('/^\[U:1:([0-9]{1,19})\]$/i', $SteamID, $Matches)) {
                if(count($Matches) == 2 && (int)$Matches[1] > 0)
                    return true;
            }
        }
        return false;
    }
    public function Steam64ToID($Steam64, $NewID = false) {
        PrintDebug('Called Steam->Steam64ToID with \''.$Steam64.'\' AND \''.($NewID?'true':'false').'\'', 2);
        if(!$NewID) {
            $Server = bcsub($Steam64, '76561197960265728') & 1;
            $Auth = (string)bcdiv(bcsub(bcsub($Steam64, '76561197960265728'), $Server), '2');
            if(strpos($Auth, '.') !== false)
                $Auth = substr($Auth, 0, strpos($Auth, '.'));
            $SteamID = 'STEAM_0:'.$Server.':'.$Auth;
            if($this->ValidID($SteamID, 1))
                return $SteamID;
        } else {
            $Server = bcsub($Steam64, '76561197960265728') & 1;
            $Auth = bcsub(bcsub($Steam64, '76561197960265728'), $Server);
            $ID = (int)bcadd($Auth, $Server);
            $SteamID = '[U:1:'.$ID.']';
            if($this->ValidID($SteamID, 2))
                return $SteamID;
        }
        return false;
    }
    public function SteamIDTo64($SteamID) {
        PrintDebug('Called Steam->SteamIDTo64 with \''.$SteamID.'\'', 2);
        if(preg_match('/^STEAM_[0-5]:([0-8]):([0-9]{1,19})$/i', $SteamID, $Matches)) {
            if(count($Matches) == 3) {
                $Steam64 = (string)bcadd(bcmul($Matches[2], '2'), bcadd('76561197960265728', $Matches[1]));
                if(strpos($Steam64, '.') !== false)
                    $Steam64 = substr($Steam64, 0, strpos($Steam64, '.'));
                return $Steam64;
            }
        }
        if(preg_match('/^\[U:1:([0-9]{1,19})\]$/i', $SteamID, $Matches)) {
            if(count($Matches) == 2 && $Matches[1] != 0) {
                $Steam64 = (string)bcadd('76561197960265728', $Matches[1]);
                if(strpos($Steam64, '.') !== false)
                    $Steam64 = substr($Steam64, 0, strpos($Steam64, '.'));
                return $Steam64;
            }
        }
        return false;
    }
    public function SteamIDToID($SteamID) {
        PrintDebug('Called Steam->SteamIDToID with \''.$SteamID.'\'', 2);
        if(preg_match('/^STEAM_[0-5]:([0-8]):([0-9]{1,19})$/i', $SteamID, $Matches)) {
            if(count($Matches) == 3) {
                $SteamID = '[U:1:'.(int)bcadd(bcmul($Matches[2], '2'), $Matches[1]).']';
                return $SteamID;
            }
        }
        if(preg_match('/^\[U:1:([0-9]{1,19})\]$/i', $SteamID, $Matches)) {
            if(count($Matches) == 2 && $Matches[1] != 0) {
                $Server = $Matches[1] & 1;
                $Auth = (int)bcdiv(bcsub($Matches[1], $Server), '2');
                $SteamID = 'STEAM_0:'.$Server.':'.$Auth;
                return $SteamID;
            }
        }
        return false;
    }
}
?>