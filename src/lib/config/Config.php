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

namespace Synopsy\Config;

use SimpleXMLElement;
use Synopsy\Exceptions\SynopsyException;

/**
 * Singleton class holding application configuration file contents.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Config {
    
    /**
     * Singleton instance of Config class.
     * 
     * @var Config
     */
    private static $instance = null;
    
    /**
     * Array loaded with configuration file
     * 
     * @var Array
     */
    private $xml = [];
    
    /**
     * Loads SRC/config/config.xml file into local array
     * 
     * @throws SynopsyException
     */
    private function __Construct() {
	$xml = new SimpleXMLElement(APP.'config/config.xml',0,true);
	if ($xml) {
	    $this->xml = $xml;
	} else {
	    throw new SynopsyException('Unable to parse SRC/config/config.xml file!');
	}
    }
    
    /**
     * Returns (and creates if neccessary) instance of Config class
     * 
     * @return Array
     * @throws SynopsyException
     */
    public static function get($key=null) {
	if (self::$instance == null) {
	    self::$instance = new Config();
	}
	if ($key) {
	    if (!property_exists(self::$instance->xml,$key)) {
		throw new SynopsyException("Key '$key' does not exist in the configuration file array!");
	    }
            return self::$instance->xml->$key;
	} else {
	    return self::$instance->xml;
	}
    }
    
    /**
     * 
     * @return Array
     * @throws SynopsyException
     */
    public static function getDatabase() {
	$cfg = self::get('database');
        $cf = null;
        if (IS_LOCALHOST) {
            $cf = $cfg->local;
        } else {
            $cf = $cfg->remote;
        }
        $configFile = APP.'config/db/'.$cf.'.ini';
        if (!file_exists($configFile)) {
            throw new SynopsyException("Database configuration file '$configFile' doesn't exist!");
        }
        return parse_ini_file($configFile);
    }
    
}
