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

namespace Synopsy\Routing\Routers;

use HeaderController;
use FooterController;
use Synopsy\Config\Config;
use Synopsy\Exceptions\InvalidUrlException;
use Synopsy\Exceptions\UndefinedLanguageException;
use Synopsy\Exceptions\UnauthenticatedException;
use Synopsy\Exceptions\UnauthorizedRoleException;
use Synopsy\Lang\Language;
use Synopsy\Routing\Redirect;
use Synopsy\Routing\Requests\WebRequest;
use Synopsy\Routing\RouterInterface;
use Synopsy\Mvc\Head;
use Synopsy\Mvc\TemplateEngine;
use Synopsy\Mvc\View;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class SyncRouter implements RouterInterface {
    
    /**
     *
     * @var type 
     */
    private $request = null;
    
    /**
     * Constructor. Creates new DefaultRequest class instance. Verifies if URL
     * address contains valid language code. If doesn't, prepends valid language
     * code in front of the URL adress and redirects user to new URL address
     * using HTTP response code 301.
     */
    public function __Construct() {
        require_once(SRC.'lib/routing/requests/WebRequest.php');
        try {
            $this->request = new WebRequest();
        } catch (UndefinedLanguageException $e) {
            new Redirect(Redirect::c301,URL.Language::get().'/'.filter_input(INPUT_GET,'url'));
        } catch (InvalidUrlException $e) {
            new Redirect(Redirect::c404);
        } catch (UnauthenticatedException $e) {
            new Redirect(Redirect::c403);
        } catch (UnauthorizedRoleException $e) {
            new Redirect(Recirect::c403);
        }
    }
    
    /**
     * 
     */
    public function run() {
        // Init template engine
        $templateEngine = new TemplateEngine();
        
        // --- Data processing ---------------------------------------------- //
        
        // Head data
        $websiteConfig = Config::get('website');
        
        // HeadController data
        require_once(APP.'mvc/core/header/HeaderController.php');
        $headerController = new HeaderController($this->request);
        $headerController->main();
                        
        // From URL data
        require_once(APP.'mvc/components/'.$this->request->getControllerFile());
        $c = $this->request->getController();
        $controller = new $c($this->request);
        $controller->{$this->request->getMethod()}();
        
        // FooterController data
        require_once(APP.'mvc/core/footer/FooterController.php');
        $footerController = new FooterController($this->request);
        $footerController->main();
        
        // --- Template creation -------------------------------------------- //
        
        // Head template
        $title = Head::getTitle() ? Head::getTitle() : ((string) $websiteConfig->name);
        $keywords = Head::getKeywords();
        $description = Head::getDescription();
        $templateEngine->addTemplate('Head',[
            'keywords' => $keywords,
	    'description' => $description,
	    'year' => date('Y'),
	    'title' => $title,
        ]);
        // Header controller HTML view
        $templateEngine->addView($headerController->getHtmlView());
        // Content controller HTML view
        $templateEngine->addView($controller->getHtmlView());
        // Footer controller HTML view
        $templateEngine->addView($footerController->getHtmlView());
        // Javascript template
        $templateEngine->addTemplate('Javascript',[]);
        // Header controller Javascript view
        if ($headerController->getJavascriptView() instanceof View) {
            $templateEngine->addView($headerController->getJavascriptView());
        }
        // Content controller javascript view
        if ($controller->getJavascriptView() instanceof View) {
            $templateEngine->addView($controller->getJavascriptView());
        }
        // Footer controller javascript view
        if ($footerController->getJavascriptView() instanceof View) {
            $templateEngine->addView($footerController->getJavascriptView());
        }
        // Tail template
        $templateEngine->addTemplate('Tail',[]);
        
        // --- HTML rendering -------------------------------------------- //
        
        // Return HTML
        return $templateEngine->render();
    }
    
}