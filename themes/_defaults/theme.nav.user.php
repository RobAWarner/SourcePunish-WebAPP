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

function Theme_Nav_User($Data, $Level = 0) {
    global $Theme;
    $Build = '<ul>'."\n";
    foreach($Data['items'] as $Nav) {
        if($Nav['active'])
            $Theme->AddAttr($Nav, 'class', 'active');
        $Theme->AddAttr($Nav, 'class', 'nav-level-'.$Level);
        $Build .= '<li'.$Theme->Attr($Nav).'>'."\n";
        $Build .= '<a'.$Theme->Attr($Nav['link']).'>'.$Nav['link']['text'].'</a>'."\n";
        if(isset($Nav['children']))
            $Build .= Theme_Nav_User($Nav['children'], ($Level + 1));
        $Build .= '</li>'."\n";
    }
    $Build .= '</ul>'."\n";
    return $Build;
}
?>