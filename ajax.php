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

// Set basefile for init.php
$BASEFILE = basename(__FILE__);

// Include error/exception handlers
require_once('./errors.php');

// Include procedural functions
require_once('./functions.php');

// Include initialization
require_once('./init.php');

// Open database connection
Synopsy\Db\Database::connect();

// Compiled routes
require_once(CACHE.'routes.php');

// Start AJAX processing
require_once(APP.'EntryPoint.php');
echo (new Synopsy\App\EntryPoint(Synopsy\App\EntryPoint::AJAX_ROUTER))->start();

// Close database connection
Synopsy\Db\Database::disconnect();
