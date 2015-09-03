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

namespace Synopsy\Config;

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Role {
    
    private static $instance = null;
    
    private $roles = [];
    
    private function __Construct() {
        $rolesConfig = Config::get('auth')->roles->role;
        foreach ($rolesConfig as $role) {
            $this->roles[] = (string) $role;
        }
    }
    
    public static function exists($role) {
        if (self::$instance == null) {
            self::$instance = new Role();
        }
        return (in_array($role,self::$instance->roles));
    }
    
    public static function getAll() {
        if (self::$instance == null) {
            self::$instance = new Role();
        }
        return self::$instance->roles;
    }
    
}