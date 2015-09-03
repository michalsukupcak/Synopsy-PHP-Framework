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

namespace Synopsy\Form;

/**
 * Class containing functions used for verification/validation of variable
 * contents.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Validate {

    /**
     * Placeholder for required validation
     * 
     */
    const REQUIRED = 'required';
    
    /**
     * Verifies input string to match email address in format: [something]@[domain_name].[global_domain]
     */
    const EMAIL = 'email';
    const EMAIL_REGEX = '^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$';
    
    /**
     * Verifies input string to match a date format: dd.mm.YYYY
     */
    const DATE = 'date';
    const DATE_REGEX = '^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})$';
    
    /**
     * Verifies input string to match time format: hh:mm
     */
    const TIME = 'time';
    const TIME_REGEX = '^([0-9]{1,2}):([0-9]{1,2})$';
    
    /**
     * Verifies input string to match login requirements:
     * - at least 6 characters, which can be
     * - - numbers
     * - - lowercase letters
     * - - uppercase letters
     */
    const LOGIN = 'login';
    const LOGIN_REGEX = '^([a-zA-Z0-9]){6,}$';
    
    /**
     * Verifies input string to match password requirements:
     * - at least 8 characters, of which
     * - - at least 1 number
     * - - at least 1 lowercase letter
     * - - at least 1 uppercase letter
     * - - at least 1 special character (#?!@$%^&*-)
     * - contains valid symbols 0-9, a-z, A-Z, *, #, ?, !, =, -, _
     */
    const PASSWORD = 'password';
    const PASSWORD_REGEX = '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$';
    
    /**
     * 
     * @param String $placeholder
     * @param String $variable
     * @return Boolean
     */
    public static function variable($placeholder,$variable) {
        switch ($placeholder) {
            case self::EMAIL:
                return self::regex(self::EMAIL_REGEX,$variable);
            case self::DATE:
                return self::regex(self::DATE_REGEX,$variable);
            case self::TIME:
                return self::regex(self::TIME_REGEX,$variable);
            case self::LOGIN:
                return self::regex(self::LOGIN_REGEX,$variable);
            case self::PASSWORD:
                return self::regex(self::PASSWORD_REGEX,$variable);
            default:
                return self::regex($placeholder,$variable);
        }
    }
    
    /**
     * 
     * @param String $regex
     * @param String $variable
     * @return Boolean
     */
    public static function regex($regex,$variable) {
        return (preg_match('/'.$regex.'/',$variable));
    }
    /**
     * Verifies input string to match an email address.
     * 
     * @param String $email
     * @return Boolean
     */
    /*
    public static function email($email) {
	if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/",$email)) {
	    return false;
	}
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++) {
	    if (!preg_match("<(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$>",$local_array[$i])) {
	      return false;
	    }
	}
	if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) {
	    $domain_array = explode(".", $email_array[1]);
	    if (sizeof($domain_array) < 2) {
		return false;
	    }
	    for ($i = 0; $i < sizeof($domain_array); $i++) {
		if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/",$domain_array[$i])) {
		    return false;
		}
	    }
	}
	return true;
    }
    */
    
}
