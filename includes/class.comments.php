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

class Comments {
	public function __construct() {
        if(!isset($GLOBALS['sql']))
            die('Error: SQL class not initiated in class.comments, cannot continue!');
        if(!isset($GLOBALS['theme']))
            die('Theme class not initiated in class.comments, cannot continue!');
        if(!defined('SQL_COMMENTS') || !defined('SQL_UPLOADS'))
            die('Missing definitions in class.comments, cannot continue!');
    }
    public function GetComments($Relation, $RelationID, $Flags = 0) {
        $Relation = $GLOBALS['sql']->Escape($Relation);
        $RelationID = $GLOBALS['sql']->Escape($RelationID);

        if($Flags & SP_COMMENT_NOATTACH)
            $CommentQuery = $GLOBALS['sql']->Query('SELECT id, comment_time, comment_user_id, comment_user_name, comment_text FROM '.SQL_COMMENTS.' WHERE comment_relation = \''.$Relation.'\' AND comment_relation_id = \''.$RelationID.'\' ORDER BY id ASC');
        else
            $CommentQuery = $GLOBALS['sql']->Query('SELECT c.id, c.comment_time, c.comment_user_id, c.comment_user_name, c.comment_text, u.upload_id, u.upload_time, u.upload_relation_id, u.upload_name, u.upload_type, u.upload_file FROM '.SQL_COMMENTS.' AS c LEFT OUTER JOIN '.SQL_UPLOADS.' AS u ON (u.upload_relation_id = c.id AND u.upload_relation = \'comment\') WHERE (c.comment_relation = \''.$Relation.'\' AND c.comment_relation_id = \''.$RelationID.'\') ORDER BY c.comment_time ASC');

        $Comments = array('attr'=>array('class'=>'comments comments-appeal'), 'comments'=>array());
        while($Row = $GLOBALS['sql']->FetchArray($CommentQuery)) {
            if((isset($Row['upload_time'], $Row['upload_name'], $Row['upload_type'], $Row['upload_file']) && !empty($Row['upload_time'])) && (!empty($Row['upload_relation_id']) && $Row['upload_relation_id'] === $Row['id']))
                $TmpUpload = array('time'=>date(DATE_FORMAT, $Row['upload_time']), 'name'=>SpecialChars($Row['upload_name']), 'file'=>($Row['upload_type']=='demo'?HTML_UPLOADS_DEMOS:HTML_UPLOADS).$Row['upload_file']);
            unset($Row['upload_time'], $Row['upload_name'], $Row['upload_type'], $Row['upload_file']);

            if(!isset($Comments['comments'][$Row['id']])) {
                $Comments['comments'][$Row['id']]['time'] = date(DATE_FORMAT, $Row['comment_time']);
                $Comments['comments'][$Row['id']]['user_id'] = $Row['comment_user_id'];
                $Comments['comments'][$Row['id']]['user_name'] = SpecialChars($Row['comment_user_name']);
                $Comments['comments'][$Row['id']]['text'] = nl2br(SpecialChars($Row['comment_text']));
            }

            if(isset($TmpUpload)) {
                $Comments['comments'][$Row['id']]['attachments'][$Row['upload_id']] = $TmpUpload;
                unset($TmpUpload);
            }
        }
        $GLOBALS['sql']->Free($CommentQuery);
        if($Flags & SP_COMMENT_RETURN)
            return $GLOBALS['theme']->BuildComments($Comments);
        else
            $GLOBALS['theme']->AddContent($GLOBALS['trans'][3017], $GLOBALS['theme']->BuildComments($Comments));
    }
    public function DeleteComments($Relation, $RelationID) {
        $Relation = $GLOBALS['sql']->Escape($Relation);
        $RelationID = $GLOBALS['sql']->Escape($RelationID);

        $GLOBALS['sql']->Query('DELETE c.*, u.* FROM '.SQL_COMMENTS.' c LEFT JOIN '.SQL_UPLOADS.' u ON c.id=u.upload_relation_id AND u.upload_relation=\'comment\' WHERE (c.comment_relation=\''.$Relation.'\' AND c.comment_relation_id=\''.$RelationID.'\')');
    }
    public function ParseComment($Relation, $RelationID) {
        if(!isset($_POST['comment-text']) || $_POST['comment-text'] == '' || !USER_LOGGEDIN)
            return false;
        
        $Text = ParseUserInput($_POST['comment-text'], 200);
        if(strlen($Text) < 3)
            return false;

        /* Rate limiting */
        
        $CommentID = $this->SubmitComment($Relation, $RelationID, $Text, SP_COMMENT_NOPARSE);
        
        /* Uploads */
        $Uploaded = $this->_ParseUploads('comment', $CommentID);
    }
    private function _ParseUploads($Relation, $RelationID) {
        if(!isset($_FILES['comment-file']) || empty($_FILES['comment-file']['tmp_name']) || $_FILES['comment-file']['error'] > 0)
            return false;
        
        if(!is_uploaded_file($_FILES['comment-file']['tmp_name']))
            return false;
        
        if($_FILES['comment-file']['type'] !== 'application/octet-stream' || !preg_match('/^.+\.(([dD][eE][mM]))$/', $_FILES['comment-file']['name']))
            return false;
        
        $NewFileName = sha1_file($_FILES['comment-file']['tmp_name']).'.dem';
        
        $MoveTempFile = move_uploaded_file($_FILES['comment-file']['tmp_name'], DIR_UPLOADS_DEMOS.$NewFileName);
        if(!$MoveTempFile)
            return false;
        
        $Relation = $GLOBALS['sql']->Escape($Relation);
        $RelationID = $GLOBALS['sql']->Escape($RelationID);
        $UploadFileName = $GLOBALS['sql']->Escape($_FILES['comment-file']['name']);
        $NewFileName = $GLOBALS['sql']->Escape($NewFileName);
        
        $GLOBALS['sql']->Query('INSERT INTO '.SQL_UPLOADS.' (upload_relation, upload_relation_id, upload_time, upload_name, upload_type, upload_file) 
        VALUES (\''.$Relation.'\', \''.$RelationID.'\', \''.time().'\', \''.$UploadFileName.'\', \'demo\', \''.$NewFileName.'\')');
        
        return true;
    }
    public function SubmitComment($Relation, $RelationID, $Text, $Flags = 0) {
        if($Flags & SP_COMMENT_SYSTEM)
            $User = array('name'=>'System', 'id'=>0);
        else
            $User = array('name'=>$GLOBALS['sql']->Escape($GLOBALS['auth']->GetName()), 'id'=>$GLOBALS['sql']->Escape($GLOBALS['auth']->GetUser64()));
        
        if(!($Flags & SP_COMMENT_NOPARSE))
            $Text = ParseUserInput($Text, 200, SP_INPUT_ESCAPE);
        else
            $Text = $GLOBALS['sql']->Escape($Text);
        
        $Relation = $GLOBALS['sql']->Escape($Relation);
        $RelationID = $GLOBALS['sql']->Escape($RelationID);
        
        $InsertID = $GLOBALS['sql']->Query_InsertID('INSERT INTO '.SQL_COMMENTS.' (comment_relation, comment_relation_id, comment_time, comment_user_id, comment_user_name, comment_user_level, comment_text) 
        VALUES (\''.$Relation.'\', \''.$RelationID.'\', \''.time().'\', \''.$User['id'].'\', \''.$User['name'].'\', \''.CurrentAuthLevel().'\', \''.$Text.'\')');
        
        return $InsertID;
    }
    public function AddCommentInput($ActionURL) {
        $Build = '<form name="comment-input" id="form-comment" action="'.$ActionURL.'" enctype="multipart/form-data" method="post">';
        $Build .= '<textarea name="comment-text" rows="10" class="form-textarea" required autofocus></textarea><br /><br />';
        $Build .= '<p>Attachment</p>';
        $Build .= '<input type="file" name="comment-file"  accept=".dem" /> ('.$GLOBALS['trans'][3019].')<br /><br />';
        $Build .= '<input type="submit" value="'.$GLOBALS['trans'][3013].'" />';
        $Build .= '</form>';
        $GLOBALS['theme']->AddContent($GLOBALS['trans'][3018], $Build);
    }
}
?>