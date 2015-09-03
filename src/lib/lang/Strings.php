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

use Synopsy\Exceptions\SynopsyException;

/**
 * Library used to access localized string in /app/strings/*.ini files.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Strings {
    
    /**
     * Singleton instance
     * 
     * @var Language
     */
    private static $instance = null;
    
    /**
     * Parsed array created from language files
     * 
     * @var Array
     */
    private $languageArray = [];
    
    /**
     * Loads (if not already loaded) content of language file according to
     * current language code stored in Session.
     * 
     * @throws InvalidArgumentException
     */
    private function __Construct() {
	$this->languageArray = parse_ini_file(APP.'strings/'.Language::get().'.ini');
    }
        
    /**
     * Instantiates (if not instantiated) new instance of Language class.
     * 
     */
    public static function create() {
	if (self::$instance == null) {
	    self::$instance = new Strings();
	}
    }
    
    /**
     * Returns localized string according to supplied key.
     * 
     * @param String $key
     * @return String
     * @throws SynopsyException
     */
    public static function get($key) {
        self::create();
	if ($key == null) {
	    throw new SynopsyException('Parameter $key can\' be empty!');
	}
	if (!array_key_exists($key,self::$instance->languageArray)) {
	    throw new SynopsyException('Key \''.$key.'\' does not exist in language file \''.Language::get().'.ini\'!');
	}
	return self::$instance->languageArray[$key];
    }
    
    /**
     * 
     * @param type $key
     * @param type $values
     * @return type
     */
    public static function getReplaced($key,$values) {
        self::create();
	$string = self::$instance->get($key);
	$placeholders = [];
	$replaces = [];
        $c = count($values);
	for ($i = 0; $i < $c; $i++) {
	    $placeholders[] = '{'.($i).'}';
	    $replaces[] = $values[$i];
	}
	return str_replace($placeholders,$replaces,$string);
    }
    
}
