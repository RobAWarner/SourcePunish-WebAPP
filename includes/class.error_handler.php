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

/* config to show debug backtrace */

/* Other Error Codes:
    User: user.format.invalid, user.invalid, user.group.default.invalid, user.group.default.format.invalid
*/

class ErrorHandler extends Exception {
    public function __construct($ErrorMessage, $ErrorRefCode = null, $ShowFileInfo = true, $ErrorDetail = null, $TrueFile = null, $TrueLine = null) {
        $ErrorFile = null;
        $ErrorLine = null;
        if($ShowFileInfo) {
            $ErrorFile = (!is_null($TrueFile)?$TrueFile:$this->file);
            $ErrorLine = (!is_null($TrueLine)?$TrueLine:$this->line);

            /* Remove the system path so that we don't display it */
            if(defined('DIR_ROOT'))
                $ErrorFile = preg_replace('#'.DIR_ROOT.'#i', '', $ErrorFile);
        }

        /* Remove the system path so that we don't display it */
        if(defined('DIR_ROOT')) {
            $ErrorMessage = preg_replace('#'.DIR_ROOT.'#i', '', $ErrorMessage);
            $ErrorDetail = preg_replace('#'.DIR_ROOT.'#i', '', $ErrorDetail);
        }
        
        ErrorDisplay::ShowError($ErrorMessage, $ErrorRefCode, $ErrorFile, $ErrorLine, $ErrorDetail);

        parent::__construct($ErrorMessage);
    }
}

Class SiteError extends ErrorHandler {
    private $ErrorMap = array(
        'unknown'=>'An unknown error occurred',
        'folder.missing'=>'A required folder seems to be missing or could not be accessed',
        'file.missing'=>'Failed to load a required file as it seems to be missing or could be not read',
        'file.config.empty'=>'The core configuration file seems to be empty, have you run first-time setup for this site yet?',
        'file.config.data'=>'The core configuration file is missing some required data',
        'translation.language'=>'An invalid language code was specified when trying to load translations',
        'translation.missing'=>'Failed to load translations as a required translation folder does not exist',
        'translation.missing.module'=>'Failed to load a required translation module as it seems to be missing or could be not read',
        'translation.data'=>'A required translation file seems to be empty or the data set incorrectly',
        'theme.missing'=>'A required translation file seems to be empty or the data set incorrectly',
    );

    public function __construct($ID, $Message = null) {
        if(!isset($this->ErrorMap[$ID]))
            $ID = 'unknown';
        parent::__construct($this->ErrorMap[$ID], $ID, true, $Message);
    }
}

class SQLError extends ErrorHandler {
    private $ErrorMap = array(
        'sql.other'=>'There was an error executing a MySQL function or query',
        'sql.connect'=>'There was an error trying to connect to the MySQL server',
        'sql.query'=>'There was an error executing a MySQL query'
    );

    public function __construct($ID, $Message = null, $Code = null) {
        if(!isset($this->ErrorMap[$ID]))
            $ID = 'sql.other';

        /* We want a backtrace to work out where the error actually occurred */
        $TrueFile = null;
        $TrueLine = null;

        /* debug_backtrace's parameters changes as of 5.3.6 */
        if(version_compare(PHP_VERSION, '5.3.6') < 0)
            $BacktraceArray = debug_backtrace(false);
        else
            $BacktraceArray = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        /* Loop the backtrace to find where the error would have occurred */
        foreach($BacktraceArray as $Backtrace) {
            if(isset($Backtrace['class'], $Backtrace['file']) && $Backtrace['class'] === 'SQL' && !preg_match('#class.sql.php$#i', $Backtrace['file'])) {
                $TrueFile = $Backtrace['file'];
                $TrueLine = $Backtrace['line'];
            }
        }
        unset($BacktraceArray);

        $Detail = (!empty($Code)?'MySQL Error '.$Code.': ':'').(!empty($Message)?trim($Message):'');

        parent::__construct($this->ErrorMap[$ID], $ID, (!is_null($TrueFile)?true:false), $Detail, $TrueFile, $TrueLine);
    }
}

class PHPError extends ErrorHandler {
    private $ErrorMap = array(
        'php.other'=>'There was an unknown PHP error',
        'php.error'=>'PHP encountered a fatal run-time error',
        'php.warning'=>'PHP encountered a run-time warning',
        'php.parse'=>'PHP encountered an error parsing a script',
        'php.notice'=>'PHP gave notice of a potential error that was not fatal',
    );

    public function __construct($ErrorLevel, $ErrorString, $ErrorFile, $ErrorLine) {
        $PHPErrorRef = self::GetPHPErrorRef($ErrorLevel);
        if($PHPErrorRef === false || !isset($this->ErrorMap[$PHPErrorRef]))
            $PHPErrorRef = 'php.other';

        parent::__construct($this->ErrorMap[$PHPErrorRef], $PHPErrorRef, true, $ErrorString, $ErrorFile, $ErrorLine);
    }
    
    private static function GetPHPErrorRef($ErrorLevel) {
       $ID = false;
       switch($ErrorLevel) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                $ID = 'php.error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                $ID = 'php.warning';
                break;
            case E_PARSE:
                $ID = 'php.parse';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $ID = 'php.parse'; 
                break;
        }
        return $ID;
    }

    public static function CheckForFatality() {
        $LastError = error_get_last();
        if(self::GetPHPErrorRef($LastError['type']) !== false)
            new PHPError($LastError['type'], $LastError['message'], $LastError['file'], $LastError['line']);
    }
}

class ErrorDisplay {
    public static function ShowError($ErrorMessage, $ErrorRefCode = null, $ErrorFile = null, $ErrorLine = null, $ErrorDetail = null) {
        self::ClearOutput();
        if(defined('AJAX_REQUEST') && AJAX_REQUEST == true) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success'=>false, 'errorid'=>(is_null($ErrorRefCode)?'unknown':$ErrorRefCode)));
        } else {
            $ErrorArray = array('id'=>$ErrorRefCode, 'msg'=>$ErrorMessage, 'detail'=>$ErrorDetail, 'file'=>$ErrorFile, 'line'=>$ErrorLine);
            header('Content-Type: text/html; charset=utf-8');
            if(defined('DIR_ROOT') && file_exists(DIR_ROOT.'includes/page.error.php'))
                self::DisplayErrorPage($ErrorArray);
            else
                self::DisplayErrorPageFallback($ErrorArray);
        }
    }

    private static function ClearOutput() {
        ob_end_clean();
    }

    private static function DisplayErrorPage($ErrorArray) {
        require_once(DIR_ROOT.'includes/page.error.php');
        echo FormatErrorPage($ErrorArray);
    }

    private static function DisplayErrorPageFallback($ErrorArray) {
        echo '<!DOCTYPE html><html lang="en"><head><title>SourcePunish - Fatal Error</title></head><body><div style="color:#FF0000">SourcePunish encountered a fatal error</div><hr />'.(!is_null($ErrorArray['msg'])?htmlentities($ErrorArray['msg']).'<br />':'').(!is_null($ErrorArray['detail'])?htmlentities($ErrorArray['detail']).'<br />':'').((!is_null($ErrorArray['file']) && !is_null($ErrorArray['file']))?'The error occurred in file: "'.htmlentities($ErrorArray['file']).'" on line: '.htmlentities(number_format($ErrorArray['line'])).'<br />':'').(!is_null($ErrorArray['id'])?'<br />Error refference code: '.htmlentities($ErrorArray['id']):'').((defined('SP_WEBAPP_VERSION') && defined('SP_WEBAPP_NAME'))?'<br /><br />'.htmlentities(SP_WEBAPP_NAME.' '.SP_WEBAPP_VERSION):'').'</body></html>';
    }
}
?>