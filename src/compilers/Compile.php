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

namespace Synopsy\Compilers;

use Synopsy\Config\Config;

/**
 * Compiler load and init class.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Compile {

    /**
     * 
     */
    public function __Construct() {
        
        // Imports
        require_once(SRC.'compilers/CompilerException.php');
        require_once(SRC.'compilers/AbstractCompiler.php');
        
        // Read configuration
        $config = Config::get('compilers');
        
        // Css
        if ((string) $config->css == 'On') {
            require_once(SRC.'compilers/CssCompiler.php');
            (new CssCompiler())->compile();
        }
        
        // Js
        if ((string) $config->js == 'On') {
            require_once(SRC.'compilers/JsCompiler.php');
            (new JsCompiler())->compile();
        }
            
        // Entities
        if ((string) $config->entities == 'On') {
            require_once(SRC.'compilers/EntitiesCompiler.php');
            (new EntitiesCompiler())->compile();
        }
                
        // Controllers
        if ((string) $config->controllers == 'On') {
            require_once(SRC.'compilers/ControllersCompiler.php');
            (new ControllersCompiler())->compile();
        }
        
        // Apis
        if ((string) $config->apis == 'On') {
            require_once(SRC.'compilers/ApiCompiler.php');
            (new ApiCompiler())->compile();
        }

    }
    
}

// Self-execute Compile class
new Compile();