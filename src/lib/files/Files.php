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

namespace Synopsy\Files;

use FilesystemIterator;
use IteratorIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Synopsy\Exceptions\SynopsyException;

/**
 * Library for file manipulation functions.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Files {
    
    /**
     * Removes file / directory (incl. folder content) from server.
     * 
     * @param String $path
     * @return Boolean
     * @throws SynopsyException
     */
    public static function delete($path) {
	if (file_exists($path)) {
	    if (is_dir($path)) {
		$result = true;
		$files = self::getDirContent($path,true,true);
		foreach ($files as $object) {
		    if (is_file($object)) {
			if (!unlink($object)) {
			    $result = false;
			}			
		    } else {
			if (!rmdir($object)) {
			    $result = false;
			}
		    }
		}
		if (!rmdir($path)) {
		    $result = false;
		}
		return $result;
	    } else {
		return unlink($path);
	    }
	} else {
	    throw new SynopsyException("Path '$path' is not a valid path to a folder or a file!");
	}
    }
    
    /**
     * Moves file on server from one path to another.
     * 
     * @param String $path
     * @param String $newPath
     */
    public static function move($path,$newPath) {
        
    }
    
    /**
     * Returns files form within a directory.
     * 
     * @param String $directory
     * @param Boolean $recursive
     * @return Array
     */
    public static function getDirContent($directory,$recursive=false,$includeDirectories=false) {
	$files = [];
	if ($recursive) {
	    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,FilesystemIterator::SKIP_DOTS),($includeDirectories ? RecursiveIteratorIterator::CHILD_FIRST : null));
	    foreach(new IteratorIterator($rii) as $object => $data) {
		$files[] = $object;
	    }
	} else {
	    $dir = openDir($directory);
	    while ($f = readDir($dir)) {
                if ($f == '.' || $f == '..') {
                    continue;
                }
		$file = $directory.$f;
		if (is_file($file)) {
		    $files[] = $file;
		} elseif ($includeDirectories) {
                    $files[] = $file;
                }
	    }
	    sort($files);
	}
	return $files;
    }
 
    /**
     * Returns maximum file size from PHP/Apache configuration (in bytes).
     * 
     * @return Integer
     */
    public static function getMaxSystemFileSize() {
	return min((int) ini_get('upload_max_filesize'), (int) ini_get('post_max_size'), (int) ini_get('memory_limit'));
    }
    
}
