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

use Synopsy\Auth\AuthToken;
use Synopsy\Exceptions\MissingAuthHeaderException;
use Synopsy\Exceptions\UnauthenticatedException;
use Synopsy\Exceptions\UnauthorizedRoleException;
use Synopsy\Exceptions\UndefinedLanguageException;
use Synopsy\Exceptions\InvalidUrlException;
use Synopsy\Lang\Language;
use Synopsy\Routing\RequestInterface;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class ApiRequest implements RequestInterface {
    
    /**
     *
     * @var String
     */
    private $version = null;
    
    /**
     *
     * @var String 
     */
    private $apiFile = null;
    
    /**
     *
     * @var String 
     */
    private $apiMaps = [];
    
    /**
     *
     * @var String 
     */
    private $api = null;
    
    /**
     *
     * @var String 
     */
    private $call = null;
    
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
    private $urlDataStart = 4;
    
    /**
     *
     * @var Mixed 
     */
    private $data = [];
    
    /**
     * 
     * @throws InvalidUrlException
     * @throws UndefinedLanguageRequestException
     */
    public function __Construct() {
        $apis = null;
        $this->urlString = filter_input(INPUT_GET,'url');
        $this->urlParameters = explode('/',$this->urlString);
        // Version
        if (isset($this->urlParameters[0])) {
            $apis = cached_apis($this->urlParameters[0]);
        } else {
            throw new InvalidUrlException();
        }
        // Language
        if (isset($this->urlParameters[1])) {
            if (array_key_exists($this->urlParameters[1],Language::getAll())) {
                Language::set($this->urlParameters[1]);
            } else {
                throw new UndefinedLanguageException();
            }
        } else {
            throw new InvalidUrlException();
        }
        // Api
        if (isset($this->urlParameters[2])) {
            $apiUrl = $this->urlParameters[2];
        } else {
            throw new InvalidUrlException();
        }
        // Call
        if (isset($this->urlParameters[3])) {
            $callUrl = $this->urlParameters[3];
        } else {
            throw new InvalidUrlException();
        }
        // Process URL
        if (!array_key_exists($apiUrl,$apis)) {
            throw new InvalidUrlException();
        }
        $apiEntry = $apis[$apiUrl];
        $apiPath = $apiEntry['api'];
        $apiMaps = $apiEntry['maps'];
        $apiCalls = $apiEntry['calls']; 
        if (!array_key_exists($callUrl,$apiCalls)) {
            throw new InvalidUrlException();
        }
        $call = $apiCalls[$callUrl]['call'];
        // Authentication
        $authenticated = $apis[$apiUrl]['calls'][$callUrl]['authenticated'];
        if ($authenticated) {
            $headers = getAllHeaders();
            if (!isset($headers['x-auth-token'])) {
                throw new MissingAuthHeaderException();
            }
            $authToken = $headers['x-auth-token'];
            if (!AuthToken::isValid($authToken)) {
                throw new UnauthenticatedException();
            }
            $authorizedRole = false;
            $roles = $apis[$apiUrl]['calls'][$callUrl]['roles'];
            if (!empty($roles)) {
                foreach ($roles as $role) {
                    if (AuthToken::isAuthorized(null,$role)) {
                        $authorizedRole = true;
                        break;
                    }
                }
                if (!$authorizedRole) {
                    throw new UnauthorizedRoleException();
                }
            }
        }
        // Final configuration
        $api = explode('/',$apiPath);
        $this->apiFile = $apiPath.'.php';
        $this->apiMaps = $apiMaps;
        $this->api = $api[count($api)-1];
        $this->call = $call;
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
    public function getApiFile() {
        return $this->apiFile;
    }

    /**
     * 
     * @return String
     */
    public function getApiMaps() {
        return $this->apiMaps;
    }
    
    /**
     * 
     * @return String
     */
    public function getApi() {
        return $this->api;
    }
    
    /**
     * 
     * @return String
     */
    public function getCall() {
        return $this->call;
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