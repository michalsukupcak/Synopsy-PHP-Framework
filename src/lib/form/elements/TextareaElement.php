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

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class TextareaElement extends AbstractPlaceholderElement {

    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    /**
     * Number of rows of a textarea.
     *
     * @var type 
     */
    private $rows = null;  

    /* ---------------------------------------------------------------------- */
    /* Constructor */

    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
        
    /**
     * Sets textarea rows attribute.
     * 
     * @param Integer $rows
     * @return Element
     * @throws SynopsyException
     */
    public function setRows($rows) {
        $r = intval($rows);
        if ($r < 1) {
            throw new SynopsyException("Parameter '\$rows' must be a positive number (1+), '$rows' given!");
        }
        $this->rows = $rows;
        return $this;
    }
    
    /**
     * Retrieves textarea rows.
     * 
     * @return type
     */
    public function getRows() {
        return $this->rows;
    }
    
    /**
     * Retrieves rows HTML representation.
     *
     * @return String
     */
    public function getRowsHtml() {
       return ($this->rows ? ' rows="'.$this->getRows().'"' : ''); 
    }
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    /**
     * Retrieves element HTML code representation.
     * 
     * @return String
     */
    public function getHtml($key=null) {
        return ''
            . '<textarea '
                . 'name="'.$this->getNameHtml($key).'" '
                . $this->getCssIdHtml()
                . $this->getCssClassHtml()
                . $this->getDataAttributesHtml()
                . $this->getValidateHtml()
                . $this->getDatatypeHtml()
                . $this->getPlaceholderHtml($key)
                . $this->getRowsHtml()
            . '>'
                . $this->getActualValue($key)
            . '</textarea>'
        ;
    }
            
}
