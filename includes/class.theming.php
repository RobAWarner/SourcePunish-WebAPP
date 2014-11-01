<?php
/*--------------------------------------------------------+
| SourcePunish WebApp                                     |
| Copyright (C) https://sourcepunish.net                  |
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
    - Require theme file
    - Check subthemes
*/
class Theming {
    private $ThemeName = '';
    private $Title = $GLOBALS['settings']['site_title'];
    private $Headers = '';
    private $Styles = '';
    private $StylesRaw = '';
    private $Scripts = '';
    private $ScriptsRaw = '';
    private $Content = '';

    public function __construct($ThemeName) {
        $this->ThemeName = $ThemeName;
    }
    public function AddTitle($Text) {
        if($Text == '')
            return;
        if($this->Title == '')
            $this->Title = htmlspecialchars('Default Title');
        $this->Title = htmlspecialchars(substr($Text, 0, 20)).' | '.$this->Title;
    }
    public function AddHeader($Content) {
        if($Content == '')
            return;
        $this->Headers .= $Content."\n";
    }
    public function AddStyle($Sheet, $Media = 'all', $If = '') {
        if($Sheet == '')
            return;
        $this->Styles .= (($If!='')?'<!--['.$If.']>':'').'<link href="'.$Sheet.'" rel="stylesheet" type="text/css" '.(($Media!='')?'media="'.$Media.'"':'').'/>'.(($If!='')?'<![endif]-->':'')."\n";
    }
    public function AddStyleRaw($Content, $Media = 'all', $If = '') {
        if($Content == '')
            return;
        $this->StylesRaw .= (($If!='')?'<!--['.$If.']>':'').'<style'.(($Media!='')?' media="'.$Media.'"':'').'>'.$Content.'</style>'.(($If!='')?'<![endif]-->':'')."\n";
    }
    public function AddScript($URL, $If = '') {
        if($URL == '')
            return;
        $this->Scripts .= (($If!='')?'<!--['.$If.']>':'').'<script src="'.$URL.'" type="text/javascript"></script>'.(($If!='')?'<![endif]-->':'')."\n";
    }
    public function AddScriptRaw($Content, $If = '') {
        if($Content == '')
            return;
        $this->ScriptsRaw .= (($If!='')?'<!--['.$If.']>':'').'<script type="text/javascript">'.$Content.'</script>'.(($If!='')?'<![endif]-->':'')."\n";
    }
    public function BuildHeaders() {
        $this->AddHeader('<title>'.$this->Title.'</title>');
        $this->AddHeader('<link rel="icon" href="'.HTML_ROOT.'favicon.ico" type="image/x-icon">');
        $this->AddHeader('<meta name="description" content="'.$GLOBALS['settings']['site_description'].'" />');
        $this->AddHeader('<meta name="keywords" content="'.$GLOBALS['settings']['site_keywords'].'" />');
        $this->AddHeader('<meta name="generator" content="SourcePunish WebApp '.SP_WEB_VERSION.'" />');
        return $this->Headers.$this->Styles.$this->StylesRaw.$this->Scripts.$this->ScriptsRaw;
    }
    public function GetLogo() {
        return '<img src="'.HTML_IMAGES.'logo.png" alt="SourcePunish">';
    }
    public function GetFooter() {
        return 'Powered by <a title="Visit SourcePunish.net" href="http://SourcePunish.net">SourcePunish WebApp '.SP_WEB_VERSION.'</a>';
    }
    /* Page theming */
    public function BuildNavs() {
        $AuthTypes = 'nav_auth=\'0\'';
        if(USER_LOGGEDIN) {
            $AuthTypes .= ' OR nav_auth=\'2\'';
            if(USER_ADMIN) {
                $AuthTypes .= ' OR nav_auth=\'3\'';
                if(USER_SUPERADMIN) {
                    $AuthTypes .= ' OR nav_auth=\'4\'';
                }
            }
        } else
            $AuthTypes .= ' OR nav_auth=\'1\'';
        $GetNavs = $GLOBALS['sql']->Query('SELECT * FROM '.SQL_PREFIX.'navigation WHERE nav_showing=\'1\' AND '.$AuthTypes.' ORDER BY nav_position ASC, nav_order ASC');
        $Navs = array(1=>array(), 0=>array());
        while($Row = $GLOBALS['sql']->FetchArray($GetNavs)) {
            $NavItem = array('title'=>$Row['nav_title'],'url'=>$Row['nav_url']);
            if($Row['nav_target'] == 1)
                $NavItem['target'] = '_blank';
            if($Row['nav_parent'] != 0) {
                $Navs[$Row['nav_position']][$Row['nav_parent']]['children'][$Row['id']] = $NavItem;
            } else {
                if(isset($Navs[$Row['nav_position']][$Row['id']]['children']))
                    $NavItem['children'] = $Navs[$Row['nav_position']][$Row['id']]['children'];
                $Navs[$Row['nav_position']][$Row['id']] = $NavItem;
            }
        }
        $GLOBALS['sql']->Free($GetNavs);
    }
    public function AddContent($Content, $Title = '', $Classes = '', $ID = '') {
        $Array = array('content'=>$Content);
        if($Title != '') $Array['title'] = $Title;
        if($Classes != '') $Array['class'] = $Classes;
        if($ID != '') $Array['id'] = $ID;
        if(function_exists('subtheme_addcontent')) {
            $this->Content = subtheme_addcontent($Array);
        } else if(function_exists('theme_addcontent')) {
            $this->Content = theme_addcontent($Array);
        } else {
            $this->Content = defaulttheme_addcontent($Array);
        }
    }
    public function Paginate() {
    
    }
}
?>