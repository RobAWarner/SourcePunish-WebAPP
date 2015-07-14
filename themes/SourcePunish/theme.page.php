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

if(!defined('SP_LOADED')) die('Access Denied!');

function Theme_Page($Array) {
    $Build = '<!DOCTYPE html>'."\n";
    $Build .= '<html lang="en">'."\n";
    $Build .= '<head>'."\n";
    $Build .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
    $Build .= '<meta charset="utf-8">'."\n";
    $Build .= $Array['headers'];
    $Build .= '</head>'."\n";
    $Build .= '<body>'."\n";
    $Build .= '<div id="wrapper-main">'."\n";
    $Build .= '<header id="header-main">'."\n";
    $Build .= '<div id="top-bar">'."\n";
    $Build .= '<div id="top-bar-stats">'.$Array['stats'].'</div>'."\n";
    $Build .= '<nav id="nav-user" class="notooltip">'."\n";
    $Build .= $Array['usernav'];
    $Build .= '</nav>'."\n";
    $Build .= '</div>'."\n";
    $Build .= '<div id="logo-main">'."\n";
    $Build .= $Array['header'];
    $Build .= '</div>'."\n";
    $Build .= '<div id="bottom-bar">'."\n";
    $Build .= '<nav id="nav-main" class="notooltip">'."\n";
    $Build .= $Array['mainnav'];
    $Build .= '</nav>'."\n";
    $Build .= '</div>'."\n";
    $Build .= '</header>'."\n";
    $Build .= '<div class="wrapper-content">'."\n";
    $Build .= $Array['content'];
    $Build .= '</div>'."\n";
    $Build .= '<footer id="footer">'."\n";
    $Build .= $Array['footer'];
    $Build .= '</footer>'."\n";
    $Build .= '</div>'."\n";
    $Build .= $Array['late-load'];
    $Build .= '</body>'."\n";
    $Build .= '</html>';
    return $Build;
}
?>