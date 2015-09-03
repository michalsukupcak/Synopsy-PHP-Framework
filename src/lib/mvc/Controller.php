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

namespace Synopsy\Mvc;

use ReflectionClass;
use Synopsy\Config\Config;
use Synopsy\Exceptions\SynopsyException;
use Synopsy\Routing\RequestInterface;

/**
 * Abstract controller class for client interface. All client interface
 * controller classes *MUST* extend this class.
 * 
 * Main objective of controller is to retrieve data from corresponding model,
 * process them and supply them to corresponding view.
 * 
 * Unless a custom constructor controller is neccessary, default constructor
 * is not needed. If a custom constructor is defined for child class, first
 * line of the constructor MUST call parent constructor!
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
abstract class Controller {
    
    /**
     * Instance of model class associated with current controller.
     * 
     * @var Model
     */
    protected $model = null;
    
    /**
     * Instance of view class created in current controller for HTML code.
     *
     * @var View
     */
    protected $htmlView = null;
    
    /**
     * Instance of view class created in current controller for Javascript code.
     *
     * @var View 
     */
    protected $javascriptView = null;
        
    /**
     * Name of child class of current controller instance.
     *
     * @var String 
     */
    protected $class = null;

    /**
     * Folder in which controller is located.
     *
     * @var String 
     */
    protected $folder = null;
        
    /**
     *
     * @var Request 
     */
    protected $request = null;
    
    /**
     * Constructor. Creates instance of current controller. Creates
     * corresponding model class instance and calls controller methods.
     * 
     * @param Request $request
     * @throws SynopsyException
     */
    public function __Construct($request) {
        if (!$request instanceof RequestInterface) {
            throw new SynopsyException("Parameter \$request must implement RequestInterface!");
        }
        $this->request = $request;
	$this->class = get_called_class();
        $reflectionClass = new ReflectionClass($this->class);        
        $this->folder = dirname($reflectionClass->getFileName());
	$modelClass = explode('Controller',$this->class)[0].'Model';
	require_once($this->folder.'/'.$modelClass.'.php');
	$this->model = new $modelClass();
	$this->onLoad();
    }
        
    /**
     * Initial method of the controller, called immediately after creating new
     * controller instance.
     * 
     */
    protected abstract function onLoad();
             
    /**
     * Default MVC method, called when no method is defined in URL.
     * 
     */
    public abstract function main();

    /**
     * Returns instance of View class for HTML View.
     * 
     * @return View
     */
    public function getHtmlView() {
        return $this->htmlView;
    }
    
    /**
     * Returns instance of View class for Javascript View.
     * 
     * @return View
     */
    public function getJavascriptView() {
        return $this->javascriptView;
    }
    
    /**
     * Creates new view instance.
     * 
     * @param String $view
     * @return View
     */
    protected function newView($view) {
        return new View($view,$this->class,$this->folder);
    }
    
    /**
     * Set html head title.
     * @return View
     */
    protected function setTitle($title) {
        Head::setTitle($title.' - '.((string)Config::get('website')->name));
    }
    
    /**
     * Set html head keywords. It should be a string of words
     * separated by comma.
     * @return View
     */
    protected function setKeywords($keywords) {
        Head::setKeywords($keywords);
    }
    
    /**
     * Set html head description.
     * @return View
     */
    protected function setDescription($description) {
        Head::setDescription($description);
    }
}
