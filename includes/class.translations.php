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

class Translations {
    private $TranslationFolder = null;
    private $LoadDefault = false;
    private $Language = null;
    private $Translations = array();

    /* Initiate the Translations class */
    /* Set $TranslationFolder to the directory that the translation folders & files are in */
    /* Set $UserLanguage to the language folder to use */
    /* Set $LoadDefaultEnglish to true/false (enable/disable) to specify whether to load English translations 
       in case of missing translations in other languages */
    public function __construct($TranslationFolder, $UserLanguage = 'en', $LoadDefaultEnglish = true) {
        /* Ensure '$UserLanguage' is safe */
        if(!$this->_ValidString($UserLanguage))
            throw new SiteError('translation.language', 'Language "'.$UserLanguage.'" contains invalid characters');

        /* Set the translation folder */
        $this->TranslationFolder = $TranslationFolder;

        /* If the user language is already English, we don't need to load it twice */ 
        if($UserLanguage == 'en')
            $LoadDefaultEnglish = false;

        /* Ensure the user language folder exists */
        if(!$this->_TranslationFileExists(null, $UserLanguage)) {
            if(!$LoadDefaultEnglish)
                throw new SiteError('translation.missing', 'The folder for the specified user language "'.$UserLanguage.'" is missing');
            $UserLanguage = null;
        }

        /* If we are loading English defaults, ensure the folder exists */
        if($LoadDefaultEnglish) {
            if(!$this->_TranslationFileExists(null, 'en'))
                throw new SiteError('translation.missing', 'The default English translation folder "en" seems to be missing but is required');
            else {
                if(is_null($UserLanguage)) {
                    /* User language was not found but defaults were, use these instead */
                    $UserLanguage = 'en';
                    $LoadDefaultEnglish = false;
                }
            }
        }

        $this->LoadDefault = $LoadDefaultEnglish;
        $this->Language = $UserLanguage;
    }

    /* Load a translation module from within the language folder */
    /* Set $TranslationModule to file to load. E.G. 'base' */
    /* Set $Required to true/false (yes/no) to throw an error if the module cannot be loaded */
    public function Load($TranslationModule, $Required = false) {
        $DefaultLoaded = false;
        $UserLoaded = false;
        
        /* Load defaults if needed */
        if($this->LoadDefault) {
            if($this->_TranslationFileExists($TranslationModule, 'en'))
                $DefaultLoaded = $this->_LoadTranslationModule($TranslationModule, 'en');
        }
        
        /* Load user language */
        if(!is_null($this->Language) && $this->_TranslationFileExists($TranslationModule)) {
            $UserLoaded = $this->_LoadTranslationModule($TranslationModule, $this->Language, ((($this->LoadDefault && !$DefaultLoaded) || !$this->LoadDefault)?true:false));
        }
        
        /* Did we successfully load one of the translation files */
        $Success = (($this->LoadDefault && $DefaultLoaded) || (!$this->LoadDefault && $UserLoaded))?true:false;

        /* Should we throw an error? */
        if(!$Success && $Required)
            throw new SiteError('translation.missing.module', 'Failed to load translation module: "'.$TranslationModule.'" for language: "'.(($this->LoadDefault && !$DefaultLoaded)?'en':$this->Language).'"');
        else
            return $Success;
    }

    /* Get a translation by reference */
    /* Set $TranslationReference to reference to get. E.G. 'base.help' where 'base' is the file it was loaded from */
    /* Set $Replacements to an array of key, values to replace in the translation. Replacements in the translation
       appear as {REPLACEME}, so the key you would use is 'REPLACEME'. E.G: array('REPLACEME'=>'Some text here') */
    public function T($TranslationReference, $Replacements = array()) {
        /* Ensure '$TranslationReference' is safe */
        if(!$this->_ValidRefString($TranslationReference))
            return $TranslationReference;
        /* Split translation reference */
        list($TranslationModule, $TranslationCode) = explode('.', $TranslationReference, 2);

        /* Ensure the translation is set */
        if(!isset($this->Translations[$TranslationModule])) {
            $this->Load($TranslationModule);
        }

        if(!isset($this->Translations[$TranslationModule][$TranslationCode]))
            return $TranslationReference;

        $Translation = $this->Translations[$TranslationModule][$TranslationCode];

        /* Action replacements */
        if(is_array($Replacements) && !empty($Replacements)) {
            foreach($Replacements as $Replace => $With) {
                $Translation = str_replace('{'.strtoupper($Replace).'}', $With, $Translation);
            }
        }

        return $Translation;
    }

    /*****************************************
    |    DON'T WORRY ABOUT ANYTHING BELOW    |
    *****************************************/

    private function _TranslationFileExists($TranslationModule = null, $LoadLanguage = null) {
        if(is_null($LoadLanguage))
            $LoadLanguage = $this->Language;
        
        if(!$this->_ValidString($LoadLanguage))
            return false;

        if(!is_null($TranslationModule) && !$this->_ValidString($TranslationModule))
            return false;
        
        if(!is_null($TranslationModule)) {
            return file_exists($this->TranslationFolder.$LoadLanguage.'/trans.'.$TranslationModule.'.php');
        } else {
            return file_exists($this->TranslationFolder.$LoadLanguage.'/');
        }

        return false;
    }

    private function _LoadTranslationModule($TranslationModule, $LoadLanguage = null, $Required = false) {
        if(is_null($LoadLanguage))
            $LoadLanguage = $this->Language;
        
        if(!$this->_ValidString($TranslationModule) || !$this->_ValidString($LoadLanguage))
            return false;

        $AttemptLoad = @include_once($this->TranslationFolder.$LoadLanguage.'/trans.'.$TranslationModule.'.php');
        if($AttemptLoad === false) {
            if($Required)
                throw new SiteError('translation.missing', 'Failed to load translation module: "'.$TranslationModule.'" for language: "'.$LoadLanguage.'"');
            else
                return false;
        }
        unset($AttemptLoad);

        if(!isset($Translations) || !is_array($Translations) || empty($Translations)) {
            if($Required)
                throw new SiteError('translation.data', 'Language: "'.$LoadLanguage.'". Translation File: "'.$TranslationModule.'"');
            else
                return false;
        }

        if(!isset($this->Translations[$TranslationModule]))
            $this->Translations[$TranslationModule] = $Translations;
        else
            $this->Translations[$TranslationModule] = array_merge($this->Translations[$TranslationModule], $Translations);
        unset($Translations);

        return true;
    }

    private function _ValidString($String) {
        return preg_match('#^[a-z0-9-_]+$#i', $String);
    }

    private function _ValidRefString($String) {
        return preg_match('#^[a-z0-9-_]+[.][a-z0-9-_.]+$#i', $String);
    }
}

?>