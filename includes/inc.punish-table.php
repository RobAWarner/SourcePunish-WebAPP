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

function Punish_Table($Rows, $Attrs = array()) {
    global $Theme, $Trans;
    /* Punish table data */
    $PunishTable = array(
        'attrs'=>$Attrs, 
        'headings'=>array(
            'date'=>array('attrs'=>array('class'=>array('table-col-date')), 'text'=>$Trans->t('punish.date')),
            'server'=>array('attrs'=>array('class'=>array('table-col-server')), 'text'=>$Trans->t('punish.server')),
            'country'=>array('attrs'=>array('class'=>array('table-col-country')), 'text'=>$Trans->t('punish.country-short')),
            'player'=>array('attrs'=>array('class'=>array('table-col-player')), 'text'=>$Trans->t('punish.player')),
            'type'=>array('attrs'=>array('class'=>array('table-col-type')), 'text'=>$Trans->t('punish.type')),
            'reason'=>array('attrs'=>array('class'=>array('table-col-reason')), 'text'=>$Trans->t('punish.reason')),
            'length'=>array('attrs'=>array('class'=>array('table-col-length')), 'text'=>$Trans->t('punish.length')),
        ), 
        'rows'=>array(),
    );

    /* Loop Rows */
    foreach($Rows as $Row) {
        $BuildRow = array('attrs'=>array('data'=>array('pid'=>SpecialChars($Row['Punish_ID']))),
            'cells'=>array(
                'date'=>array('attrs'=>array(), 'text'=>''),
                'server'=>array('attrs'=>array(), 'text'=>''),
                'country'=>array('attrs'=>array(), 'text'=>''),
                'player'=>array('attrs'=>array(), 'text'=>''),
                'type'=>array('attrs'=>array(), 'text'=>''),
                'reason'=>array('attrs'=>array(), 'text'=>''),
                'length'=>array('attrs'=>array(), 'text'=>''),
            ));
        /* Date */
        $BuildRow['cells']['date']['text'] = SpecialChars($Trans->t('time.ago', array('TIME'=>ucwords(SP_PrintTimeDiff(SP_TimeDiff(time()-$Row['Punish_Time']), 1)))));
        $BuildRow['cells']['date']['attrs']['title'] = SpecialChars(date(DATE_FORMAT, $Row['Punish_Time']));

        /* Server */
        $Server = SP_GetServerInfo($Row['Punish_Server_ID']);
        $BuildRow['cells']['server']['text'] = $Theme->Render('image', array('type'=>'game', 'for'=>'table-punish-list', 'attrs'=>array('src'=>SpecialChars(HTML_IMAGES_GAMES.$Server['mod']['image']), 'alt'=>SpecialChars($Server['mod']['short'])), 'tooltip'=>SpecialChars($Server['name'])));
        unset($Server);

        /* Country */
        if(isset($Row['Punish_Player_IP'])) {
            $GeoIPQuery = SP_GeoIPCountry($Row['Punish_Player_IP']);
            if($GeoIPQuery !== false) {
                $BuildRow['cells']['country']['text'] = $Theme->Render('image', array('type'=>'flag', 'for'=>'table-punish-list', 'attrs'=>array('src'=>SpecialChars($GeoIPQuery['country_flag']), 'alt'=>SpecialChars($GeoIPQuery['country_code'])), 'tooltip'=>SpecialChars($GeoIPQuery['country'])));
            }
            unset($GeoIPQuery);
        }

        /* Player */
        $BuildRow['cells']['player']['text'] = SpecialChars($Row['Punish_Player_Name']);

        /* Type */
        $BuildRow['cells']['type']['text'] = SpecialChars(ucfirst($Row['Punish_Type']));

        /* Reason */
        $BuildRow['cells']['reason']['text'] = SpecialChars($Row['Punish_Reason']);

        /* Length */
        $Time = SP_LengthString($Row['Punish_Length']);
        if($Row['UnPunish'] == 1) { // Unpunished
            $BuildRow['cells']['length']['text'] = SpecialChars(ucfirst($Trans->t('punish.unpunished')));
            $BuildRow['cells']['length']['attrs']['title'] = SpecialChars(ucfirst($Trans->t('punish.unpunished')));
            $BuildRow['cells']['length']['attrs']['class'][] = 'punishment-removed';
        } else if(($Row['Punish_Time']+($Row['Punish_Length']*60) < time()) && $Row['Punish_Length'] > 0) { // Expired
            $BuildRow['cells']['length']['text'] = SpecialChars(ucwords($Time));
            $BuildRow['cells']['length']['attrs']['title'] = SpecialChars(ucfirst($Trans->t('punish.expired')));
            $BuildRow['cells']['length']['attrs']['class'][] = 'punishment-expired';
        } else if(($Row['Punish_Length'] == 0 && $Row['Punish_Type'] == 'kick') || $Row['Punish_Length'] == -1) { // No time (Legacy fix for kick)
            $BuildRow['cells']['length']['text'] = SpecialChars(strtoupper($Trans->t('base.na')));
            $BuildRow['cells']['length']['attrs']['title'] = SpecialChars(strtoupper($Trans->t('base.na')));
            $BuildRow['cells']['length']['attrs']['class'][] = 'punishment-na';
        } else if($Row['Punish_Length'] == 0) { // Permanent
            $BuildRow['cells']['length']['text'] = SpecialChars(ucfirst($Trans->t('time.permanent')));
            $BuildRow['cells']['length']['attrs']['title'] = SpecialChars(ucfirst($Trans->t('punish.active')));
            $BuildRow['cells']['length']['attrs']['class'][] = 'punishment-permanent';
        } else { // Active
            $BuildRow['cells']['length']['text'] = SpecialChars(ucwords($Time));
            $BuildRow['cells']['length']['attrs']['title'] = SpecialChars(ucfirst($Trans->t('punish.active')));
            $BuildRow['cells']['length']['attrs']['class'][] = 'punishment-active';
        }

        /* To to table */
        $PunishTable['rows'][] = $BuildRow;
    }

    return $PunishTable;
}
?>