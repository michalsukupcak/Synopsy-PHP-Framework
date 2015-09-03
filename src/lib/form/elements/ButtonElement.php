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

namespace Synopsy\Form\Elements;

use Synopsy\Exceptions\SynopsyException;
use Synopsy\Form\Datatype;
use Synopsy\Form\Element;
use Synopsy\Routing\Route;

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class ButtonElement extends Element {
    
    /* ---------------------------------------------------------------------- */
    /* Constants */
        
    /**
     * CSS class of submit buttons
     */
    const SUBMIT = '__submitButton';
    
    /**
     * Submit types
     */
    const SYNC = 'sync';
    const AJAX = 'ajax';
    
    /**
     * Submit type array
     *
     * @var Array 
     */
    private static $submitTypes = [
        self::SYNC,
        self::AJAX
    ];
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    /**
     * Determines if for button is a submit button.
     *
     * @var Boolean 
     */
    private $isSubmit = false;
    
    /**
     * Submit URL for submit button.
     *
     * @var String
     */
    private $submitUrl = null;
    
    /**
     * Submit button type;
     *
     * @var String 
     */
    private $submitType = null;
    
    /**
     * Ajax submit button target.
     *
     * @var String
     */
    private $submitTarget = null;
 
    /* ---------------------------------------------------------------------- */
    /* Constructor */
        
    /**
     * Constructor, presets Boolean datatype for every button input.
     * 
     * @param type $name
     */
    public function __Construct($name) {
        parent::__Construct($name);
        $this->datatype = Datatype::BOOLEAN;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
    
    /**
     * Sets button as submit button.
     * 
     * @param Boolean $isSubmit
     * @return Element
     */
    public function setIsSubmit($isSubmit) {
        $this->isSubmit = ($isSubmit == true);
        return $this;
    }
    
    /**
     * Retrieves isSubmit status for a button.
     * 
     * @return Boolean
     */
    public function isSubmit() {
        return $this->isSubmit;
    }
    
    /**
     * Sets submit URL.
     * 
     * @param String $submitUrl
     * @return \ButtonFormElement
     */
    public function setSubmitUrl($submitUrl) {
        if ($submitUrl instanceof Route) {
            $this->submitUrl = $submitUrl->get();
        } else {
            $this->submitUrl = $submitUrl;
        }
        return $this;
    }
    
    /**
     * Retrieves submit URL.
     * 
     * @return String
     */
    public function getSubmitUrl() {
        return $this->submitUrl;
    }

    /**
     * Retrieves submit URL HTML representation.
     * 
     * @return String
     */
    public function getSubmitUrlHtml() {
        return ($this->submitUrl ? ' data-'.Element::DATA_SUBMIT_URL.'="'.$this->submitUrl.'"' : '');
    }
    
    /**
     * Sets submit type - allowed values: SYNC or AJAX
     * 
     * @param String $submitType
     * @return \ButtonFormElement
     * @throws SynopsyException
     */
    public function setSubmitType($submitType) {
        if (!in_array($submitType,self::$submitTypes)) {
            throw new SynopsyException("Submit type can be only SYNC or AJAX, '$submitType' given!");
        }
        $this->submitType = $submitType;
        return $this;
    }
    
    /**
     * Retrieves submit type.
     * 
     * @return String
     */
    public function getSubmitType() {
        return $this->submitType;
    }
    
    /**
     * Retrieves submit type HTML representation.
     * 
     * @return String
     */
    public function getSubmitTypeHtml() {
        return ' data-'.Element::DATA_SUBMIT_TYPE.'="'.$this->submitType.'"';
    }

    /**
     * Sets submit target html element.
     * 
     * @param String $submitTarget
     * @return \ButtonFormElement
     */
    public function setSubmitTarget($submitTarget) {
        $this->submitTarget = $submitTarget;
        return $this;
    }
    
    /**
     * Retrieves submit target html element.
     * 
     * @return String
     */
    public function getSubmitTarget() {
        return $this->submitTarget;
    }
    
    /**
     * Retrieves submit target HTML representation.
     * 
     * @return String
     */
    public function getSubmitTargetHtml() {
        return ($this->submitTarget ? ' data-'.Element::DATA_SUBMIT_TARGET.'="'.$this->submitTarget.'"' : '');
    }
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    /**
     * Unsupported operation
     * 
     * @param String $datatype
     * @throws SynopsyException
     */
    public function setDatatype($datatype) {
        throw new SynopsyException('Operation ButtonElement::setDatatype($datatype) is not supported !');
    }
    
    /**
     * Unsupported operation
     * 
     * @param String $validate
     * @throws SynopsyException
     */
    public function setValidate($validate) {
        throw new SynopsyException('Operation ButtonElement::setValidate($validate) is not supported !');
    }
    
    
    /**
     * Retrieves element HTML code representation.
     * 
     * @return String
     */
    public function getHtml($key=null) {
        if ($this->isSubmit) {
            if (!$this->submitType) {
                throw new SynopsyException("Parameter submit type was not supplied to the button '$this->name'!");
            }
            if ($this->submitType == self::AJAX) {
                if (!$this->submitTarget) {
                    throw new SynopsyException("Parameter submit target was not supplied to the AJAX button '$this->name'!");
                }
            }
            $this->addCssClass(self::SUBMIT);
        }
        return ''
            . '<button'
                . ' name="'.$this->getNameHtml($key).'"'
                . $this->getCssIdHtml()
                . $this->getCssClassHtml()
                . $this->getDataAttributesHtml()
                . $this->getSubmitTypeHtml()
                . $this->getSubmitUrlHtml()
                . $this->getSubmitTargetHtml()
            . '>'
                .$this->getDefaultValue($key)
            .'</button>'
        ;
    }
    
}
