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
if(preg_match('/core.php/i', $_SERVER['PHP_SELF'])) die('Access Denied!');

/* *TODO*
    - Add system to allow banning users from logging in
    - Custom error reporting
    - Clean code! (With comments?)
    - Config for timezone
    - ParseText use flags?
    - IsValidIP to CheckVar?
    - Split translations into pages and add SP_LoadTranslations($Name) ?
*/

/* Site definitions */
    define('IN_SP', true);
    define('SP_WEB_VERSION', '0.0.4');
    define('SP_WEB_NAME', 'SourcePunish WebApp');

/* Global variable for caching */
    $GLOBALS['varcache'] = array();
    $GLOBALS['varcache']['debug']['starttime'] = microtime(true);
    $GLOBALS['varcache']['debug']['lasttime'] = $GLOBALS['varcache']['debug']['starttime'];
    $GLOBALS['varcache']['debug']['lastmem'] = memory_get_usage();

/* Set the time zone */
    if(function_exists('date_default_timezone_set'))
        date_default_timezone_set('UTC');
    else
        ini_set('date.timezone', 'UTC');

/* Test if the system is running 32bit PHP */
    if(PHP_INT_SIZE == 4)
        define('IS32BIT', true);
    else
        define('IS32BIT', false);

/* Set shutdown function */
    register_shutdown_function('SP_ScriptShutdown');

/* Load the configuration file */
    require_once('includes/config.php');
    if(!isset($GLOBALS['config']))
        die('Error: Configuration(s) missing in file config.php');

/* Show PHP errors for development purposes */
    if(isset($GLOBALS['config']['system']['phperrors']) && $GLOBALS['config']['system']['phperrors'] == true) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    } else {
        error_reporting(0);
        ini_set('display_errors', '0');
    }

/* PHP Paths */
    if(isset($GLOBALS['config']['system']['path_php']) && !empty($GLOBALS['config']['system']['path_php'])) {
        if(substr($GLOBALS['config']['system']['path_php'], -1) != '/' || substr($GLOBALS['config']['system']['path_php'], -1) != '\\')
            $GLOBALS['config']['system']['path_php'] .= '/';
        define('DIR_ROOT', $GLOBALS['config']['system']['path_php']);
    } else
        define('DIR_ROOT', dirname(dirname(__FILE__)).'/');
    unset($GLOBALS['config']['system']['path_php']);
    /* Define the paths */
    define('DIR_INCLUDE', DIR_ROOT.'includes/');
    define('DIR_CACHE', DIR_ROOT.'data/');
    define('DIR_PAGES', DIR_INCLUDE.'pages/');
    define('DIR_THEMES',  DIR_ROOT.'themes/');
    define('DIR_TRANSLATIONS',  DIR_ROOT.'translations/');

/* Are we serving an ajax request? */
    if(isset($_GET['ajax']) && $_GET['ajax'] == '1')
        define('AJAX', true);
    else
        define('AJAX', false);

/* Debugging message function */
    function PrintDebug($Text, $Level = 1) {
        if(!AJAX && isset($GLOBALS['config']['system']['printdebug']) && $GLOBALS['config']['system']['printdebug'] > 0 && $Level <= $GLOBALS['config']['system']['printdebug']) {
            $Time = microtime(true);
            $Mem = memory_get_usage();
            echo '<!-- DEBUG "'.$Text.'" TIME:'.number_format($Time-$GLOBALS['varcache']['debug']['starttime'], 11).'/'.number_format($Time-$GLOBALS['varcache']['debug']['lasttime'], 11).' |  MEM:'.number_format($Mem).'B/'.number_format($Mem-$GLOBALS['varcache']['debug']['lastmem']).'B -->'."\n";
            $GLOBALS['varcache']['debug']['lasttime'] = $Time;
            $GLOBALS['varcache']['debug']['lastmem'] = $Mem;
        }
    }
    PrintDebug('Config Loaded');

/* Load & connect to MySQL */
    /* Check the configs exist */
    if(!isset($GLOBALS['config']['sql'], $GLOBALS['config']['sql']['host'], $GLOBALS['config']['sql']['username'], $GLOBALS['config']['sql']['password'], $GLOBALS['config']['sql']['database'], $GLOBALS['config']['sql']['prefix']))
        die('Error: SQL configuration(s) missing in file config.php'); 

    /* Create the SQL object */
    require_once(DIR_INCLUDE.'class.sql.php');
    $GLOBALS['sql'] = new SQL($GLOBALS['config']['sql']['host'], $GLOBALS['config']['sql']['username'], $GLOBALS['config']['sql']['password'], $GLOBALS['config']['sql']['database']);
    PrintDebug('SQL Connected');

    /* Escape & set the SQL table prefix */
    define('SQL_PREFIX', $GLOBALS['sql']->Escape($GLOBALS['config']['sql']['prefix']));
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

/* Load definitions */
    require_once(DIR_INCLUDE.'definitions.php');
    PrintDebug('Definitions loaded');

/* Load translations */
    /* Always load EN translations and then overwrite with other */
    if(!file_exists(DIR_TRANSLATIONS.'translations.en.php'))
        die('ERROR! Cannot find base English translation file.');
    require_once(DIR_TRANSLATIONS.'translations.en.php');
    PrintDebug('Loaded base English translations');

    /* Check site language */
    if(isset($GLOBALS['settings']['site_lang']) && $GLOBALS['settings']['site_lang'] != '' && $GLOBALS['settings']['site_lang'] != 'en') {
        if(file_exists(DIR_TRANSLATIONS.'translations.'.$GLOBALS['settings']['site_lang'].'.php')) {
            require_once(DIR_TRANSLATIONS.'translations.'.$GLOBALS['settings']['site_lang'].'.php');
            PrintDebug('Site language loaded as \''.$GLOBALS['settings']['site_lang'].'\'');
        }
    }

/* Load & initiate Steam class */
    require_once(DIR_INCLUDE.'class.steam.php');
    $GLOBALS['steam'] = new Steam();
    PrintDebug('Steam Class Loaded');

/* Load & initiate session/auth class */
    require_once(DIR_INCLUDE.'class.auth.php');
    $GLOBALS['auth'] = new Auth();

    /* Check the current session */
    $IsValidSession = $GLOBALS['auth']->ValidateSession();
    if($IsValidSession) {
        define('USER_LOGGEDIN', true);
        if($GLOBALS['auth']->IsAdmin()) {
            define('USER_ADMIN', true);
            if($GLOBALS['auth']->HasAdminFlag($GLOBALS['settings']['auth_superadmin_flag']))
                define('USER_SUPERADMIN', true);
        }
    }

    /* Session definition defaults */
    if(!defined('USER_ADMIN')) define('USER_ADMIN', false);
    if(!defined('USER_SUPERADMIN')) define('USER_SUPERADMIN', false);
    if(!defined('USER_LOGGEDIN')) define('USER_LOGGEDIN', false);
    PrintDebug('Auth Class Loaded'.(USER_LOGGEDIN?' & Checked as \''.$GLOBALS['auth']->GetUser64().'\'':''));

/* Check & load main site theme */
    $ThemeName = $GLOBALS['settings']['site_theme'];

    /* Check the theme name is safe */
    if(!preg_match('/^[a-z0-9_\-\.]+$/i', $ThemeName))
        die('ERROR! Invalid theme file name.');

    /* Check the theme file exists */
    if(!file_exists(DIR_THEMES.$ThemeName.'/theme.php')) {
        /* Log as an error ? */
        /* Try the default theme */
        if(!file_exists(DIR_THEMES.'SourcePunish/theme.php'))
            die('ERROR! Cannot find user or default theme file.');
        else
            $ThemeName = 'SourcePunish';
    }

    /* Set theme definitions */
    define('THEME_CURRENT', $ThemeName); 
    unset($ThemeName);
    define('THEME_PATH', DIR_THEMES.THEME_CURRENT.'/');
    define('HTML_THEME_PATH', HTML_ROOT.'themes/'.THEME_CURRENT.'/');
    PrintDebug('Theme Selected as \''.THEME_CURRENT.'\'');

    /* Load * initiate theming class */
    require_once(DIR_INCLUDE.'class.theming.php');
    $GLOBALS['theme'] = new Theming();
    PrintDebug('Theming Class Loaded');

    /* Include default files and site theme */
    require_once(DIR_THEMES.'default.php');
    require_once(DIR_THEMES.'global.php');
    require_once(THEME_PATH.'theme.php');
    PrintDebug('Main Theme Files Loaded');

/* Check & load sub-theme */
    $SubthemeName = '';

    /* Does the current theme support sub-themes? */
    if(isset($GLOBALS['themeinfo']['subthemes']['enabled']) && $GLOBALS['themeinfo']['subthemes']['enabled'] == true) {
        if(isset($GLOBALS['settings']['site_theme_subtheme']) && $GLOBALS['settings']['site_theme_subtheme'] != '') {
            $SubthemeName = $GLOBALS['settings']['site_theme_subtheme'];

            /* Check the sub-theme name is safe */
            if(!preg_match('/^[a-z0-9_\-\.]+$/i', $SubthemeName))
                die('ERROR! Invalid sub-theme file name.');

            /* Check the sub-theme file exists */
            if(!file_exists(THEME_PATH.'subthemes/'.$SubthemeName.'/subtheme.php')) {
                /* Does the current theme require a sub-theme? */
                if(isset($GLOBALS['themeinfo']['subthemes']['required']) && $GLOBALS['themeinfo']['subthemes']['required'] == true && isset($GLOBALS['themeinfo']['subthemes']['default'])) {
                    $SubthemeName = $GLOBALS['themeinfo']['subthemes']['default'];

                    /* Check the default sub-theme name is safe */
                    if(!preg_match('/^[a-z0-9_\-\.]+$/i', $SubthemeName))
                        die('ERROR! Invalid default sub-theme file name.');

                    /* Check the default sub-theme file exists */
                    if(!file_exists(THEME_PATH.'subthemes/'.$SubthemeName.'/subtheme.php'))
                        die('ERROR! Cannot find user or default sub-theme file.');
                }
            }
        } else {
            if(isset($GLOBALS['themeinfo']['subthemes']['required']) && $GLOBALS['themeinfo']['subthemes']['required'] == true && isset($GLOBALS['themeinfo']['subthemes']['default'])) {
                $SubthemeName = $GLOBALS['themeinfo']['subthemes']['default'];

                /* Check the default sub-theme name is safe */
                if(!preg_match('/^[a-z0-9_\-\.]+$/i', $SubthemeName))
                    die('ERROR! Invalid default sub-theme file name.');

                /* Check the default sub-theme file exists */
                if(!file_exists(THEME_PATH.'subthemes/'.$SubthemeName.'/subtheme.php'))
                    die('ERROR! Cannot find user or default sub-theme file.');
            }
        }
    }

    /* Load sub-theme if set */
    if($SubthemeName != '') {
        /* set sub-theme definitons */
        define('SUBTHEME_CURRENT', $SubthemeName); 
        define('SUBTHEME_PATH', THEME_PATH.'subthemes/'.SUBTHEME_CURRENT.'/');
        define('HTML_SUBTHEME_PATH', HTML_THEME_PATH.'subthemes/'.SUBTHEME_CURRENT.'/');
        PrintDebug('Sub-theme Selected as \''.SUBTHEME_CURRENT.'\'');

        require_once(SUBTHEME_PATH.'subtheme.php');
        PrintDebug('Sub-Theme File Loaded');
    }
    unset($SubthemeName);

/* Main site functions */
    /* Function to run at the end of script execution */
    function SP_ScriptShutdown() {
        $GLOBALS['sql']->Close();
        PrintDebug('End, PEAK MEM:'.number_format(memory_get_peak_usage()).'B');
    }

    /* Check if website is using SSL */
    function IsSSL() {
        if(isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
            return true;
        } else if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
            return true;
        }
        return false;
    }

    /* Validate a variable using flags */ 
    function CheckVar($Var, $Flags = 0) {
        if(!($Flags & SP_VAR_EMPTY) && $Var == '')
            return false;
        if($Flags & SP_VAR_INT || $Flags & SP_VAR_FLOAT) {
            if($Flags & SP_VAR_INT && !preg_match('/^[-+]?[0-9]+$/', $Var))
                return false;
            if($Flags & SP_VAR_FLOAT && !preg_match('/^[-+]?[0-9]*\.?[0-9]+$/', $Var) )
                return false;
            if(!($Flags & SP_VAR_NEGATIVE) && $Var < 0)
                return false;
        }
        return true;
    }

    /* Check if variable is a number, to depreciate */
    function IsNum($Number) {
        if(preg_match('/^[0-9]+$/', $Number))
            return true;
        return false;
    }

    /* Check if input is a valid IP adress, should move to 'CheckVar'? */
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

    /* Check if user has given auth level */
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

    /* Redirect user to given URL */
    function Redirect($URL = '') {
        if($URL == '')
            $URL = ParseURL('^');
        else
            $URL = ParseURL($URL);
        header('Location: '.$URL);
        die(sprintf($GLOBALS['trans'][2009], $URL));
    }

    /* Make input HTML safe */
    function SpecialChars($Input) {
        $Return = '';
        if(is_array($Input)) {
            foreach($Input as $Key => $Value) {
                $Return[$Key] = SpecialChars($Value);
            }
        } else
            $Return = htmlspecialchars($Input);
        return $Return;
    }

    /* Parse internal URL */
    function ParseURL($URL) {
        if(substr($URL, 0, 1) == '^') {
            if(substr($URL, 1) == 'index' || $URL == '^')
                return HTML_ROOT.URL_PAGE;
            return HTML_ROOT.URL_PAGE.URL_QUERY.substr($URL, 1);
        }
        return $URL;
    }

    /* Parse text for translations, BBCodes etc */
    function ParseText($Text, $Trans = true, $BBCode = false, $AllowHTML = false) {
        if(preg_match_all('/#TRANS_([0-9]{3,4})/', $Text, $Matches)) {
            foreach($Matches[1] as $Key => $Match) {
                if(isset($GLOBALS['trans'][(int)$Match])) {
                    $Text = preg_replace('/#TRANS_'.$Match.'/', $GLOBALS['trans'][(int)$Match], $Text);
                }
            }
        }
        if(!$AllowHTML)
            $Text = SpecialChars($Text);
        if($BBCode) {
            $Text = preg_replace('#\[b\](.*?)\[/b\]#si', '<span class="bold">\1</span>', $Text);
            $Text = preg_replace('#\[u\](.*?)\[/u\]#si', '<span class="underscore">\1</span>', $Text);
            $Text = preg_replace('#\[i\](.*?)\[/i\]#si', '<span class="italic">\1</span>', $Text);
            $Text = preg_replace('#\[s\](.*?)\[/s\]#si', '<span class="strike">\1</strike>', $Text);
            $Text = preg_replace('#\[color=([\#a-f0-9]*?)\](.*?)\[/color\]#si', '<span style=\'color:\\1\'>\\2</span>', $Text);
            $Text = preg_replace('#\[br\]#si', '<br />', $Text);
            $Text = preg_replace('#\[center\](.*?)\[/center\]#si', '<div class="center">\1</div>', $Text);
            $Text = preg_replace('#\[img\]([\r\n]*)(?:([a-z0-9]*:\/{2}))?([a-z0-9\-_\/\.\+?&\#@:;\!=]*?)(\.(jpg|jpeg|gif|png))([\r\n]*)\[/img\]#sie', "'<img src=\'\\1\\2'.str_replace(array('?','&amp;','&','='),'','\\3').'\\4\' alt=\'\\1\\2'.str_replace(array('?','&amp;','&','='),'','\\3').'\\4\' />'", $Text);
            $Text = preg_replace('#\[url\]([\r\n]*)(?:([a-z0-9]*:\/{2}))?([a-z0-9\-_\/\.\+?&\#@:;\!=]*?)([\r\n]*)\[/url\]#sie', "'<a href=\"\\2\\3\" title=\"".sprintf($GLOBALS['trans'][3002], "\\2\\3")."\" target=\"_blank\" rel=\"nofollow\">\\2\\3</a>'", $Text);
            $Text = preg_replace('#\[url=([\r\n]*)(?:([a-z0-9]*:\/{2}))?([a-z0-9\-_\/\.\+?&\#@:;\!=]*?)([\r\n]*)\](.*?)\[/url\]#sie', "'<a href=\"\\2\\3\" title=\"".sprintf($GLOBALS['trans'][3002], "\\2\\3")."\" target=\"_blank\" rel=\"nofollow\">\\5</a>'", $Text);
        }
        return $Text;
    }
    
    /* Parse a user input and remove unneeded space, characters, html etc */
    function ParseUserInput($Text, $Limit = 0, $Flags = 0) {
        if(!($Flags & SP_INPUT_NOTRIM))
            $Text = trim($Text);
        if($Limit > 0)
            $Text = substr($Text, 0, $Limit);
        if(!($Flags & SP_INPUT_HTML))
            $Text = SpecialChars($Text);
        if($Flags & SP_INPUT_ESCAPE)
            $Text = $GLOBALS['sql']->Escape($Text);

        return $Text;
    }

    /* Get GeoIP country information for IP address */
    function SP_GeoIPCountry($IP) {
        if(!IsValidIP($IP, 'ipv4'))
            return false;
        $IP = $GLOBALS['sql']->Escape($IP);
        $CountryInfo = array();
        $GeoIPQuery = $GLOBALS['sql']->Query_FetchArray('SELECT geoip_country_code, geoip_country FROM '.SQL_GEOIP.' WHERE INET_ATON(\''.$IP.'\') BETWEEN geoip_locid_start AND geoip_locid_end LIMIT 1');
        if(empty($GeoIPQuery) || !isset($GeoIPQuery['geoip_country_code'], $GeoIPQuery['geoip_country']))
            return false;
        $CountryInfo['country'] = $GeoIPQuery['geoip_country'];
        $CountryInfo['country_code'] = $GeoIPQuery['geoip_country_code'];
        $CountryInfo['country_flag'] = HTML_IMAGES_FLAGS.strtolower($CountryInfo['country_code']).'.png';
        return $CountryInfo;
    }

    /* Format & build a tabling containing a punishment list */
    function SP_BuildPunishTable($Rows, $Class = 'table-punish', $ID = '') {
        $Table = array('headings'=>array(), 'rows'=>array(), 'class'=>$Class, 'id'=>$ID);
        $Table['headings'] = array(
            array('content'=>ucfirst($GLOBALS['trans'][1100]), 'class'=>'col-date'),
            array('content'=>ucfirst($GLOBALS['trans'][1101]), 'class'=>'col-server'),
            array('content'=>ucfirst($GLOBALS['trans'][1102]), 'class'=>'col-player'),
            array('content'=>ucfirst($GLOBALS['trans'][1103]), 'class'=>'col-type'),
            array('content'=>ucfirst($GLOBALS['trans'][1104]), 'class'=>'col-reason'),
            array('content'=>ucfirst($GLOBALS['trans'][1105]), 'class'=>'col-length')
        );
        foreach($Rows as $Row) {
            $Server = SP_GetServerInfo($Row['Punish_Server_ID']);
            $ATime = array();
            $Time = SP_LengthString($Row['Punish_Length']);
            if($Row['UnPunish'] == 1) {
                $ATime = array('content'=>ucfirst($GLOBALS['trans'][1140]), 'custom'=>'title="'.ucfirst($GLOBALS['trans'][1140]).'"', 'class'=>'removed');
            } else if(($Row['Punish_Time']+($Row['Punish_Length']*60) < time()) && $Row['Punish_Length'] > 0) {
                $ATime = array('content'=>$Time, 'custom'=>'title="'.ucfirst($GLOBALS['trans'][1139]).'"', 'class'=>'expired');
            } else if(($Row['Punish_Length'] == 0 && $Row['Punish_Type'] == 'kick') || $Row['Punish_Length'] == -1) {
                $ATime = array('content'=>SP_LengthString(-1), 'class'=>'notapplicable');
            } else if($Row['Punish_Length'] == 0) {
                $ATime = array('content'=>$Time, 'class'=>'permanent');
            } else {
                $ATime = array('content'=>$Time, 'custom'=>'title="'.ucfirst($GLOBALS['trans'][1158]).'"', 'class'=>'active');
            }
            unset($Time);
            $Table['rows'][] = array('cols'=>array(
                array('content'=>sprintf($GLOBALS['trans'][3003], ucwords(SP_PrintTimeDiff(SP_TimeDiff(time()-$Row['Punish_Time']), 1))), 'custom'=>'title="'.date(DATE_FORMAT, $Row['Punish_Time']).'"'),
                array('content'=>'<img alt="'.$Server['mod']['short'].'" title="'.$Server['name'].'" src="'.HTML_IMAGES_GAMES.$Server['mod']['image'].'" />'),
                array('content'=>SpecialChars($Row['Punish_Player_Name'])),
                array('content'=>SpecialChars(ucwords($Row['Punish_Type']))),
                array('content'=>SpecialChars($Row['Punish_Reason'])),
                $ATime
            ), 'custom'=>'data-pid="'.$Row['Punish_ID'].'"');
            unset($ATime);
        }
        return $GLOBALS['theme']->BuildTable($Table);
    }

    /* Check if a custom page exists */
    function SP_CustomPageExists($Ref) {
        $PageRef = $GLOBALS['sql']->Escape($Ref);
        $PageQuery = $GLOBALS['sql']->Query_Rows('SELECT page_ref FROM '.SQL_PAGES.' WHERE page_ref=\''.$PageRef.'\' LIMIT 1');
        if($PageQuery == 1)
            return true;
        return false;
    }

    /* Get the content of a custom page */
    function SP_GetCustomPage($Ref) {
        $PageRef = $GLOBALS['sql']->Escape($Ref);
        $PageQuery = $GLOBALS['sql']->Query_FetchArray('SELECT * FROM '.SQL_PAGES.' WHERE page_ref=\''.$PageRef.'\' LIMIT 1');
        if(HasAuthLevel($PageQuery['page_auth'])) {
            $PageQuery['title'] = ParseText($PageQuery['page_title']);
            unset($PageQuery['page_title']);
            $PageQuery['content'] = ParseText($PageQuery['page_content'], true, ($PageQuery['page_format']==1?true:false), ($PageQuery['page_format']==2?true:false));
            unset($PageQuery['page_content'], $PageQuery['page_format']);
        } else {
            unset($PageQuery);
            $PageQuery['title'] = '';
            $PageQuery['content'] = $GLOBALS['trans'][2010];
        }
        unset($PageQuery['page_auth']);
        return $PageQuery;
    }

    /* Get information on a given server */
    function SP_GetServerInfo($ServerID, $ReturnUnknow = true) {
        if(isset($GLOBALS['varcache']['servers'][$ServerID]))
            return $GLOBALS['varcache']['servers'][$ServerID];
        if($ServerID == 0) {
            $GetServerInfo['name'] = $GLOBALS['trans'][3006];
            $GetServerInfo['host'] = $GLOBALS['trans'][3007];
            $GetServerInfo['ip'] = $GLOBALS['trans'][3007];
            $GetServerInfo['mod']['id'] = $GLOBALS['trans'][3007];
            $GetServerInfo['mod']['short'] = 'Web';
            $GetServerInfo['mod']['name'] = 'Web Panel';
            $GetServerInfo['mod']['image'] = 'web.png';
            $GLOBALS['varcache']['servers'][$ServerID] = $GetServerInfo;
            return $GetServerInfo;
        }
        $ServerID = $GLOBALS['sql']->Escape($ServerID);
        $GetServerInfo_Row = $GLOBALS['sql']->Query_FetchArray('SELECT s.*, m.* FROM '.SQL_SERVERS.' s LEFT JOIN '.SQL_SERVER_MODS.' m ON m.Mod_ID = s.Server_Mod WHERE s.Server_ID=\''.$ServerID.'\' LIMIT 1');
        if(empty($GetServerInfo_Row)) {
            if(!$ReturnUnknow)
                return false;
            $GetServerInfo['name'] = 'Unknown';
            $GetServerInfo['host'] = 'N/A';
            $GetServerInfo['ip'] = 'N/A';
            $GetServerInfo['mod']['id'] = 'N/A';
            $GetServerInfo['mod']['short'] = 'N/A';
            $GetServerInfo['mod']['name'] = 'Unknown';
            $GetServerInfo['mod']['image'] = 'unknown.png';
            $GLOBALS['varcache']['servers'][$ServerID] = $GetServerInfo;
            return $GetServerInfo;
        }
        $GetServerInfo['host'] = $GetServerInfo_Row['Server_Host'];
        $GetServerInfo['ip'] = $GetServerInfo_Row['Server_IP'];
        $GetServerInfo['name'] = $GetServerInfo_Row['Server_Name'];
        $GetServerInfo['mod']['id'] = $GetServerInfo_Row['Server_Mod'];
        if(!empty($GetServerInfo_Row['Mod_ID'])) {
            $GetServerInfo['mod']['short'] = $GetServerInfo_Row['Mod_Short'];
            $GetServerInfo['mod']['name'] = $GetServerInfo_Row['Mod_Name'];
            $GetServerInfo['mod']['image'] = $GetServerInfo_Row['Mod_Image'];
        } else {
            $GetServerInfo['mod']['short'] = 'N/A';
            $GetServerInfo['mod']['name'] = 'Unknown';
            $GetServerInfo['mod']['image'] = 'unknown.png';
        }
        $GLOBALS['varcache']['servers'][$ServerID] = $GetServerInfo;
        return $GetServerInfo;
    }

    /* Return server IP & port from an address string */
    function SP_GetAddressFromString($Address) {
        $Addr['port'] = 27015;
        $Addr['address'] = $Address;
        /* Get port number */
        if(strpos($Address, ':') !== false) {
            $IPTMP = explode(':', $Address, 2);
            $Addr['address'] = $IPTMP[0];
            if(IsNum((int)$IPTMP[1]))
                $Addr['port'] = (int)$IPTMP[1];
        }
        return $Addr;
    }

    /* Get formatted punishment length string */
    function SP_LengthString($Time, $Count = 1) {
        if($Time == -1)
            return ucfirst($GLOBALS['trans'][3007]);
        else if($Time == 0)
            return ucfirst($GLOBALS['trans'][1141]);
        $GetTime = SP_TimeDiff($Time*60, true);
        return SP_PrintTimeDiff($GetTime, $Count, true);
    }

    /* Time ago calculation */ 
    function SP_TimeDiff($FTime, $Trans = true) {
        $Time = (int)$FTime;
        $TimeArray = array();

        if($Time < 1) {
            $TimeArray[($Trans?1154:'second')] = 0;
            return $TimeArray;
        }

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

    /* Print 'TimeDiff' as human readable time */
    function SP_PrintTimeDiff($TimeAgo, $Count = 0, $Trans = true) {
        $TimeString = '';
        $i = 1;
        if($Count < 0) {
            if(abs($Count) >= count($TimeAgo))
                $Count = 1;
            else
                $Count = count($TimeAgo) + $Count;
        }
        foreach($TimeAgo as $STime => $Time) {
            /*f($Time > 0) {*/
            if(true) {
                if($i > 1)
                    $TimeString .= ', ';
                if($Trans)
                    $TimeString .= $Time.' '.(($Time>1 || $Time==0)?$GLOBALS['trans'][$STime+1]:$GLOBALS['trans'][$STime]);
                else
                    $TimeString .= $Time.' '.(($Time>1 || $Time==0)?$STime.'s':$STime);
                if($Count != 0 && $i >= $Count)
                    return ucfirst($TimeString);
                $i++;
            }
        }
        return $TimeString;
    }

    /* Get appeal status text */
    function SP_AppealStatusText($StatusCode) {
        $Text = '';
        switch($StatusCode) {
            case 0: $Text = $GLOBALS['trans'][1505]; break;
            case 1: $Text = $GLOBALS['trans'][1506]; break;
            case 2: $Text = $GLOBALS['trans'][1507]; break;
            case 3: $Text = $GLOBALS['trans'][1508]; break;
        }
        return $Text;
    }

/* Finial core debug message */
    PrintDebug('End Core');
?>