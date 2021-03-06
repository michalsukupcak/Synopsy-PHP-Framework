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

use Synopsy\Config\Config;
use Synopsy\Mvc\Controller;

/**
 * Footer controller
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class FooterController extends Controller {
    
    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // No constants
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */    
    
    // No attributes
    
    /* ---------------------------------------------------------------------- */
    /**
     * Controller::onLoad()
     * 
     * Initial method of the controller, called immediately after creating new
     * controller instance.
     */
    protected function onLoad() {}
        
    /* ---------------------------------------------------------------------- */
    /* MVC methods */
    
    public function main() {
        // System information for copyright
	$systemConfig = Config::get('system');
        // Render view
	$this->htmlView = $this->newView('FooterView');
	$this->htmlView->setData([
            'copyright' => [
                'year' => date('Y'),
                'system' => [
                    'name' => (string) $systemConfig->name,
                    'version' => (string) $systemConfig->version,
                    'codename' => (string) $systemConfig->codename
                ]
            ]
	]);
    }
    
    /* ---------------------------------------------------------------------- */
    /**
     * Controller::beforeRender()
     * 
     * Final processing of controller data. Called automatically as last method
     * of controller before its rendering.
     */
    
    // Not used
    
    /* ---------------------------------------------------------------------- */
    /* Additional private methods */    
    
    // No methods
    
}