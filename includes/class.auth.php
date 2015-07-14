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

class Auth {
    private $CookieName = 'SP_SESSION_ID';

    public function SetSession($UserID) {
        global $SQL, $Settings;

        $Time = time();
        $SessionID = $SQL->Escape($this->_GenerateID($UserID));
        $UserID = $SQL->Escape($UserID);
        $UserIP = $SQL->Escape(USER_ADDRESS);

        if($SQL->Query_Rows('SELECT 1 FROM '.SQL_SESSIONS.' WHERE Session_User=\''.$UserID.'\' LIMIT 1') === 1)
            $SQL->Query('UPDATE '.SQL_SESSIONS.' SET Session_ID=\''.$SessionID.'\', Session_Time=\''.$Time.'\', Session_IP=\''.$UserIP.'\' WHERE Session_User=\''.$UserID.'\' LIMIT 1');
        else
            $SQL->Query('INSERT INTO '.SQL_SESSIONS.' (Session_ID, Session_User, Session_IP, Session_Time) VALUES (\''.$SessionID.'\', \''.$UserID.'\', \''.$UserIP.'\', \''.$Time.'\')');

        setcookie($this->CookieName, $SessionID, (int)((isset($Settings['site_session_timeout']) && CheckVar($Settings['site_session_timeout'], SP_VAR_INT))?$Settings['site_session_timeout']:0));

        return true;
    }

    public function ValidateSession() {
        global $SQL;

        if(isset($_COOKIE[$this->CookieName])) {
            if(strlen($_COOKIE[$this->CookieName]) !== 40) {
                $this->EndSession();
                return false;
            }

            $SessionID = $SQL->Escape($_COOKIE[$this->CookieName]);
            $SessionQuery = $SQL->Query('SELECT Session_User, Session_IP, Session_Time FROM '.SQL_SESSIONS.' WHERE Session_ID=\''.$SessionID.'\' LIMIT 1');

            if($SQL->Rows($SessionQuery) === 1) {
                $SessionArray = $SQL->FetchArray($SessionQuery);
                $SessionTime = (int)((isset($Settings['site_session_timeout']) && CheckVar($Settings['site_session_timeout'], SP_VAR_INT))?$Settings['site_session_timeout']:0);
                if(USER_ADDRESS === $SessionArray['Session_IP'] && ($SessionTime === 0 || ($SessionTime > 0 && ($SessionArray['Session_Time']+$SessionTime) > time()))) {
                    $SQL->Free($SessionQuery);
                    return (int)$SessionArray['Session_User']);
                } else
                    $this->EndSession();
            }
            $SQL->Free($SessionQuery);
        }
        return false;
    }

    public function EndSession() {
        setcookie($this->CookieName, '', 1);
        setcookie($this->CookieName, false);
        unset($_COOKIE[$this->CookieName]);
        /* Should we redirect ? */
    }

    private function _GenerateID($Prefix) {
        return sha1($Prefix.':'.uniqid(mt_rand(), true).':'.USER_ADDRESS.':'.(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'UNKNOWN-AGENT'));
    }
}
