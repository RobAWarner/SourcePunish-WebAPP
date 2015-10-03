<?php
/*{{BOILER}}*/

/*{{CORE_REQUIRED}}*/

    global $Settings;

/* PHP Paths */
    define('DIR_INCLUDE', DIR_ROOT.'includes/');
    define('DIR_DATA', DIR_ROOT.'data/');
    define('DIR_PAGES', DIR_ROOT.'pages/');
    define('DIR_THEMES',  DIR_ROOT.'themes/');
    define('DIR_TRANSLATIONS',  DIR_ROOT.'translations/');
    define('DIR_UPLOADS_SITE',  DIR_ROOT.'uploads/site/');
    define('DIR_UPLOADS_DEMOS',  DIR_ROOT.'uploads/user/demos/');

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
    define('HTML_CSS', HTML_ROOT.'static/css/');
    define('HTML_IMAGES', HTML_ROOT.'static/img/');
    define('HTML_IMAGES_GAMES', HTML_IMAGES.'games/');
    define('HTML_IMAGES_FLAGS', HTML_IMAGES.'flags/');
    define('HTML_SCRIPTS', HTML_ROOT.'static/js/');
    define('HTML_THEMES', HTML_ROOT.'themes/');
    define('HTML_UPLOADS_SITE', HTML_ROOT.'uploads/site/');
    define('HTML_UPLOADS_DEMOS', HTML_ROOT.'uploads/user/demos/');
    define('URL_PAGE', 'index.php');
    define('URL_QUERY', '?q=');

/* MySQL definitions */
    define('SQL_APPEALS', SQL_PREFIX.'appeals');
    define('SQL_COMMENTS', SQL_PREFIX.'comments');
    define('SQL_GEOIP', SQL_PREFIX.'geoip');
    define('SQL_NAVIGATION', SQL_PREFIX.'navigation');
    define('SQL_PAGES', SQL_PREFIX.'pages');
    define('SQL_PUNISHMENTS', SQL_PREFIX.'punishments');
    define('SQL_SERVERS', SQL_PREFIX.'servers');
    define('SQL_SETTINGS', SQL_PREFIX.'settings');
    define('SQL_SERVER_MODS', SQL_PREFIX.'server_mods');
    define('SQL_SESSIONS', SQL_PREFIX.'sessions');
    define('SQL_UPLOADS', SQL_PREFIX.'uploads');
    define('SQL_USERS', SQL_PREFIX.'users');
    define('SQL_USER_DATA', SQL_PREFIX.'user_data');
    define('SQL_USER_GROUPS', SQL_PREFIX.'user_groups');
    define('SQL_USER_OVERRIDES', SQL_PREFIX.'user_overrides');

/* Flags for 'CheckVar' */
    define('SP_VAR_INT', 1); // Var should be an int
    define('SP_VAR_FLOAT', 2); // Var should be an int
    define('SP_VAR_NEGATIVE', 4); // Var can be negative
    define('SP_VAR_IP_BOTH', 8);
    define('SP_VAR_IP_V4', 16);
    define('SP_VAR_IP_V6', 32);

/* Permission defaults */
    if(isset($Settings['permissions_default_group']) && CheckVar($Settings['permissions_default_group'], SP_VAR_INT))
        define('PERMISSIONS_DEFAULT_GROUP', (int)$Settings['permissions_default_group']);
    else
        define('PERMISSIONS_DEFAULT_GROUP', 5);

/* User IP address */
    $IPAddress = '';
    if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
        $IPAddress = $_SERVER['REMOTE_ADDR'];

    if($IPAddress != '' && CheckVar($IPAddress, SP_VAR_IP_BOTH))
        define('USER_ADDRESS', $IPAddress);
    else
        define('USER_ADDRESS', 'UNKNOWN');
    unset($IPAddress);

/* Time format definition */
    if(isset($Settings['site_time_format']) && !empty($Settings['site_time_format']))
        define('DATE_FORMAT', $Settings['site_time_format']);
    else
        define('DATE_FORMAT', 'H:i - jS F Y');
    unset($Settings['site_time_format']);
?>