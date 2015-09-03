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

namespace Synopsy\Routing\Requests;

use Synopsy\Auth\AuthSession;
use Synopsy\Exceptions\InvalidUrlException;
use Synopsy\Exceptions\SynopsyException;
use Synopsy\Exceptions\UnauthenticatedException;
use Synopsy\Exceptions\UnauthorizedRoleException;
use Synopsy\Exceptions\UndefinedLanguageException;
use Synopsy\Lang\Language;
use Synopsy\Routing\RequestInterface;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class WebRequest implements RequestInterface { 
   
    /**
     *
     * @var String 
     */
    private $controllerFile = null;
    
    /**
     *
     * @var String 
     */
    private $controllerMaps = [];
    
    /**
     *
     * @var String 
     */
    private $controller = null;
    
    /**
     *
     * @var String 
     */
    private $method = null;
    
    /**
     *
     * @var String 
     */
    private $urlString = null;
    
    /**
     *
     * @var Mixed 
     */
    private $urlParameters = [];
    
    /**
     *
     * @var Integer 
     */
    private $urlDataStart = 3;
    
    /**
     *
     * @var Mixed 
     */
    private $data = [];
        
    /**
     * Parses URL string and sets controller file, maps, class and method
     * according to data in URL address (and default data from routes.php).
     * 
     */
    public function __Construct() {
        $this->urlString = filter_input(INPUT_GET,'url');
        $this->urlParameters = explode('/',$this->urlString);
        // Language
        if (isset($this->urlParameters[0])) {
            if (array_key_exists($this->urlParameters[0],Language::getAll())) {
                Language::set($this->urlParameters[0]);
            } else {
                throw new UndefinedLanguageException();
            }
        }
        // Controller
        if (isset($this->urlParameters[1])) {
            $controllerUrl = $this->urlParameters[1];
        } else {
            $controllerUrl = null;
        }
        // Method
        if (isset($this->urlParameters[2])) {
            $methodUrl = $this->urlParameters[2];
        } else {
            $methodUrl = null;
        }
        // Process URL
        $setDefaultMethod = true;
        $routes = cached_routes(Language::get());
        if ($controllerUrl) {
            if (array_key_exists($controllerUrl,$routes)) {
                $controllerEntry = $routes[$controllerUrl];
                $controllerPath = $controllerEntry['controller'];
                $controllerMaps = $controllerEntry['maps'];
                if ($methodUrl != null) {
                    $controllerMethods = $controllerEntry['methods'];
                    if (array_key_exists($methodUrl,$controllerMethods)) {
                        $setDefaultMethod = false;
                        $method = $controllerMethods[$methodUrl]['method'];
                    } else {
                        $this->urlDataStart = 2;
                    }
                }
            } else {
                throw new InvalidUrlException();
            }
        } else {
            $urls = cached_urls(Language::get());
            $controllerPath = default_controller();
            $controllerMaps = $urls[$controllerPath]['maps'];
            $controllerUrl = $urls[$controllerPath]['route'];
        }
        if ($setDefaultMethod) {
            $methodUrl = default_method();
            $method = default_method();
        }
        // Authentication      
        $authenticated = $routes[$controllerUrl]['methods'][$methodUrl]['authenticated'];
        if ($authenticated) {
            if (!AuthSession::isAuthenticated()) {
                throw new UnauthenticatedException();
            }
            $authorizedRole = false;
            $roles = $routes[$controllerUrl]['methods'][$methodUrl]['roles'];
            foreach ($roles as $role) {
                if (AuthSession::isAuthorized($role)) {
                    $authorizedRole = true;
                    break;
                }
            }
            if (!$authorizedRole) {
                throw new UnauthorizedRoleException();
            }
        }
        // Final configuration
        $controller = explode('/',$controllerPath);
        $this->controllerFile = $controllerPath.'.php';
        $this->controllerMaps = $controllerMaps;
        $this->controller = $controller[count($controller)-1];
        $this->method = $method;
        $this->setDataFromMaps();
    }
        
    /**
     * Sets $this->data array with values according to matching controller map.
     * 
     */
    private function setDataFromMaps() {
        $mapData = [];
        if (!empty($this->controllerMaps)) {
            $mapData = array_fill_keys(array_keys($this->controllerMaps),[]);
            foreach ($this->controllerMaps as $mapName => $map) {
                $mapData[$mapName] = [];
                $mapComponents = explode('/',$map);
                $i = $this->urlDataStart;
                foreach ($mapComponents as $mapComponent) {
                    if (isset($this->urlParameters[$i])) {
                        $url = $this->urlParameters[$i];
                        if ($mapComponent[0] == '<') {
                            $value = null;
                            $components = explode('|',substr($mapComponent,1,-1));
                            switch ($components[1]) {
                                case 'integer':
                                    $value = intVal($url);
                                    break;
                                case 'float':
                                    $value = floatVal($url);
                                    break;
                                case 'string':
                                    $value = $url;
                                    break;
                                default:
                                    throw new SynopsyException("Invalid datatype declaration for URL variable '$components[0]' in controller '$this->controller'!");
                            }
                            $mapData[$mapName][$components[0]] = $value;
                        } else {
                            if ($mapComponent != $url) {                                
                                unset($mapData[$mapName]);
                                break;
                            }
                        }
                        $i++;
                    } else {
                        break;
                    }
                }
            }
        }
        if (is_array($mapData)) {
            $this->data = reset($mapData);
        } else {
            $this->data = [];
        }
    }
    
    /**
     * 
     * @return String
     */
    public function getControllerFile() {
        return $this->controllerFile;
    }

    /**
     * 
     * @return String
     */
    public function getControllerMaps() {
        return $this->controllerMaps;
    }
    
    /**
     * 
     * @return String
     */
    public function getController() {
        return $this->controller;
    }
    
    /**
     * 
     * @return String
     */
    public function getMethod() {
        return $this->method;
    }
    
    /**
     * 
     * @return String
     */
    public function getUrlString() {
        return $this->urlString;
    }
    
    /**
     * 
     * @param String $key
     * @return Mixed
     */
    public function get($key) {
        if (is_array($this->data)) {
            if (array_key_exists($key,$this->data)) {
                return $this->data[$key];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

}