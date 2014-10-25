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
        if(preg_match('/^([0-9]{17,19})+$/', $Steam64) && (int)$Steam64 > 76561197960265728 && (int)$Steam64 < 9223372036854775807)
            return true;
        else
            return false;
    }
    public function ValidID($SteamID, $Type = 0) {
        if($SteamID == 'BOT' || $SteamID == 'UNKNOWN' || $SteamID == 'STEAM_ID_PENDING')
            return false;
        if($Type == 0 || $Type == 1) {
            if(preg_match('/^STEAM_[0-5]:([1-8]):([0-9]{1,19})$/i', $SteamID, $Matches)) {
                if(count($Matches) == 3 && $Matches[2] > 0)
                    return true;
            }
        }
        if($Type == 0 || $Type == 2) {
            if(preg_match('/^\[U:1:([0-9]{1,19})\]$/i', $SteamID, $Matches)) {
                if(count($Matches) == 2 && $Matches[1] > 0)
                    return true;
            }
        }
        return false;
    }
    public function Steam64ToID($Steam64, $NewID = false) {
        if(!$NewID) {
            $Server = bcsub($Steam64, '76561197960265728') & 1;
            $Auth = (int)bcdiv(bcsub(bcsub($Steam64, '76561197960265728'), $Server), '2');
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
        if(preg_match('/^STEAM_[0-5]:([1-8]):([0-9]{1,19})$/i', $SteamID, $Matches)) {
            if(count($Matches) == 3) {
                $Steam64 = bcmul($Matches[2], '2');
                $Steam64 = (int)bcadd($Steam64, bcadd('76561197960265728', $Matches[1]));
                return $Steam64;
            }
        }
        if(preg_match('/^\[U:1:([0-9]{1,19})\]$/i', $SteamID, $Matches)) {
            if(count($Matches) == 2 && $Matches[1] != 0) {
                $Steam64 = (int)bcadd('76561197960265728', $Matches[1]);
                return $Steam64;
            }
        }
        return false;
    }
}
?>