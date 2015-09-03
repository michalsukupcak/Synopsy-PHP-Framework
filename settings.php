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

// Allow only on localhost
if (!IS_LOCALHOST) {
    die(synopsy_message('<strong>ACCESS DENIED:</strong> This page is only accessible on local/development server instance!</div>','red'));
}

// Open database connection
Synopsy\Db\Database::connect();

// Run compiler scripts
require_once(SRC.'compilers/Compile.php');

// Compiled routes
require_once(CACHE.'routes.php');

// Compiled APIs
require_once(CACHE.'apis.php');

// System configuration
$systemConfig = Synopsy\Config\Config::get('system');

?>
<!DOCTYPE html>
<!-- Copyright (2014) Webdesign Studio s.r.o. (synopsy@webdesign-studio.sk) -->
<html>
    <head>
	<meta charset="utf-8">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="Webdesign Studio (c) 2014">
	<meta name="language" value="en">
	<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
	<title>Synopsy PHP Framework Settings Page</title>
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Ubuntu&subset=latin,latin-ext">
        <link rel="stylesheet" type="text/css" href="<?=URL?>resources/plugins/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="<?=URL?>resources/plugins/bootstrap/css/bootstrap-theme.min.css">
        <link rel="stylesheet" type="text/css" href="<?=URL?>resources/plugins/error/error.css">
        <style>
            #reload {
                position: fixed;
                right: 15px;
                top: 55px;
                z-index: 999999;
            }
            
            #permissions {
                margin: 5px 0 0 0;
            }
            #permissions li {
                padding: 5px 0;
            }
            #permissionDeleteForm {
                display: inline;
            }
            
            textarea.box {
                background: #FAFAFA;
                display: block;
                padding: 10px;
                width: 100%;
            }
        </style>
    </head>
    <body>
        <div id="wrapper">
            <a href="" id="reload" class="btn btn-default">Reload page</a>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-6" id="topBlue"></div>
                    <div class="col-xs-6" id="topGreen"></div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 text-right" id="email">
                        <a href="mailto:support@webdesign-studio.sk">support@webdesign-studio.sk</a>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 text-center" id="logo">
                        <a href="<?=URL?>">
                            <img src="<?=URL?>resources/plugins/error/logo.png" title="Webdesign Studio s.r.o." alt="Webdesign Studio s.r.o.">
                        </a>
                    </div>
                </div>
            </div>
            <div class="container-fluid" id="title">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h1><strong>Synopsy PHP Framework Settings Page</strong></h1>
                        <div class="container">
                            <h3>
                                <i>
                                    Be careful! Weird magic happens in these parts of the woods.
                                </i>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div class="container">
                
                <!-- Sessions -->
                <div class="row">
                    <div class="col-xs-6 col-xs-offset-3 text-left">
                        <h3>List of active sessions:</h3>
                        <?
                        if (filter_input(INPUT_POST,'resetSession')) {
                            Synopsy\Auth\Session::clear();
                            Synopsy\Auth\Session::destroy();
                            echo '<br><div class="alert alert-success">All sessions have been cleared!</div>';
                        }
                        ?>
                        <ul>
                            <?
                            foreach ($_SESSION as $key => $value) {
                                echo '<li>'.$key.' = '.$value.'</li>';
                            }
                            ?>
                        </ul>
                        <form method="post" action="">
                            <button name="resetSession" value="1" class="btn btn-danger btn-xs">Clear sessions</button>
                        </form>
                    </div>
                </div>
                
                <hr>
                
                <!-- Passwords -->
                <div class="row">
                    <div class="col-xs-6 col-xs-offset-3 text-left">
                        <h3>Generate password hash from string:</h3>
                        <form method="post" action="" class="form">
                            <div class="row">
                                <div class="col-xs-9">
                                    <input type="text" name="passwordString" value="<? echo filter_input(INPUT_POST,'passwordString'); ?>" placeholder="String" class="form-control input-sm">
                                </div>
                                <div class="col-xs-3">
                                    <button name="generatePassword" value="1" class="btn btn-primary btn-sm">Generate password hash</button>
                                </div>
                            </div>
                        </form>
                        <?
                        if (filter_input(INPUT_POST,'generatePassword')) {
                            ?>
                            <br>
                            <h4>Original string:</h4>
                            <textarea class="box" rows="1" disabled><? echo filter_input(INPUT_POST,'passwordString'); ?></textarea>
                            <br>
                            <h4>Generated hash:</h4>
                            <textarea class="box" rows="3" disabled><? echo Synopsy\Auth\Password::generateHash(filter_input(INPUT_POST,'passwordString')); ?></textarea>
                            <?
                        }
                        ?>
                    </div>
                </div>
                <hr>
                
                <!-- Passwords -->
                <div class="row">
                    <div class="col-xs-6 col-xs-offset-3 text-left">
                        <h3>REST API Explorer:</h3>
                        Work in progress ...
                        <?
                        $apiVersions = Synopsy\Files\Files::getDirContent(APP.'rest/',false,true);
                        foreach ($apiVersions as $v) {
                            $a = explode('/',$v);
                            $apiVersion = array_pop($a);
                            $apis = cached_apis($apiVersion);
                            foreach ($apis as $apiName => $apiData) {
                                
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <br>
                
            </div>
            <br>
            <br>
            <div id="footer">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <a href="<?=URL?>">
                                <img src="<?=URL?>resources/plugins/error/logo-grayscale.png" title="Webdesign Studio s.r.o." alt="Webdesign Studio s.r.o.">
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            Copyright &copy; <?=date('Y')?> <a href="http://webdesign-studio.sk" target="_blank">Webdesign Studio s.r.o.</a>
                            <br>
                            Powered By <a href="http://synopsy.webdesign-studio.sk" target="_blank"><?=(string) $systemConfig->name?></a> <?=(string) $systemConfig->version?> (<?=(string) $systemConfig->codename?>)
                        </div>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </body>
</html>
<?

Synopsy\Db\Database::disconnect();