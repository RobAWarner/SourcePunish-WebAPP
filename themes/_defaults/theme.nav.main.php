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

/* Available:
    type - What the image represents. E.G: 'flag',
    for - A known parent/container for the image. E.G: '#table-punish-list' / 'button',
    attr - 
        src - The image source
        alt - Alternate text for the image
        * - Can also container class, id, data etc
    tooltip (optional) - Optional text to be used for a title/tooltip
*/
function Theme_Nav_Main($Data, $Level = 1) {
    global $Theme;
    $Build = '<ul>'."\n";
    foreach($Data['items'] as $Nav) {
        if($Nav['active'])
            $Theme->AddAttr($Nav, 'class', 'active');
        $Theme->AddAttr($Nav, 'class', 'nav-level-'.$Level);
        $Build .= '<li'.$Theme->Attr($Nav).'>'."\n";
        $Build .= '<a'.$Theme->Attr($Nav['link']).'>'.$Nav['link']['text'].'</a>'."\n";
        if(isset($Nav['children']))
            $Build .= Theme_Nav_Main($Nav['children'], ($Level + 1));
        $Build .= '</li>'."\n";
    }
    $Build .= '</ul>'."\n";
    return $Build;
}

?>