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

function Theme_Table_Punish_List($Data) {
    global $Theme;
    $Build = '<table'.$Theme->Attr($Data).'>'."\n";
    $Build .= '<thead>'."\n";
    $Build .= '<tr>'."\n";
    foreach($Data['headings'] as $Name => $Heading) {
        $Build .= '<th'.$Theme->Attr($Heading).'>'.$Heading['text'].'</th>';
    }
    $Build .= '</tr>'."\n";
    $Build .= '</thead>'."\n";
    $Build .= '<tbody>'."\n";
    foreach($Data['rows'] as $Row) {
        $Build .= '<tr'.$Theme->Attr($Row).'>'."\n";
        foreach($Row['cells'] as $Name => $Cell) {
            $Build .= '<td'.$Theme->Attr($Cell).'>';
            $Build .= $Cell['text'];
            $Build .= '</td>';
        }
        $Build .= '</tr>'."\n";
    }
    $Build .= '</tbody>'."\n";
    $Build .= '</table>'."\n";
    return $Build;
}
?>