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

use Synopsy\Mvc\Controller;

/**
 * @CONTROLLER
 * @Route-en db
 * @Route-sk databaza
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class DbController extends Controller {
    
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
	$this->htmlView = $this->newView('DbView');
    }
    
    /* ---------------------------------------------------------------------- */
    /* Additional private methods */    
    
    // No methods
    
}