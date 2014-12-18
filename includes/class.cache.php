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

class Cache {
    private $CacheDir = '';
    
    public function __construct($Directory) {
        if(file_exists($Directory)) {
            $this->CacheDir = $Directory;
        }
    }
    public function Valid($Cache, $Expiry, $Type = 'json') {
        if(file_exists($this->CacheDir.$Cache.'.'.$Type)) {
            if($Expiry == 0 || filemtime($this->CacheDir.$Cache.'.'.$Type) > (time() - $Expiry))
                return true;
        }
        return false;
    }
    public function Read($Cache, $Decode = true, $Type = 'json') {
        if(file_exists($this->CacheDir.$Cache.'.'.$Type)) {
            $GetCache = @file_get_contents($this->CacheDir.$Cache.'.'.$Type);
            if($GetCache !== false) {
                if($Decode)
                    $GetCache = $this->Decode($GetCache, $Type);
                return $GetCache;
            }
        }
        return false;
    }
    public function Encode($Content, $Type = 'json') {
        if($Type == 'json') {
            $Content = @json_encode($Content);
            if(json_last_error() == JSON_ERROR_NONE)
                return $Content;
        }
        return false;
    }
    public function Decode($Content, $Type = 'json') {
        if($Type == 'json') {
            $Content = @json_decode($Content, true);
            if(json_last_error() == JSON_ERROR_NONE)
                return $Content;
        }
        return false;
    }
    public function Write($Cache, $Content, $Encode = true, $Type = 'json') {
        mb_internal_encoding('UTF-8');
        if($Encode) {
            $Content = $this->Encode($Content, $Type);
            if($Content === false)
                return false;
        }
        return (@file_put_contents($this->CacheDir.$Cache.'.'.$Type, $Content)===false?false:true);
    }
}
?>