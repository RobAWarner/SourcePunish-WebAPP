<?php
/*{{BOILER}}*/

/*{{CORE_REQUIRED}}*/

/* config to show debug backtrace */

class ErrorHandler extends Exception {
    public function __construct($ErrorMessage, $ErrorRefCode = null, $ShowFileInfo = true, $TrueFile = null, $TrueLine = null) {
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
        if(defined('DIR_ROOT'))
            $ErrorMessage = preg_replace('#'.DIR_ROOT.'#i', '', $ErrorMessage);
        
        ErrorDisplay::ShowError($ErrorMessage, $ErrorRefCode, $ErrorFile, $ErrorLine);

        parent::__construct($ErrorMessage);
    }
}

Class SiteError extends ErrorHandler {
    public function __construct($ID, $Message = null) {
        parent::__construct($Message, $ID, true);
    }
}

class SQLError extends ErrorHandler {
    public function __construct($ID, $Message = null, $Code = null) {
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
                break;
            }
        }
        unset($BacktraceArray);

        $Message = (!empty($Code)?'MySQL Error '.$Code.': ':'').(!empty($Message)?trim($Message):'');

        parent::__construct($Message, $ID, (!is_null($TrueFile)?true:false), $TrueFile, $TrueLine);
    }
}

class PHPError extends ErrorHandler {
    public function __construct($ErrorLevel, $ErrorString, $ErrorFile, $ErrorLine) {
        $PHPErrorRef = self::GetPHPErrorRef($ErrorLevel);
        if($PHPErrorRef === false)
            $PHPErrorRef = 'php.other';

        parent::__construct($ErrorString, $PHPErrorRef, true, $ErrorFile, $ErrorLine);
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
    public static function ShowError($ErrorMessage, $ErrorRefCode = null, $ErrorFile = null, $ErrorLine = null) {
        self::ClearOutput();
        if(defined('AJAX_REQUEST') && AJAX_REQUEST == true) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success'=>false, 'errorid'=>(is_null($ErrorRefCode)?'unknown':$ErrorRefCode)));
        } else {
            $ErrorArray = array('id'=>$ErrorRefCode, 'msg'=>$ErrorMessage, 'file'=>$ErrorFile, 'line'=>$ErrorLine);
            header('Content-Type: text/html; charset=utf-8');
            if(defined('DIR_ROOT') && file_exists(DIR_ROOT.'includes/inc.page.error.php'))
                self::DisplayErrorPage($ErrorArray);
            else
                self::DisplayErrorPageFallback($ErrorArray);
        }
    }

    private static function ClearOutput() {
        ob_end_clean();
    }

    private static function DisplayErrorPage($ErrorArray) {
        require_once(DIR_ROOT.'includes/inc.page.error.php');
        echo FormatErrorPage($ErrorArray);
    }

    private static function DisplayErrorPageFallback($ErrorArray) {
        echo '<!DOCTYPE html><html lang="en"><head><title>SourcePunish - Fatal Error</title></head><body><div style="color:#FF0000">SourcePunish encountered a fatal error</div><hr />'.(!is_null($ErrorArray['msg'])?htmlentities($ErrorArray['msg']).'<br />':'').((!is_null($ErrorArray['file']) && !is_null($ErrorArray['file']))?'The error occurred in file: "'.htmlentities($ErrorArray['file']).'" on line: '.htmlentities(number_format($ErrorArray['line'])).'<br />':'').(!is_null($ErrorArray['id'])?'<br />Error refference code: '.htmlentities($ErrorArray['id']):'').((defined('SP_WEBAPP_VERSION') && defined('SP_WEBAPP_NAME'))?'<br /><br />'.htmlentities(SP_WEBAPP_NAME.' '.SP_WEBAPP_VERSION):'').'</body></html>';
    }
}
?>