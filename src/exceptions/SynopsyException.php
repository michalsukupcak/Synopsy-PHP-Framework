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

namespace Synopsy\Exceptions;

use Exception;

/**
 * Universal framework exception, use everywhere except for cases where more
 * complex exceptions are required.
 *
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
class SynopsyException extends Exception {
        
    /**
     * Constructor
     * 
     * @param String $type
     * @param String $message
     */
    public function __Construct($message = '') {
        $class = '{N/A}';
        $trace = $this->getTrace();
        if (is_array($trace)) {
            if (isset($this->getTrace()[0]['class'])) {
                $class = $this->getTrace()[0]['class'];
            } else {
                $class = '';
            }
        }
	parent::__construct("<p>".$class."</p>$message");
    }
        
}
