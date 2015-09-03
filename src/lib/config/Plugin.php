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

use Synopsy\Exceptions\SynopsyException;

/**
 * Singleton class processing system-wide plugins from Config class (defined
 * in SRC/config/config.xml file).
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Plugin {
    
    /**
     * Singleton instance of plugins class
     * 
     * @var Plugins
     */
    private static $instance = null;
    
    /**
     * List of javascript files
     * 
     * @var Array
     */
    private $js = [];
    
    /**
     * List of css files
     * 
     * @var Array
     */
    private $css = [];
    
    /**
     * Constructor. Retrieves plugins XML data from Config class and loads
     * them to array based on their filetype.
     * 
     * @throws SynopsyException
     */
    private function __Construct() {
	$pluginsConfig = Config::get('plugins')->plugin;
	foreach ($pluginsConfig as $pluginConfig) {
            $files = $pluginConfig->file;
	    $d = count($files);
	    for ($j = 0; $j < $d; $j++) {
		$file = $files[$j];
		switch ((string) $file['type']) {
		    case 'js':
			$this->js[] = (string) $file['src'];
			break;
		    case 'css':
			$this->css[] = (string) $file['src'];
			break;
		    default:
			throw new SynopsyException("Plugin defined in &lt;plugins&rt; section of XML file has invalid type!");
		}
	    }
	}
    }
    
    /**
     * Returns array of plugin files according to specified type
     * 
     * @param String $type
     * @return Array
     * @throws SynopsyException
     */
    public static function get($type) {
	if (self::$instance == null) {
	    self::$instance = new Plugin();
	}
	switch ($type) {
	    case 'js':
		return self::$instance->js;
	    case 'css':
		return self::$instance->css;
	    default:
		throw new SynopsyException("Parameter \$type has value '$type', which is not valid!");
	}
    }
    
}
