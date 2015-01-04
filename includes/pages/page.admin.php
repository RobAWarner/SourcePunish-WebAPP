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
if(!defined('IN_SP')) die('Access Denied!');

/* TODO
    - Dashboard - List appeals? Only show X amount?
    - Setting Cats?
    - List Servers/Current players - Place punishment quick?
*/

$PageQuery = isset($_GET['a'])?$_GET['a']:$GLOBALS['settings']['admin_index'];
switch($PageQuery) {
    case 'dashboard':
        require_once(DIR_PAGES.'page.admin.dashboard.php');
    break;
    case 'edit':
        require_once(DIR_PAGES.'page.admin.edit-punishment.php');
    break;
    default:
        Redirect('^');
    break;
}
?>