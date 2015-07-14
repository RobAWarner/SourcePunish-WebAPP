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
    for - A known parent/container for the image. E.G: 'table-punish-list' / 'button',
    attr - 
        src - The image source
        alt - Alternate text for the image
        * - Can also container class, id, data etc
    tooltip (optional) - Optional text to be used for a title/tooltip
*/
function Theme_Image($Data) {
    global $Theme;

    if(substr($Data['for'], 0, 6) == 'table-')
        $Theme->AddAttr($Data, 'class', 'img-inline-table');

    return '<img '.$Theme->Attr($Data).(isset($Data['tooltip'])?' title="'.$Data['tooltip'].'"':'').'>';
}

?>