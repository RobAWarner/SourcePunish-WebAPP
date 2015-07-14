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

function Theme_Alert($Data) {
    global $Theme;
    switch($Data['type']) {
        default:
        case 'info':
            $Class = 'info';
            break;
        case 'warning':
            $Class = 'warning';
            break;
        case 'danger':
            $Class = 'danger';
            break;
        case 'success':
            $Class = 'success';
            break;
    }
    $Theme->AddAttr($Data, 'class', 'message '.$Class);

    return '<div class="page-alert"><div'.$Theme->Attr($Data).'>'.$Data['text'].'</div></div>';
}
?>