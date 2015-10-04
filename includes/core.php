<?php
/*{{BOILER}}*/

/*{{CORE_ACCESS}}*/

/*{{TODO}}*/
/*
    - Set timezone to user specific
    - Validate/Sanitise paths for theme etc
*/

/* Site definitions */
    define('SP_LOADED', true);
    define('SP_WEBAPP_NAME', 'SourcePunish WebApp');
    define('SP_WEBAPP_VERSION', '0.2.1');
    define('SP_WEBAPP_URL', 'https://SourcePunish.net');
    define('SP_WEBAPP_URL_ERROR', 'https://SourcePunish.net/help/errors/%s');

    $GlobalCache = array();

/* Define correct file path separator for the OS */
    if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        define('SP_PS', '\\');
    else
        define('SP_PS', '/');

/* Define the current operating directory */
    define('DIR_ROOT', FilePath(dirname(dirname(__FILE__))));

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

/* Load the error handler */
    require_once(DIR_ROOT.'includes/class.error_handler.php');

/* Set script shutdown function */
    register_shutdown_function('ScriptShutdown');

/* Load the config file */
    require_once(DIR_ROOT.'includes/config.php');

    /* Check configurations are loaded */
        if(!isset($GLOBALS['config']) || isset($GLOBALS['new-install']))
            throw new SiteError('file.config.empty', 'The site configuration file is empty or the site has not yet been configured');

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
        throw new SiteError('file.config.sqldata', 'Missing MySQL server information');

    /* Attempt to load the SQL class */
    require_once(DIR_ROOT.'includes/class.sql.php');

    /* Create the SQL object */
    $SQL = new SQL($GLOBALS['config']['sql']['host'], $GLOBALS['config']['sql']['username'], $GLOBALS['config']['sql']['password'], $GLOBALS['config']['sql']['database']);

    /* Escape & set the SQL table prefix */
    define('SQL_PREFIX', $SQL->Escape($GLOBALS['config']['sql']['prefix']));
    unset($GLOBALS['config']['sql']);

/* Load the definitions file */
    require_once(DIR_ROOT.'includes/inc.definitions.php');

/* Load settings from MySQL */
    $Settings = array();
    $SettingsQuery = $SQL->Query('SELECT * FROM '.SQL_SETTINGS);
    while($SettingsRow = $SQL->FetchArray($SettingsQuery)) {
        $Settings[$SettingsRow['Setting_Name']] = $SettingsRow['Setting_Value'];
    }
    $SQL->Free($SettingsQuery);

/* Attempt to load the translations class */
    require_once(DIR_INCLUDE.'class.translations.php');

    /* Create the Translations object */
    $Trans = new Translations(DIR_TRANSLATIONS, (isset($Settings['site_language']) && strlen($Settings['site_language']) >= 2) ? $Settings['site_language'] : 'en');


/*************************
|    System Functions    |
*************************/

    /* Shutdown Function */
    function ScriptShutdown() {
        //global $SQL;
        /* Ensure MySQL connection gets closed */
        //if(isset($SQL) && method_exists($SQL, 'Close'))
        //    $SQL->Close();

        /* Use error handler to check for a fatal error */
        if(class_exists('PHPError', false))
            PHPError::CheckForFatality();
    }

    /* Get the real path for a given path */
    function FilePath($Path) {
        return realpath($Path).SP_PS;
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
    function SafeHTML($Input) {
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

        $URL = filter_var($URL, FILTER_SANITIZE_URL);
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

?>
