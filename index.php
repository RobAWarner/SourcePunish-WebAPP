<?php
require_once('includes/core.php');
    
/* TODO:
    - Validate custom home page
*/

/* Get basic stats */
$TotalPunishments = $GLOBALS['sql']->Query_FetchArray('SELECT count(*) AS prows FROM '.SQL_PUNISHMENTS.'');
$GLOBALS['varcache']['punishcount'] = $TotalPunishments['prows'];
unset($TotalPunishments);
$GLOBALS['theme']->AddHeaderStat(sprintf(ParseText('#TRANS_1300'), number_format($GLOBALS['varcache']['punishcount'])));

/* Select Page */
$PageQuery = isset($_GET['q'])?$_GET['q']:$GLOBALS['settings']['site_index'];
switch($PageQuery) {
    case 'index':
        require_once(DIR_PAGES.'page.home.php');
    break;
    case 'punishments':
        require_once(DIR_PAGES.'page.punishments.php');
    break;
    case 'view':
        require_once(DIR_PAGES.'page.view.php');
    break;
    case 'search':
    case 'searchme':
        require_once(DIR_PAGES.'page.search.php');
    break;
    case 'appeal':
        require_once(DIR_PAGES.'page.appeal.php');
    break;
    case 'servers':
        require_once(DIR_PAGES.'page.servers.php');
    break;
    case 'stats':
        require_once(DIR_PAGES.'page.statistics.php');
    break;
    case 'stats2':
        require_once(DIR_PAGES.'page.statistics2.php');
    break;
    case 'login':
    case 'logout':
        require_once(DIR_PAGES.'page.login.php');
    break;
    case 'admin':
        require_once(DIR_PAGES.'page.admin.php');
    break;
    case 'me':
        require_once(DIR_PAGES.'page.me.php');
    break;
    case 'steamid':
        require_once(DIR_PAGES.'page.steamid.php');
    break;
    case 'serverquery':
        if(AJAX)
            require_once(DIR_PAGES.'page.ajax.serverquery.php');
        else
            Redirect('^');
    break;
    default:
        if(CustomPageExists($PageQuery) && $PageQuery != 'home_intro')
            require_once(DIR_PAGES.'page.custom.php');
        else
            Redirect('^');
    break;
}

/* Build final page */
echo $GLOBALS['theme']->BuildPage();
?>