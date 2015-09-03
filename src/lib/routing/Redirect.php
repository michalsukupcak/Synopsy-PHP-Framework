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
use Synopsy\Routing\Route;

/**
 * Redirect
 *
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Redirect {
    
    const c200 = 'HTTP/1.1 200 Ok';
    const c301 = 'HTTP/1.1 301 Moved Permanently';
    const c403 = 'HTTP/1.1 403 Forbidden';
    const c404 = 'HTTP/1.1 404 Not Found';
    const c500 = 'HTTP/1.1 500 Internal Server Error';
    
    /**
     * Redirects current HTTP request
     * 
     * @param Route/String $url
     * @param String $code
     * @param Boolean $absolute
     * @throws SynopsyException
     */
    public function __Construct($code,$url=null) {
        if (!in_array($code,[self::c200,self::c301,self::c403,self::c404,self::c500])) {
            throw new SynopsyException("Redirect code '$code' is not a valid HTTP redirect code!");
        }
        if ($code == self::c404) {
            if ($url) {
                throw new SynopsyException("You can't set parameter \$url for error code Redirect::c404!");
            } else {
                $u = URL.'404.php';
            }
        } elseif ($code == self::c403) {
            if ($url) {
                throw new SynopsyException("You can't set parameter \$url for error code Redirect::c404!");
            } else {
                $u = URL.'403.php';
            }
        } elseif ($code == self::c500) {
            if ($url) {
                throw new SynopsyException("You can't set parameter \$url for error code Redirect::c500!");
            } else {
                $u = URL.'500.php';
            }            
        } else {
            if ($url instanceof Route) {
                $u = $url->get();
            } else {
                $u = $url;
            }
        }
        header($code);
        header("Location: $u");
    }
        
}
