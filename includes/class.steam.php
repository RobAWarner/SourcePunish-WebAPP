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

/* Define flags for SteamID formats */
define('STEAM_ID', 1);
define('STEAM_ID3', 2);
define('STEAM_ID64', 4);
define('STEAM_ALLOW_UNKNOWN', 8);

class Steam {
    public $ProfilesURL = 'http://steamcommunity.com/profiles/';

    /* Tests whether '$SteamID' is a valid SteamID */
    /* Set flags 'STEAM_ID', 'STEAM_ID3' or 'STEAM_ID64' to specify which type of ID to test */
    /* Set flag 'STEAM_ALLOW_UNKNOWN' to allow ID's aren't actual players but can be assigned by a server  */
    /* Note: A SteamID64 should be given as a string to avoid issues on 32bit systems */
    public function ValidID($InputID, $Flags = 0) {
        /* Are we to allow unknown ID's? */
        if($Flags & STEAM_ALLOW_UNKNOWN) {
            if($InputID == 'BOT' || $InputID == 'STEAM_ID_PENDING')
                return true;
        }

        /* Check for a valid SteamID format matching the specified type */
        if($Flags & STEAM_ID) {
            if($this->_ValidID($InputID))
                return true;
        }
        if($Flags & STEAM_ID3) {
            if($this->_ValidID3($InputID))
                return true;
        }
        if($Flags & STEAM_ID64) {
            if($this->_ValidID64($InputID))
                return true;
        }

        /* Clearly an ID flag was not set, silly! */
        return false;
    }

    /* Convert one SteamID format to another */
    /* Set a flag 'STEAM_ID', 'STEAM_ID3' or 'STEAM_ID64' to specify which SteamID format to convert to */
    /* The input SteamID format is detected automatically */
    /* Note: A SteamID64 should be given as and will be returned as a string to avoid issues on 32bit systems */
    public function ConvertID($InputID, $Flags = 0) {
        /* Convert '$InputID' into a SteamID64, unless it's already a SteamID64 */
        if(!$this->_ValidID64($InputID)) {
            if($this->_ValidID($InputID)) {
                if($Flags & STEAM_ID)
                    return $InputID;
                $SteamID64 = $this->_ConvertIDToID64($InputID);
            } else if($this->_ValidID3($InputID)) {
                if($Flags & STEAM_ID3)
                    return $InputID;
                $SteamID64 = $this->_ConvertID3ToID64($InputID);
            } else
                return false;

            /* Did it fail to convert? */
            if($SteamID64 === false)
                return false;
        } else
            $SteamID64 = $InputID;

        /* Format the desired output SteamID type */
        if($Flags & STEAM_ID)
            return $this->_ConvertID64ToID($SteamID64);
        else if($Flags & STEAM_ID3)
            return $this->_ConvertID64ToID3($SteamID64);
        else if($Flags & STEAM_ID64)
            return $SteamID64;

        /* Something went wrong :( */
        return false;
    }

    /* Return a formatted profile URL for the given '$InputID' */
    /* Note: '$InputID' can be a SteamID64 ID OR a SteamID3 */
    /* Note: A SteamID64 should be given as a string to avoid issues on 32bit systems */
    public function GetProfileURL($InputID) {
        /* Really simple, do I need to explain? */
        return $this->ProfilesURL.urlencode($InputID);
    }

    /* Fetches the name of the player matching the given SteamID from their profile */
    /* Note: '$InputID' can be a SteamID64 ID OR a SteamID3 */
    /* Note: A SteamID64 should be given as a string to avoid issues on 32bit systems */
    public function GetSteamName($InputID) {
        /* Do we have a valid ID? */
        if!($this->ValidID($InputID, STEAM_ID3 | STEAM_ID64))
            return false;
        
        /* Get their profile URL with the 'xml' tag appended */
        $ProfileURL = $this->ProfilesURL.urlencode($InputID).'/?xml=1&l=english';

        /* Create the steam context for the connection */
        $Stream = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'timeout' => 4,
                'header' => "Accept-language: en\r\nConnection: close\r\n",
            ),
        ));

        /* Get the data from the profile */
        $SteamResponse = file_get_contents($ProfileURL, false, $Stream);
        unset($ProfileURL, $Stream);

        /* Ensure we got a response */
        if($SteamResponse !== false) {
            /* Cool. Now we can load the XML data */
            $SteamData = @simplexml_load_string($SteamResponse);
            if($SteamData !== false && isset($SteamData->steamID)) {
                /* Excellent, we got their name! */
                return (string)$SteamData->steamID;
            }
        }

        /* All didn't go to plan */
        return false;
    }
    
    /*****************************************
    |    DON'T WORRY ABOUT ANYTHING BELOW    |
    *****************************************/
    
    /* Check for a valid SteamID, E.G: STEAM_0:1:20319872 */
    private function _ValidID($SteamID, $ReturnMatches = false) {
        if(preg_match('/^STEAM_[0-5]:([0-8]):([0-9]{1,19})$/i', $SteamID, $Matches)) {
            if($ReturnMatches)
                return $Matches;
            else
                return true;
        }
        return false;
    }

    /* Check for a valid SteamID3, E.G: [U:1:40639745] */
    private function _ValidID3($SteamID3, $ReturnMatches = false) {
        if(preg_match('/^\[U:1:([0-9]{1,19})\]$/i', $SteamID3, $Matches))
            if($ReturnMatches)
                return $Matches;
            else
                return true;
        return false;
    }

    /* Check for a valid SteamID64, E.G: 76561198000905473 */
    private function _ValidID64($SteamID64) {
        if(preg_match('/^([0-9]{17,19})+$/', $SteamID64) && bccomp($SteamID64, '76561197960265728') >= 0 && bccomp('9223372036854775807', $SteamID64) >= 0)
            return true;
        return false;
    }

    /* Convert a SteamID64 to a SteamID */
    private function _ConvertID64ToID($SteamID64) {
        $Server = bcsub($SteamID64, '76561197960265728') & 1;
        $Auth = (string)bcdiv(bcsub(bcsub($SteamID64, '76561197960265728'), $Server), '2');
        if(strpos($Auth, '.') !== false)
            $Auth = substr($Auth, 0, strpos($Auth, '.'));
        $SteamID = 'STEAM_0:'.$Server.':'.$Auth;
        if($this->_ValidID($SteamID))
            return $SteamID;
        return false;
    }

    /* Convert a SteamID64 to a SteamID3 */
    private function _ConvertID64ToID3($SteamID64) {
        $Server = bcsub($SteamID64, '76561197960265728') & 1;
        $Auth = bcsub(bcsub($SteamID64, '76561197960265728'), $Server);
        $ID = (int)bcadd($Auth, $Server);
        $SteamID = '[U:1:'.$ID.']';
        if($this->_ValidID3($SteamID))
            return $SteamID;
        return false;
    }

    /* Convert a SteamID to a SteamID64 */
    private function _ConvertIDToID64($SteamID) {
        $TryMatch = $this->_ValidID($SteamID, true);
        if($TryMatch === false)
            return false;
        $SteamID64 = (string)bcadd(bcmul($TryMatch[2], '2'), bcadd('76561197960265728', $TryMatch[1]));
        if(strpos($SteamID64, '.') !== false)
            $SteamID64 = substr($SteamID64, 0, strpos($SteamID64, '.'));
        return $SteamID64;
    }

    /* Convert a SteamID3 to a SteamID64 */
    private function _ConvertID3ToID64($SteamID3) {
        $TryMatch = $this->_ValidID3($SteamID3, true);
        if($TryMatch === false)
            return false;
        $SteamID64 = (string)bcadd('76561197960265728', $TryMatch[1]);
        if(strpos($SteamID64, '.') !== false)
            $SteamID64 = substr($SteamID64, 0, strpos($SteamID64, '.'));
        return $SteamID64;
    }

    /* Convert a SteamID to a SteamID3 */
    private function _ConvertIDToID3($SteamID) {
        $TryMatch = $this->_ValidID($SteamID, true);
        if($TryMatch === false)
            return false;
        $SteamID3 = '[U:1:'.(int)bcadd(bcmul($TryMatch[2], '2'), $TryMatch[1]).']';
        return $SteamID3;
    }

    /* Convert a SteamID3 to a SteamID */
    private function _ConvertID3ToID($SteamID3) {
        $TryMatch = $this->_ValidID3($SteamID3, true);
        if($TryMatch === false)
            return false;
        $Server = $Matches[1] & 1;
        $Auth = (int)bcdiv(bcsub($Matches[1], $Server), '2');
        $SteamID = 'STEAM_0:'.$Server.':'.$Auth;
        return $SteamID;
    }
}
?>