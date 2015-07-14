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

class User {
    public $UserID = null;
    public $UserID64 = null;
    public $UserName = null;
    public $GroupID = null;
    private $UserPermissions = array();
    private $PermissionsMap = array(
        'root'=>'*', // All permissions
        'site.annonymous'=>'$', // Given to users who are not logged in
		'site.loggedin'=>'&', // Given to users who are logged in
		'site.access'=>'A', // View/access the site
        'punish.view'=>'B', // View punishments
        'punish.view.sensitive'=>'C', // View sensitive information on a punishment (Mainly just IP address)
        'punish.add'=>'D', // Add punishments
        'punish.edit'=>'E', // Edit punishment information on any punishment
        'punish.edit.own'=>'F', // Edit information on punishment against self
        'punish.edit.state'=>'G', // Can un-punish/reinstate a punishment
        'punish.edit.state.own'=>'H', // Can un-punish/reinstate a punishment against self
        'punish.delete'=>'I', // Delete a punishment
        'punish.delete.own'=>'J', // Delete a punishment against self
        'punish.search'=>'K', // Search punishments
        'server.view'=>'L', // View servers
        'server.add'=>'M', // Add a new server
        'server.edit'=>'N', // Edit server info
        'server.hide'=>'O', // Hide a server from server list
        'server.delete'=>'P', // Delete a server
        'mod.add'=>'Q', // Add a new server mod
        'mod.edit'=>'R', // Edit server mod info
        'mod.delete'=>'S', // Delete a server mod
        'appeal.view.own'=>'T', // View an appeal created by self
        'appeal.view.any'=>'U', // View any appeal
        'appeal.view.sensitive'=>'V', // View sensitive information on an appeal (Mainly just email)
        'appeal.create'=>'W', // Create an appeal for own punishment
        'appeal.edit.basic'=>'X', // Edit basic/non-admin info of any appeal (email, reason)
        'appeal.edit.basic.own'=>'Y', // Edit basic/non-admin info of an appeal created by self
        'appeal.edit'=>'Z', // Edit info of any appeal 
        'appeal.edit.own'=>'AA', // Edit info of an appeal created by self
        'appeal.delete'=>'AB', // Delete any appeal
        'appeal.delete.own'=>'AC', // Delete any appeal created by self
        'appeal.comment'=>'AD', // Comment on an appeal
        'report.view.own'=>'AE', // View a report created by self
        'report.view.any'=>'AF', // View any report
        'report.view.subject'=>'AG', // View report created against self
        'report.create'=>'AH', // Create a report
        'report.edit.basic'=>'AI', // Edit basic/non-admin info of any report
        'report.edit.basic.own'=>'AJ', // Edit basic/non-admin info of a report created by self
        'report.edit.basic.subject'=>'AK', // Edit basic/non-admin info of a report against self
        'report.edit.any'=>'AL', // Edit info of any report 
        'report.edit.own'=>'AM', // Edit info of a report created by self
        'report.edit.subject'=>'AN', // Edit info of a report against self
        'report.delete'=>'AO', // Delete any report
        'report.delete.own'=>'AP', // Delete a report created by self
        'report.delete.subject'=>'AQ', // Delete a report against self
        'report.comment'=>'AR', // Comment on a report
        'comment.edit'=>'AS', // Edit any comment
        'comment.edit.own'=>'AT', // Edit a comment by self
        'comment.delete'=>'AU', // Delete any comment
        'comment.delete.own'=>'AV', // Delete comment created by self
        'admin.access'=>'LA',
        'admin.site.settings'=>'LB',
        'admin.user.add'=>'LC',
        'admin.user.edit'=>'LD',
        'admin.user.delete'=>'LE',
    );
    private $PermissionDelimiator = '.';

    public function __construct($UserID = 0) {
        global $Settings;

        if(!CheckVar($UserID, SP_VAR_INT))
            $UserID = 0;

        if($UserID <= 0 || $this->_LoadUserInfo($UserID) === false) {
            $UserID = 0;

            if(!isset($Settings['permissions_default_group']) || !CheckVar($Settings['permissions_default_group'], SP_VAR_INT))
                throw new SiteError('Invalid default group ID given to User class', 'user.group.default.format.invalid', true);
            else {
                $DefaultPermissions = $this->_GetPermissions((int)$Settings['permissions_default_group']);
                if($DefaultPermissions === false)
                    throw new SiteError('Invalid default group ID given to User class', 'user.group.default.invalid', true, 'Group ID "'.$Settings['permissions_default_group'].'" does not exist');
                else {
                    $this->_LoadPermissions($DefaultPermissions);
                }
            }
        }
    }
    
    /*{{REMOVE}}*/
    public function PrintPermissions() {
        $HasArray = array();
        $NotArray = array();
        foreach($this->PermissionsMap as $Name => $Permisson) {
            if($this->HasPermission($Name))
                $HasArray[] = $Name;
            else
                $NotArray[] = $Name;
        }
        
        echo '<h2>Has</h2>';
        foreach($HasArray as $Has) {
            echo $Has.'<br />';
        }
        echo '<h2>Doesnt Have</h2>';
        foreach($NotArray as $Not) {
            echo $Not.'<br />';
        }
    }
    /*{{REMOVE_END}}*/

    public function Has($Permission) {
        if(empty($this->UserPermissions) || !isset($this->PermissionsMap[$Permission]))
            return false;

        if(in_array($this->PermissionsMap['root'], $this->UserPermissions))
            return true;
        else if(in_array($this->PermissionsMap[$Permission], $this->UserPermissions))
            return true;
        else
            return false;
    }

    /*****************************************
    |    DON'T WORRY ABOUT ANYTHING BELOW    |
    *****************************************/

    private function _LoadUserInfo($UserID) {
        global $SQL;

        //$GetUserInfo = $SQL->Query_FetchArray('SELECT u.*, ud.* FROM '.SQL_USERS.' u LEFT JOIN '.SQL_USER_DATA.' ud ON ud.userid = u.id WHERE u.id=\''.$SQL->Escape($UserID).'\' LIMIT 1');
        $GetUserInfo = $SQL->Query_FetchArray('SELECT * FROM '.SQL_USERS.' WHERE User_ID=\''.$SQL->Escape($UserID).'\' LIMIT 1');
        if(empty($GetUserInfo) || !isset($GetUserInfo['User_Name'], $GetUserInfo['User_SteamID64'], $GetUserInfo['User_GroupID']))
            return false;

        $this->UserName = (string)$GetUserInfo['User_Name'];
        $this->UserID64 = (string)$GetUserInfo['User_SteamID64'];
        $this->GroupID = (int)$GetUserInfo['User_GroupID'];
        unset($GetUserInfo);

        $this->_LoadUserPermissions($UserID, $this->GroupID);
    }
    
    private function _LoadUserPermissions($UserID, $GroupID) {
        $GetPermissions = $this->_GetPermissions($GroupID, $UserID);
        if(!is_array($GetPermissions)) 
            return false;

        $this->_LoadPermissions($GetPermissions);
        return true;
    }

    private function _GetPermissions($GroupID, $UserID = null) {
        global $SQL;

        $GroupID = $SQL->Escape($GroupID);
        if(!is_null($UserID)) {
            $UserID = $SQL->Escape($UserID);
            $GetPermissions = $SQL->Query_FetchArray('SELECT ug.Group_Permissions, uo.User_Permissions_Add, uo.User_Permissions_Sub FROM '.SQL_USER_GROUPS.' ug LEFT JOIN '.SQL_USER_OVERRIDES.' uo ON uo.User_ID=\''.$UserID.'\' WHERE ug.Group_ID=\''.$GroupID.'\'');
        } else 
            $GetPermissions = $SQL->Query_FetchArray('SELECT Group_Permissions FROM '.SQL_USER_GROUPS.' WHERE Group_ID=\''.$GroupID.'\' LIMIT 1');

        if(empty($GetPermissions) || !isset($GetPermissions['Group_Permissions']))
            return false;

        $PermissionArray = array();

        /* Get group permissions */
        $GroupPermissions = $this->_SplitPermissionString($GetPermissions['Group_Permissions']);
        unset($GetPermissions['Group_Permissions']);

        if($GroupPermissions === false)
            return false;

        if(is_array($GroupPermissions) && !empty($GroupPermissions)) {
            foreach($GroupPermissions as $Permission) {
                if(!in_array($Permission, $PermissionArray))
                    array_push($PermissionArray, $Permission);
            }
        }

        /* Get permissions addition overrides */
        $AddPermissions = array();
        if(isset($GetPermissions['User_Permissions_Add']) && !empty($GetPermissions['User_Permissions_Add']))
            $AddPermissions = $this->_SplitPermissionString($GetPermissions['User_Permissions_Add']);
        unset($GetPermissions['User_Permissions_Add']);

        if(is_array($AddPermissions) && !empty($AddPermissions)) {
            foreach($AddPermissions as $Permission) {
                if(!in_array($Permission, $PermissionArray))
                    array_push($PermissionArray, $Permission);
            }
        }
        
        /* Get permissions subtraction overrides */
        $SubPermissions = array();
        if(isset($GetPermissions['User_Permissions_Sub']) && !empty($GetPermissions['User_Permissions_Sub']))
            $SubPermissions = $this->_SplitPermissionString($GetPermissions['User_Permissions_Sub']);
        unset($GetPermissions);

        if(is_array($SubPermissions) && !empty($SubPermissions)) {
            foreach($SubPermissions as $Permission) {
                if(($PermissionKey = array_search($Permission, $PermissionArray)) !== false)
                    unset($PermissionArray[$PermissionKey]);
            }
        }

        return $PermissionArray;
    }

    private function _SplitPermissionString($PermissionString) {
        if(empty($PermissionString))
	        return false;

        $PermissionArray = array();

        if(strpos($PermissionString, $this->PermissionDelimiator)) {
            $Permissions = explode($this->PermissionDelimiator, $PermissionString);
            foreach($Permissions as $Permission) {
                if(!empty($Permission))
                    array_push($PermissionArray, $Permission);
            }
        } else {
            array_push($PermissionArray, $PermissionString);
        }

        return $PermissionArray;
    }

    private function _LoadPermissions($PermissionArray) {
        if(empty($PermissionArray))
	        return false;

        foreach($PermissionArray as $Permission) {
            if(!empty($Permission) && !in_array($Permission, $this->UserPermissions))
                array_push($this->UserPermissions, $Permission);
        }

        return true;
    }
}
?>