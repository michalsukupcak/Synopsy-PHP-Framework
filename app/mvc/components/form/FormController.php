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

use Synopsy\Form\Datatype;
use Synopsy\Form\Elements\ButtonElement;
use Synopsy\Form\Elements\CheckboxElement;
use Synopsy\Form\Elements\SelectElement;
use Synopsy\Form\Elements\TextElement;
use Synopsy\Form\Form;
use Synopsy\Form\Validate;
use Synopsy\Lang\Strings;
use Synopsy\Mvc\Controller;
use Synopsy\Routing\Route;

/**
 * @CONTROLLER
 * @Route-en form
 * @Route-sk formular
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class FormController extends Controller {
    
    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // No constants
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */    
    
    private $form;
    
    /* ---------------------------------------------------------------------- */
    /**
     * Controller::onLoad()
     * 
     * Initial method of the controller, called immediately after creating new
     * controller instance.
     */
    protected function onLoad() {
        $this->form = new Form('demoForm','form-horizontal');
        $this->form->setElements([
            /* -------------------------------------------------------------- */
            /* Texts */
            (new TextElement('inputText'))
                ->setDatatype(Datatype::STRING)
                ->setValidate(Validate::REQUIRED)
                ->setDefaultValue('Lorem ipsum dolor sit amet')
            ,
            (new TextElement('inputInteger'))
                ->setDatatype(Datatype::INTEGER)
                ->setValidate(Validate::REQUIRED)
                ->setDefaultValue(15)
            ,
            (new SelectElement('select'))
                ->setDatatype(Datatype::INTEGER)
                ->setValidate(Validate::REQUIRED)
                ->setDefaultValue(3)
                ->setOptions([
                    0 => Strings::get('form.select.option0'),
                    1 => Strings::get('form.select.option1'),
                    2 => Strings::get('form.select.option2'),
                    3 => Strings::get('form.select.option3'),
                    4 => Strings::get('form.select.option4'),
                    5 => Strings::get('form.select.option5')
                ])
            ,
            (new CheckboxElement('checkbox'))
                ->setValidate(Validate::REQUIRED)
                ->setLabel(Strings::get('form.checkbox'))
                ->setDefaultValue(true)
            ,
            /* -------------------------------------------------------------- */
            /* Buttons */
            (new ButtonElement('submitSync'))
                ->setDefaultValue(Strings::get('form.buttonSync'))
                ->setCssClass('btn-primary')
                ->setIsSubmit(true)
                ->setSubmitUrl(new Route('form/FormController:submitSync'))
                ->setSubmitType(ButtonElement::SYNC)
            ,
            (new ButtonElement('submitAjax'))
                ->setDefaultValue(Strings::get('form.buttonAjax'))
                ->setCssClass('btn-info')
                ->setIsSubmit(true)
                ->setSubmitUrl(new Route('form/FormController:submitAjax'))
                ->setSubmitType(ButtonElement::AJAX)
                ->setSubmitTarget('formResponse')
        ]);

    }
        
    /* ---------------------------------------------------------------------- */
    /* MVC methods */
    
    public function main() {
	$this->htmlView = $this->newView('FormView');
        $this->htmlView->setData([
            'form' => $this->form
        ]);
    }
    
    /**
     * @METHOD
     * @Route-en sync
     * @Route-sk synchronne
     */
    public function submitSync() {
        if ($this->form->validate()) {
            $this->htmlView = $this->newView('FormSyncView');
            $this->htmlView->setData([
                'form' => $this->form
            ]);
        }
    }
    
    /**
     * @METHOD
     * @Route-en ajax
     * @Route-sk asynchronne
     */
    public function submitAjax() {
        sleep(1);
        $this->htmlView = $this->newView('FormAjaxView');
        $this->htmlView->setData([
            'form' => $this->form
        ]);
    }
            
    /* ---------------------------------------------------------------------- */
    /* Additional private methods */    
    
    // No methods
    
}