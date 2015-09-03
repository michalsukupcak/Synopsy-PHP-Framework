<!DOCTYPE html>
<!--

    Synopsy PHP Framework (c) by Michal Sukupčák

    Synopsy PHP Framework is licensed under a
    Creative Commons Attribution 4.0 International License.

    You should have received a copy of the license along with this
    work. If not, see <http://creativecommons.org/licenses/by/4.0/>.

-->

{* --- Open <html> --- *}
<html>
    
{* --- Head --- *}
<head>
    <meta charset="utf-8">
    <meta name="keywords" content="{$keywords}">
    <meta name="description" content="{$description}">
    <meta name="author" content="Webdesign Studio (c) 2013 - {$year}">
    <meta name="language" value="">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title>{$title}</title>
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Ubuntu&amp;subset=latin,latin-ext">
    <link rel="stylesheet" type="text/css" href="{$smarty.const.URL}cache/style.css">
</head>

{* --- Open <body> --- *}
<body>
        
{* --- Noscript warning --- *}
<noscript>
    <div>
        WARNING: MISSING JAVASCRIPT SUPPORT
    </div>
    <div>
        Your browser has disabled Javascript support. We're sorry, but this 
        website requires Javascript enabled in order to work properly. Please
        consider enabling Javascript before using this website!.
    </div>
</noscript>
    
{* --- Loading overlay --- *}
<div id="loading">
    <div>
        <img src="{$smarty.const.URL}resources/images/framework/loading.gif" alt="Loading" title="Loading">
    </div>
</div>
    
{* --- Modal --- *}
<div id="modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
                <h4 id="modalTitle" class="modal-title"></h4>
            </div>
            <div id="modalContent" class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{* --- Open #wrapper --- *}
<div id="wrapper">
	    