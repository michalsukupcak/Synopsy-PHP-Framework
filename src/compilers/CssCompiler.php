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
final class CssCompiler extends AbstractCompiler {
 
    /**
     * 
     */
    public function compile() {
        $cssFiles = $this->getCssFiles();
        if ($this->filesChanged('css',$cssFiles)) {
            $cssFileTimes = [];
            $compiledCssFile = CACHE.'style.css'; // Define destination file
            file_put_contents($compiledCssFile,''); // Clear destination file
            foreach ($cssFiles as $cssFile) { // Read content of each source file and append it into destination file
                $cssFileTimes[$this->getFileName($cssFile)] = $this->getFileTime($cssFile);
                file_put_contents($compiledCssFile,$this->minimizeCssFile($cssFile),FILE_APPEND); // Write file contents
            }
            $this->setCacheFile('css',$cssFileTimes);
            $this->compilerExecuted();
        }
    }
    
    /**
     * 
     * @return type
     */
    private function getCssFiles() {
        $cssPluginFiles = Plugin::get('css');
        foreach ($cssPluginFiles as $key => $cssPluginFile) {
            $cssPluginFiles[$key] = DIR.$cssPluginFile;
        }
        return array_merge($cssPluginFiles,Files::getDirContent(DIR.'resources/css/'));
    }
    
    /**
     * 
     * @param type $cssFile
     * @return type
     */
    private function minimizeCssFile($cssFile) {
        $css1 = file_get_contents($cssFile); // Read file contents
        $css2 = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$css1); // Remove comments
        $css3 = str_replace(': ',':',$css2); // Remove spaces after colons
        return str_replace(["\r\n","\r","\n","\t",'  ','    ','    '],'',$css3); // Remove tabs, newlines and whitespaces
    }
    
}