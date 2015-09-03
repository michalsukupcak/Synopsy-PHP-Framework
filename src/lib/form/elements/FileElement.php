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
use Synopsy\Files\Files;
use Synopsy\Files\Extension;
use Synopsy\Form\Datatype;
use Synopsy\Form\Element;

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class FileElement extends Element {
    
    /* ---------------------------------------------------------------------- */
    /* Constants */

    // Empty
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    /**
     * Determines if fileinput should allow selection of multiple files.
     *
     * @var Boolean
     */
    private $isMultiple = null;
    
    /**
     * Maximum number of files to be uploaded.
     *
     * @var Integer
     */
    private $maxFileCount = 1;
    
    /**
     * Maximum file size.
     *
     * @var Integer
     */
    private $maxFileSize = 0;
    
    /**
     * List of allowed file allowedExtensions.
     *
     * @var Array
     */
    private $allowedExtensions = [];
    
    /* ---------------------------------------------------------------------- */
    /* Constructor */

    // Empty
    
    /**
     * Constructor, presets String datatype for every file input.
     * 
     * @param type $name
     */
    public function __Construct($name) {
        parent::__Construct($name);
        $this->datatype = Datatype::STRING;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
    
    /**
     * 
     * @return Boolean
     */
    public function isMultiple() {
        return $this->isMultiple;
    }
    
    /**
     * 
     * @param Integer $maxFileCount
     * @return \FileElement
     * @throws SynopsyException
     */
    public function setMaxFileCount($maxFileCount) {
        if ($maxFileCount < 1) {
            throw new SynopsyException("MaxFileCount must be at least 1 (or bigger)!");
        }
        if ($maxFileCount > 1) {
            $this->isMultiple = true;
        } else {
            $this->isMultiple = false;
        }
        $this->maxFileCount = $maxFileCount;
        return $this;
    }
    
    /**
     * 
     * @return Integer
     */
    public function getMaxFileCount() {
        return $this->maxFileCount;
    }
    
    /**
     * 
     * @return String
     */
    public function getMaxFileCountHtml() {
        return ' data-'.Element::DATA_UPLOAD_MAX_FILE_COUNT.'="'.$this->maxFileCount.'"';
    }
    
    /**
     * 
     * @param Integer $maxFileSize In MB
     * @return \FileElement
     * @throws SynopsyException
     */
    public function setMaxFileSize($maxFileSize) {
        if ($maxFileSize < 1) {
            throw new SynopsyException("FileSize must be at least 1kB!");
        } elseif ($maxFileSize > Files::getMaxSystemFileSize()) {
            throw new SynopsyException("FileSize is larger then max. system file size ($maxFileSize > ".Files::getMaxSystemFileSize().")!");
        }
        $this->maxFileSize = $maxFileSize;
        return $this;
    }
    
    /**
     * 
     * @return Integer
     */
    public function getMaxFileSize() {
        return $this->maxFileSize;
    }
    
    /**
     * 
     * @return String
     */
    public function getMaxFileSizeHtml() {
        return ' data-'.Element::DATA_UPLOAD_MAX_FILE_SIZE.'="'.$this->maxFileSize.'"';
    }
    
    /**
     * 
     * @param Array $allowedExtensions
     * @return \FileElement
     * @throws SynopsyException
     */
    public function setAllowedExtensions($allowedExtensions) {
        if (!is_array($allowedExtensions)) {
            throw new SynopsyException("Extensions parameter must be an array of valid allowedExtensions!");
        }
        foreach ($allowedExtensions as $extension) {
            if (!in_array($extension,Extension::$extensions)) {
                throw new SynopsyException("Extension '$extension' is not a valid file extension!");
            }
        }
        $this->allowedExtensions = $allowedExtensions;
        return $this;
    }
    
    /**
     * 
     * @return Array
     */
    public function getAllowedExtensions() {
        return $this->allowedExtensions;
    }
    
    /**
     * 
     * @return String
     */
    public function getAllowedExtensionsHtml() {
        return ' data-'.Element::DATA_UPLOAD_ALLOWED_EXTENSIONS.'="'.implode('|',$this->allowedExtensions).'"';
    }
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    /**
     * Unsupported operation
     * 
     * @param String $datatype
     * @throws UnsupportedOperationException
     */
    public function setDatatype($datatype) {
        throw new SynopsyException('Operation FileElement::setDatatype($datatype) is not supported !');
    }

    /**
     * Unsupported operation
     * 
     * @param String $isArray
     * @throws SynopsyException
     */
    public function setIsArray($isArray) {
        throw new SynopsyException('Operation FileElement::setIsArray($isArray) is not supported !');
    }
    
    /**
     * Unsupported operation
     * 
     * @param String $validate
     * @throws SynopsyException
     */
    public function setValidate($validate) {
        throw new SynopsyException('Operation FileElement::setValidate($validate) is not supported !');
    }
    
    /**
     * Retrieves element HTML code representation.
     * 
     * @return String
     */
    public function getHtml() {
        return ''
            . '<input'
                . ' type="file"'
                . ' name="'.$this->getName().($this->isMultiple ? '[]' : '').'"'
                . ' value="'.$this->getActualValue().'"'
                . $this->getCssIdHtml()
                . $this->getCssClassHtml()
                . $this->getDataAttributesHtml()
                . $this->getDatatypeHtml()
                . $this->getValidateHtml()
                . $this->getMaxFileCountHtml()
                . $this->getMaxFileSizeHtml()
                . $this->getAllowedExtensionsHtml()
            . ''.($this->isMultiple ? ' multiple' : '').'>'
        ;
    }
    
}
