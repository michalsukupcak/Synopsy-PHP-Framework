<?php
/*
 * Synopsy PHP Framework (c) by Webdesign Studio s.r.o.
 * 
 * Synopsy PHP Framework is licensed under a
 * Creative Commons Attribution 4.0 International License.
 *
 * You should have received a copy of the license along with this
 * work. If not, see <http://creativecommons.org/licenses/by/4.0/>.
 *
 * Any files in this application that are NOT marked with this disclaimer are
 * not part of the framework's open-source implementation, the CC 4.0 licence
 * does not apply to them and are protected by standard copyright laws!
 */

namespace Synopsy\Lang;

use Synopsy\Auth\Session;
use Synopsy\Config\Config;
use Synopsy\Exceptions\SynopsyException;

/**
 * Library used to store and retrieve current language information.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Language {

    /**
     * Singleton instance
     * 
     * @var Language
     */
    private static $instance = null;
    
    /**
     * List of language codes
     * 
     * @var Array
     */
    private $languages = [];
    
    /**
     * Loads (if not already loaded) content of language file according to
     * current language code stored in Session.
     * 
     * @throws InvalidArgumentException
     */
    private function __Construct() {
        $languagesConfig = Config::get('languages')->language;
        $defaultCode = (string) $languagesConfig[0]['code'];
        foreach ($languagesConfig as $language) {
	    $this->languages[(string) $language['code']] = (string) $language['name'];
	}
	if (!Session::get('language')) {
	    Session::set('language',$defaultCode);
	}
    }

    /**
     * Instantiates (if not instantiated) new instance of Language class.
     * 
     */
    public static function create() {
	if (self::$instance == null) {
	    self::$instance = new Language();
	}
    }
    
    /**
     * Sets supplied language code into session.
     * 
     * @param String $code
     * @throws SynopsyException
     */
    public static function set($code) {
	self::create();
	if (!array_key_exists($code,self::$instance->languages)) {
	    throw new SynopsyException("Language code '$code' is not a valid language code!");
	}
	Session::set('language',$code);
	self::create(true);
    }
    
    /**
     * Retrieves current language code from session.
     * 
     * @return String
     */
    public static function get() {
	self::create();
	return Session::get('language');
    }
    
    /**
     * Returns system language information (language codes an names).
     * 
     * @return Array
     */
    public static function getAll() {
	self::create();
	return self::$instance->languages;
    }
    
}
