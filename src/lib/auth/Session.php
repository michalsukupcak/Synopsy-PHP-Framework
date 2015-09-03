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

namespace Synopsy\Auth;

use Synopsy\Exceptions\SynopsyException;

/**
 * Library used for session management.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Session {

    /**
     * 
     */
    const PREFIX = '';
    
    /**
     * 
     */
    const SID = '__SID';
    
    /**
     * Initialization of sessions
     * 
     * @param boolean $force
     */
    public static function start($force=false) {
	if (!isset($_SESSION[self::PREFIX.self::SID]) || $force) {
	    self::clear();
	    $_SESSION[self::PREFIX.self::SID] = session_id();
	}
    }
       
    /**
     * Lists through all active sessions and clears values from each session.
     * 
     */
    public static function clear() {
	foreach ($_SESSION as $key => $value) {
	    unset($_SESSION[$key]);
	}
    }
    
    /**
     * Completely invalidates current session (including session_id()).
     * 
     */
    public static function destroy() {
	session_unset();
	session_destroy();
    }
            
    /**
     * Set key for a value into session.
     * 
     * @param String $key
     * @param Mixed $value
     * @throws SynopsyException
     */
    public static function set($key,$value) {
	$_SESSION[self::PREFIX.$key] = $value;
    }
    
    /**
     * Get value for a key from a session.
     * 
     * @param type $key
     * @return type
     * @throws InvalidArgumentExceptions
     */
    public static function get($key) {
	if (isset($_SESSION[self::PREFIX.$key])) {
	    return $_SESSION[self::PREFIX.$key];
	} else {
	    return null;
	}
    }  
        
}
