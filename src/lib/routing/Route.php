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

namespace Synopsy\Routing;

use Synopsy\Exceptions\SynopsyException;
use Synopsy\Lang\Language;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Route {
    
    /**
     * 
     */
    const AJAX_LINK = '__ajaxLink';
    
    /**
     *
     * @var type 
     */
    private $url = null;
    
    /**
     * Coverts path to router and method into URL address.
     * 
     * Format of parameter $route is: <path_to_controller>:<method>, where
     * <method> can be ommited.
     * 
     * Example usage: pages/HomeController:demo
     * 
     * Format of parameter $var is: <var1>=<value1>/<var1>=<value1>..., where
     * variable names (var1, var2, ...) are names of variables from map
     * parameter.
     * 
     * Example: var1=10/var2=hello...
     * 
     * @param String $route
     * @param String $map 
     * @param String $vars
     * @throws ViewException
     */    
    public function __Construct($route,$map=null,$vars=null,$relative=false) {
        $languageCode = Language::get();
        $urls = cached_urls($languageCode);
        if (!$route) {
            throw new SynopsyException("Parameter 'url' can't be empty!");
        }
        $routeComponents = explode(':',$route);
        $c = $routeComponents[0];
        if (!array_key_exists($c,$urls)) {
            throw new SynopsyException("Controller '$c' is not defined in cached_urls!");
        }
        $controller = $urls[$c];
        if (array_key_exists(1,$routeComponents)) {
            $m = $routeComponents[1];
            $methods = $controller['methods'];
            if (!array_key_exists($m,$methods)) {
                throw new SynopsyException("Method '$m' $routeComponents[1] is not defined in cached_urls for controller '$controller'!");
            }
            $method = '/'.$methods[$m];
        } else {
            $method = '';
        }
        if ($map && $vars) {
            if (!is_array($vars)) {
                throw new SynopsyException('Parameter $vars must be an array!');
            }
            $variables = '';
            $m = $controller['maps'][$map];
            if (!$m) {
                throw new SynopsyException("Map '$map' is not defined in cached_urls for controller '$controller'");
            }
            $components = explode('/',$m);
            $keys = array_keys($vars);
            $prefix = '';
            $i = 0;
            foreach ($components as $component) {
                if ($component[0] == '<') {
                    if (isset($keys[$i])) {
                        $k = $keys[$i];
                        $v = $vars[$k];
                        $c = explode('|',substr($component,1,-1));
                        if ($k == $c[0]) {
                            $variables .= $prefix.$v.'/';
                            $prefix = '';
                            $i++;
                        } else {
                            throw new SynopsyException("Parameter used in map '$map' with value '$c[0]' is different then expected parameter '$k'!");
                        }
                    } else {
                        break;
                    }
                } else {
                    $prefix .= $component.'/';
                }
            }
            $variables = '/'.$variables;
        } else {
            $variables = null;
        }
        $this->url = rtrim(($relative ? '' : URL).$languageCode.'/'.$controller['route'].$method.$variables,'/');        
    }
    
    public function get() {
        return $this->url;
    }
    
}
