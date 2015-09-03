<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Synopsy\Exceptions\SynopsyException;
use Synopsy\Form\Element;
use Synopsy\Form\Elements\ButtonElement;
use Synopsy\Form\Elements\CheckboxElement;
use Synopsy\Form\Elements\FileElement;
use Synopsy\Form\Elements\PasswordElement;
use Synopsy\Form\Elements\RadioElement;
use Synopsy\Form\Elements\SelectElement;
use Synopsy\Form\Elements\TextareaElement;
use Synopsy\Form\Elements\TextElement;
use Synopsy\Lang\Strings;
use Synopsy\Routing\Route;

/* -------------------------------------------------------------------------- */
/* Smarty wrapper functions */

/**
 * Smarty wrapper function for (new Route(...))->get() method.
 * 
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 * @throws SynopsyException
 */
function smarty_route_to_url($params,&$smarty) {
    if (!isset($params['url'])) {
        throw new SynopsyException("Parameter 'url' must be defined for function {route}!");
    }
    if (isset($params['map'])) {
        $map = $params['map'];
    } else {
        $map = null;
    }
    if (isset($params['vars'])) {
        $vars = $params['vars'];
    } else {
        $vars = null;
    }
    if (($map && !$vars) || (!$map && $vars)) {
        throw new SynopsyException("If you want to use parameter handling, you must define both 'map' and 'vars' parameters!");
    }
    if (isset($params['relative'])) {
        $relative = true;
    } else {
        $relative = false;
    }
    return (new Route($params['url'],$map,$vars,$relative))->get();
}

/**
 * Smarty wrapper function for Language::get(...) method.
 * 
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 * @throws SynopsyException
 */
function smarty_strings_get($params,&$smarty) {
    if (!isset($params['key'])) {
        throw new SynopsyException("Parameter 'key' must be defined for function {getString}!");
    }
    return Strings::get($params['key']);
}

/**
 * Smarty wrapper function for Language::getReplaced(...) method.
 * 
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 * @throws SynopsyException
 */
function smarty_strings_get_replaced($params,&$smarty) {
    if (!isset($params['key'])) {
        throw new SynopsyException("Parameter 'key' must be defined for function {getString}!");
    }
    if (!isset($params['values'])) {
        throw new SynopsyException("Parameter 'value' must be defined for function {getReplacedString}!");
    }
    return Strings::getReplaced($params['key'],$params['values']);
}

/* -------------------------------------------------------------------------- */
/* Smarty wrapper functions - Bootstrap */

/**
 * 
 * @param type $params
 * @param type $smarty
 * @return type
 */
function smarty_bootstrap_wrap_element($params,&$smarty) {
    // Load element and key params
    $element = (isset($params['element']) ? $params['element'] : null);
    $key = (isset($params['key']) ? $params['key'] : null);
    // Verify element param
    if (!$element instanceof Element) {
        throw new SynopsyException("Parameter 'element' must be instance of class Element!");
    }
    // Set element isBootstrap flag
    $element->addCssClass(Element::IS_BOOTSTRAP);
    // Call bootstrap_wrap_* function according to element type
    if ($element instanceof TextElement || $element instanceof PasswordElement || $element instanceof SelectElement || $element instanceof TextareaElement) {
        return bootstrap_wrap_element($element,$key,$params);
    } elseif ($element instanceof CheckboxElement) { // Checkbox
        return bootstrap_wrap_checkbox_element($element,$key,$params);
    } elseif ($element instanceof RadioElement) { // Radio
        return bootstrap_wrap_radio_element($element,$key,$params);
    } elseif ($element instanceof ButtonElement) { // Button
        return bootstrap_wrap_button_element($element,$key,$params);
    } elseif ($element instanceof FileElement) { // File
        return bootstrap_wrap_file_element($element,$key,$params);
    } else { // Hidden (no bootstrap)
        return $element->getHtml($key);
    }
}

/**
 * Helper function - Bootstrap wrapper for text, password, select and textarea elements.
 * 
 * @param Element $element
 * @param String key
 * @param Array $params
 * @return String
 */
function bootstrap_wrap_element($element,$key,$params) {
    // Load params
    $label = (isset($params['label']) ? $params['label'] : null);
    $wrapperClass = (isset($params['wrapperClass']) ? ' '.$params['wrapperClass'] : null);
    $labelClass = (isset($params['labelClass']) ? ' '.$params['labelClass'] : null);
    $divClass = (isset($params['divClass']) ? $params['divClass'] : null);
    $inputAddonLeft = (isset($params['inputAddonLeft']) ? '<span class="input-group-addon">'.$params['inputAddonLeft'].'</span>' : null);
    $inputAddonRight = (isset($params['inputAddonRight']) ? '<span class="input-group-addon">'.$params['inputAddonRight'].'</span>' : null);
    // Set bootstrap css
    $element->addCssClass('form-control');
    // Generate and set CSS id
    $id = ($element->getCssId() ? $element->getCssId() : uniqid('bootstrap'));
    $element->setCssId($id);
    // Add input addon class
    $divClass .= ($inputAddonLeft || $inputAddonRight ? ' input-group' : '');
    // Return bootstrap HTML
    return ''
        . '<div class="form-group'.$wrapperClass.'">'
            . ($label ? '<label for="'.$id.'" class="control-label'.$labelClass.'">'.$label.'</label>' : '')
            . ($label || $divClass ? '<div class="'.$divClass.'">' : '')
                . $inputAddonLeft
                . $element->getHtml($key)
                . $inputAddonRight
            . ($label || $divClass ? '</div>' : '')
        . '</div>'
    ;
}

/**
 * Helper function - Bootstrap wrapper for checkbox element.
 * 
 * @param Element $element
 * @param String key
 * @param Array $params
 * @return String
 */
function bootstrap_wrap_checkbox_element($element,$key,$params) {
    // Load params
    $wrap = (isset($params['wrap']) ? true : false);
    $wrapperClass = (isset($params['wrapperClass']) ? ' '.$params['wrapperClass'] : null);
    $divClass = (isset($params['divClass']) ? $params['divClass'] : null);
    $inline = (isset($params['inline']) ? true : false);
    // Return bootstrap HTML
    return ''
        . ($wrap ? '<div class="form-group'.$wrapperClass.'">' : '')
            . ($divClass ? '<div class="'.$divClass.'">' : '')
                . '<div class="'.($inline ? 'checkbox-inline' : 'checkbox').'">'
                    . $element->getHtml($key)
                . '</div>'
            . ($divClass ? '</div>' : '')
        . ($wrap  ? '</div>' : '')
    ;
}

/**
 * Helper function - Bootstrap wrapper for radio element.
 * 
 * @param Element $element
 * @param String key
 * @param Array $params
 * @return String
 */
function bootstrap_wrap_radio_element($element,$key,$params) {
    // Load params
    $wrap = (isset($params['wrap']) ? true : false);
    $wrapperClass = (isset($params['wrapperClass']) ? ' '.$params['wrapperClass'] : null);
    $divClass = (isset($params['divClass']) ? $params['divClass'] : null);
    $inline = (isset($params['inline']) ? true : false);
    // Return bootstrap HTML
    return ''
        . ($wrap ? '<div class="form-group'.$wrapperClass.'">' : '')
            . ($divClass ? '<div class="'.$divClass.'">' : '')
                . '<div class="'.($inline ? 'radio-inline' : 'radio').'">'
                    . $element->getHtml($key)
                . '</div>'
            . ($divClass ? '</div>' : '')
        . ($wrap  ? '</div>' : '')
    ;
}

/**
 * Helper function - Bootstrap wrapper for file element.
 * 
 * @param Element $element
 * @param String key
 * @param Array $params
 * @return String
 */
function bootstrap_wrap_file_element($element,$key,$params) {
    // Load params
    $wrapperClass = (isset($params['wrapperClass']) ? ' '.$params['wrapperClass'] : null);
    $name = (isset($params['name']) ? ' '.$params['name'] : null);
    $btnClass = (isset($params['btnClass']) ? ' '.$params['btnClass'] : ' btn-default');
    // Set bootstrap css
    // Return bootstrap HTML
    return ''
        . '<div class="form-group'.$wrapperClass.'">'
            . '<div class="input-group">'
                . '<span class="input-group-btn">'
                    . '<span class="file-input btn btn-file'.$btnClass.'"> '.$name
                        . $element->getHtml($key)
                    . '</span>'
                . '</span>'
                . '<input type="text" class="form-control" readonly>'
            . '</div>'
        . '</div>'
    ;
}

/**
 * Helper function - Bootstrap wrapper for button element.
 * 
 * @param Element $element
 * @param String key
 * @param Array $params
 * @return String
 */
function bootstrap_wrap_button_element($element,$key,$params) {
    // Load params
    $wrap = (isset($params['wrap']) ? true : false);
    $wrapperClass = (isset($params['wrapperClass']) ? ' '.$params['wrapperClass'] : null);
    // Set bootstrap css
    $element->addCssClass('btn');
    // Return bootstrap HTML
    return ''
        . ($wrap ? '<div class="form-group'.$wrapperClass.'">' : '')
            . $element->getHtml($key)
        . ($wrap  ? '</div>' : '')
    ;
}