<?php
    require_once('includes/core.php');
    $GLOBALS['theme']->AddTitle('Test');
    $TestContent = '<p>Welcome to the our group punishment database, powered by SourcePunish.<br /><br />Here you can view all punishments, view your punishments and appeal punishments you think were placed incorrectly. To do this, simple sign in through Steam.</p>';
    $GLOBALS['theme']->AddContent('Welcome to SourcePunish', $TestContent, 'testingclass', 'welcome-content');
    $PageNumbers = $GLOBALS['theme']->Paginate(515, 515, 'index.php?q=punishments&amp;page=');
    $GLOBALS['theme']->AddContent('Some test block', ParseText('Hello and welcome #TRANS_1102 to the #TRANS_1121 system.').$PageNumbers);
    echo $GLOBALS['theme']->BuildPage();
?>