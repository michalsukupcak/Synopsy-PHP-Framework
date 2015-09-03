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

use JWT;
use Synopsy\Config\Config;

/**
 * 
 *
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Token {
        
    /**
     * 
     * @param type $payload
     * @return type
     */
    public static function encode($payload) {
        require_once(DIR.'resources/plugins/jwt/Authentication/JWT.php');
        return JWT::encode($payload,self::getJwtKey());
    }
    
    /**
     * 
     * @param type $jwt
     * @return type
     */
    public static function decode($jwt) {
        require_once(DIR.'resources/plugins/jwt/Authentication/JWT.php');
        return JWT::decode($jwt,self::getJwtKey());
    }
    
    /**
     * 
     * @return string
     */
    private static function getJwtKey() {
        return (string) Config::get('auth')->jwtKey;
    }
    
}