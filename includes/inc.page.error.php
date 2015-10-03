<?php

function FormatErrorPage($ErrorArray) {
return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SourcePunish - Fatal Error</title>
<style>
html,body{font-weight:normal;font-style:normal;font-size:20px;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;text-align:center;vertical-align:baseline;color:#2F4050;background-color:#F3F3F4;}
a{color:#4480e7;text-decoration:underline;}
a:hover{color:#4480e7;text-decoration:none;}
#wrapper{width:100%;max-width:800px;margin:0px auto;}
.container{margin-top:50px;padding:15px;}
#main-title{font-size:28px;font-weight:bold;color:#A94442;background-color:#F2DEDE;border-top:3px solid #A94442;padding:15px;}
#help-title,#message-title{font-weight:bold;font-size:20px;border-bottom:1px dashed #2F4050;padding-bottom:5px;}
#help-title{color:#4480e7;}
#message-title{color:#fe4d4d;}
#help,#message{background-color:#fff;border-top:3px solid #2F4050;}
#help-body,
#message-body{font-size:18px;margin:15px 0px;}
.detail{font-size:17px;color:#676A6C;background-color:#F3F3F4;padding:10px;font-style:italic;margin:15px 0px;}
#message-file-info{font-size:14px;margin:15px 0px;}
#message-link{font-size:17px;}
#footer{margin-top:30px;font-size:15px;}
</style>
</head>
<body>
<div id="wrapper">
<div id="main-title" class="container">SourcePunish Encountered A Fatal Error</div>
<div id="help" class="container">
<div id="help-title">Why Am I Here?</div>
<div id="help-body">
The SourcePunish WebApp encountered a fatal error trying to display this page.
<div class="detail">Try going back <a href="'.((defined('HTML_ROOT') && defined('URL_PAGE'))?HTML_ROOT.URL_PAGE:'index.php').'" title="Go to the home page">Home</a>'.(isset($GLOBALS['config']['site']['email'])?' or, if the error persists, contact the <a href="mailto:'.htmlentities($GLOBALS['config']['site']['email']).'" title="Send a friendly email to the site admin">site admin</a>':'').'.<br /></div>
</div>
</div>
<div id="message" class="container">
<div id="message-title">Error Details</div>
<div id="message-body">
<div class="detail">'.htmlentities($ErrorArray['msg']).'</div>
</div>
'.((!is_null($ErrorArray['file']) && !is_null($ErrorArray['line']))?'<div id="message-file-info">The error occurred in file:<br />"'.htmlentities($ErrorArray['file']).'" on line '.htmlentities(number_format($ErrorArray['line'])).'</div>':'').'
'.((!is_null($ErrorArray['id']) && defined('SP_WEBAPP_URL_ERROR'))?'<div id="message-link">See more information and potential fixes for: <a href="'.htmlentities(sprintf(SP_WEBAPP_URL_ERROR, $ErrorArray['id'])).'" title="See more about this error" target="_blank">'.htmlentities($ErrorArray['id']).'</a></div>':'').'
</div>
'.((defined('SP_WEBAPP_VERSION') && defined('SP_WEBAPP_NAME') && defined('SP_WEBAPP_URL'))?'<div id="footer"><a href="'.SP_WEBAPP_URL.'" title="Visit the SourcePunish Website" target="_blank">'.htmlentities(SP_WEBAPP_NAME.' '.SP_WEBAPP_VERSION).'</a></div>':'').'
</div>
</body>
</html>';
}
?>