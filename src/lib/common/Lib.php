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
 * Library containing general-purpose functions.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Lib {

    /**
     * Unescapes user input loaded from database as string (converts newlines to
     * br tags, all other tags are kept as escaped html entities).
     * 
     * @param String $string
     * @return String
     */
    public static function unescapeString($string) {
	return nl2br($string,true);
    }
    
    /**
     * Unescapes user input loaded from database as html code (converts all
     * escaped tags from html entities back to html tags).
     * 
     * !!! WARNING !!!
     * This method allows all potential forms of *XSS ATTACKS*! Should be used
     * only if *ABSOLUTELY* neccessary.
     * 
     * @param String $string
     * @return String
     */
    public static function unescapeHtml($string) {
	return nl2br(htmlspecialchars_decode($string,true));
    }
    
    /**
     * Removes special characters from supplied string and leaves only
     * numbers, letters of english alphabet and the character "-".
     * 
     * @param type $string
     * @return type 
     */
    public static function removeSpecialChars($string,$dot=false) {
	$chars = [
	    'a' => ['á','A'],
	    'b' => ['B'],
	    'c' => ['č','ć','C','Č','Ć'],
	    'd' => ['ď','D','Ď'],
	    'e' => ['é','E','É'],
	    'f' => ['F'],
	    'g' => ['G'],
	    'h' => ['H'],
	    'i' => ['í','I','í'],
	    'j' => ['J'],
	    'k' => ['K'],
	    'l' => ['ĺ','ľ','L','Ĺ','Ľ'],
	    'm' => ['M'],
	    'n' => ['ń','ň','N','Ń','Ň'],
	    'o' => ['ó','ô','O','Ó','Ô'],
	    'p' => ['P'],
	    'q' => ['Q'],
	    'r' => ['ŕ','ř','R','Ŕ','Ř'],
	    's' => ['š','S','Š'],
	    't' => ['ť','T','Ť'],
	    'u' => ['ú','U','Ú'],
	    'v' => ['V'],
	    'z' => ['ž','Z','Ž'],
	    'w' => ['W'],
	    'x' => ['X'],
	    'y' => ['ý','Y','Ý'],
	    '-' => [' ']
	];
	if ($dot) {
	    $chars['.'] = ['\.'];
	    $chars[''] = [',','!','\?','[^A-Za-z0-9-\.]'];
	} else {
	    $chars[''] = ['\.',',','!','\?','[^A-Za-z0-9-]'];
	}
	foreach ($chars as $replacement => $patterns) {
	    if (is_array($patterns)) {
		$delimitedPatterns = [];
		foreach ($patterns as $pattern) {
		    $delimitedPatterns[] = '/'.$pattern.'/';
		}
		$string = preg_replace($delimitedPatterns,$replacement,$string);
	    }
	}
	return trim($string,'-');
    }
    
}
