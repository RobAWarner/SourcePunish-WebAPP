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
if(!defined('IN_SP')) die('Access Denied!');

$SteamID = $GLOBALS['auth']->GetUser64();

$SteamIDTable = array('class'=>'table-steamid table-format-data', 'rows'=>array());
$SteamIDTable['rows'][] = array('cols'=>array(array('content'=>'Steam Name'), array('content'=>htmlspecialchars($GLOBALS['auth']->GetName()))));
$SteamIDTable['rows'][] = array('cols'=>array(array('content'=>'SteamID'), array('content'=>htmlspecialchars($GLOBALS['steam']->Steam64ToID($SteamID)))));
$SteamIDTable['rows'][] = array('cols'=>array(array('content'=>'SteamID 3'), array('content'=>htmlspecialchars($GLOBALS['steam']->Steam64ToID($SteamID, true)))));
$SteamIDTable['rows'][] = array('cols'=>array(array('content'=>'Steam Friend ID'), array('content'=>htmlspecialchars($SteamID))));
$SteamIDTable['rows'][] = array('cols'=>array(array('content'=>'Steam Profile'), array('content'=>'<a href="'.$GLOBALS['steam']->GetProfileURL($SteamID).'" target="_blank">'.$GLOBALS['steam']->GetProfileURL($SteamID).'</a>')));
$SteamIDTable = $GLOBALS['theme']->BuildTable($SteamIDTable);
$GLOBALS['theme']->AddContent('SteamID Information', $SteamIDTable);
?>