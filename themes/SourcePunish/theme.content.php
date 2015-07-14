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

function Theme_Content($Data) {
    global $Theme;
    $Theme->AddAttr($Data, 'class', 'section-content');

    $Build = '<section'.$Theme->Attr($Data).'>'."\n";
    if(isset($Data['title']))
        $Build .= '<header><h1>'.$Data['title'].'</h1></header>'."\n";
    $Build .= '<div class="section-content-inner">'.$Data['text'].'</div>'."\n";
    $Build .= '</section>'."\n";
    return $Build;
}
?>