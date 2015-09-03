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

use Synopsy\Exceptions\InvalidUrlException;
use Synopsy\Exceptions\UndefinedLanguageException;
use Synopsy\Exceptions\UnauthenticatedException;
use Synopsy\Exceptions\UnauthorizedRoleException;
use Synopsy\Lang\Language;
use Synopsy\Routing\Redirect;
use Synopsy\Routing\Requests\WebRequest;
use Synopsy\Routing\RouterInterface;
use Synopsy\Mvc\TemplateEngine;
use Synopsy\Mvc\View;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class AjaxRouter implements RouterInterface {
    
    /**
     *
     * @var type    
     */
    private $request = null;
    
    /**
     * 
     */
    public function __Construct() {
        require_once(SRC.'lib/routing/requests/WebRequest.php');
        try {
            $this->request = new WebRequest();
        } catch (UndefinedLanguageException $e) {
            new Redirect(Redirect::c301,URL.'ajax/'.Language::get().'/'.filter_input(INPUT_GET,'url'));
        } catch (InvalidUrlException $e) {
            new Redirect(Redirect::c404);
        } catch (UnauthenticatedException $e) {
            new Redirect(Redirect::c403);
        } catch (UnauthorizedRoleException $e) {
            new Redirect(Recirect::c403);
        }
    }
    
    /**
     * Retrieves controller and method data from request. Creates instance
     * of given controller and calls given method on the controller.
     * 
     */
    public function run() {
        // Init template engine
        $templateEngine = new TemplateEngine();
                
        // Controller from URL address
        require_once(APP.'mvc/components/'.$this->request->getControllerFile());
        $c = $this->request->getController();
        $controller = new $c($this->request);
        $controller->{$this->request->getMethod()}();
        if ($controller->getHtmlView() instanceof View) {
            $templateEngine->addView($controller->getHtmlView());
        }
        if ($controller->getJavascriptView() instanceof View) {
            $templateEngine->addView($controller->getJavascriptView());
        }
        
        // Return HTML
        return $templateEngine->render();
    }
    
}