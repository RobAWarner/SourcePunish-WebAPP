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

global $PageQuery, $SQL, $User, $Theme;

$Navs = array('user'=>array(), 'main'=>array());

/* Get Links */
$GetAllNavs = $SQL->Query('SELECT * FROM '.SQL_NAVIGATION.' WHERE Nav_Show=\'1\' ORDER BY Nav_Order DESC');
if($SQL->Rows($GetAllNavs)) {
    while($Row = $SQL->FetchArray($GetAllNavs)) {
        if(!empty($Row['Nav_Permission']) && !$User->Has($Row['Nav_Permission']))
            continue;
        $NavBuild = array(
            'parent' => $Row['Nav_Parent'],
            'order' => $Row['Nav_Order'],
            'active' => ($_SERVER['REQUEST_URI'] == ParseURL($Row['Nav_Url'])?true:false),
            'link' => array(
                'text' => ParseText($Row['Nav_Text']),
                'attrs' => array(
                    'href' => ParseURL($Row['Nav_Url']),
                ),
            ),
        );
        if($Row['Nav_NewTab'])
            $NavBuild['attrs']['target'] = '_blank';

        //array_push($Navs[($Row['usernav']==1?'user':'main')], $NavBuild);
        $Navs[($Row['Nav_User']==1?'user':'main')][$Row['Nav_ID']] = $NavBuild;
    }
}
$SQL->Free($GetAllNavs);

/* Parent/Child */
foreach($Navs as $Type => $NavArray) {
    foreach($NavArray as $ID => $Nav) {
        if($Navs[$Type][$ID]['parent'] > 0) {
            if(isset($Navs[$Type][$Navs[$Type][$ID]['parent']])) {
                $Navs[$Type][$Navs[$Type][$ID]['parent']]['children']['items'][$ID] = $Navs[$Type][$ID];
                unset($Navs[$Type][$ID]);
            } else
                unset($Navs[$Type][$ID]);
        }
    }
}

$Build['main'] = $Theme->Render('nav.main', array('items'=>array_reverse($Navs['main'])));
$Build['user'] = $Theme->Render('nav.main', array('items'=>array_reverse($Navs['user'])));

return $Build;
?>