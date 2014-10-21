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
class Auth {
    private $OpenIDURL = 'https://steamcommunity.com/openid/login';

    public function GetLoginURL() {
        $OpenIDParams = array(
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off')?'https':'http'.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            'openid.realm' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off')?'https':'http'.'://'.$_SERVER['HTTP_HOST'],
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id'	=> 'http://specs.openid.net/auth/2.0/identifier_select',
        );
        return $this->OpenIDURL.'?'.http_build_query($OpenIDParams, '', '&amp;');
    }
    public function ValidateLogin() {
        $OpenIDParams = array(
            'openid.assoc_handle' => $_GET['openid_assoc_handle'],
            'openid.signed' => $_GET['openid_signed'],
            'openid.sig' => $_GET['openid_sig'],
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
        );
        $SignedArray = explode(',', $_GET['openid_signed']);
        foreach($SignedArray as $Signed) {
            $SignedValue = $_GET['openid_' . str_replace('.', '_', $Signed)];
            $OpenIDParams['openid.' . $Signed] = get_magic_quotes_gpc()?stripslashes($SignedValue):$SignedValue; 
        }
        unset($SignedArray);
        $OpenIDParams['openid.mode'] = 'check_authentication';
        $HTTPQuery = http_build_query($OpenIDParams);
        unset($OpenIDParams);
        $Stream = stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => "Accept-language: en\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($HTTPQuery)."\r\nConnection: close\r\n",
                'content' => $HTTPQuery,
            ),
        ));
        unset($HTTPQuery);
        $GetResponse = file_get_contents($this->OpenIDURL, false, $Stream);
        unset($Stream);
        if($GetResponse === false)
            return false;
        if(preg_match('#^http://steamcommunity.com/openid/id/([0-9]{17,20})#', $_GET['openid_claimed_id'], $Matches)) {
            if(count($Matches) == 2)
                return preg_match('/is_valid\s*:\s*true/i', $GetResponse) == 1?$Matches[1]:'';
        }
        return false;
    }
    public function ValidateSession($SQL) {
        if(isset($_COOKIE['SP_SESSION_ID']) && $_COOKIE['SP_SESSION_ID'] != '') {
            // Check session id & time with database
        }
        return false;
    }
    public function SetCookie() {
    
    }
    public function SetSession() {
        
    }
}
