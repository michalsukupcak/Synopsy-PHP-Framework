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

namespace Synopsy\Compilers;

/**
 * Abstract class for all file/data compilers in /src/scripts/compilers folder.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
abstract class AbstractCompiler {

    /**
     * Constructor, includes exception file.
     * 
     */
    public function __Construct() {}
    
    /**
     * Function executes main compiler code.
     * 
     * @return Boolean True if compiler modified any part of the system, false
     *                 if there were no changes.
     */
    public abstract function compile();
    
    /**
     * Echoes a message (unifies compiler message output design).
     * 
     * @param String $string
     */
    public function compilerExecuted() {
        echo synopsy_message('NOTICE: <b>'.get_called_class().' was executed!</b> Click message to dismiss.');
    }
    
    /**
     * Retrieves .ini file contents.
     * 
     * @param String $file
     * @return Mixed
     */
    protected function getCacheFile($file) {
        return parse_ini_file(SRC.'compilers/tmp/'.$file.'.ini');
    }
    
    /**
     * Writes an array into .ini file .
     * 
     * @param String $cacheFile
     * @param Mixed $array
     */
    protected function setCacheFile($cacheFile,$array) {
        $string = '';
        foreach ($array as $key => $value) {
            $string .= $key.' = '.$value."\n";
        }
        $fileHandler = fOpen(SRC.'compilers/tmp/'.$cacheFile.'.ini','w+');
        fWrite($fileHandler,$string);
        fClose($fileHandler);
    }
    
    /**
     * Retrieves file name (WITHOUT extension) from full file path.
     * 
     * @param String $filePath
     * @return String
     */
    protected function getFileName($filePath) {
        $f = explode('/',$filePath);
        $e = explode('.',$f[count($f)-1]);
        $c = count($e);
        if ($c > 2) {
            unset($e[$c-1]);
            return implode('.',$e);
        } else {
            return $e[0];
        }
    }
    
    /**
     * Retrives timestamp when file was modified (from full file path).
     * 
     * @param String $filePath
     * @return Integer
     */
    protected function getFileTime($filePath) {
        return filemtime($filePath);
    }
    
    /**
     * Compares records in a .ini file with array of files and determines if
     * some files were changed. Changed condition: File has no record in .ini
     * file (new file) or different timestamp than the timestamp saved in the
     * .ini file.
     * 
     * @param String $cacheFile
     * @param Mixed $files
     * @return Boolean
     */
    protected function filesChanged($cacheFile,$files) {
        $storedFileTimes = $this->getCacheFile($cacheFile);
        foreach ($files as $file) {
            if (file_exists($file)) {
                $fileName = $this->getFileName($file);
                $fileTime = $this->getFileTime($file);
                if (!array_key_exists($fileName,$storedFileTimes)) {
                    return true;
                } elseif ($fileTime != $storedFileTimes[$fileName]) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Removes whitespaces from Doc string (* & / characters)
     * 
     * @param String $doc
     * @return String
     */
    protected function removeWhitespaces($doc) {
        return preg_replace(['/\/\*/','/\*\//','/\*/'],'',$doc);
    }
        
}