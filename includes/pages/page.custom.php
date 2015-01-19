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

/* Check page reference is valid */
if(!isset($_GET['q']) || $_GET['q'] == '' || !SP_CustomPageExists($_GET['q']) || $_GET['q'] == 'home_intro')
    Redirect();

/* Get the page content */
$CustomPage = SP_GetCustomPage($_GET['q']);

/* Add page title */
$GLOBALS['theme']->AddTitle($CustomPage['title']);

/* Add the content to the page */
$GLOBALS['theme']->AddContent($CustomPage['title'], $CustomPage['content']);

unset($CustomPage);
?>