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

session_name('SP_USER_SESSION');
session_start();

ob_start();

try {
    require_once('includes/core.php');
    
    $Theme->Title_Add('SourcePunish');

    /* Select Page */
    $PageQuery = isset($_GET['q'])?$_GET['q']:(isset($GLOBALS['settings']['site_index'])?$GLOBALS['settings']['site_index']:'index');
    switch($PageQuery) {
        case 'index':
            SP_Require(DIR_PAGES.'page.home.php');
        break;
        case 'punishments':
            SP_Require(DIR_PAGES.'page.punishments.php');
        break;
        default:
            //Redirect('^');
        break;
    }
    
    $Theme->RenderPage();
    
    //echo '</body></html>';
} catch (Exception $e) {}

echo "\n".'<!-- Peak Memory Usage: '.number_format(memory_get_peak_usage()).' Bytes -->';

session_write_close();

/* TODO:
      - Validate custom home page
*/

/* END TESTING */

/* Get basic stats */
/*$TotalPunishments = $GLOBALS['sql']->Query_FetchArray('SELECT count(*) AS prows FROM '.SQL_PUNISHMENTS.'');
$GLOBALS['varcache']['punishcount'] = $TotalPunishments['prows'];
unset($TotalPunishments);
$Theme->AddHeaderStat(sprintf($Trans[1300], number_format($GLOBALS['varcache']['punishcount'])));*/

/* Select Page */
/*$PageQuery = isset($_GET['q'])?$_GET['q']:$GLOBALS['settings']['site_index'];
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
    break;*/
    /*case 'appeal':
        require_once(DIR_PAGES.'page.appeal.php');
    break;*/
/*    case 'servers':
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
        if(SP_CustomPageExists($PageQuery) && $PageQuery != 'home_intro')
            require_once(DIR_PAGES.'page.custom.php');
        else
            Redirect('^');
    break;
}*/

/* Build final page */
/*echo $Theme->BuildPage();*/
?>