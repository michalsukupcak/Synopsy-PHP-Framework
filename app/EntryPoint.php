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

namespace Synopsy\App;

use Synopsy\Config\Config;

/**
 * Entry point of the application.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class EntryPoint {
    
    const SYNC_ROUTER = 'sync';
    const AJAX_ROUTER = 'ajax';
    const REST_ROUTER = 'rest';
    
    /**
     *
     * @var Router 
     */
    private $router;
    
    /**
     * Entry point of the application.
     * 
     * @throws SynopsyException
     */
    public function __Construct($type) { 
        $routerConfig = Config::get('routing');
        switch ($type) {
            case self::SYNC_ROUTER:
                $router = (string) $routerConfig->syncRouter;
                break;
            case self::AJAX_ROUTER:
                $router = (string) $routerConfig->ajaxRouter;
                break;
            case self::REST_ROUTER:
                $router = (string) $routerConfig->restRouter;
                break;
            default:
                throw new SynopsyException("Invalid router type in EntryPoint class: '$type'! Should be SYNC, AJAX or REST!");
        }
        $routerFile = SRC.'lib/routing/routers/'.$router.'.php';
        if (!file_exists($routerFile)) {
            throw new SynopsyException("Router '$router' doesn't exist!");
        }
        require_once($routerFile);
        $c = "Synopsy\Routing\Routers\\$router";
        $this->router = new $c();
    }
    
    /**
     * 
     * @return String
     */
    public function start() {
        return $this->router->run();
    }
            
}
