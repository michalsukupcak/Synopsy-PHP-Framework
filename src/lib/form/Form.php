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

namespace Synopsy\Form;

use Synopsy\Exceptions\SynopsyException;

/**
 * Form generating/processing class. Handles form element generation and
 * $_POST data processing.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Form {

    /**
     * CSS class of form divs
     */
    const HTML_FORM = '__htmlForm';
    
    /**
     * 
     */
    const DATA_MULTIPART = 'multipart';
    
    /**
     *
     * @var String
     */
    private $cssId = null;
    
    /**
     *
     * @var String
     */
    private $cssClass = null;
    
    /**
     *
     * @var Boolean
     */
    private $multipart = false;
    
    /**
     *
     * @var Array
     */
    private $elements = [];
    
    /**
     * 
     * 
     * @var Array
     */
    public function __Construct($cssId,$cssClass=null) {
        $this->cssId = $cssId;
        $this->cssClass = $cssClass;
    }
    
    /**
     * 
     * @param Element $elements
     */
    public function setElements($elements) {
        foreach ($elements as $element) {
            $this->addElement($element);
        }
    }
    
    /**
     * 
     * @param Element $element
     * @throws SynopsyException
     */
    public function addElement($element) {
        if (!$element instanceof Element) {
            throw new SynopsyException("Element '$element' is not an object of class Element!");
        }
        if ($element instanceof FileElement) {
            $this->multipart = true;
        }
        $name = $element->getName();
        $this->elements[$name] = $element;   
    }
    
    /**
     * 
     * @param String $name
     * @return Element
     * @throws SynopsyException
     */
    public function getElement($name) {
        if (!array_key_exists($name,$this->elements)) {
            throw new SynopsyException("Element with name '$name' doesn't exist!");
        }
        return $this->elements[$name];
    }

    /**
     * 
     * @return String
     */
    public function openFormHtml() {
        return '<form id="'.$this->cssId.'" class="'.self::HTML_FORM.(($this->cssClass) ? (' '.$this->cssClass) : '').'"'.($this->multipart ? ' data-'.self::DATA_MULTIPART : '').'>';
    }
    
    /**
     * 
     * @return String
     */
    public function closeFormHtml() {
        return '</form>';
    }
    
    
    /**
     * 
     * @return Boolean
     */
    public function validate() {
        foreach ($this->elements as $name => $element) {
            $validate = $element->getValidate();
            if ($validate != null) {
                $value = $element->getPostValue();
                if ($element->isArray()) {
                    foreach ($value as $i => $v) {
                        if (!$this->validateValue($validate,$v)) {
                            return false;
                        }
                    }
                } else {
                    if (!$this->validateValue($validate,$value)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    /**
     * 
     * @param type $validate
     * @param type $value
     */
    private function validateValue($validate,$value) {
        if ($validate == Validate::REQUIRED) {
            return ($value != null);
        } else {
            return (Validate::variable($validate,$value));
        }
    }
}

