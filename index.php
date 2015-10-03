<?php
/*{{BOILER}}*/

/* THIS IS A TEST SCRIPT! */
ob_start();
try {
    require('includes/core.php');
    require('includes/class.theme.php');
    
    $Theme = new Theme(DIR_THEMES, HTML_THEMES);
    
    $Theme->Load('SourcePunish');

    $Theme->Title_Add('SourcePunish');
    
    $Theme->Content_Add($Theme->Render('content', array('title'=>'Test Title', 'text'=>'HEY! Welcome to a test site!<br />This is test content!')));
    
    $Theme->RenderPage();

} catch (Exception $e) {}
?>