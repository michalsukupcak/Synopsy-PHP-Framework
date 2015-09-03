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
 * 
 *
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class AuthSession {
    
    public static function create($userId,$roles) {
        foreach ($roles as $role) {
            if (!Role::exists($role)) {
                throw new SynopsyException("Role '$role' is not defined in config.xml file!");
            }
        }
        Session::set('__userId',$userId);
        Session::set('__userRoles',$roles);
    }
    
    public static function delete() {
        Session::set('__userId',null);
        Session::set('__userRoles',null);
    }
    
    public static function isAuthenticated() {
        return (Session::get('__userId') > 0);
    }
    
    public static function isAuthorized($role) {
        return (in_array($role,Session::get('__userRoles')));
    }
        
}