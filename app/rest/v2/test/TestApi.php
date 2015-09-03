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

use Synopsy\Rest\Api;

/**
 * @API
 * @Route test
 * @Map x {user}/<userId|integer>
 * @Map y <a|string>/{fluff}/<b|integer>
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class TestApi extends Api {
    
    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // No constants
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */    
    
    // No attributes
    
    /* ---------------------------------------------------------------------- */
    /**
     * Api::onLoad()
     * 
     * Initial method of the api, called immediately after creating new api
     * instance.
     */
    protected function onLoad() {}
        
    /* ---------------------------------------------------------------------- */
    /* API methods */
    
    /**
     * @CALL
     * @Route save-something
     */
    public function saveSomething() {
        $this->json = new Json(true,'Test API, nothing but mushrooms here now.');
    }
            
    /* ---------------------------------------------------------------------- */
    /* Additional private methods */    
    
    // No methods
    
}