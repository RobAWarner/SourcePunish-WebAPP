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
    theme file as theme_<function>
*/

function defaulttheme_addcontent($Array) {
    $Build = '<section'.(isset($Array['id'])?' id="'.$Array['id'].'"'?'').' class="section-content'.(isset($Array['class'])?' '.$Array['class']?'').'">\n';
    if(isset($Array['title'])) $Build .= '<header><h1>'.$Array['title'].'</h1></header>';
    $Build .= $Array['content'];
    $Build .= '</section>';
    return $Build;
}
?>