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
    - Add system to allow banning users from logging in
    - Custom error reporting
    - Translations
    - Clean code!
*/

$StartTime = microtime(true);
$LastTime = $StartTime;
$StartMem = memory_get_usage();
$LastMem = $StartMem;

define('IN_SP', true);
define('SP_WEB_VERSION', '0.0.3');
define('SP_WEB_NAME', 'SourcePunish WebApp');
date_default_timezone_set('UTC');

/* Show PHP errors for development purposes */
error_reporting(E_ALL);
ini_set('display_errors', '1');

register_shutdown_function('ScriptShutdown');

/* Some definitions */
define('DATE_FORMAT', 'H:i - jS F Y');
define('DIR_ROOT',    dirname(dirname(__FILE__)).'/');
define('DIR_INCLUDE', DIR_ROOT.'includes/');
define('DIR_PAGES', DIR_INCLUDE.'pages/');
define('DIR_THEMES',  DIR_ROOT.'themes/');
define('DIR_TRANSLATIONS',  DIR_ROOT.'translations/');
$HTMLROOT = dirname($_SERVER['PHP_SELF']);
$HTMLROOT = str_replace('\\', '/', $HTMLROOT);
if(substr($HTMLROOT, -1, 1) != '/')
    $HTMLROOT .= '/';
if($HTMLROOT == '.')
    $HTMLROOT = '/';
define('HTML_ROOT', $HTMLROOT);
unset($HTMLROOT);
define('HTML_IMAGES', HTML_ROOT.'static/images/');
define('HTML_IMAGES_GAMES', HTML_IMAGES.'games/');
define('HTML_SCRIPTS', HTML_ROOT.'static/scripts/');
define('HTML_CSS', HTML_ROOT.'static/css/');
define('URL_PAGE', 'index.php');
define('URL_QUERY', '?q=');

/* Global Variable to cache some variables */
$GLOBALS['varcache'] = array();

/* Get the best IP address for the user */
$IPAddress = '';
if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $Explode = array_values(array_filter(explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'])));
    $IPAddress = end($Explode);
} else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
    $IPAddress = $_SERVER['REMOTE_ADDR'];
else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']))
    $IPAddress = $_SERVER['HTTP_CLIENT_IP'];

if($IPAddress != '' && IsValidIP($IPAddress))
    define('USER_ADDRESS', $IPAddress);
else
    define('USER_ADDRESS', 'UNKNOWN');
unset($IPAddress);

/* Load the configuration file */
require_once(DIR_INCLUDE.'config.php');
if(!isset($GLOBALS['config']))
    die('Error: Configuration(s) missing in file config.php');

/* Debugging message function */
function PrintDebug($Text, $Level = 1) {
    global $StartTime, $LastTime, $StartMem, $LastMem;
    if(isset($GLOBALS['config']['system']['printdebug']) && $GLOBALS['config']['system']['printdebug'] > 0 && $Level <= $GLOBALS['config']['system']['printdebug']) {
        $Time = microtime(true);
        $Mem = memory_get_usage();
        echo '<!-- DEBUG "'.$Text.'" TIME:'.number_format($Time-$StartTime, 11).'/'.number_format($Time-$LastTime, 11).' |  MEM:'.number_format($Mem).'B/'.number_format($Mem-$LastMem).'B -->'."\n";
        $LastTime = $Time;
        $LastMem = $Mem;
    }
}
PrintDebug('Config Loaded');

/* Load & connect to MySQL */
if(!isset($GLOBALS['config']['sql'], $GLOBALS['config']['sql']['host'], $GLOBALS['config']['sql']['username'], $GLOBALS['config']['sql']['password'], $GLOBALS['config']['sql']['database'], $GLOBALS['config']['sql']['prefix']))
    die('Error: SQL configuration(s) missing in file config.php'); 
require_once(DIR_INCLUDE.'class.sql.php');
$GLOBALS['sql'] = new SQL($GLOBALS['config']['sql']['host'], $GLOBALS['config']['sql']['username'], $GLOBALS['config']['sql']['password'], $GLOBALS['config']['sql']['database']);
PrintDebug('SQL Connected');
define('SQL_PREFIX', $GLOBALS['sql']->Escape($GLOBALS['config']['sql']['prefix']));
define('SQL_PUNISHMENTS', SQL_PREFIX.'punishments');
unset($GLOBALS['config']['sql']);

/* Load settings */
$GLOBALS['settings'] = array();
$SettingsQuery = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PREFIX.'settings');
while($Row = $GLOBALS['sql']->FetchArray($SettingsQuery)) {
    $GLOBALS['settings'][$Row['setting_name']] = $Row['setting_value'];
    switch($Row['setting_value']) {
        case 'true': $GLOBALS['settings'][$Row['setting_name']] = true; break;
        case 'false': $GLOBALS['settings'][$Row['setting_name']] = false; break;
    }
}
$GLOBALS['sql']->Free($SettingsQuery);
PrintDebug('Settings Loaded ('.count($GLOBALS['settings']).')');

/* Load Translations */
/* Always load EN translations and then overwrite with other */
if(!file_exists(DIR_TRANSLATIONS.'translations.en.php')) {
    die('ERROR! Cannot find base English translation file.');
}
require_once(DIR_TRANSLATIONS.'translations.en.php');
PrintDebug('Loaded base English translations');
/* Check site language */
if(isset($GLOBALS['settings']['site_lang']) && $GLOBALS['settings']['site_lang'] != '' && $GLOBALS['settings']['site_lang'] != 'en') {
    if(file_exists(DIR_TRANSLATIONS.'translations.'.$GLOBALS['settings']['site_lang'].'.php')) {
        require_once(DIR_TRANSLATIONS.'translations.'.$GLOBALS['settings']['site_lang'].'.php');
        PrintDebug('Site language loaded as \''.$GLOBALS['settings']['site_lang'].'\'');
    }
}

/* Steam */
require_once(DIR_INCLUDE.'class.steam.php');
$GLOBALS['steam'] = new Steam();
PrintDebug('Steam Class Loaded');

/* Session/Auth */
require_once(DIR_INCLUDE.'class.auth.php');
$GLOBALS['auth'] = new Auth();
$IsValidSession = $GLOBALS['auth']->ValidateSession();
if($IsValidSession) {
    if($GLOBALS['auth']->IsAdmin()) {
        define('USER_ADMIN', true);
        if($GLOBALS['auth']->HasAdminFlag($GLOBALS['settings']['auth_superadmin_flag']))
            define('USER_SUPERADMIN', true);
    }
    define('USER_LOGGEDIN', true);
    define('USER_AUTH', $GLOBALS['auth']->GetUser64());
}
if(!defined('USER_ADMIN')) define('USER_ADMIN', false);
if(!defined('USER_SUPERADMIN')) define('USER_SUPERADMIN', false);
if(!defined('USER_LOGGEDIN')) define('USER_LOGGEDIN', false);
if(!defined('USER_AUTH')) define('USER_AUTH', false);
PrintDebug('Auth Class Loaded & Checked as \''.$GLOBALS['auth']->GetUser64().'\'');

/* Theming */
$ThemeName = $GLOBALS['settings']['site_theme'];
if(!file_exists(DIR_THEMES.$ThemeName.'/theme.php')) {
    /* Log as an error ? */
    if(!file_exists(DIR_THEMES.'SourcePunish/theme.php'))
        die('ERROR! Cannot find user or default theme file.');
    else
        $ThemeName = 'SourcePunish';
}
define('THEME_CURRENT', $ThemeName); 
unset($ThemeName);
define('THEME_PATH', DIR_THEMES.THEME_CURRENT.'/');
define('HTML_THEME_PATH', HTML_ROOT.'themes/'.THEME_CURRENT.'/');
PrintDebug('Theme Selected as \''.THEME_CURRENT.'\'');

require_once(DIR_INCLUDE.'class.theming.php');
$GLOBALS['theme'] = new Theming();
PrintDebug('Theming Class Loaded');
/* Include default files and site theme */
require_once(DIR_THEMES.'default.php');
require_once(DIR_THEMES.'global.php');
require_once(THEME_PATH.'theme.php');
PrintDebug('Main Theme Files Loaded');

/* Subthemes */
$SubthemeName = '';
if(isset($GLOBALS['themeinfo']['subthemes']['enabled']) && $GLOBALS['themeinfo']['subthemes']['enabled'] == true) {
    if(isset($GLOBALS['settings']['site_theme_subtheme']) && $GLOBALS['settings']['site_theme_subtheme'] != '') {
        $SubthemeName = $GLOBALS['settings']['site_theme_subtheme'];
        if(!file_exists(THEME_PATH.'subthemes/'.$SubthemeName.'/subtheme.php')) {
            if(isset($GLOBALS['themeinfo']['subthemes']['required']) && $GLOBALS['themeinfo']['subthemes']['required'] == true && isset($GLOBALS['themeinfo']['subthemes']['default'])) {
                $SubthemeName = $GLOBALS['themeinfo']['subthemes']['default'];
                if(!file_exists(THEME_PATH.'subthemes/'.$SubthemeName.'/subtheme.php'))
                    die('ERROR! Cannot find user or default sub-theme file.');
            }
        }
    } else {
        if(isset($GLOBALS['themeinfo']['subthemes']['required']) && $GLOBALS['themeinfo']['subthemes']['required'] == true && isset($GLOBALS['themeinfo']['subthemes']['default'])) {
            $SubthemeName = $GLOBALS['themeinfo']['subthemes']['default'];
            if(!file_exists(THEME_PATH.'subthemes/'.$SubthemeName.'/subtheme.php'))
                die('ERROR! Cannot find user or default sub-theme file.');
        }
    }
}
if($SubthemeName != '') {
    define('SUBTHEME_CURRENT', $SubthemeName); 
    define('SUBTHEME_PATH', THEME_PATH.'subthemes/'.SUBTHEME_CURRENT.'/');
    define('HTML_SUBTHEME_PATH', HTML_THEME_PATH.'subthemes/'.SUBTHEME_CURRENT.'/');
    PrintDebug('Sub-theme Selected as \''.SUBTHEME_CURRENT.'\'');
    require_once(SUBTHEME_PATH.'subtheme.php');
    PrintDebug('Sub-Theme File Loaded');
}
unset($SubthemeName);

/* Functions */
function ScriptShutdown() {
    $GLOBALS['sql']->Close();
    PrintDebug('End, PEAK MEM:'.number_format(memory_get_peak_usage()).'B');
}
function IsSSL() {
    if(isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
        return true;
    } else if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
        return true;
    }
    return false;
}
function IsNum($Number) {
    if(preg_match('/^[0-9]+$/', $Number))
        return true;
    return false;
}
function IsValidIP($IPAddress, $Type = 'both') {
    if($Type == 'ipv4' || $Type == 'both') {
        if(preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $IPAddress))
            return true;
    }
    if($Type == 'ipv6' || $Type == 'both') {
        if(preg_match('/^(((?=.*(::))(?!.*\3.+\3))\3?|([\dA-F]{1,4}(\3|:\b|$)|\2))(?4){5}((?4){2}|(((2[0-4]|1\d|[1-9])?\d|25[0-5])\.?\b){4})\z$/i', $IPAddress))
            return true;
    }
    return false;
}
function HasAuthLevel($Level) {
    if($Level == 0)
        return true;
    else if($Level == 1 && !USER_LOGGEDIN)
        return true;
    else if($Level == 2 && USER_LOGGEDIN)
        return true;
    else if($Level == 3 && USER_ADMIN)
        return true;
    else if($Level == 4 && USER_SUPERADMIN)
        return true;
    return false;
}
function Redirect($URL = '') {
    if($URL == '')
        $URL = ParseURL('^');
    else
        $URL = ParseURL($URL);
    header('Location: '.$URL);
    die(ParseText('#TRANS_2009').': '.$URL);
}
function ParseURL($URL) {
    if(substr($URL, 0, 1) == '^') {
        if(substr($URL, 1) == 'index' || $URL == '^')
            return HTML_ROOT.URL_PAGE;
        return HTML_ROOT.URL_PAGE.URL_QUERY.substr($URL, 1);
    }
    return $URL;
}
function ParseText($Text, $Trans = true, $BBCode = false, $AllowHTML = false) {
    if(preg_match_all('/#TRANS_([0-9]{3,4})/', $Text, $Matches)) {
        foreach($Matches[1] as $Key => $Match) {
            if(isset($GLOBALS['trans'][(int)$Match])) {
                $Text = preg_replace('/#TRANS_'.$Match.'/', $GLOBALS['trans'][(int)$Match], $Text);
            }
        }
    }
    if(!$AllowHTML)
        $Text = htmlspecialchars($Text);
    if($BBCode) {
        $Text = preg_replace('#\[b\](.*?)\[/b\]#si', '<span class="bold">\1</span>', $Text);
        $Text = preg_replace('#\[u\](.*?)\[/u\]#si', '<span class="underscore">\1</span>', $Text);
        $Text = preg_replace('#\[i\](.*?)\[/i\]#si', '<span class="italic">\1</span>', $Text);
        $Text = preg_replace('#\[s\](.*?)\[/s\]#si', '<span class="strike">\1</strike>', $Text);
        $Text = preg_replace('#\[color=([\#a-f0-9]*?)\](.*?)\[/color\]#si', '<span style=\'color:\\1\'>\\2</span>', $Text);
        $Text = preg_replace('#\[br\]#si', '<br />', $Text);
        $Text = preg_replace('#\[center\](.*?)\[/center\]#si', '<div class="center">\1</div>', $Text);
        $Text = preg_replace('#\[img\]([\r\n]*)(?:([a-z0-9]*:\/{2}))?([a-z0-9\-_\/\.\+?&\#@:;\!=]*?)(\.(jpg|jpeg|gif|png))([\r\n]*)\[/img\]#sie', "'<img src=\'\\1\\2'.str_replace(array('?','&amp;','&','='),'','\\3').'\\4\' alt=\'\\1\\2'.str_replace(array('?','&amp;','&','='),'','\\3').'\\4\' />'", $Text);
        $Text = preg_replace('#\[url\]([\r\n]*)(?:([a-z0-9]*:\/{2}))?([a-z0-9\-_\/\.\+?&\#@:;\!=]*?)([\r\n]*)\[/url\]#sie', "'<a href=\"\\2\\3\" title=\"".ParseText('#TRANS_3002 ')."\\2\\3\" target=\"_blank\" rel=\"nofollow\">\\2\\3</a>'", $Text);
        $Text = preg_replace('#\[url=([\r\n]*)(?:([a-z0-9]*:\/{2}))?([a-z0-9\-_\/\.\+?&\#@:;\!=]*?)([\r\n]*)\](.*?)\[/url\]#sie', "'<a href=\"\\2\\3\" title=\"".ParseText('#TRANS_3002 ')."\\2\\3\" target=\"_blank\" rel=\"nofollow\">\\5</a>'", $Text);
    }
    return $Text;
}
function CustomPageExists($Ref) {
    $PageRef = $GLOBALS['sql']->Escape($Ref);
    $PageQuery = $GLOBALS['sql']->Query_Rows('SELECT page_ref FROM '.SQL_PREFIX.'pages WHERE page_ref=\''.$PageRef.'\' LIMIT 1');
    if($PageQuery == 1)
        return true;
    return false;
}
function GetCustomPage($Ref) {
    $PageRef = $GLOBALS['sql']->Escape($Ref);
    $PageQuery = $GLOBALS['sql']->Query_FetchArray('SELECT * FROM '.SQL_PREFIX.'pages WHERE page_ref=\''.$PageRef.'\' LIMIT 1');
    if(HasAuthLevel($PageQuery['page_auth'])) {
        $PageQuery['title'] = ParseText($PageQuery['page_title']);
        unset($PageQuery['page_title']);
        $PageQuery['content'] = ParseText($PageQuery['page_content'], true, ($PageQuery['page_format']==1?true:false), ($PageQuery['page_format']==2?true:false));
        unset($PageQuery['page_content'], $PageQuery['page_format']);
    } else {
        unset($PageQuery);
        $PageQuery['title'] = '';
        $PageQuery['content'] = ParseText('#TRANS_2010');
    }
    unset($PageQuery['page_auth']);
    return $PageQuery;
}
function GetServerInfo($GetServerInfo_ID) {
    if(isset($GLOBALS['varcache']['servers'][$GetServerInfo_ID]))
        return $GLOBALS['varcache']['servers'][$GetServerInfo_ID];
    if($GetServerInfo_ID == 0) {
        $GetServerInfo['name'] = ParseText('#TRANS_3006');
        $GetServerInfo['host'] = ParseText('#TRANS_3007');
        $GetServerInfo['ip'] = ParseText('#TRANS_3007');
        $GetServerInfo['mod']['id'] = ParseText('#TRANS_3007');
        $GetServerInfo['mod']['short'] = 'Web';
        $GetServerInfo['mod']['name'] = 'Web Panel';
        $GetServerInfo['mod']['image'] = 'web.png';
        $GLOBALS['varcache']['servers'][$GetServerInfo_ID] = $GetServerInfo;
        return $GetServerInfo;
    }
    $GetServerInfo_ID = $GLOBALS['sql']->Escape($GetServerInfo_ID);
    $GetServerInfo_Row = $GLOBALS['sql']->Query_FetchArray('SELECT * FROM '.SQL_PREFIX.'servers WHERE Server_ID=\''.$GetServerInfo_ID.'\' LIMIT 1');
    if(empty($GetServerInfo_Row)) {
        $GetServerInfo['name'] = 'Unknown';
        $GetServerInfo['host'] = 'N/A';
        $GetServerInfo['ip'] = 'N/A';
        $GetServerInfo['mod']['id'] = 'N/A';
        $GetServerInfo['mod']['short'] = 'N/A';
        $GetServerInfo['mod']['name'] = 'Unknown';
        $GetServerInfo['mod']['image'] = 'unknown.png';
        $GLOBALS['varcache']['servers'][$GetServerInfo_ID] = $GetServerInfo;
        return $GetServerInfo;
    }
    $GetServerInfo['host'] = $GetServerInfo_Row['Server_Host'];
    $GetServerInfo['ip'] = $GetServerInfo_Row['Server_IP'];
    $GetServerInfo['name'] = $GetServerInfo_Row['Server_Name'];
    $GetServerInfo['mod']['id'] = $GetServerInfo_Row['Server_Mod'];
    unset($GetServerInfo_Row);
    $GetServerInfoMod_row = $GLOBALS['sql']->Query_FetchArray('SELECT * FROM '.SQL_PREFIX.'server_mods WHERE Mod_ID=\''.$GetServerInfo['mod']['id'].'\' LIMIT 1');
    $GetServerInfo['mod']['short'] = $GetServerInfoMod_row['Mod_Short'];
    $GetServerInfo['mod']['name'] = $GetServerInfoMod_row['Mod_Name'];
    $GetServerInfo['mod']['image'] = $GetServerInfoMod_row['Mod_Image'];
    $GLOBALS['varcache']['servers'][$GetServerInfo_ID] = $GetServerInfo;
    return $GetServerInfo;
}
function PunishTime($Time, $Count = 1) {
    if($Time == -1)
        return ParseText('#TRANS_3007');
    else if($Time == 0)
        return ParseText('#TRANS_1141');
    $GetTime = TimeDiff($Time*60, true);
    return PrintTimeDiff($GetTime, $Count, true);
}
function TimeDiff($FTime, $Trans = true) {
    $Time = (int)$FTime;
    $TimeArray = array();

    if($Time > 31556900) {
        $TimeArray[($Trans?1142:'year')] = floor($Time / 31556900);
        $Time = $Time % 31556900;
    }
    if($Time > 2629740) {
        $TimeArray[($Trans?1144:'month')] = floor($Time / 2629740);
        $Time = $Time % 2629740;
    }
    if($Time > 604800) {
        $TimeArray[($Trans?1146:'week')] = floor($Time / 604800);
        $Time = $Time % 604800;
    }
    if($Time > 86400) {
        $TimeArray[($Trans?1148:'day')] = floor($Time / 86400);
        $Time = $Time % 86400;
    }
    if($Time > 3600) {
        $TimeArray[($Trans?1150:'hour')] = floor($Time / 3600);
        $Time = $Time % 3600;
    }
    if($Time > 60) {
        $TimeArray[($Trans?1152:'minute')] = floor($Time / 60);
        $Time = $Time % 60;
    }
    if($Time > 0) {
        $TimeArray[($Trans?1154:'second')] = $Time;
    }
    return $TimeArray;
}
function PrintTimeDiff($TimeAgo, $Count = 0, $Trans = true) {
    $TimeString = '';
    $i = 1;
    foreach($TimeAgo as $STime => $Time) {
        if($Time > 0) {
            if($i > 1)
                $TimeString .= ', ';
            if($Trans)
                $TimeString .= $Time.' '.(($Time>1)?ParseText('#TRANS_'.($STime+1)):ParseText('#TRANS_'.$STime));
            else
                $TimeString .= $Time.' '.(($Time>1)?$STime.'s':$STime);
            if($Count != 0 && $i >= $Count)
                return $TimeString;
            $i++;
        }
    }
    return $TimeString;
}
PrintDebug('End Core');
?>