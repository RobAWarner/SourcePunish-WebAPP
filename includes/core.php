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

/*{{TODO}}*/
/*
    - Set timezone to user specific
    - Validate/Sanitise paths for theme etc
*/

/* Site definitions */
    define('SP_LOADED', true);
    define('SP_WEBAPP_NAME', 'SourcePunish WebApp');
    define('SP_WEBAPP_VERSION', '0.1.0');
    define('SP_WEBAPP_URL', 'https://SourcePunish.net');
    define('SP_WEBAPP_URL_ERROR', 'https://SourcePunish.net/help/errors/%s');

    $GlobalCache = array();

/* Define the current operating directory */
    define('DIR_ROOT', dirname(dirname(__FILE__)).'/');

/* Are we serving an ajax request? */
    if((isset($_GET['ajax']) && $_GET['ajax'] == '1') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')))
        define('AJAX_REQUEST', true);
    else
        define('AJAX_REQUEST', false);

/* Set time zone  */
    if(function_exists('date_default_timezone_set'))
        date_default_timezone_set('UTC');
    else
        ini_set('date.timezone', 'UTC');

/* Set script shutdown function */
    register_shutdown_function('ScriptShutdown');

/* Load the error handler */
    if(file_exists(DIR_ROOT.'includes/class.error_handler.php'))
        require_once(DIR_ROOT.'includes/class.error_handler.php');
    else
        die('SourcePunish encountered a fatal error. Unable to load error handler "includes/class.error_handler.php". See more information and potential fixes for: <a href="'.sprintf(SP_WEBAPP_URL_ERROR, 'file.missing').'" title="See more about this error" target="_blank">file.missing</a>.');

/* Load the config file */
    SP_Require(DIR_ROOT.'includes/config.php');

    /* Check configurations are loaded */
        if(!isset($GLOBALS['config']) || isset($GLOBALS['new-install']))
            throw new SiteError('file.config.empty');
    
/* Show PHP errors for development purposes */
    if(isset($GLOBALS['config']['system']['phperrors']) && $GLOBALS['config']['system']['phperrors'] == true) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    } else {
        error_reporting(0);
        ini_set('display_errors', '0');
    }

/* Load & connect to MySQL */
    /* Check the configs exist */
    if(!isset($GLOBALS['config']['sql'], $GLOBALS['config']['sql']['host'], $GLOBALS['config']['sql']['username'], $GLOBALS['config']['sql']['password'], $GLOBALS['config']['sql']['database'], $GLOBALS['config']['sql']['prefix']))
        throw new SiteError('file.config.data', 'Missing MySQL server information');

    /* Attempt to load the SQL class */
    SP_Require(DIR_ROOT.'includes/class.sql.php');

    /* Create the SQL object */
    $SQL = new SQL($GLOBALS['config']['sql']['host'], $GLOBALS['config']['sql']['username'], $GLOBALS['config']['sql']['password'], $GLOBALS['config']['sql']['database']);

    /* Escape & set the SQL table prefix */
    define('SQL_PREFIX', $SQL->Escape($GLOBALS['config']['sql']['prefix']));
    unset($GLOBALS['config']['sql']);

/* Load the definitions file */
    SP_Require(DIR_ROOT.'includes/definitions.php');

/* Load settings from MySQL */
    $Settings = array();
    $SettingsQuery = $SQL->Query('SELECT * FROM '.SQL_SETTINGS);
    while($SettingsRow = $SQL->FetchArray($SettingsQuery)) {
        $Settings[$SettingsRow['Setting_Name']] = $SettingsRow['Setting_Value'];
    }
    $SQL->Free($SettingsQuery);

/* Attempt to load the translations class */
    SP_Require(DIR_INCLUDE.'class.translations.php');
    
    /* Create the Translations object */
    $Trans = new Translations(DIR_TRANSLATIONS, (isset($Settings['site_language']) && strlen($Settings['site_language']) >= 2) ? $Settings['site_language'] : 'en');

    /* Load the base translations */
    $Trans->Load('base', true);

/* Attempt to load the auth class */


/* Attempt to load the user class */
    SP_Require(DIR_INCLUDE.'class.user.php');
    
    /* Create the User object */
    $User = new User();

/* Attempt to load the theme class */
    SP_Require(DIR_INCLUDE.'class.theme.php');
    
    /* Create the User object */
    $ThemeName = 'SourcePunish';
    $Theme = new Theme(DIR_THEMES, HTML_THEMES, $ThemeName);
    
    /* Create theme definitions */
    define('THEME_NAME', $Theme->Name); // VAL
    define('DIR_THEME', $Theme->Path);
    define('HTML_THEME', $Theme->HTMLPath);

/*************************
|    System Functions    |
*************************/
    
    /* Custom require function */
    function SP_Require($File) {
        if(file_exists($File))
            return require_once($File);
        else
            throw new SiteError('file.missing', 'File "'.$File.'"');
    }

    /* Shutdown Function */
    function ScriptShutdown() {
        global $SQL;
        /* Ensure MySQL connection gets closed */
        if(isset($SQL) && method_exists($SQL, 'Close'))
            $SQL->Close();

        /* Use error handler to check for a fatal error */
        if(class_exists('PHPError', false))
            PHPError::CheckForFatality();
    }

    /* Validate a variable */ 
    function CheckVar($Var, $Flags = 0) {
        /* Int / float */
        if($Flags & SP_VAR_INT || $Flags & SP_VAR_FLOAT) {
            if($Flags & SP_VAR_INT && !preg_match('/^[-+]?[0-9]+$/', $Var))
                return false;
            if($Flags & SP_VAR_FLOAT && !preg_match('/^[-+]?[0-9]*\.?[0-9]+$/', $Var) )
                return false;
            if(!($Flags & SP_VAR_NEGATIVE) && (float)$Var < 0)
                return false;
            return true;
        }
        /* IP's */
        if((($Flags & SP_VAR_IP_V4) || ($Flags & SP_VAR_IP_BOTH)) && filter_var($Var, FILTER_VALIDATE_IP, array('flags'=>FILTER_FLAG_IPV4)) !== false)
            return true;
        if((($Flags & SP_VAR_IP_V6) || ($Flags & SP_VAR_IP_BOTH)) && filter_var($Var, FILTER_VALIDATE_IP, array('flags'=>FILTER_FLAG_IPV6)) !== false)
            return true;
        
        return false;
    }

    /* Make input HTML safe */
    function SpecialChars($Input) {
        $Return = '';
        if(is_array($Input)) {
            foreach($Input as $Key => $Value) {
                $Return[$Key] = SpecialChars($Value);
            }
        } else
            $Return = htmlspecialchars($Input, ENT_QUOTES | ENT_HTML401, 'UTF-8');
        return $Return;
    }

    /* Parse internal URL */
    function ParseURL($URL, $Params = array()) {
        if(substr($URL, 0, 1) == '^') {
            if(substr($URL, 1) == 'index' || $URL == '^')
                return HTML_ROOT.URL_PAGE;

            $URL = HTML_ROOT.URL_PAGE.URL_QUERY.substr($URL, 1);
        }
        if(is_array($Params) && !empty($Params))
            $URL .= (strpos($URL, '?')===false?'?':'&').http_build_query($Params);

        return $URL;
    }

    /**/
    function ParseText($Text) {
        global $Trans;
        /* Translations */
        if(preg_match_all('/@T:([a-z0-9-_]+[.][a-z0-9-_.]+)/i', $Text, $Matches)) {
            foreach($Matches[1] as $Key => $Match) {
                $Text = preg_replace('/@T:'.$Match.'/', $Trans->T($Match), $Text);
            }
        }

        $Text = trim($Text);
        return $Text;
    }

    /* Redirect page to given URL */
    function Redirect($URL = '^', $Params = array()) {
        $URL = ParseURL($URL, $Params);
        header('Location: '.$URL);
        if(isset($Trans) && method_exists($Trans, 'T'))
            die($Trans->T('base.redirect', array('URL'=>$URL)));
        else
            die('Redirecting to: '.$URL);
    }

/******************************
|    Application Functions    |
******************************/

    /* Get GeoIP country information for IP address */
    function SP_GeoIPCountry($IP) {
        global $SQL;
        if(!CheckVar($IP, SP_VAR_IP_V4))
            return false;
        $CountryInfo = array();
        $IPLong = $SQL->Escape(sprintf("%u", ip2long($IP)));
        $GeoIPQuery = $SQL->Query_FetchArray('SELECT Geoip_Country_Code, Geoip_Country FROM '.SQL_GEOIP.' FORCE INDEX(Geoip_Locid_Start) WHERE Geoip_Locid_Start <= '.$IPLong.' AND Geoip_Locid_End >= '.$IPLong.' LIMIT 1');
        if(empty($GeoIPQuery) || !isset($GeoIPQuery['Geoip_Country_Code'], $GeoIPQuery['Geoip_Country']))
            return false;
        $CountryInfo['country'] = $GeoIPQuery['Geoip_Country'];
        $CountryInfo['country_code'] = $GeoIPQuery['Geoip_Country_Code'];
        $CountryInfo['country_flag'] = HTML_IMAGES_FLAGS.strtolower($CountryInfo['country_code']).'.png';
        return $CountryInfo;
    }

    /* Time ago calculation */ 
    function SP_TimeDiff($Time) {
        $Time = (int)$Time;
        $TimeArray = array();

        if($Time < 1) {
            $TimeArray['second'] = 0;
            return $TimeArray;
        }

        if($Time > 31556900) {
            $TimeArray['year'] = floor($Time / 31556900);
            $Time = $Time % 31556900;
        }
        if($Time > 2629740) {
            $TimeArray['month'] = floor($Time / 2629740);
            $Time = $Time % 2629740;
        }
        if($Time > 604800) {
            $TimeArray['week'] = floor($Time / 604800);
            $Time = $Time % 604800;
        }
        if($Time > 86400) {
            $TimeArray['day'] = floor($Time / 86400);
            $Time = $Time % 86400;
        }
        if($Time > 3600) {
            $TimeArray['hour'] = floor($Time / 3600);
            $Time = $Time % 3600;
        }
        if($Time > 60) {
            $TimeArray['minute'] = floor($Time / 60);
            $Time = $Time % 60;
        }
        if($Time > 0) {
            $TimeArray['second'] = $Time;
        }
        return $TimeArray;
    }

    /* Print 'TimeDiff' as human readable time */
    function SP_PrintTimeDiff($TimeAgo, $Count = 0) {
        global $Trans;
        $TimeString = '';
        $i = 1;
        if($Count < 0) {
            if(abs($Count) >= count($TimeAgo))
                $Count = 1;
            else
                $Count = count($TimeAgo) + $Count;
        }
        foreach($TimeAgo as $STime => $Time) {
            if($i > 1)
                $TimeString .= ', ';
            $TimeString .= (($Time>1 || $Time==0)?$Trans->t('time.'.$STime.'.plural', array('time'=>$Time)):$Trans->t('time.'.$STime, array('time'=>$Time)));
            if($Count != 0 && $i >= $Count)
                return ucfirst($TimeString);
            $i++;
        }
        return $TimeString;
    }

    /* Get formatted punishment length string */
    function SP_LengthString($Time, $Count = 1) {
        global $Trans;
        if($Time == -1)
            return ucfirst($Trans->t('base.na'));
        else if($Time == 0)
            return ucfirst($Trans->t('time.permanent'));

        $GetTime = SP_TimeDiff($Time*60);
        return SP_PrintTimeDiff($GetTime, $Count);
    }

    /* Get information on a given server */
    function SP_GetServerInfo($ServerID, $ReturnUnknow = true) {
        global $GlobalCache, $SQL, $Trans;
        if(isset($GlobalCache['servers'][$ServerID]))
            return $GlobalCache['servers'][$ServerID];
        if($ServerID == 0) {
            $GetServerInfo['name'] = $Trans->t('server.web');
            $GetServerInfo['host'] = $Trans->t('base.na');
            $GetServerInfo['ip'] = $Trans->t('base.na');
            $GetServerInfo['mod']['id'] = $Trans->t('base.na');
            $GetServerInfo['mod']['short'] = $Trans->t('server.web.short');
            $GetServerInfo['mod']['name'] = $Trans->t('server.web');
            $GetServerInfo['mod']['image'] = 'web.png';
            $GlobalCache['servers'][$ServerID] = $GetServerInfo;
            return $GetServerInfo;
        }
        $ServerID = $SQL->Escape($ServerID);
        $GetServerInfo_Row = $SQL->Query_FetchArray('SELECT s.*, m.* FROM '.SQL_SERVERS.' s LEFT JOIN '.SQL_SERVER_MODS.' m ON m.Mod_ID = s.Server_Mod WHERE s.Server_ID = \''.$ServerID.'\' LIMIT 1');
        if(empty($GetServerInfo_Row)) {
            if(!$ReturnUnknow)
                return false;
            $GetServerInfo['name'] = 'Unknown';
            $GetServerInfo['host'] = $Trans->t('base.na');
            $GetServerInfo['ip'] = $Trans->t('base.na');
            $GetServerInfo['mod']['id'] = $Trans->t('base.na');
            $GetServerInfo['mod']['short'] = $Trans->t('base.na');
            $GetServerInfo['mod']['name'] = ucfirst($Trans->t('base.na'));
            $GetServerInfo['mod']['image'] = 'unknown.png';
            $GlobalCache['servers'][$ServerID] = $GetServerInfo;
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
        $GlobalCache['servers'][$ServerID] = $GetServerInfo;
        return $GetServerInfo;
    }

    /* Check if a custom page exists */
    function SP_CustomPageExists($Ref) {
        global $SQL;
        $PageQuery = $SQL->Query_Rows('SELECT 1 FROM '.SQL_PAGES.' WHERE page_ref=\''.$SQL->Escape($Ref).'\' LIMIT 1');
        if($PageQuery == 1)
            return true;
        return false;
    }

    /* Get the content of a custom page */
    function SP_GetCustomPage($Ref) {
        global $SQL, $Trans, $User;
        $PageRef = $SQL->Escape($Ref);
        $PageQuery = $SQL->Query_FetchArray('SELECT * FROM '.SQL_PAGES.' WHERE page_ref=\''.$PageRef.'\' LIMIT 1');
        if(empty($PageQuery['Page_Permission']) || (empty($PageQuery['Page_Permission']) && $User->Has($PageQuery['Page_Permission']))) {
            $PageQuery['title'] = ParseText($PageQuery['Page_Title']);
            unset($PageQuery['Page_Title']);
            $PageQuery['text'] = ParseText($PageQuery['Page_Content'], true, ($PageQuery['Page_Format']==1?true:false), ($PageQuery['Page_Format']==2?true:false));
            unset($PageQuery['Page_Content'], $PageQuery['Page_Format']);
        } else {
            unset($PageQuery);
            $PageQuery['title'] = '';
            $PageQuery['text'] = $Trans->t('base.nopermission');
        }
        unset($PageQuery['Page_Permission']);
        return $PageQuery;
    }
?>