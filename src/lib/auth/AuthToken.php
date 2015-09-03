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

use Synopsy\Config\Config;
use Synopsy\Db\Database;
use Synopsy\Sql\Queries\Delete;
use Synopsy\Sql\Queries\Select;

/**
 * 
 *
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class AuthToken {
    
    public static function create($userId,$role) {
        return null;
    }
 
    public static function delete($token) {
        return null;
    }
    
    public static function deleteAll($userId) {
        return null;
    }
    
    /**
     * Validates given token to data in database. First checks if token is in
     * database. If a timeout is set in config.xml for jwt tokens, checks for
     * token timeout. If token has timed out, deletes token, otherwise confirm
     * token is valid.
     * 
     * @param string $token
     * @return boolean
     */
    public static function isValid($token) {
        $timeout = (int) Config::get('auth')->jwtExpires;
        Database::executeQuery((new Select(UserToken::$dbTable))->cols('id','timestamp')->where('token','=',$token));
        if ($timeout > 0) {
            if (Database::changed()) {
                $dbToken = Database::fetchOne();
                if (($dbToken['timestamp'] + $timeout) > time()) {
                    return true;
                } else {
                    Database::executeQuery((new Delete(UserToken::$dbTable))->where('id','=',$dbToken['id']));
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return Database::changed();
        }
    }
    
    public static function isAuthorized($token,$role) {
        return false;
    }
    
}