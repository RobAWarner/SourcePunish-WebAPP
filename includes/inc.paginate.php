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

function Paginate($Total, $Current, $URLPre, $URLSuf = '', $Attrs = array()) {
    global $Theme, $Trans;
    
    if($Total <= 1)
        return false;

    $MaxLinks = 20;
    $Build = array(
        'attrs'=>$Attrs,
        'pager'=>array(
            'first'=>array('attrs'=>array('href'=>$URLPre.'1'.$URLSuf, 'title'=>SpecialChars(ucfirst($Trans->t('paginate.first')))), 'text'=>SpecialChars(ucfirst($Trans->t('paginate.first')))),
            'last'=>array('attrs'=>array('href'=>$URLPre.$Total.$URLSuf, 'title'=>SpecialChars(ucfirst($Trans->t('paginate.last')))), 'text'=>SpecialChars(ucfirst($Trans->t('paginate.last')))),
        ), 
        'numbers'=>array(),
    );

    if($Current > 1)
        $Build['pager']['previous'] = array('attrs'=>array('href'=>$URLPre.($Current-1).$URLSuf, 'title'=>SpecialChars(ucfirst($Trans->t('paginate.previous')))), 'text'=>SpecialChars(ucfirst($Trans->t('paginate.previous'))));
    else {
        $Build['pager']['previous'] = array('disabled'=>true, 'attrs'=>array('href'=>$URLPre.'1'.$URLSuf, 'title'=>SpecialChars(ucfirst($Trans->t('paginate.previous')))), 'text'=>SpecialChars(ucfirst($Trans->t('paginate.previous'))));
        $Build['pager']['first']['disabled'] = true;
    }

    if($Current < $Total)
        $Build['pager']['next'] = array('attrs'=>array('href'=>$URLPre.($Current+1).$URLSuf, 'title'=>SpecialChars(ucfirst($Trans->t('paginate.next')))), 'text'=>SpecialChars(ucfirst($Trans->t('paginate.next'))));
    else {
        $Build['pager']['next'] = array('disabled'=>true, 'attrs'=>array('href'=>$URLPre.$Total.$URLSuf, 'title'=>SpecialChars(ucfirst($Trans->t('paginate.next')))), 'text'=>SpecialChars(ucfirst($Trans->t('paginate.next'))));
        $Build['pager']['last']['disabled'] = true;
    }

    if($Total <= $MaxLinks) {
        for($i = 1; $i <= $Total; $i++) {
            $TmpBuild = array('attrs'=>array('href'=>$URLPre.$i.$URLSuf, 'title'=>SpecialChars($i)), 'text'=>SpecialChars($i));
            if($i == $Current) $TmpBuild['active'] = true;
            $Build['numbers'][] = $TmpBuild;
        }
    } else {
        $Half = floor($MaxLinks / 2);
        $Start = $Current - $Half;
        if($Start < 1) $Start = 1;
        $TotalNums = $Start + ($Half * 2);
        if($TotalNums > $Total) {
            $Diff = $TotalNums - $Total;
            $Start -= $Diff;
            $TotalNums = $Total;
        }
        unset($Half);
        for($i = $Start; $i <= $TotalNums; $i++) {
            $TmpBuild = array('attrs'=>array('href'=>$URLPre.$i.$URLSuf, 'title'=>SpecialChars($i)), 'text'=>SpecialChars($i));
            if($i == $Current) $TmpBuild['active'] = true;
            $Build['numbers'][] = $TmpBuild;
        }
        unset($Start, $TotalNums);
    }

    return $Build;
}
?>