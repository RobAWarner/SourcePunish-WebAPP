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

/* Navigation Items / Pages */
    $GLOBALS['trans'][1001] = 'Home';
    $GLOBALS['trans'][1002] = 'Punishments';
    $GLOBALS['trans'][1003] = 'View All';
    $GLOBALS['trans'][1004] = 'Search';
    $GLOBALS['trans'][1005] = 'My Punishments';
    $GLOBALS['trans'][1006] = 'Appeal A Punishment';
    $GLOBALS['trans'][1007] = 'Servers';
    $GLOBALS['trans'][1008] = 'Stats';
    $GLOBALS['trans'][1009] = 'Login';
    $GLOBALS['trans'][1010] = 'Logout';
    $GLOBALS['trans'][1011] = 'Admin';
    $GLOBALS['trans'][1012] = 'Me';
    $GLOBALS['trans'][1013] = 'My Appeals';
    $GLOBALS['trans'][1014] = 'My SteamID';
/* Punishments */
    $GLOBALS['trans'][1100] = 'Date';
    $GLOBALS['trans'][1101] = 'Server';
    $GLOBALS['trans'][1102] = 'Player';
    $GLOBALS['trans'][1103] = 'Type';
    $GLOBALS['trans'][1104] = 'Reason';
    $GLOBALS['trans'][1105] = 'Length';
    $GLOBALS['trans'][1120] = 'Player Information';
    $GLOBALS['trans'][1121] = 'Punishment Information';
    $GLOBALS['trans'][1122] = 'Admin Information';
    $GLOBALS['trans'][1123] = 'Name';
    $GLOBALS['trans'][1124] = 'ID';
    $GLOBALS['trans'][1125] = 'Date/Time';
    $GLOBALS['trans'][1126] = 'IP Address';
    $GLOBALS['trans'][1127] = 'Steam Profile';
    $GLOBALS['trans'][1128] = 'Total Punishments';
    $GLOBALS['trans'][1129] = 'Auth Type';
    $GLOBALS['trans'][1130] = 'Expires';
    $GLOBALS['trans'][1131] = 'Removal Information';
    $GLOBALS['trans'][1132] = 'Removed Date/Time';
    $GLOBALS['trans'][1133] = 'Removed Reason';
    $GLOBALS['trans'][1134] = 'Remover';
    $GLOBALS['trans'][1135] = 'Remover ID';
    $GLOBALS['trans'][1136] = 'Appeal';
    $GLOBALS['trans'][1137] = 'Edit';
    $GLOBALS['trans'][1138] = 'Search';
    $GLOBALS['trans'][1139] = 'expired';
    $GLOBALS['trans'][1140] = 'removed';
    $GLOBALS['trans'][1141] = 'permanent';
    $GLOBALS['trans'][1142] = 'year';
    $GLOBALS['trans'][1143] = 'years';
    $GLOBALS['trans'][1144] = 'month';
    $GLOBALS['trans'][1145] = 'months';
    $GLOBALS['trans'][1146] = 'week';
    $GLOBALS['trans'][1147] = 'weeks';
    $GLOBALS['trans'][1148] = 'day';
    $GLOBALS['trans'][1149] = 'days';
    $GLOBALS['trans'][1150] = 'hour';
    $GLOBALS['trans'][1151] = 'hours';
    $GLOBALS['trans'][1152] = 'minute';
    $GLOBALS['trans'][1153] = 'minutes';
    $GLOBALS['trans'][1154] = 'second';
    $GLOBALS['trans'][1155] = 'seconds';
    $GLOBALS['trans'][1156] = 'Recent Punishments';
    $GLOBALS['trans'][1157] = 'Punishment List - Page %s / %s';
    $GLOBALS['trans'][1158] = 'Active';
    $GLOBALS['trans'][1159] = 'Applicable On';
    $GLOBALS['trans'][1160] = 'Just this server';
    $GLOBALS['trans'][1161] = 'All servers running %s';
    $GLOBALS['trans'][1162] = 'Every server';
    $GLOBALS['trans'][1163] = 'Appeal this punishment';
    $GLOBALS['trans'][1164] = 'Edit this punishment';
    $GLOBALS['trans'][1165] = 'Previous punishments';
    $GLOBALS['trans'][1166] = 'Delete Record';
    $GLOBALS['trans'][1167] = 'Unpunish';
    $GLOBALS['trans'][1168] = 'Reinstate';
/* Servers */
    $GLOBALS['trans'][1200] = 'Mod';
    $GLOBALS['trans'][1201] = 'Server Address';
    $GLOBALS['trans'][1202] = 'Hostname';
    $GLOBALS['trans'][1203] = 'Players';
    $GLOBALS['trans'][1204] = 'Map';
    $GLOBALS['trans'][1205] = 'VAC';
    $GLOBALS['trans'][1206] = 'Location';
    $GLOBALS['trans'][1207] = 'Time';
    $GLOBALS['trans'][1208] = 'Connect to this server';
    $GLOBALS['trans'][1209] = 'Server list';
    $GLOBALS['trans'][1210] = 'Player list';
    $GLOBALS['trans'][1211] = 'Player Name';
    $GLOBALS['trans'][1212] = 'Score';
    $GLOBALS['trans'][1213] = 'Connection Time';
/* Stats */
    $GLOBALS['trans'][1300] = 'Total punishments on record: %s';
    $GLOBALS['trans'][1301] = 'Total punishments for %s';
    $GLOBALS['trans'][1302] = 'Total punishments by type for %s';
    $GLOBALS['trans'][1303] = 'Total punishments by time';
    $GLOBALS['trans'][1304] = 'Most punished players';
    $GLOBALS['trans'][1340] = 'all time';
    $GLOBALS['trans'][1341] = 'last year';
    $GLOBALS['trans'][1342] = 'the last 12 months';
    $GLOBALS['trans'][1343] = 'last month';
    $GLOBALS['trans'][1344] = 'the last 30 days';
    $GLOBALS['trans'][1345] = 'last week';
    $GLOBALS['trans'][1346] = 'the last 7 days';
    $GLOBALS['trans'][1347] = 'yesterday';
    $GLOBALS['trans'][1348] = 'today';
/* Search Terms */
    $GLOBALS['trans'][1400] = 'Player name';
    $GLOBALS['trans'][1401] = 'Player ID';
    $GLOBALS['trans'][1402] = 'Player friend ID';
    $GLOBALS['trans'][1403] = 'Admin name';
    $GLOBALS['trans'][1404] = 'Admin ID';
    $GLOBALS['trans'][1405] = 'Auth type';
    $GLOBALS['trans'][1406] = 'Punishment type';
    $GLOBALS['trans'][1407] = 'Reason';
    $GLOBALS['trans'][1408] = 'Length';
    $GLOBALS['trans'][1409] = 'Active punishments';
    $GLOBALS['trans'][1410] = 'Removed punishments';
    $GLOBALS['trans'][1411] = 'Server';
    $GLOBALS['trans'][1412] = 'Date';
    $GLOBALS['trans'][1413] = 'Day';
    $GLOBALS['trans'][1414] = 'Month';
    $GLOBALS['trans'][1415] = 'Year';
    $GLOBALS['trans'][1416] = 'E.G: %s';
    $GLOBALS['trans'][1417] = 'YYYY';
    $GLOBALS['trans'][1418] = 'Remover name';
    $GLOBALS['trans'][1419] = 'Remover ID';
    $GLOBALS['trans'][1420] = 'Removal reason';
/* Appeals */
    $GLOBALS['trans'][1500] = 'Appeal created';
    $GLOBALS['trans'][1501] = 'Appeal reason';
    $GLOBALS['trans'][1502] = 'Current status';
    $GLOBALS['trans'][1503] = 'Original punishment';
    $GLOBALS['trans'][1504] = '%s had a %s %s placed on them for %s';
    $GLOBALS['trans'][1505] = 'New';
    $GLOBALS['trans'][1506] = 'Under review';
    $GLOBALS['trans'][1507] = 'Accepted';
    $GLOBALS['trans'][1508] = 'Declined';
    $GLOBALS['trans'][1509] = 'Original punishment details';
    $GLOBALS['trans'][1510] = 'Appeal details';
    $GLOBALS['trans'][1511] = $GLOBALS['trans'][1403]; // 'Admin name'
    $GLOBALS['trans'][1512] = 'Decision reason';
    $GLOBALS['trans'][1513] = 'View original punishment';
    $GLOBALS['trans'][1514] = 'Please explain why you wish to appeal this punishment';
    $GLOBALS['trans'][1515] = 'I agree the above information is correct and understand that appeals are subject to review';
    $GLOBALS['trans'][1516] = 'Decision date';
/* System */
    $GLOBALS['trans'][2001] = '';
    $GLOBALS['trans'][2009] = 'Redirecting page to %s';
    $GLOBALS['trans'][2010] = 'You do not have permission to view this page';
    $GLOBALS['trans'][2011] = 'No records found';
/* Other */
    $GLOBALS['trans'][3001] = 'Login through Steam';
    $GLOBALS['trans'][3002] = 'Visit %s';
    $GLOBALS['trans'][3003] = '%s ago';
    $GLOBALS['trans'][3004] = 'Unknown';
    $GLOBALS['trans'][3005] = 'Web';
    $GLOBALS['trans'][3006] = 'Web console';
    $GLOBALS['trans'][3007] = 'N/A';
    $GLOBALS['trans'][3008] = 'E.G. %s';
    $GLOBALS['trans'][3009] = '%s and %s';
    $GLOBALS['trans'][3010] = 'View more';
    $GLOBALS['trans'][3011] = 'Go back';
    $GLOBALS['trans'][3012] = '%s left';
    $GLOBALS['trans'][3013] = 'Submit';
?>