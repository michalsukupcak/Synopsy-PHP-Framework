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

/**
 * Class containing function for password manipulation.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Password {

    /**
     * Generates hash from passowrd.
     * 
     * @param String $password
     * @return String
     */
    public static function generateHash($password) {
	$chars = array(1 => '0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	$max = count($chars);
	$prefix = '';
	$postfix = '';
	for ($i = 0; $i < 16; $i++) {
	    $prefix .= $chars[mt_rand(1,$max)];
	    $postfix .= $chars[mt_rand(1,$max)];
	}
	return $prefix.self::hash($prefix.$password.$postfix).$postfix;
    }

    /**
     * Generates SHA512 hash from input string.
     * 
     * @param String $string
     * @return String
     */
    public static function hash($string) {
	return hash('sha512',$string);
    }
    
    /**
     * Verifies a password against a hash.
     * 
     * @param String $password
     * @param String $hash
     * @return Boolean
     */
    public static function compareHashAndString($password,$hash) {
	$prefix = substr($hash,0,16);
	$postfix = substr($hash,144,16);
	$hashedInput =  $prefix.self::hash($prefix.$password.$postfix).$postfix;
	return ($hashedInput == $hash);
    }
    
}
