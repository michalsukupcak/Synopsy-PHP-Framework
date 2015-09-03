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

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class CheckboxElement extends AbstractLabelElement {
    
    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Constructor */
    
    /**
     * Constructor, presets Boolean datatype for every checkbox.
     * 
     * @param type $name
     */
    public function __Construct($name) {
        parent::__Construct($name);
        $this->datatype = Datatype::BOOLEAN;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    /**
     * Unsupported operation
     * 
     * @param String $datatype
     * @throws SynopsyException
     */
    public function setDatatype($datatype) {
        throw new SynopsyException('Operation CheckboxElement::setDatatype($datatype) is not supported !');
    }
    
    /**
     * Retrieves element HTML code representation.
     * 
     * @return String
     */
    public function getHtml($key=null) {
        return ''
            . '<label'
            . $this->getLabelCssIdHtml()
            . $this->getLabelCssClassHtml()
            . '>'
                . (!$this->getAlign() ? $this->getLabel($key).' ' : '')
                . '<input'
                . ' type="checkbox"'
                . ' name="'.$this->getNameHtml($key).'"'
                . ' value="1"'
                . $this->getCssIdHtml()
                . $this->getCssClassHtml()
                . $this->getDataAttributesHtml()
                . $this->getValidateHtml()
                . ($this->getActualValue($key) == true ? ' checked="checked"' : '')
                . '>'
                . ($this->getAlign() ? ' '.$this->getLabel($key) : '')
            . '</label>'
        ;
    }
    
}
