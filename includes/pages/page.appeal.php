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
if(!defined('IN_SP')) die('Access Denied!');

/* TODO
    - Everything?
    - List my appeals
    - Error messages instead of Redirect?
    - If status change, change punishment status and unpunish reason?
    - Cleaner code, dont repeat?
*/

/* Add page title */
$GLOBALS['theme']->AddTitle($GLOBALS['trans'][1518]);

if(isset($_GET['id']) && CheckVar($_GET['id'], SP_VAR_INT)) {
    /* The user needs to be logged in */
    if(!USER_LOGGEDIN)
        Redirect();

    /* Make the ID safe */
    $PunishID = $GLOBALS['sql']->Escape($_GET['id']);

    /* Fetch appeal info from DB, if it exists */
    $AppealQuery = $GLOBALS['sql']->Query_FetchArray('SELECT * FROM '.SQL_APPEALS.' WHERE appeal_punish_id=\''.$PunishID.'\' LIMIT 1');
    
    /* Fetch punishment player ID from DB, if it exists */
    $PunishmentQuery = $GLOBALS['sql']->Query_FetchArray('SELECT Punish_Time, Punish_Player_Name, Punish_Player_ID, Punish_Type, Punish_Length, Punish_Reason, UnPunish FROM '.SQL_PUNISHMENTS.' WHERE Punish_ID=\''.$PunishID.'\' LIMIT 1');
    if(empty($PunishmentQuery))
        Redirect();

    if(empty($AppealQuery)) {
        /* No appeal found, let the user submit one */

        /* Are we allowed to create this appeal? */
        $PunishmentUser64 = $GLOBALS['steam']->SteamIDTo64($PunishmentQuery['Punish_Player_ID']);
        if($PunishmentUser64 === false || $PunishmentUser64 != $GLOBALS['auth']->GetUser64())
            Redirect(); 

        /* Has the appeal form been submitted */
        if(isset($_POST['explanation'], $_POST['agreement'], $_POST['originid'])) {
            /* Check original ID */
            if($_POST['originid'] != $PunishID)
                Redirect();
            
            /* Check input */
            if(!empty($_POST['explanation']) && strlen($_POST['explanation']) > 10) {
                /* Create appeal */
                $GLOBALS['sql']->Query('INSERT INTO '.SQL_APPEALS.' (appeal_punish_id, appeal_time, appeal_status) VALUES (\''.$PunishID.'\', \''.time().'\', \'0\')');

                /* Post explanation as comment */
                require_once(DIR_INCLUDE.'class.comments.php');
                $Comment = new Comments();
                $InsertedComment = $Comment->SubmitComment('appeal', $PunishID, $_POST['explanation']);
                unset($Comment);
                
                /* Reload page */
                Redirect('^appeal&id='.$PunishID);
            }
        }

        /* Can we actually appeal this punishment? */
        if($PunishmentQuery['Punish_Length'] < 0 || $PunishmentQuery['UnPunish'] == 1 || (($PunishmentQuery['Punish_Time']+($PunishmentQuery['Punish_Length']*60) < time()) && $PunishmentQuery['Punish_Length'] > 0))
            Redirect();

        /* Create table for appeal information */
        $AppealTableArray = array('class'=>'table-appeal table-format-data', 'rows'=>array());
        $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1503]), array('content'=>sprintf($GLOBALS['trans'][1504], SpecialChars($PunishmentQuery['Punish_Player_Name']), SpecialChars(SP_LengthString(($PunishmentQuery['Punish_Type']=='kick')?-1:$PunishmentQuery['Punish_Length'], 0)), SpecialChars($PunishmentQuery['Punish_Type']), SpecialChars($PunishmentQuery['Punish_Reason'])).' - <a href="'.ParseURL('^view&amp;id='.$AppealQuery['appeal_punish_id']).'" title="'.$GLOBALS['trans'][1513].'" target="_blank">'.$GLOBALS['trans'][1513].'</a>')));

        /* Add form inputs to table */
        $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1514]), array('content'=>'<textarea name="explanation" class="input-textarea-large" maxlength="200" autofocus required>'.(isset($_POST['explanation'])?$_POST['explanation']:'').'</textarea>')));
        $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>''), array('content'=>'<input type="checkbox" name="agreement" required />&nbsp;'.$GLOBALS['trans'][1515])));
        $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>''), array('content'=>'<input type="submit" value="Submit" />')));

        /* Build appeal information table and add it to the page */
        $AppealTable = $GLOBALS['theme']->BuildTable($AppealTableArray);
        unset($AppealTableArray);
        
        /* Add the form */
        $AppealTableForm = '<form name="appeal-create" id="form-appeal" action="'.ParseURL('^appeal&amp;id='.$PunishID).'" method="post">';
        $AppealTableForm .= '<input type="hidden" name="originid" value="'.$PunishID.'" />';
        $AppealTableForm .= $AppealTable;
        $AppealTableForm .= '</form>';
        unset($AppealTable);

        /* Add the content to the page */
        $GLOBALS['theme']->AddContent($GLOBALS['trans'][1006], $AppealTableForm);
        unset($AppealTable);
    } else {
        /* Appeal exists, show details */

        /* Are we allowed to view this appeal? */
        $PunishmentUser64 = $GLOBALS['steam']->SteamIDTo64($PunishmentQuery['Punish_Player_ID']);
        if(($PunishmentUser64 === false || $PunishmentUser64 != $GLOBALS['auth']->GetUser64()) && !USER_ADMIN)
            Redirect();

        $Buttons = '';

        if(isset($_GET['a']) && $_GET['a'] == 'edit') {
            /* Are we allowed to edit this */
            if(!USER_ADMIN || (isset($GLOBALS['settings']['flag_appeals']) && !$GLOBALS['auth']->HasAdminFlag($GLOBALS['settings']['flag_appeals'])))
                Redirect('^appeal&id='.$PunishID);

            /* Has the edit form been submitted */
            if(isset($_POST['status'], $_POST['originid'])) {
                /* Check original ID */
                if($_POST['originid'] != $PunishID)
                    Redirect();

                /* Check status */
                if(CheckVar($_POST['status'], SP_VAR_INT) && $_POST['status'] <= 3) {
                    if($_POST['status'] !== $AppealQuery['appeal_status']) {
                        /* Check input */
                        $InputStatus = $GLOBALS['sql']->Escape($_POST['status']);

                        /* Update appeal */
                        $GLOBALS['sql']->Query('UPDATE '.SQL_APPEALS.' SET appeal_status=\''.$InputStatus.'\' WHERE appeal_punish_id=\''.$PunishID.'\' LIMIT 1');

                        /* Post update as comment */
                        require_once(DIR_INCLUDE.'class.comments.php');
                        $Comment = new Comments();
                        $InsertedComment = $Comment->SubmitComment('appeal', $PunishID, sprintf($GLOBALS['trans'][1517], SP_AppealStatusText($AppealQuery['appeal_status']), SP_AppealStatusText($_POST['status']), $GLOBALS['auth']->GetName()), SP_COMMENT_SYSTEM);
                        unset($Comment);

                        if($InputStatus == 2) {
                            /* if status is * -> accepted ask to remove punishment? */
                            Redirect('^view&id='.$PunishID.'&a=unpunish');
                        } else {
                            /* Reload page */
                            Redirect('^appeal&id='.$PunishID);
                        }
                        /* If accepted -> declined ask to re-instate? */
                    }
                }
            }

            /* Create table for appeal information */
            $AppealTableArray = array('class'=>'table-appeal table-format-data', 'rows'=>array());
            $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1503]), array('content'=>sprintf($GLOBALS['trans'][1504], SpecialChars($PunishmentQuery['Punish_Player_Name']), SpecialChars(SP_LengthString(($PunishmentQuery['Punish_Type']=='kick')?-1:$PunishmentQuery['Punish_Length'], 0)), SpecialChars($PunishmentQuery['Punish_Type']), SpecialChars($PunishmentQuery['Punish_Reason'])).' - <a href="'.ParseURL('^view&amp;id='.$AppealQuery['appeal_punish_id']).'" title="'.$GLOBALS['trans'][1513].'" target="_blank">'.$GLOBALS['trans'][1513].'</a>')));
            $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1500]), array('content'=>SpecialChars(date(DATE_FORMAT, $AppealQuery['appeal_time'])))));

            /* Option select for appeal status */
            $StatusSelect = '<select name="status">';
            $StatusSelect .= '<option value="0"'.($AppealQuery['appeal_status']==0?' selected="selected"':'').'>'.SP_AppealStatusText(0).'</option><option value="1"'.($AppealQuery['appeal_status']==1?' selected="selected"':'').'>'.SP_AppealStatusText(1).'</option><option value="2"'.($AppealQuery['appeal_status']==2?' selected="selected"':'').'>'.SP_AppealStatusText(2).'</option><option value="3"'.($AppealQuery['appeal_status']==3?' selected="selected"':'').'>'.SP_AppealStatusText(3).'</option>';
            $StatusSelect .= '</select>';

            $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1502]), array('content'=>$StatusSelect)));
            unset($StatusSelect);

            $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>''), array('content'=>'<input type="submit" value="Submit" />'.(USER_SUPERADMIN?'&nbsp;<a href="'.ParseURL('^appeal&amp;id='.$PunishID.'&amp;a=delete').'" class="button">'.$GLOBALS['trans'][1166].'</a>':'').'&nbsp;<a href="'.ParseURL('^appeal&amp;id='.$PunishID).'" class="button">'.$GLOBALS['trans'][3011].'</a>')));

            /* Build appeal information table and add it to the page */
            $AppealTableTmp = $GLOBALS['theme']->BuildTable($AppealTableArray);
            unset($AppealTableArray);

            /* Add the form */
            $AppealTable = '<form name="appeal-edit" id="form-appeal" action="'.ParseURL('^appeal&amp;id='.$PunishID.'&amp;a=edit').'" method="post">';
            $AppealTable .= '<input type="hidden" name="originid" value="'.$PunishID.'" />';
            $AppealTable .= $AppealTableTmp;
            $AppealTable .= '</form>';
            unset($AppealTableTmp);

            /* Action buttons */
            $Buttons = '<span class="action-buttons"><a href="'.ParseURL('^appeal&amp;id='.$PunishID).'" title="'.$GLOBALS['trans'][3011].'">'.$GLOBALS['trans'][3011].'</a></span>';
            
            /* Add the content to the page */
            $GLOBALS['theme']->AddContent($GLOBALS['trans'][1006].$Buttons, $AppealTable);
            unset($AppealTable, $Buttons);

        } else if(isset($_GET['a']) && $_GET['a'] == 'delete' && USER_SUPERADMIN) {
            if(isset($_POST['confirmed']) && $_POST['confirmed'] == 1) {
                /* Ensure we are editing the correct record */
                if(!isset($_POST['originid']) || $_POST['originid'] !== $PunishID)
                    Redirect();

                /* Perform query */
                $GLOBALS['sql']->Query('DELETE FROM '.SQL_APPEALS.' WHERE appeal_punish_id=\''.$PunishID.'\' LIMIT 1');
                require_once(DIR_INCLUDE.'class.comments.php');
                $Comment = new Comments();
                $Comment->DeleteComments('appeal', $PunishID);
                unset($Comment);
                Redirect();
            } else {
                $BuildForm = '<div class="center message error">'.$GLOBALS['trans'][1519].'</div><br />';
                $BuildForm .= '<form name="form-punish-edit" action="'.ParseURL('^appeal&amp;id='.$PunishID.'&amp;a=delete&amp;submitted=true').'" method="post" enctype="multipart/form-data">';
                $BuildForm .= '<input type="hidden" name="originid" value="'.$PunishID.'" />';
                $BuildForm .= '<input type="hidden" name="confirmed" value="1" />';
                $BuildForm .= '<div class="center"><input name="submitted" type="submit" value="'.$GLOBALS['trans'][3015].'" />&nbsp;<a href="'.ParseURL('^view&appeal;id='.$PunishID.'&amp;a=edit').'" class="button">'.$GLOBALS['trans'][3011].'</a></div>';
                $BuildForm .= '</form>';
                $GLOBALS['theme']->AddContent($GLOBALS['trans'][3014], $BuildForm);
                unset($BuildForm);
            }
        } else if(isset($_GET['a']) && $_GET['a'] == 'comment') {
            require_once(DIR_INCLUDE.'class.comments.php');
            $Comment = new Comments();
            $Comment->ParseComment('appeal', $PunishID);
            unset($Comment);
            Redirect('^appeal&id='.$PunishID);
        } else {
            /* Create table for appeal information */
            $AppealTableArray = array('class'=>'table-appeal table-format-data', 'rows'=>array());
            $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1503]), array('content'=>sprintf($GLOBALS['trans'][1504], SpecialChars($PunishmentQuery['Punish_Player_Name']), SpecialChars(SP_LengthString(($PunishmentQuery['Punish_Type']=='kick')?-1:$PunishmentQuery['Punish_Length'], 0)), SpecialChars($PunishmentQuery['Punish_Type']), SpecialChars($PunishmentQuery['Punish_Reason'])).' - <a href="'.ParseURL('^view&amp;id='.$AppealQuery['appeal_punish_id']).'" title="'.$GLOBALS['trans'][1513].'" target="_blank">'.$GLOBALS['trans'][1513].'</a>')));
            $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1500]), array('content'=>SpecialChars(date(DATE_FORMAT, $AppealQuery['appeal_time'])))));

            /* What class should be apply */
            $Class = 'new';
            if($AppealQuery['appeal_status'] == 1)
                $Class = 'review';
            else if($AppealQuery['appeal_status'] == 2)
                $Class = 'accepted';
            else if($AppealQuery['appeal_status'] == 3)
                $Class = 'declined';

            $AppealTableArray['rows'][] = array('cols'=>array(array('content'=>$GLOBALS['trans'][1502]), array('class'=>$Class, 'content'=>SpecialChars(SP_AppealStatusText($AppealQuery['appeal_status'])))));
            unset($Class);

            /* Build appeal information table and add it to the page */
            $AppealTable = $GLOBALS['theme']->BuildTable($AppealTableArray);
            unset($AppealTableArray);
            
            /* Action buttons */
            if(USER_ADMIN && (isset($GLOBALS['settings']['flag_appeals']) && $GLOBALS['auth']->HasAdminFlag($GLOBALS['settings']['flag_appeals'])))
                $Buttons = '<span class="action-buttons"><a href="'.ParseURL('^appeal&amp;id='.$PunishID.'&amp;a=edit').'" title="'.$GLOBALS['trans'][1137].'">'.$GLOBALS['trans'][1137].'</a></span>';
            
            /* Add the content to the page */
            $GLOBALS['theme']->AddContent($GLOBALS['trans'][1006].$Buttons, $AppealTable);
            unset($AppealTable, $Buttons);
        }

        /* Add the content to the page */
        //$GLOBALS['theme']->AddContent($GLOBALS['trans'][1006].$Buttons, $AppealTable);
        //unset($AppealTable, $Buttons);

        /* Get the comments */
        require_once(DIR_INCLUDE.'class.comments.php');
        $Comment = new Comments();
        $Comment->GetComments('appeal', $PunishID);
        $Comment->AddCommentInput(ParseURL('^appeal&amp;id='.$PunishID.'&amp;a=comment'));
        unset($Comment);
    }
} else {
 /* List user's appeals */
}
?>