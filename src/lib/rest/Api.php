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

namespace Synopsy\Rest;

use ReflectionClass;
use Synopsy\Exceptions\SynopsyException;
use Synopsy\Routing\RequestInterface;

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
abstract class Api {
    
    /**
     * Instance of storage class associated with current api.
     * 
     * @var Storage
     */
    protected $storage = null;
    
    /**
     * Instances of models loaded through loadModels method
     * 
     * @var Storage
     */
    protected $models = null;
    
    /**
     * Instance of json output created by api.
     *
     * @var Json 
     */
    protected $json = null;
    
    /**
     * Folder in which api is located.
     *
     * @var String 
     */
    protected $folder = null;
        
    /**
     *
     * @var Request 
     */
    protected $request = null;
    
    /**
     * Constructor. Creates instance of current controller. Creates
     * corresponding model class instance and calls controller methods.
     * 
     * @param Request $request
     * @throws SynopsyException
     */
    public function __Construct($request) {
        if (!$request instanceof RequestInterface) {
            throw new SynopsyException("Parameter \$request must implement RequestInterface!");
        }
        $this->request = $request;
	$this->class = get_called_class();
        $reflectionClass = new ReflectionClass($this->class);        
        $this->folder = dirname($reflectionClass->getFileName());
	$storageClass = explode('Api',$this->class)[0].'Storage';
	require_once($this->folder.'/'.$storageClass.'.php');
	$this->storage = new $storageClass();
	$this->onLoad();
    }
    
    /**
     * Initial method of the api, called immediately after creating new api
     * instance.
     * 
     */
    protected abstract function onLoad();
       
    /**
     * Returns instance of Json class from executed API method.
     * 
     * @return Json
     */
    public function getJson() {
        return $this->json;
    }
    
    /**
     * 
     * @param type $models
     * @throws SynopsyException
     */
    protected function loadModels($models) {
        if (!is_array($models)) {
            throw new SynopsyException('Parameter $models for Api::loadModels method must be an array!');
        }
        foreach ($models as $model) {
            $modelFile = APP.'mvc/'.$model.'.php';
            if (!file_exists($modelFile)) {
                throw new SynopsyException("File '$modelFile' for model '$model' doesn't exist!");
            }
            require_once($modelFile);
        }
    }
    
}