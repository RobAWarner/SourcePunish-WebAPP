<?php
/*{{BOILER}}*/

/*{{CORE_REQUIRED}}*/

/* TODO: Render logo? */

class Theme {
    public $Name = '';
    public $Path = '';
    public $HTMLPath = '';
    private $ThemeLoaded = false;
    private $BaseDirectory = null;
    private $BaseHTMLDirectory = null;
    private $ThemeDefaults = '_defaults';
    private $ThemeFallback = 'SourcePunish';

    public function __construct($ThemeDirectory, $ThemeHTMLDirectory) {
        /* Check theme directory */
        $ThemeDirectory = FilePath($ThemeDirectory);

        if(!file_exists($ThemeDirectory))
            throw new SiteError('theme.basedir.missing', 'Themes directory "/themes" is missing or inaccessable');

        $this->BaseDirectory = $ThemeDirectory;

        /* Check theme HTML directory */
        if(substr($ThemeHTMLDirectory, -1) !== '/' && substr($ThemeHTMLDirectory, -1) !== '\\')
            $ThemeHTMLDirectory .= '/';
        $this->BaseHTMLDirectory = $ThemeHTMLDirectory;
    }

    public function Load($ThemeName) {
        /* Check theme */
        if(empty($ThemeName) || !$this->_ValidString($ThemeName) || !$this->_ThemeExists($ThemeName)) {
            /* Try fallback theme */
            if(!$this->_ThemeExists($this->ThemeFallback))
                throw new SiteError('theme.dir.missing', 'Could not load theme "'.$ThemeName.'" or the fallback "'.$this->ThemeFallback.'" theme as their directories are missing');
            else
                $ThemeName = $this->ThemeFallback;
        }

        /* Set theme variables */
        $this->Name = $ThemeName;
        $this->Path = $this->BaseDirectory.$ThemeName.SP_PS;
        $this->HTMLPath = $this->BaseHTMLDirectory.$ThemeName.'/';

        /* Load theme init */
        if(file_exists($this->Path.'theme.init.php'))
            require_once($this->Path.'theme.init.php');

        $this->ThemeLoaded = true;
    }

    public function Render($ThemeComponent, $Data = array(), $Required = false) {
        if(!$this->ThemeLoaded || !is_array($Data) || !$this->_ValidString($ThemeComponent)) {
            if($Required)
                throw new SiteError('theme.component.invalid', 'Invalid theme component render call for "'.$ThemeComponent.'"');
            else
                return '';
        }

        $FuncName = $this->_ComponentFuncName($ThemeComponent);

        if(!function_exists($FuncName)) {
            $ComponentFile = $this->_CompontentFile($ThemeComponent);
            if($ComponentFile === false) {
                $ComponentFile = $this->_CompontentFile($ThemeComponent, true);
                if($ComponentFile === false) {
                    if($Required)
                        throw new SiteError('theme.component.missing', 'Could not load a required theme component "'.$ThemeComponent.'"');
                    else
                        return '';
                }
            }
            require_once($ComponentFile);
        }

        if(!function_exists($FuncName))
            return '';

        $Return = call_user_func($FuncName, $Data);
        if($Return === false)
            return '';
        else
            return $Return;
    }

    public function PrintAttr($MainArray) {
        if(!isset($MainArray['attrs']))
            return '';
        $Attributes = $MainArray['attrs'];
        if(empty($Attributes) || !is_array($Attributes))
            return '';
        $Build = '';
        foreach($Attributes as $Name => $Value) {
            if($Name == 'data' && is_array($Value)) {
                foreach($Value as $Data => $DValue) {
                    $Build .= 'data-'.$Data.'="'.htmlspecialchars($DValue).'" ';
                }
            } else {
                if(is_array($Value))
                    $Build .= $Name.'="'.htmlspecialchars(implode(' ', $Value)).'" ';
                else
                    $Build .= $Name.'="'.htmlspecialchars($Value).'" ';
            }
        }
        return ' '.trim($Build);
    }

    public function AddAttr(array &$MainArray, $Tag, $Value, $DataVal = '') {
        if(!isset($MainArray['attrs']))
            $MainArray['attrs'] = array();
        if($Tag == 'class') {
            if(isset($MainArray['attrs']['class']) && is_array($MainArray['attrs']['class'])) {
                if(!in_array($Value, $MainArray['attrs']['class']))
                    $MainArray['attrs']['class'][] = $Value;
            } else if(isset($MainArray['attrs']['class']) && is_string($MainArray['attrs']['class'])) {
                if(!in_array($Value, explode(' ', $MainArray['attrs']['class'])))
                    $MainArray['attrs']['class'] = trim($MainArray['attrs']['class']).' '.$Value;
            } else
                $MainArray['attrs']['class'] = array($Value);
        } else if($Tag == 'data') {
            if(!empty($DataVal)) {
                if(isset($MainArray['attrs']['data']) && is_array($MainArray['attrs']['data'])) {
                    if(!isset($MainArray['attrs']['data'][$Value]))
                        $MainArray['attrs']['data'][$Value] = $DataVal;
                } else
                    $MainArray['attrs']['data'] = array($Value=>$DataVal);
            }
        } else {
            $MainArray['attrs'][$Tag] = $Value;
        }
    }

    /*****************************************
    |    DON'T WORRY ABOUT ANYTHING BELOW    |
    *****************************************/

    private function _ThemeExists($ThemeName) {
        if(file_exists($this->BaseDirectory.$ThemeName.SP_PS) === true)
            return true;
        else
            return false;
    }

    private function _ValidString($String) {
        return preg_match('#^[a-z0-9-_.]+$#i', $String);
    }

    private function _ComponentFuncName($ComponentName) {
        $ComponentName = str_replace('.', '_', $ComponentName);
        $ComponentName = preg_replace('/[^a-z0-9-_]/i', '', $ComponentName);
        $ComponentName = str_replace('_', ' ', $ComponentName);
        $ComponentName = ucwords($ComponentName);
        $ComponentName = str_replace(' ', '_', $ComponentName);
        $ComponentName = 'Theme_'.$ComponentName;
        return $ComponentName;
    }

    private function _CompontentFile($ComponentName, $TryDefault = false) {
        if($TryDefault)
            $ComponentPath = $this->BaseDirectory.$this->ThemeDefaults.SP_PS;
        else
            $ComponentPath = $this->Path;
        $ComponentFile = $ComponentPath.'theme.'.$ComponentName.'.php';
        if(file_exists($ComponentFile) === true)
            return $ComponentFile;
        else
            return false;
    }

    /* Theming functions */
    private $TitleSeparator = '|';
    private $Titles = array();
    private $Headers = array('custom'=>array(), 'styles'=>array(), 'style-blocks'=>array(), 'scripts'=>array(), 'late-scripts'=>array());
    private $Content = array();

    /* Page Title */
    public function Title_Add($Text) {
        $this->Titles[] = htmlspecialchars($Text);
    }

    /* Styles */
    public function Style_Add($ID, $URL, $Media = '', $If = '') {
        $URL = trim(str_replace('"', '', $URL));
        $Media = trim(str_replace('"', '', $Media));
        $If = trim(str_replace('"', '', $If));
        $this->Headers['styles'][$ID] = ($If != ''?'<!--[if '.$If.']>':'').'<link href="'.$URL.'" rel="stylesheet" type="text/css" '.($Media != ''?'media="'.$Media.'" ':'').'/>'.($If != ''?'<![endif]-->':'');
    }
    public function Style_Add_Custom($ID, $Content, $If = '') {
        $Content = trim($Content);
        $If = trim(str_replace('"', '', $If));
        $this->Headers['style-blocks'][$ID] = ($If != ''?'<!--[if '.$If.']>':'').'<style>'.$Content.'</style>'.($If != ''?'<![endif]-->':'');
    }
    public function Style_Remove($ID) {
        if(isset($this->Headers['styles'][$ID]))
            unset($this->Headers['styles'][$ID]);
        if(isset($this->Headers['style-blocks'][$ID]))
            unset($this->Headers['style-blocks'][$ID]);
    }

    /* Scripts */
    public function Script_Add($ID, $URL, $LateLoad = true, $If = '') {
        $URL = trim(str_replace('"', '', $URL));
        $If = trim(str_replace('"', '', $If));
        $this->Headers[($LateLoad==true?'late-scripts':'scripts')][$ID] = ($If != ''?'<!--[if '.$If.']>':'').'<script src="'.$URL.'" type="text/javascript"></script>'.($If != ''?'<![endif]-->':'');
    }
    public function Script_Add_Custom($ID, $Content, $LateLoad = true, $If = '') {
        $Content = trim($Content);
        $If = trim(str_replace('"', '', $If));
        $this->Headers[($LateLoad==true?'late-scripts':'scripts')][$ID] = ($If != ''?'<!--[if '.$If.']>':'').'<script type="text/javascript">'.$Content.'</script>'.($If != ''?'<![endif]-->':'');
    }
    public function Script_Remove($ID) {
        if(isset($this->Headers['scripts'][$ID]))
            unset($this->Headers['scripts'][$ID]);
        if(isset($this->Headers['late-scripts'][$ID]))
            unset($this->Headers['late-scripts'][$ID]);
    }

    /* Other Headers */
    public function Header_Add($ID = '', $Content = '', $If = '') {
        $Content = trim($Content);
        $If = trim(str_replace('"', '', $If));
        $ID = (!empty($ID)?$ID:'custom-'.count($this->Headers['custom']));
        if(!empty($Content))
            $this->Headers['custom'][$ID] = ($If != ''?'<!--[if '.$If.']>':'').$Content.($If != ''?'<![endif]-->':'');
    }

    /* Content */
    public function Content_Add($Content, $ForceTop = false) {
        if($ForceTop)
            array_unshift($this->Content, $Content);
        else
            array_push($this->Content, $Content);
    }

    /* Render */
    public function RenderPage() {
        echo $this->_BuildPage();
    }

    private function _BuildTitle() {
        $Build = implode(' '.$this->TitleSeparator.' ', array_reverse($this->Titles));
        unset($this->Titles);
        return '<title>'.$Build.'</title>'.PHP_EOL;
    }
    private function _BuildHeaders() {
        $Build = '';
        foreach($this->Headers as $HeaderName => $HeaderArray) {
            if($HeaderName == 'late-scripts')
                continue;
            foreach($HeaderArray as $Header) {
                $Build .= $Header.PHP_EOL;
            }
            unset($this->Headers[$HeaderName]);
        }
        return $Build;
    }
    private function _BuildLateLoad() {
        $Build = '';
        foreach($this->Headers['late-scripts'] as $LateLoad) {
            $Build .= $LateLoad.PHP_EOL;
        }
        unset($this->Headers['late-load']);
        return $Build;
    }
    private function _BuildContent() {
        $Build = '';
        foreach($this->Content as $Content) {
            $Build .= $Content;
        }
        unset($this->Content);
        return $Build;
    }
    private function _BuildNavigation() {
        $Build = array('user'=>'', 'main'=>'');
        $Build = require(DIR_INCLUDE.'inc.navigation.php');
        return $Build;
    }

    private function _BuildPage() {
        $BuildArray = array();
        $BuildArray['headers'] = $this->_BuildTitle();
        $BuildArray['headers'] .= $this->_BuildHeaders();
        $BuildArray['late-load'] = $this->_BuildLateLoad();
        $BuildArray['content'] = $this->_BuildContent();

        $BuildArray['footer'] = 'Powered by <a title="Visit '.SP_WEBAPP_URL.'" href="'.SP_WEBAPP_URL.'">'.SP_WEBAPP_NAME.' '.SP_WEBAPP_VERSION.'</a>';

        $Navs = $this->_BuildNavigation();
        $BuildArray['usernav'] = $Navs['user'];
        $BuildArray['mainnav'] = $Navs['main'];
        unset($Navs);
        $BuildArray['stats'] = '';
        $BuildArray['header'] = '<img src="'.HTML_IMAGES.'logo.png" alt="'.SP_WEBAPP_NAME.'">';

        $Build = $this->Render('page', $BuildArray, true);
        return $Build;
    }
}
?>
