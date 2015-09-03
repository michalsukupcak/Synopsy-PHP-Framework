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
 * File containing error handling functions.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */

/* -------------------------------------------------------------------------- */
/* Register error functions */

// Register error handler
set_error_handler('synopsy_error_handler');

// Register shutdown handler
register_shutdown_function('synopsy_shutdown_handler');

// Register exception handler
set_exception_handler('synopsy_exception_handler');

/* -------------------------------------------------------------------------- */
/* Error functions implementation */

/**
 * Custom error handler.
 * 
 * @param type $errorLevel
 * @param type $errorMessage
 * @param type $errorFile
 * @param type $errorLine
 * @param type $errorContext
 */
function synopsy_error_handler($errorLevel,$errorMessage=null,$errorFile=null,$errorLine=0,$errorContext=null) {
    $errorNames = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED'
    ];
    switch ($errorLevel) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
            $background = '#C42B2E';
            $title = 'FATAL ERROR';
            break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
        case E_USER_WARNING:
            $background = '#FFAE00';
            $title = 'WARNING';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_STRICT:
        case E_USER_WARNING:
            $background = '#FFE100';
            $title = 'NOTICE';
            break;
        default:
            $background = '#BBBBBB';
            $title = 'OTHER';
            break;
    }
    __synopsy_error_font();
    __synopsy_error_style();
    __synopsy_trace_style();
    ?>
    <table class="__synopsyError" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="__title" colspan="2" style="background: <? echo $background; ?>;">
                    <div>
                        <? echo $title; ?>
                    </div>
                    <? echo $errorMessage ?>
                </td>
            </tr>
            <tr>
                <td class="__short">Error type:</td>
                <td><? echo $errorNames[$errorLevel]; ?></td>
            </tr>
            <tr>
                <td class="__short">Message:</td>
                <td><? echo $errorMessage; ?></td>
            </tr>
            <tr>
                <td class="__short">In file:</td>
                <td><? echo $errorFile; ?></td>
            </tr>
            <tr>
                <td class="__short">On line:</td>
                <td><? echo $errorLine; ?></td>
            </tr>
            <tr>
                <td class="__short">Debug backtrace:</td>
                <td><? __synopsy_backtrace(); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="__footer">Synopsy PHP Framework @ <? echo date('H:i:s j.n.Y'); ?></td>
            </tr>
        </tbody>
    </table>
    <?
}

/**
 * Custom shutdown hander.
 * 
 */
function synopsy_shutdown_handler() {
    $error = error_get_last();
    if ($error) {
        synopsy_error_handler($error['type'],$error['message'],$error['file'],$error['line']);
    }
}

/**
 * Custom exception handler.
 * 
 * @param Exception $exception
 */
function synopsy_exception_handler($exception) {
    __synopsy_error_font();
    __synopsy_error_style();
    __synopsy_trace_style();
    $c = explode('\\',get_class($exception));
    $l = array_pop($c);
    $l = trim($l,'Exception');
    ?>
    <table class="__synopsyError" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="__title" colspan="2" style="background: #7D54A8;">
                    <div>
                        <? echo strToUpper($l); ?> EXCEPTION
                    </div>
                    <? echo $exception->getMessage(); ?>
                </td>
            </tr>
            <tr>
                <td class="__short">Exception type:</td>
                <td><? echo get_class($exception); ?></td>
            </tr>
            <tr>
                <td class="__short">In class:</td>
                <td><? echo $exception->getTrace()[0]['class']; ?></td>
            </tr>
            <tr>
                <td class="__short">In file:</td>
                <td><? echo $exception->getFile(); ?></td>
            </tr>
            <tr>
                <td class="__short">On line:</td>
                <td><? echo $exception->getLine(); ?></td>
            </tr>
            <tr>
                <td class="__short">Code:</td>
                <td><? echo $exception->getCode(); ?></td>
            </tr>
            <tr>
                <td class="__short">Stack trace:</td>
                <td><? __synopsy_backtrace($exception->getTrace()); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="__footer">Synopsy PHP Framework @ <? echo date('H:i:s j.n.Y'); ?></td>
            </tr>
        </tbody>
    </table>
    <?
}

/**
 * Custom exception handler.
 * 
 * @param Exception $exception
 */
function synopsy_sql_handler($sql,$message,$code) {
    __synopsy_error_font();
    __synopsy_error_style();
    ?>
    <table class="__synopsyError" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="__title" colspan="2" style="background: #967050;">
                    <div>
                        SQL ERROR
                    </div>
                    <? echo $message; ?>
                </td>
            </tr>
            <tr>
                <td class="__short">Error code:</td>
                <td><? echo $code; ?></td>
            </tr>
            <tr>
                <td class="__short">Message:</td>
                <td><? echo $message; ?></td>
            </tr>
            <tr>
                <td class="__short">Query:</td>
                <td><? echo $sql; ?></td>
            </tr>
            <tr>
                <td class="__short">Debug trace:</td>
                <td><? __synopsy_backtrace(); ?></td>
            </tr>
            <tr>
                <td colspan="2" class="__footer">Synopsy PHP Framework @ <? echo date('H:i:s j.n.Y'); ?></td>
            </tr>
        </tbody>
    </table>
    <?
}

/* -------------------------------------------------------------------------- */
/* Helper functions */

/**
 * 
 * @param type $trace
 */
function __synopsy_backtrace($trace=null) {
    if ($trace == null) {
        $trace = debug_backtrace();
        $skipFirst = true;
    } else {
        $skipFirst = false;
    }
    $i = 0;
    foreach ($trace as $t) {
        if ($skipFirst) {
            $skipFirst = false;
            continue;
        }
        ?>
        <div class="__traceWrapper">
            <table class="__trace" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="__short">Trace:</td>
                    <td><b><? echo ++$i; ?></b></td>
                </tr>
                <tr>
                    <td class="__short">In file:</td>
                    <td>
                        <?
                        if (isset($t['file'])) {
                            echo $t['file'];
                        } else {
                            echo 'n/a';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="__short">On line:</td>
                    <td>
                        <?
                        if (isset($t['line'])) {
                            echo $t['line'];
                        } else {
                            echo 'n/a';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="__short">Call:</td>
                    <td>
                        <?
                        if (isset($t['class'])) {
                            echo $t['class'] . ' ' . $t['type'] . ' ' . $t['function'];
                        } else {
                            echo $t['function'];
                        }
                        echo ' ( ';
                        if (isset($t['args'])) {
                            $args = '';
                            foreach ($t['args'] as $a) {
                                if (is_array($a)) {
                                    $args .= '&lt;Array&gt; , ';
                                } elseif (is_object($a)) {
                                    $args .= '&lt;' . get_class($a) . '&gt; , ';
                                } else {
                                    $args .= var_export($a, true) . ' , ';
                                }
                            }
                            echo rtrim($args, ' , ');
                        } else {
                            echo '';
                        }
                        echo ' )';
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <?
    }
}

/* -------------------------------------------------------------------------- */
/* CSS design */

function __synopsy_error_font() {
    ?>
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Ubuntu&amp;subset=latin,latin-ext">
    <?
}

function __synopsy_error_style() {
    ?>
    <style type="text/css">
        body {
            background: #E5E5E5;
            color: #505050;
        }
        .__synopsyError {
            background-color: white;
            border: 1px solid #AAA;
            box-shadow: 3px 3px 3px #999;
            font-family: 'Ubuntu';
            font-size: 1em;
            margin: 10px auto;
            width: 60%;
        }
        .__synopsyError tr {
            background-color: white;
        }
        .__synopsyError tr td {
            background-color: white;
            border: none;
            border-bottom: 1px solid #EFEFEF;
            border-right: 1px solid #EFEFEF;
            padding: 10px;
            vertical-align: top;
        }
        .__synopsyError tr td.__title {
            background: #EFEFEF;
            color: #EEE;
            font-size: 1.4em;
            line-height: 1.4em;
            padding: 20px;
            text-align: center;
            text-shadow: 1px 1px 1px #333;
        }
        .__synopsyError tr td.__title div {
            font-size: 1.7em;
            font-weight: bold;
            margin: 10px 0;
        }
        .__synopsyError tr td.__title p {
            font-weight: bold;
            margin: 20px 0 10px 0;
        }
        .__synopsyError tr td.__short {
            font-weight: bold;
            text-align: right;
            width: 150px;
        }
        .__synopsyError tr td.__footer {
            font-size: .8em;
            text-align: center;
        }
    </style>
    <?
}

function __synopsy_trace_style() {
    ?>
    <style type="text/css">
        .__traceWrapper {
            border: 1px solid #EFEFEF;
            margin: 0 0 0 0;
            padding: 0;
        }
        .__traceWrapper .__trace {
            background-color: white;
            font-family: 'Ubuntu';
            font-size: 1em;
            margin: 0px;
            width: 100%;
        }
        .__traceWrapper .__trace tr {
            background-color: white;
        }
        .__traceWrapper .__trace tr td {
            background-color: white;
        }
    </style>
    <?
}