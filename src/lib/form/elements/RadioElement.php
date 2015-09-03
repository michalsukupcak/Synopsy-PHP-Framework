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
use Synopsy\Form\Element;

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class RadioElement extends AbstractLabelElement {

    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    /**
     * Text input placeholder attribute.
     *
     * @var Mixed
     */
    private $options = null;
        
    /* ---------------------------------------------------------------------- */
    /* Constructor */

    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
        
    /**
     * Sets options.
     * 
     * @param String $options
     * @return Element
     */
    public function setOptions($options) {
        $this->options = $options;
        return $this;
    }
    
    /**
     * Retrieves options.
     * 
     * @return String
     */
    public function getOptions() {
        return $this->options;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    /**
     * Unsupported operation
     * 
     * @param String $cssId
     * @throws SynopsyException
     */
    public function setCssId($cssId) {
        throw new SynopsyException('Operation RadioElement::setCssId($cssId) is not supported !');
    }
    
    /**
     * Unsupported operation
     * 
     * @param String $labelCssId
     * @throws SynopsyException
     */
    public function setLabelCssId($labelCssId) {
        throw new SynopsyException('Operation RadioElement::setLabelCssId($labelCssId) is not supported !');
    }
    
    /**
     * Unsupported operation
     * 
     * @param String $label
     * @param Array $key
     * @throws SynopsyException
     */
    public function setLabel($label,$key=null) {
        throw new SynopsyException('Operation RadioElement::setLabel($label,$key=null) is not supported !');
    }
    
    /**
     * Retrieves element HTML code representation.
     * 
     * @return String
     */
    public function getHtml($key=null) {
        $value = $this->getActualValue($key);
        $o = $this->getOptions();
        $return = '';
        foreach ($o as $index => $option) {
            $return .= ''
                . '<label'
                . $this->getLabelCssClassHtml()
                . '>'
                    . (!$this->getAlign() ? $option.' ' : '')
                    . '<input'
                    . ' type="radio"'
                    . ' name="'.$this->getNameHtml($key).'"'
                    . ' value="'.$index.'"'
                    . $this->getCssClassHtml()
                    . $this->getDataAttributesHtml()
                    . $this->getValidateHtml()
                    . ($this->getActualValue($key) == true ? ' checked="checked"' : '')
                    . ($value == $index ? ' checked="checked"' : '').'>'
                    . ($this->getAlign() ? ' '.$option : '')
                . '</label>'
            ;
        }
        return $return;
    }
    
}
