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
if(preg_match('/core.php/i', $_SERVER['PHP_SELF'])) die('Access Denied!');

/* *TODO*
    - Validate date format?
*/

/* HTML root */
    if(isset($GLOBALS['config']['system']['path_html']) && !empty($GLOBALS['config']['system']['path_html']))
        $HTMLROOT = $GLOBALS['config']['system']['path_html'];
    else
        $HTMLROOT = dirname($_SERVER['PHP_SELF']);
    unset($GLOBALS['config']['system']['path_html']);
    $HTMLROOT = str_replace('\\', '/', $HTMLROOT);
    if($HTMLROOT == '.')
        $HTMLROOT = '/';
    else if(substr($HTMLROOT, -1, 1) != '/')
        $HTMLROOT .= '/';
    define('HTML_ROOT', $HTMLROOT);
    unset($HTMLROOT);

/* HTML paths */
    define('HTML_IMAGES', HTML_ROOT.'static/images/');
    define('HTML_IMAGES_GAMES', HTML_IMAGES.'games/');
    define('HTML_SCRIPTS', HTML_ROOT.'static/scripts/');
    define('HTML_CSS', HTML_ROOT.'static/css/');
    define('URL_PAGE', 'index.php');
    define('URL_QUERY', '?q=');

/* MySQL definitions */
    define('SQL_APPEALS', SQL_PREFIX.'appeals');
    define('SQL_NAVIGATION', SQL_PREFIX.'navigation');
    define('SQL_PAGES', SQL_PREFIX.'pages');
    define('SQL_PUNISHMENTS', SQL_PREFIX.'punishments');
    define('SQL_SERVERS', SQL_PREFIX.'servers');
    define('SQL_SERVER_MODS', SQL_PREFIX.'server_mods');
    define('SQL_SESSIONS', SQL_PREFIX.'sessions');

/* Format definitions */
    if(isset($GLOBALS['settings']['site_time_format']) && $GLOBALS['settings']['site_time_format'] != '')
        define('DATE_FORMAT', $GLOBALS['settings']['site_time_format']);
    else
        define('DATE_FORMAT', 'H:i - jS F Y');
    unset($GLOBALS['settings']['site_time_format']);

/* Flags for 'CheckVar' */
    define('SP_VAR_INT', 1); // Var should be an int
    define('SP_VAR_FLOAT', 2); // Var should be an int
    define('SP_VAR_NEGATIVE', 4); // Var can be negative
    define('SP_VAR_EMPTY', 8); // Var can be empty

/* Flags for 'class.steam' */
    define('SP_STEAM_ID', 1); // Old SteamID
    define('SP_STEAM_ID3', 2); // New SteamID3
    define('SP_STEAM_ID_BOTH', 4); // SteamID & SteamID3
    
/* User IP address */
    $IPAddress = '';
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $AddressExploded = array_values(array_filter(explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'])));
        $IPAddress = end($AddressExploded);
        unset($AddressExploded);
    } else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
        $IPAddress = $_SERVER['REMOTE_ADDR'];
    else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']))
        $IPAddress = $_SERVER['HTTP_CLIENT_IP'];

    if($IPAddress != '' && IsValidIP($IPAddress))
        define('USER_ADDRESS', $IPAddress);
    else
        define('USER_ADDRESS', 'UNKNOWN');
    unset($IPAddress);
?>