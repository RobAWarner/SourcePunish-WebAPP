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
function Theme_Paginate($Data) {
    global $Theme;
    $Theme->AddAttr($Data, 'class', 'pagination');

    foreach($Data['pager'] as $Key => $Pager) {
        if(isset($Pager['disabled']) && $Pager['disabled'] == true)
            $Theme->AddAttr($Data['pager'][$Key], 'class', 'disabled');
    }

    $Build = '<div'.$Theme->Attr($Data).'>'."\n";
    $Build .= '<span class="pagination-container">'."\n";
        $Build .= '<a '.$Theme->Attr($Data['pager']['first']).'>&laquo;&laquo;'.$Data['pager']['first']['text'].'</a>';
        $Build .= '<a '.$Theme->Attr($Data['pager']['previous']).'>&laquo;'.$Data['pager']['previous']['text'].'</a>';
        foreach($Data['numbers'] as $Number) {
            if(isset($Number['active']))
                $Theme->AddAttr($Number, 'class', 'active');
            $Build .= '<a '.$Theme->Attr($Number).'>'.$Number['text'].'</a>';
        }
        $Build .= '<a '.$Theme->Attr($Data['pager']['next']).'>'.$Data['pager']['next']['text'].'&raquo;</a>';
        $Build .= '<a '.$Theme->Attr($Data['pager']['last']).'>'.$Data['pager']['last']['text'].'&raquo;&raquo;</a>';
    $Build .= '</span>'."\n";
    $Build .= '</div>'."\n";
    return $Build;
}
?>