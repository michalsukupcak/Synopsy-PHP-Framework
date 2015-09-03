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
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */

/**
 * Initialization of essential configuration constants and settings for all
 * entry points of the framework.
 */

// Set display errors according to config.ini file
ini_set('display_errors',0);

// Set error reporting according to config.ini file
error_reporting(null);

// Set timezone
date_default_timezone_set('Europe/Bratislava');

// Init session
session_start();

// Don't stop script upon client interrupt
ignore_user_abort(true);

// Load $_SERVER variable
$server = filter_input_array(INPUT_SERVER);

// Define application URL address
if (!isset($BASEFILE)) {
    trigger_error('You forgot to set $BASEFILE variable at the entry point of application, eg. directory index file like index.php or ajax.php! Add this line to the entry point file: $BASEFILE = basename(__FILE__);',E_USER_ERROR);
    $BASEFILE = null;
}
define('URL','http'.(((!empty($server['HTTPS']) && $server['HTTPS'] != 'off') || $server['SERVER_PORT'] == 443) ? 's' : '').'://'.$server['SERVER_NAME'].explode($BASEFILE,$server['SCRIPT_NAME'])[0]);

// Define main working directory
define('DIR',explode(basename(__FILE__),__FILE__)[0]);

// Define application directory
define('APP',DIR.'app/');

// Define application directory
define('CACHE',DIR.'cache/');

// Define source code directory
define('SRC',DIR.'src/');

// Is server running on localhost?
$remoteAddr = $server['REMOTE_ADDR'];
define('IS_LOCALHOST',($remoteAddr == '127.0.0.1' || $remoteAddr == '::1'));

// Create empty framework cache directories and folders
if (IS_LOCALHOST) {
    $required = [
        CACHE => 'd',
        CACHE.'apis.php' => 'f',
        CACHE.'routes.php' => 'f',
        CACHE.'script.js' => 'f',
        CACHE.'style.css' => 'f',
        APP.'entities' => 'd',
        DIR.'data' => 'd',
        DIR.'data/files' => 'd',
        DIR.'data/files/documents' => 'd',
        DIR.'data/files/images' => 'd',
        SRC.'compilers/backup' => 'd',
        SRC.'compilers/tmp' => 'd',
        SRC.'compilers/tmp/apis.ini' => 'f',
        SRC.'compilers/tmp/controllers.ini' => 'f',
        SRC.'compilers/tmp/css.ini' => 'f',
        SRC.'compilers/tmp/entities.ini' => 'f',
        SRC.'compilers/tmp/js.ini' => 'f'
    ];
    foreach ($required as $path => $type) {
        if (!file_exists($path)) {
            if ($type == 'd') {
                mkdir($path);
            } else {
                touch($path);
            }
        }
    }
}

// Synopsy libraries
$libraries = [
    SRC.'exceptions',
    SRC.'exceptions/extended',
    SRC.'lib/auth',
    SRC.'lib/common',
    SRC.'lib/config',
    SRC.'lib/db',
    SRC.'lib/db/databases',
    SRC.'lib/files',
    SRC.'lib/form',
    SRC.'lib/form/elements',
    SRC.'lib/lang',
    SRC.'lib/mvc',
    SRC.'lib/orm',
    SRC.'lib/rest',
    SRC.'lib/routing',
    SRC.'lib/sql',
    SRC.'lib/sql/queries',
    SRC.'entities',
    APP.'entities',
    APP.'shared'
];
foreach ($libraries as $library) {
    $dir = openDir($library);
    $files = [];
    while ($f = readDir($dir)) {
        $file = $library.'/'.$f;
        if (is_file($file)) {
            $files[] = $file;
        }
    }
    sort($files);
    foreach ($files as $file) {
        require_once($file);
    }
}

// Init session instance
Synopsy\Auth\Session::start();
