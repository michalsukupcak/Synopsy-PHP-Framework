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

use Synopsy\Form\Element;

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class HiddenElement extends Element {
        
    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Constructor */

    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    /**
     * Retrieves element HTML code representation.
     * 
     * @return String
     */
    public function getHtml($key=null) {
        return ''
            . '<input '
                . 'type="hidden" '
                . 'name="'.$this->getNameHtml($key).'" '
                . 'value="'.$this->getActualValue($key).'"'
                . $this->getCssIdHtml()
                . $this->getCssClassHtml()
                . $this->getDataAttributesHtml()
                . $this->getDatatypeHtml()
                . $this->getValidateHtml()
            . '>'
        ;
    }
    
}
