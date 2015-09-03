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
abstract class AbstractLabelElement extends Element {

    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    /**
     * Checkbox label.
     *
     * @var String
     */
    private $label = null;
    
    /**
     *
     * @var String
     */
    private $labelCssId = null;
    
    /**
     *
     * @var String
     */
    private $labelCssClass = null;
    
    /**
     * Determines if checkbox should be aligned to the right of the label
     * (true, default) or the left of label (false).
     *
     * @var Boolean
     */
    private $align = true;
    
    /* ---------------------------------------------------------------------- */
    /* Constructor */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
    
    /**
     * Sets checkbox label.
     * 
     * @param String $label
     * @param Array $key
     * @return /CheckboxFormElement
     */
    public function setLabel($label,$key=null) {
        if ($key) {
            $this->label[$this->createKey($key)] = $label;
        } else {
            $this->label = $label;
        }
        return $this;
    }
    
    /**
     * Retrieves checkbox label.
     * 
     * @param Array $key
     * @return String
     */
    public function getLabel($key=null) {
        if ($key) {
            $k = $this->createKey($key);
            if (isset($this->label[$k])) {
                return $this->label[$k];
            } else {
                return null;
            }
        } else {
            return $this->label;
        }
    }
    
    /**
     * Sets CSS id attribute.
     * 
     * @param String $labelCssId
     * @return Element
     */
    public function setLabelCssId($labelCssId) {
        $this->labelCssId = $labelCssId;
        return $this;
    }
    
    /**
     * Returns CSS id attribute.
     * 
     * @return String
     */
    public function getLabelCssId() {
        return $this->labelCssId;
    }
    
    /**
     * Returns CSS id attribute HTML representation.
     * 
     * @return String
     */
    public function getLabelCssIdHtml() {
        return ($this->labelCssId ? ' id="'.$this->labelCssId.'"' : '');
    }

    /**
     * Sets CSS class attribute.
     * 
     * @param String $labelCssClass
     * @return Element
     */
    public function setLabelCssClass($labelCssClass) {
        $this->labelCssClass = $labelCssClass;
        return $this;
    }
    
    /**
     * Appends new CSS class to existing CSS class attribute.
     * 
     * @param String $labelCssClass
     * @return Element
     */
    public function addLabelCssClass($labelCssClass) {
        $this->labelCssClass .= " $labelCssClass";
        return $this;
    }
    
    /**
     * Returns CSS class attribute.
     * 
     * @return String
     */
    public function getLabelCssClass() {
        return $this->labelCssClass;
    }
    /**
     * Returns CSS class attribute HTML representation.
     * 
     * @return String
     */
    public function getLabelCssClassHtml() {
        return ($this->labelCssClass ? ' class="'.$this->labelCssClass.'"' : '');
    }
    
    /**
     * Sets checkbox right alignment.
     * 
     * @param Boolean $alignRight
     * @return Element
     */
    public function setLeftAlign() {
        $this->align = false;
        return $this;
    }
    
    /**
     * Returns checkbox right alignment.
     * 
     * @return Boolean
     * @return Element
     */
    public function setRightAlign() {
        $this->align = true;
        return $this;
    }
    
    /**
     * Returns current align.
     * 
     * @return Boolean
     */
    public function getAlign() {
        return $this->align;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    // Empty
    
}
