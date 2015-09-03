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

use Synopsy\Auth\Token;
use Synopsy\Rest\Api;
use Synopsy\Rest\Json;

/**
 * @API
 * @Route demo
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class DemoApi extends Api {
    
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
    protected function onLoad() {
        $this->loadModels([
            'components/auth/AuthModel'
        ]);
    }
        
    /* ---------------------------------------------------------------------- */
    /* API methods */
    
    /**
     * @CALL
     * @Route token
     */
    public function getToken() {
        $payload = [
            'user' => 1,
            'time' => time()
        ];
        $token = Token::encode($payload);
        $this->storage->deleteAllTokens();
        $this->storage->addNewToken($token);
        $this->json = new Json(true,"Generated JWT Token: $token");
    }
    
    /**
     * @CALL
     * @Route authenticated-resource
     * @Authenticated
     */
    public function getAuthenticatedResource() {
        $this->json = new Json(true,'Authenticated resource!');
    }
            
    /* ---------------------------------------------------------------------- */
    /* Additional private methods */    
    
    // No methods
    
}