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

/**
 * Exception for entity classes.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class UploadException extends SynopsyException {
    
    const FILE_LIMIT_EXCEEDED = 1;
    const FILE_SIZE_EXCEEDED = 2;
    const INVALID_FILE_EXTENSION = 3;
    const UPLOAD_FAILED = 4;
        
    public function __Construct($message = '', $code = 0, $previous = null) {
	parent::__construct($message,$code,$previous);
    }
    
}