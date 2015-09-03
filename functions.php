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

/**
 * File containing procedural PHP functions.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */

/**
 * Functional representation of isset construct.
 * 
 * @param Mixed $var
 * @return Boolean
 */
function is_set($var) {
    return isset($var);
}

/**
 * Custom var_dump function alternative.
 * 
 * @param Mixed $var
 */
function dump($var,$recursive=null,$formatStrings=false) {
    if ($recursive === null) {
        $recursive = 0;
        $divs = true;
    } else {
        $divs = false;
    }
    if ($divs) {
        echo ''
            . '<div style="'
                . 'background: #FCFCFC;'
                . 'border: 1px solid #CCC;'
                . 'border-radius: 5px;'
                . 'font-family: Courier, monospace;'
                . 'font-size: .8em;'
                . 'margin: 0 0 5px 0;'
                . 'padding: 10px 15px;'
            . '">'
        . '';
    }
    if (is_object($var)) {   
        echo '<span style="color: #00C2DB;">Object</span> (Class: '.get_class($var).') #'.spl_object_hash($var).' {<br>';
        $reflectionClass = new ReflectionClass($var);
        $properties = $reflectionClass->getProperties();
        $parentReflectionClass = $reflectionClass->getParentClass();
        while ($parentReflectionClass) {
            $properties += $parentReflectionClass->getProperties();
            $parentReflectionClass = $parentReflectionClass->getParentClass();
        }
        foreach ($properties as $index => $property) {
            dump_spaces($recursive+1);
            $property->setAccessible(true);
            echo '[';
            if ($property->isStatic()) {
                echo 'static';
            } elseif ($property->isPrivate()) {
                echo 'private';
            } elseif ($property->isProtected()) {
                echo 'protected';
            } else {
                echo 'public';
            }
            echo '] ';
            if ($property->getDeclaringClass()->getName() != get_class($var)) {
                echo $property->getDeclaringClass()->getName();
                echo '->';
            }
            echo $property->getName();
            echo ': ';
            dump($property->getValue($var),$recursive+1,$formatStrings);
            echo '<br>';
        }
        dump_spaces($recursive);
        echo '}';
    } elseif (is_array($var)) {
        $c = count($var);
        echo '<span style="color: #FF9D00;">Array</span> ('.$c.') {';
        if ($c > 0) {
            echo '<br>';
            foreach ($var as $index => $value) {
                dump_spaces($recursive+1);
                if (is_integer($index)) {
                    echo '<span style="color: #7ECC00;">['.$index.']</span>';
                } else {
                    echo '<span style="color: #D9534F">[\''.$index.'\']</span>';
                }
                echo ' => ';
                dump($value,$recursive+1,$formatStrings);
                echo '<br>';
            }
            dump_spaces($recursive);
        }
        echo '}';
    } else {
        if (is_integer($var)) {
            echo 'int <span style="color: #7ECC00;">'.$var.'</span>';
        } elseif (is_string($var)) {
            echo 'string ('.strlen($var).') \'<span style="color: #D9534F">'.($formatStrings ? $var : htmlspecialchars($var)).'</span>\'';
        } elseif (is_float($var)) {
            echo 'float <span style="color: #A100DB;">'.$var.'</span>';
        } elseif (is_bool($var)) {
            echo 'boolean <span style="color: #006ADB;">'.($var ? 'true' : 'false').'</span>';
        } else {
            echo '<span style="color: #006ADB;">NULL</span>';
        }
    }
    if ($divs) {
        echo '</div>';
    }
}

/**
 * Dump support function for generating new.
 * 
 * @param Integer $repeats
 */
function dump_spaces($repeats) {
    for ($i = 0; $i < ($repeats) * 3; $i++) { echo '&nbsp;'; }
}

function synopsy_message($string,$color='#333') {
    return '<div style="'
        . 'background: white;'
        . 'border: 1px solid #CCC;'
        . 'box-shadow: 0 4px 2px -2px #999;'
        . 'color: '.$color.';'
        . 'cursor: pointer;'
        . 'font-family: sans-serif;'
        . 'font-size: .9em;'
        . 'margin: 10px;'
        . 'padding: 5px 10px;'
        . 'position: fixed;'
        . 'right: 5px;'
        . 'text-align: left;'
        . 'top: 5px;'
        . 'z-index: 999999;'
    . '" onClick="this.parentNode.removeChild(this);">'.$string.'</div>';
}