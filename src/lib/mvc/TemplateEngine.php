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

use SmartyBC;
use Synopsy\Exceptions\SynopsyException;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class TemplateEngine {
    
    /**
     * Instance of Smarty Template Engine.
     *
     * @var type 
     */
    private $smarty = null;
        
    /**
     * Array of templates that will be rendered by the template engine.
     * Each element of the array is a array of two parameters:
     * - file -> path to template file
     * - data -> array of variables used in the template file
     * 
     * @var Array
     */
    private $templates = [];
    
    /**
     * Constructor creates and configures instance of Smarty engine.
     * 
     */
    public function __Construct() {
        require_once(DIR.'resources/plugins/smarty/SmartyBC.class.php');
	$this->smarty = new SmartyBC();
	$this->smarty->setTemplateDir(DIR.'resources/plugins/smarty/dirs/templates');
	$this->smarty->setCompileDir(DIR.'resources/plugins/smarty/dirs/templates_c');
	$this->smarty->setConfigDir(DIR.'resources/plugins/smarty/dirs/config');
	$this->smarty->setCacheDir(DIR.'resources/plugins/smarty/dirs/cache');
        $this->smarty->registerClass('ButtonElement','Synopsy\Form\Elements\ButtonElement');
        $this->smarty->registerClass('Datatype','Synopsy\Form\Datatype');
        $this->smarty->registerClass('Element','Synopsy\Form\Element');
        $this->smarty->registerClass('Route','Synopsy\Routing\Route');
        $this->smarty->registerClass('Form','Synopsy\Form\Form');
        $this->smarty->registerClass('Validate','Synopsy\Form\Validate');
        
        $this->smarty->registerPlugin('function','route','smarty_route_to_url');   
        $this->smarty->registerPlugin('function','getString','smarty_strings_get');   
        $this->smarty->registerPlugin('function','getReplacedString','smarty_strings_get_replaced');   
        $this->smarty->registerPlugin('function','bootstrap','smarty_bootstrap_wrap_element');   
    }
    
    /**
     * Adds View instance to the list of templates.
     * 
     * @param View $view
     * @throws SynopsyException
     */
    public function addView($view) {
        if (!$view instanceof View) {
            throw new SynopsyException('Parameter $view must be instance of class View!');
        }
        $this->templates[] = [
            'file' => $view->getFile(),
            'data' => $view->getData()
        ];
    }
    
    /**
     * Adds a template file (from folder /app/mvc/templates) and associated
     * variables to the list of templates.
     * 
     * @param String $template
     * @param String $data
     * @throws SynopsyException
     */
    public function addTemplate($template,$data) {
        $tpl = APP.'templates/'.$template.'.tpl';
        if (!file_exists($tpl)) {
            throw new SynopsyException("Template with name '$template' doesn't exist in folder /app/mvc/templates!");
        }
        $this->templates[] = [
            'file' => $tpl,
            'data' => $data
        ];
    }
    
    /**
     * Renders all templates and returns rendered HTML code.
     * 
     * @return String
     */
    public function render() {
        $html = '';
        foreach ($this->templates as $template) {
            $this->smarty->clearAllAssign();
            $data = $template['data'];
            foreach ($data as $key => $value) {
                $this->smarty->assign($key,$value);
            }
            $html .= $this->smarty->fetch($template['file']);
        }
        return $html;
    }

}
