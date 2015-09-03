<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Synopsy\Form\Elements;

use Synopsy\Form\Element;

/**
 * Description of AbstractPlaceholderElement
 *
 * @author bloodspell
 */
abstract class AbstractPlaceholderElement extends Element {

    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    /**
     * Placeholder.
     *
     * @var Mixed
     */
    private $placeholder = null;
    
    /* ---------------------------------------------------------------------- */
    /* Constructor */
    
    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
    
    /**
     * Sets placeholder.
     * 
     * @param String $placeholder
     * @param Array $key
     * @return Element
     */
    public function setPlaceholder($placeholder,$key=null) {
        if ($key) {
            $this->placeholder[$this->createKey($key)] = $placeholder;
        } else {
            $this->placeholder = $placeholder;
        }
        return $this;
    }
    
    /**
     * Retrieves placeholder.
     * 
     * @param Array $key
     * @return String
     */
    public function getPlaceholder($key=null) {
        if ($key) {
            $k = $this->createKey($key);
            if (isset($this->placeholder[$k])) {
                return $this->placeholder[$k];
            } else {
                return null;
            }
        } else {
            return $this->placeholder;
        }
    }
    
    /**
     * Returns placeholder HTML representation.
     * 
     * @param type $key
     * @return type
     */
    public function getPlaceholderHtml($key=null) {
        $p = $this->getPlaceholder($key);
        return ($p ? ' placeholder="'.$p.'"' : '');
    }
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    // Empty
    
}
