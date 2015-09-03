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

namespace Synopsy\Mvc;

/**
 * 
 * 
 * @author Matej Čurilla <curilla@webdesign-studio.sk>
 */
final class Head {

    private static $instance = null;
    
    private $title = ''; 
    private $keywords = '';
    private $description = '';
    
    private function __Construct() {}
    
   /**
     * Set text to html head title.
     * 
     * @param type $title
     */
    public static function setTitle($title) {
        self::create();
        self::$instance->title = $title;
    }
    
    /**
     * Set keywords in the html head.
     * 
     * @param string
     */
    public static function setKeywords($keywords) {
        self::create();
        self::$instance->keywords = $keywords;
    }
    
    /**
     * Set description in html head.
     * 
     * @param type $description
     */
    public static function setDescription($description) {
        self::create();
        self::$instance->description = $description;
    }
    
    /**
     * Returns string to append to html title. This is usually appended
     * to page name.
     * 
     * @return String
     */
    public static function getTitle() {
        self::create();
        return self::$instance->title;
    }
    
    /**
     * Returns keywords for html head.
     * 
     * @return String
     */
    public static function getKeywords() {
        self::create();
        return self::$instance->keywords;
    }
    
    /**
     * Returns description string for html head.
     * 
     * @return type
     */
    public static function getDescription() {
        self::create();
        return self::$instance->description;
    }
    
    private static function create() {
        if (self::$instance == null) {
            self::$instance = new Head();
        }
    }
    
}