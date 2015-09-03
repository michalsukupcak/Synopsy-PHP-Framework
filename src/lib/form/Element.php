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
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
abstract class Element {
    
    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    /**
     * Separator of keys in complex keys
     */
    const ARRAY_SEPARATOR = '.';  
    
    /**
     * CSS class defining if input has bootstrap
     */
    const IS_BOOTSTRAP = '__isBootstrap';
            
    /**
     * Data-attributes for elements.
     */
    const DATA_SUBMIT_URL = 'submit-url';
    const DATA_SUBMIT_TYPE = 'submit-type';
    const DATA_SUBMIT_TARGET = 'submit-target';
    const DATA_DATATYPE = 'datatype';
    const DATA_VALIDATE_REGEX = 'validate-regex';
    const DATA_UPLOAD_MAX_FILE_COUNT = 'upload-max-file-count';
    const DATA_UPLOAD_MAX_FILE_SIZE = 'upload-max-file-size';
    const DATA_UPLOAD_ALLOWED_EXTENSIONS = 'upload-allowed-extensions';
    
    /**
     * List of reserved data-attributes.
     *
     * @var Array
     */
    protected static $reservedDataAttributes = [
        self::DATA_SUBMIT_URL,
        self::DATA_SUBMIT_TYPE,
        self::DATA_SUBMIT_TARGET,
        self::DATA_DATATYPE,
        self::DATA_VALIDATE_REGEX
    ];
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */
    
    /**
     * Element name.
     *
     * @var String 
     */
    private $name = null;
    
    /**
     * Element datatype.
     *
     * @var String 
     */
    protected $datatype = null;
    
    /**
     * Determines if element is member of an array of elements or not.
     *
     * @var Boolean
     */
    private $isArray = false;
    
    /**
     * Default value of an element.
     *
     * @var Mixed
     */
    private $defaultValue = null;
    
    /**
     * Post value of an element
     *
     * @var Mixed
     */
    private $postValue = null;
    
    /**
     * CSS id attribute.
     *
     * @var String
     */
    private $cssId = null;
    
    /**
     * CSS class attribute.
     *
     * @var String
     */
    private $cssClass = null;
    
    /**
     * Array of data attributes of an element.
     *
     * @var Array
     */
    private $dataAttributes = [];
    
    /**
     *
     * @var String
     */
    private $validate = null;
    
    /* ---------------------------------------------------------------------- */
    /* Constructor */
    
    /**
     * 
     * @param type $name
     * @throws SynopsyException
     */
    public function __Construct($name) {
        if ($name == null) {
            throw new SynopsyException("Element 'name' parameter can't be null!");
        }
        $this->name = $name;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Element methods (attribute setters & getters) */
    
    /**
     * Retrieves element name.
     * 
     * @return String
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Retrieves element name HTML representation.
     * 
     * @return String
     */
    public function getNameHtml($key=null) {
        if ($key) {
            $k = $this->createKey($key);
            return "$this->name[$k]";
        } else {
            return $this->name;
        }
    }
    
    /**
     * Sets element Datatype.
     * 
     * @param String $datatype
     * @return Element
     * @throws SynopsyException
     */
    public function setDatatype($datatype) {
        if (!in_array($datatype,Datatype::$datatypes)) {
            throw new SynopsyException("Datatype parameter '$datatype' for element '$this->name' is NOT a valid datatype!");
        }
        $this->datatype = $datatype;
        return $this;
    }
    
    /**
     * Retrieves element datatype.
     * 
     * @return String
     */
    public function getDatatype() {
        if (!$this->datatype) {
            throw new SynopsyException("Parameter 'datatype' MUST be specified for every element, element '$this->name' is missing!");
        }
        return $this->datatype;
    }

    /**
     * Retrieves HTML representation of element datatype.
     * 
     * @throws SynopsyException
     */
    public function getDatatypeHtml() {
        if (!$this->datatype) {
            throw new SynopsyException("Parameter 'datatype' MUST be specified for every element, element '$this->name' is missing!");
        }        
        return ($this->validate ? ' data-'.self::DATA_DATATYPE.'="'.$this->datatype.'"' : '');
    }
        
    /**
     * Determines if element is a member of element array.
     * 
     * @param Boolean $isArray
     * @return Element
     */
    public function setIsArray($isArray) {
        $this->isArray = ($isArray == true);
        return $this;
    }
    
    /**
     * 
     * @return Boolean
     */
    public function isArray() {
        return $this->isArray;
    }
    
    /**
     * Sets default value for an element.
     * 
     * @param Mixed $defaultValue
     * @return Element
     * @param Array $key
     */
    public function setDefaultValue($defaultValue,$key=null) {
        if ($key) {
            $this->defaultValue[$this->createKey($key)] = $defaultValue;
        } else {
            $this->defaultValue = $defaultValue;
        }
        return $this;
    }
    
    /**
     * Returns default value for an element.
     * 
     * @param Array $key
     * @return Mixed
     */
    public function getDefaultValue($key=null) {
        if ($key) {
            $k = $this->createKey($key);
            if (isset($this->defaultValue[$k])) {
                return $this->defaultValue[$k];
            } else {
                return null;
            }
        } else {
            return $this->defaultValue;
        }
    }
    
    /**
     * Returns element value.
     * 
     * @return Mixed
     */
    public function getPostValue($key=null) {
        if ($this->postValue) {
            if ($this->isArray) {
                if ($key) {
                    if (isset($this->postValue[$this->createKey($key)])) {
                        return $this->postValue[$this->createKey($key)];
                    } else {
                        return null;
                    }
                } else {
                    return $this->postValue;
                }
            } else {
                return $this->postValue;
            }
        } else {
            $escape = null;
            switch ($this->datatype) {
                case Datatype::STRING:
                    $escape = null;
                    break;
                case Datatype::BOOLEAN:
                    $escape = 'is_set';
                    break;
                case Datatype::INTEGER:
                    $escape = 'intval';
                    break;
                case Datatype::FLOAT:
                    $escape = 'floatval';
                    break;
            }
            if ($this->isArray) {
                $value = [];
                $post = filter_input(INPUT_POST,$this->name,FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
                if ($post !== null) {
                    foreach ($post as $i => $v) {
                        if ($escape) {
                            $value[$i] = $escape($v);
                        } else {
                            $value[$i] = $v;
                        }
                    }
                }
                $this->postValue = $value;
                if ($key) {
                    if (isset($this->postValue[$this->createKey($key)])) {
                        return $this->postValue[$this->createKey($key)];
                    } else {
                        return null;
                    }
                } else {
                    return $this->postValue;
                }
            } else {
                $post = filter_input(INPUT_POST,$this->name);
                if ($post !== null) {
                    $v = $post;
                } else {
                    $v = null;
                }
                if ($escape) {
                    $value = $escape($v);
                } else {
                    $value = $v;
                }
                $this->postValue = $value;
                return $this->postValue;
            }
        }
    }
    
    /**
     * 
     * @param Array $key
     * @return String
     */
    public function getActualValue($key=null) {
        if (filter_input(INPUT_SERVER,'REQUEST_METHOD') == 'POST') {
            $value = $this->getPostValue();
            if ($key) {
                return $value[$this->createKey($key)];
            } else {
                return $value;
            }
        } else {
            return $this->getDefaultValue($key);
        }
    }
    
    /**
     * Sets CSS id attribute.
     * 
     * @param String $cssId
     * @return Element
     */
    public function setCssId($cssId) {
        $this->cssId = $cssId;
        return $this;
    }
    
    /**
     * Returns CSS id attribute.
     * 
     * @return String
     */
    public function getCssId() {
        return $this->cssId;
    }
    
    /**
     * Returns CSS id attribute HTML representation.
     * 
     * @return String
     */
    public function getCssIdHtml() {
        return ($this->cssId ? ' id="'.$this->cssId.'"' : '');
    }

    /**
     * Sets CSS class attribute.
     * 
     * @param String $cssClass
     * @return Element
     */
    public function setCssClass($cssClass) {
        $this->cssClass = $cssClass;
        return $this;
    }
    
    /**
     * Appends new CSS class to existing CSS class attribute.
     * 
     * @param String $cssClass
     * @return Element
     */
    public function addCssClass($cssClass) {
        $this->cssClass .= " $cssClass";
        return $this;
    }
    
    /**
     * Returns CSS class attribute.
     * 
     * @return String
     */
    public function getCssClass() {
        return $this->cssClass;
    }
    /**
     * Returns CSS class attribute HTML representation.
     * 
     * @return String
     */
    public function getCssClassHtml() {
        return ($this->cssClass ? ' class="'.$this->cssClass.'"' : '');
    }
    
    /**
     * Sets element data-attributes.
     * 
     * @param Array $dataAttributes
     * @return Element
     * @throws SynopsyException
     */
    public function setDataAttributes($dataAttributes) {
        if (!is_array($dataAttributes)) {
            throw new SynopsyException("\$dataAtrributes parameter for element '$this->name' must be an array!");
        }
        foreach ($dataAttributes as $attribute => $value) {
            if (in_array($attribute,self::$reservedDataAttributes)) {
                throw new SynopsyException("Data attribute name '$attribute' is a reserved keyword and cannot be used as an data-attribute!");
            }
        }
        $this->dataAttributes = $dataAttributes;
        return $this;
    }
    
    /**
     * Retrieves list of data-attributes.
     */
    public function getDataAttributes() {
        $this->dataAttributes;
    }
    
    /**
     * Returns HTML code representation for all data-attributes (data-x="...").
     * 
     * @return String
     */
    public function getDataAttributesHtml() {
        $html = '';
        foreach ($this->dataAttributes as $attribute => $value) {
            $html .= ' data-'.$attribute.'="'.$value.'"';
        }
        return $html;
    }
    
    /**
     * 
     * @param String $validate
     * @return Element
     */
    public function setValidate($validate) {
        $this->validate = $validate;
        return $this;
    }
    
    /**
     * 
     * @return String
     */
    public function getValidate() {
        return $this->validate;
    }
        
    /**
     * 
     * @return String
     */
    public function getValidateHtml() {
        return ($this->validate ? 'data-'.self::DATA_VALIDATE_REGEX.'="'.$this->validate.'"' : '');
    }
    
    /**
     * Constructs complex array key from supplied array of keys.
     * 
     * @param Array $key
     * @return String
     * @throws SynopsyException
     */
    public function createKey($key) {
        if (!$this->isArray) {
            throw new SynopsyException("Element '$this->name' is not an array, therefore it can't have keys!");
        }
	if (empty($key)) {
            throw new SynopsyException("Key must be a non-empty array!");
        }
        if (!is_array($key)) {
            throw new SynopsyException('Parameter $keys must be an array!');
        }	   
        if (count($key) > 1) {
            $newKey = '';
            foreach ($key as $k) {
                if ($k == self::ARRAY_SEPARATOR) {
                    throw new SynopsyException("Character '$k' is a reserved array separator and cannot be used as array key!");
                }
                $newKey .= $k.self::ARRAY_SEPARATOR;
            }
            return rtrim($newKey,self::ARRAY_SEPARATOR);
        } else {
            return $key[0];
        }
    }
    
    /* ---------------------------------------------------------------------- */
    /* Abstract and overriden methods */
    
    /**
     * Retrieves element HTML code representation.
     * 
     * @return String
     */
    public abstract function getHtml();
        
}
