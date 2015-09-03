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

namespace Synopsy\Mvc;

use Synopsy\Exceptions\SynopsyException;

/**
 * One and only view class. All views in system use an instance of this class
 * for their creation. Each view must be loaded (View::load()) before it is
 * created (View::create())!
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class View {
    
    /**
     * Holds filename of html associated with instance of this view.
     * 
     * @var String
     */
    private $file = null;
    
    /**
     * Holds data array for variables associated with this view.
     * 
     * @var Smarty
     */
    private $data = [];

    /**
     * Constructor. Parses input file and creates new instance of Smarty
     * framework.
     * 
     * @param String $class
     * @param String $folder
     * @throws InvalidArgumentException
     * @throws ViewException
     */
    public function __Construct($file,$class,$folder) {
	if ($file == null) {
	    throw new SynopsyException('Parameter $view must a non-empty string!');
	}
	$this->file = $folder.'/'.explode('Controller',lcfirst($class))[0].'Views/'.$file.'.tpl';
	if (!file_exists($this->file)) {
	    throw new SynopsyException("View TPL file '$this->file' doesn't exist!");
	}
    }

    /**
     * Sets variables into current view's smarty template. Values from $data
     * array, eg.: ['foo' => 1] are accessed using their keys as following:
     * <div>{$foo}</div> ---> <div>1</div>
     * 
     * @param Array $data
     * @throws InvalidArgumentException
     */
    public function setData($data) {
	if (!is_array($data)) {
	    throw new SynopsyException("Parameter \$data must be array, '$data' given!");
	}
	foreach ($data as $key => $value) {
	    if ($key == null) {
		throw new SynopsyException('Array key can never be null!');
	    }
	    $this->data[$key] = $value;
	}
    }
        
    /**
     * Works on the same principle as View::setData($data) method, but only for
     * one variable at a time.
     * 
     * @param String $key
     * @param Mixed $value
     * @throws InvalidArgumentException
     */
    public function setDataPair($key,$value) {
	if ($key == null) {
	    throw new SynopsyException('$key can never be null!');
	}
	$this->data[$key] = $value;
    }

    /**
     * Return path to template file.
     * 
     * @return String
     */
    public function getFile() {
        return $this->file;
    }
    
    /**
     * Return array of variables associated with template file.
     * 
     * @return Array
     */
    public function getData() {
        return $this->data;
    }
    
}

