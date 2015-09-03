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

// Load $_SERVER variable
$server = filter_input_array(INPUT_SERVER);

// Define application URL address
define('URL','http'.(((!empty($server['HTTPS']) && $server['HTTPS'] != 'off') || $server['SERVER_PORT'] == 443) ? 's' : '').'://'.$server['SERVER_NAME'].explode(basename(__FILE__),$server['SCRIPT_NAME'])[0]);

// Define main working directory
define('DIR',explode(basename(__FILE__),__FILE__)[0]);

// Define source code directory
define('SRC',DIR.'src/');

// Define application directory
define('APP',DIR.'app/');

// Required includes
require_once(SRC.'exceptions/SynopsyException.php');
require_once(SRC.'lib/config/Config.php');

// System configuration
$systemConfig = Synopsy\Config\Config::get('system');

/**
 * 403 Forbidden page
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
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
	<title>403 Forbidden</title>
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Ubuntu&subset=latin,latin-ext">
        <link rel="stylesheet" type="text/css" href="<?=URL?>resources/plugins/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="<?=URL?>resources/plugins/bootstrap/css/bootstrap-theme.min.css">
        <link rel="stylesheet" type="text/css" href="<?=URL?>resources/plugins/error/error.css">
    </head>
    <body>
        <div id="wrapper">
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
                        <h1><strong>403 Forbidden</strong></h1>
                        <div class="container">
                            <h3>
                                <i>
                                    You're not allowed to see this content, sorry.
                                </i>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        We're sorry, but the content on this site is kind of secret.
                        <br>
                        Actually, its not only kind of secret, its seriously secret.
                        <br>
                        In fact, its so secret that even its secrets have their own secrets.
                        <br>
                        <br>
                        Long story short, its all really hush-hush super secret stuff and you can't see it, like never ever. So, yeah, bye!
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <img src="<?=URL?>resources/plugins/error/kitty.jpg" id="puppy">
                    </div>
                </div>
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