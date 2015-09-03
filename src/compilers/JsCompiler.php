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

use Synopsy\Config\Plugin;
use Synopsy\Files\Files;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class JsCompiler extends AbstractCompiler {

    /**
     * 
     */
    public function compile() { 
        // Load files
        $jsFiles = $this->getJsFiles();
        // Compile files
        if ($this->filesChanged('js',$jsFiles)) {
            $jsFileTimes = [];
            $compiledJsFile = CACHE.'script.js'; // Define destination file
            file_put_contents($compiledJsFile,''); // Clear destination file
            foreach ($jsFiles as $jsFile) { // Read content of each source file and append it into destination file
                if (file_exists($jsFile)) {
                    $jsFileTimes[$this->getFileName($jsFile)] = $this->getFileTime($jsFile);
                    file_put_contents($compiledJsFile,$this->minimizeJsFile($jsFile),FILE_APPEND); // Write file contents
                }
            }
            $this->setCacheFile('js',$jsFileTimes);
            $this->compilerExecuted();
        }
    } 
    
    private function getJsFiles() {
        $jsFiles = Plugin::get('js');
        foreach ($jsFiles as $key => $jsFile) {
            $jsFiles[$key] = DIR.$jsFile;
        }
        return array_merge($jsFiles,Files::getDirContent(DIR.'resources/javascript/'));
    }
    
    private function minimizeJsFile($jsFile) {
        #return file_get_contents($jsFile);
        $f1 = file_get_contents($jsFile); // Read file contents
        $f2 = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$f1); // Remove comments
        #return str_replace(["\r\n","\r","\n"],' ',$f2);
        return $f2."\n\n\n";
    }
    
}