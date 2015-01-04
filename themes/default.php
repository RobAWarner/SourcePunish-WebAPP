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

/* 
    These are default theme functions, do not change these.
    If you wish to alter how these work, copy them to your
    theme file as Theme_<function>
*/

function DefaultTheme_AddContent($Array) {
    $Build = '<section'.(isset($Array['id'])?' id="'.$Array['id'].'"':'').' class="section-content'.(isset($Array['class'])?' '.$Array['class']:'').'">'."\n";
    if(isset($Array['title'])) $Build .= '<header><h1>'.$Array['title'].'</h1></header>'."\n";
    $Build .= '<div class="section-content-inner">'.$Array['content'].'</div>'."\n";
    $Build .= '</section>'."\n";
    return $Build;
}
function DefaultTheme_HeaderStat($Text) {
    return '<span class="stat">'.$Text.'</span>'."\n";
}
function DefaultTheme_NavMenuLink($Array) {
    return '<a href="'.$Array['url'].'" title="'.$Array['title'].'"'.(isset($Array['target'])?' target="'.$Array['target'].'"':'').'>'.$Array['title'].'</a>';
}
function DefaultTheme_NavMenu($Array) {
    $Build = '<ul>'."\n";
    foreach($Array as $NavItem) {
        $Build .= '<li>'.$NavItem['link'];
        if(isset($NavItem['children']))
            $Build .= DefaultTheme_NavMenu($NavItem['children']);
        $Build .= '</li>'."\n";
    }
    $Build .= '</ul>'."\n";
    return $Build;
}
function DefaultTheme_TableCell($Array) {
    return '<'.(isset($Array['heading'])?'th':'td').(isset($Array['class'])?' class="'.$Array['class'].'"':'').(isset($Array['custom'])?' '.$Array['custom']:'').'>'.$Array['content'].'</'.(isset($Array['heading'])?'th':'td').'>';
}
function DefaultTheme_TableRow($Array) {
    return '<tr'.(isset($Array['class'])?' class="'.$Array['class'].'"':'').(isset($Array['custom'])?' '.$Array['custom']:'').'>'.$Array['content'].'</tr>'."\n";
}
function DefaultTheme_Table($Array) {
    $Build = '<table'.(isset($Array['id'])?' id="'.$Array['id'].'"':'').(isset($Array['class'])?' class="'.$Array['class'].'"':'').'>'."\n";
    if(isset($Array['headings'])) {
        $Build .= '<thead>'."\n";
        $Build .= $Array['headings']."\n";
        $Build .= '</thead>'."\n";
    }
    $Build .= '<tbody>'."\n";
    $Build .= $Array['rows'];
    $Build .= '</tbody>'."\n";
    $Build .= '</table>'."\n";
    return $Build;
}
function DefaultTheme_PaginationLink($Array) {
    return '<a href="'.$Array['url'].'"'.(isset($Array['class'])?' class="'.$Array['class'].'"':'').'>'.$Array['text'].'</a>';
}
function DefaultTheme_Pagination($Links) {
    return '<div class="pagination"><span class="pagination-container">'.$Links.'</span></div>'."\n";
}
function DefaultTheme_BuildPage($Array) {
    $Build = '<!DOCTYPE html>'."\n";
    $Build .= '<html lang="en">'."\n";
    $Build .= '<head>'."\n";
    $Build .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
    $Build .= '<meta charset="utf-8">'."\n";
    $Build .= $Array['head'];
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
    $Build .= '</body>'."\n";
    $Build .= '</html>';
    return $Build;
}
?>