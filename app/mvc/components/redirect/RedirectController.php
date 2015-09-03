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
use Synopsy\Routing\Redirect;
use Synopsy\Routing\Route;

/**
 * @CONTROLLER
 * @Route-en redirect
 * @Route-sk presmerovanie
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class RedirectController extends Controller {
    
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
	$this->htmlView = $this->newView('RedirectView');
    }
    
    /**
     * @METHOD
     * @Route-en r404
     * @Route-sk r404
     */
    public function r404() {
        new Redirect(Redirect::c404);
    }
    
    /**
     * @METHOD
     * @Route-en r500
     * @Route-sk r500
     */
    public function r500() {
        new Redirect(Redirect::c500);
    }
    
    /**
     * @METHOD
     * @Route-en rHome
     * @Route-sk rHome
     */
    public function rHome() {
        new Redirect(Redirect::c301,new Route('home/HomeController'));
    }
    
    /* ---------------------------------------------------------------------- */
    /* Additional private methods */    
    
    // No methods
    
}