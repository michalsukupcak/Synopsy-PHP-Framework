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

namespace Synopsy\Common;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Date {
    
    const DATE = 'j.n.Y';
    const TIME = 'H:i';
    const TIME_PRECISE = 'H:i:s';
    const DATETIME = 'j.n.Y H:i';
    const DATETIME_PRECISE = 'j.n.Y H:i:s';
    const TIMEDATE = 'H:i j.n.Y';
    const TIMEDATE_PRECISE = 'H:i:s j.n.Y';
    
    public static function toString($value,$format) {
        
    }
    
    public static function toTimestamp($value,$format) {
        
    }
    
}
