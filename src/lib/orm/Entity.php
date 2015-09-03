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

namespace Synopsy\Orm;

use Synopsy\Db\Database;
use Synopsy\Exceptions\SynopsyException;
use Synopsy\Sql\Queries\Delete;
use Synopsy\Sql\Queries\Insert;
use Synopsy\Sql\Queries\Select;
use Synopsy\Sql\Queries\Update;

/**
 * Abstract entity class. All entities in the application *MUST* extend this
 * class. Provides initialization of entity data plus basic operations on these
 * entities: select, insert, update, delete, get, set and toArray.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
abstract class Entity {
        
    protected $table = null;
    protected $columns = [];
    
    /**
     * All columns of an entity, EXCLUDING primary key. (Copy of
     * Entity::$columns array, except for first entry.)
     *
     * @var Array 
     */
    protected $cols = [];
    
    /**
     * Data of the entity. Index (key) in array represents column name, value
     * represents value of given column.
     *
     * @var Array 
     */
    private $data = [];
    
    /**
     * List of columns that have been modified since data have been loaded into
     * the entity.
     *
     * @var Array
     */
    private $updated = [];
    
    /**
     * Name of primary key (usualy ID) of an entity.
     *
     * @var String 
     */
    private $primaryKey = null;
    
    /**
     * Variable determines if the entity is empty or not.
     *
     * @var Boolean
     */
    private $isEmpty = null;
    
    /**
     * Constructor for all entities. Initializes all columns for this entity.
     * If called constructor is empty, all values of all columns are set to
     * default null. If called constructor is NOT empty, it MUST contain the
     * SAME NUMBER of parameters as Entity::$columns array AND in the SAME
     * ORDER!
     * 
     * @throws SynopsyException
     */
    public function __Construct() {
	$argCount = func_num_args();
	$colCount = count($this->columns);
	if ($argCount > 0) {
	    if ($argCount != $colCount) {
		throw new SynopsyException('Invalid number of arguments for constructor of entity \''.get_called_class().'\'!');
	    }
	    $args = func_get_args();
	    $this->isEmpty = false;
	} else {
	    $args = null;
	    $this->isEmpty = true;
	}
	for ($i = 0; $i < $colCount; $i++) {
	    $column = $this->columns[$i];
	    $this->data[$column] = (!$this->isEmpty ? $args[$i] : null);
	    if ($i > 0) {
		$this->cols[] = $column;
		$this->updated[$column] = false;
	    }
	}
	$this->primaryKey = $this->columns[0];
    }
    
    /**
     * Selects entry from corresponding table with given $pk value as primary
     * key and loads ALL columns with values from database (including primary
     * key) as well as resets all "modified" flags on all columns in
     * Entity::$modifiedCols array.
     * 
     * Only empty entities can be loaded.
     * 
     * @param String $primaryKey
     * @return Boolean
     * @throws SynopsyException
     */
    public function load($primaryKey) {
	if (!$this->isEmpty) {
	    throw new SynopsyException('Calling method Entity::load($pk) on a non-empty entity \''.get_called_class().'\'!');
	}
	$result = Database::executeQuery((new Select($this->table))->cols($this->columns)->where($this->primaryKey,'=',$primaryKey),true);
	if (Database::changed()) {
	    $results = Database::fetchOne($result);
	    foreach ($results as $col => $value) {
		$this->data[$col] = $value;
		$this->update[$col] = false;
	    }
	    $this->isEmpty = false;
	    return true;
	} else {
	    return false;
	}
    }
    
    /**
     * Saves entity into database. Entity with non-empty primary key will be
     * UPDATED, entity with empty primary key will be INSERTED.
     * 
     * Only non-empty entities can be saved.
     * 
     * @return Boolean
     * @throws SynopsyException
     */
    public function save() {
	if ($this->isEmpty) {
	    throw new SynopsyException('Calling method Entity::save() on an empty entity \''.get_called_class().'\'!');
	}
	if ($this->data[$this->primaryKey] == null) {
	    $insert = [];
	    foreach ($this->cols as $col) {
		$insert[$col] = $this->data[$col];
	    }
	    Database::executeQuery((new Insert($this->table))->data($insert));
	    if (Database::changed()) {
		$this->data[$this->primaryKey] = Database::insertId();
		$this->isEmpty = false;
		return true;
	    } else {
		return false;
	    }
	} else {
	    $update = [];
	    foreach ($this->cols as $col) {
		if ($this->updated[$col] == true) {
		    $update[$col] = $this->data[$col];
		}
	    }
	    if (!empty($update)) {
		Database::executeQuery((new Update($this->table))->data($update)->where($this->primaryKey,'=',$this->data[$this->primaryKey]));
		if (Database::changed()) {
		    $this->updated = array_fill_keys(array_keys($this->updated),false);
		    return true;
		} else {
		    return false;
		}
	    } else {
		return false;
	    }   
	}
    }
    
    /**
     * Deletes entry with curreny primary key from database. Keeps data in
     * entity until entity is released/emptied/destroyed.
     * 
     * Only non-empty entities can be deleted.
     * 
     * @return Boolean
     * @throws SynopsyException
     */
    public function delete() {
	if ($this->isEmpty) {
	    throw new SynopsyException('Calling method Entity::delete() on an empty entity \''.get_called_class().'\'!');
	}
	Database::executeQuery((new Delete($this->table))->where($this->primaryKey,'=',$this->data[$this->primaryKey]));
	if (Database::changed()) {
	    $this->data = array_fill_keys(array_keys($this->data),null);
	    $this->isEmpty = true;   
	    return true;
	} else {
	    return false;
	}
    }
    
    /**
     * Sets given column ($col) new value ($value) from parameter, as well as
     * sets "modified" flag for given column ($this->modifiedColsp[$col])
     * (Unless $col is equal to $this->pk!).
     * 
     * @param String $col
     * @param String/Integer $value
     * @throws SynopsyException
     */
    public function set($column,$value) {
	if (!array_key_exists($column,$this->data)) {
	    throw new SynopsyException("Column '$column' accessed in method Entity::set(\$column,\$value) is not defined in entity '".get_called_class()."'!");
	}
	if ($column != $this->primaryKey) {
	    $update = true;
	} elseif ($this->isEmpty) {
	    $update = true;
	    $this->isEmpty = false;
	} else {
	    throw new SynopsyException('Attempting to set primary key for a non-empty entity in method Entity::set($column,$value)!');
	}
	if ($update) {
	    $this->data[$column] = $value;
	    $this->updated[$column] = true;	    
	}
    }
    
    /**
     * Returns currently stored value of given column ($col) of current entity
     * instance.
     * 
     * @param String $col
     * @return Mixed
     * @throws SynopsyException
     */
    public function get($column) {
	if (!array_key_exists($column,$this->data)) {
	    throw new SynopsyException("Column '$column' access in method Entity::get(\$column) is not defined in entity '".get_called_class()."'!");
	}
	return $this->data[$column];
    }
    
    /**
     * Returns table name for current entity.
     * 
     * @return String
     */
    public function getTable() {
	return $this->table;
    }
    
    /**
     * Returns array of columns (including primary key) of the current entity.
     * 
     * @return Array
     */
    public function getColumns() {
	return $this->columns;
    }
    
    /**
     * Returns array of columns (excluding primary key) of the current entity.
     * 
     * @return Array
     */
    public function getCols() {
	return $this->cols;
    }
    
    /**
     * Returns array of columns (as keys) and values of the columns in current
     * entity.
     * 
     * @return Array
     */
    public function getData() {
	return $this->data;
    }
    
    /**
     * Returns name of primary key column for current entity.
     * 
     * @return String
     */
    public function getPrimaryKey() {
	return $this->primaryKey;
    }
    
    /**
     * Static variant of Entity::load($pk) method.
     * 
     * @param String $class
     * @param Intger $primaryKey
     * @return Entity
     * @throws SynopsyException
     */
    public static function loadInstance($class,$primaryKey) {
	if (!file_exists(APP."entities/$class.php")) {
	    throw new SynopsyException("Entity '$class' supplied as parameter in static method Entity::load(\$entity,\$pk) does not exist!");
	}
	$entity = new $class();
	$entity->load($primaryKey);
	return $entity;
    }
    
    /**
     * Converts a an array of data arrays into an array of entities.
     * 
     * @param Array $arrayOfArrays
     * @return Array
     * @throws SynopsyException
     */
    public static function toEntities($arrayOfArrays) {
        $class = get_called_class();
	if (!is_array($arrayOfArrays)) {
	    throw new SynopsyException('Parameter $arrayOfArrays must be an array!');
	}
	$return = [];
	foreach ($arrayOfArrays as $a) {
	    if (!is_array($a)) {
		throw new SynopsyException("Each value of the \$array array must be an array, '$a' given!");
	    }
	    $entity = new $class();
	    foreach ($a as $column => $value) {
		$entity->set($column,$value);
	    }
	    $return[] = $entity;
	}
	return $return;
    }
    
    /**
     * Converts a data array into an entity.
     * 
     * @param Array $array
     * @return Array
     * @throws SynopsyException
     */
    public static function toEntity($array) {
        $class = get_called_class();
        $entity = new $class();
        foreach ($array as $column => $value) {
            $entity->set($column,$value);
        }
	return $entity;
    }
    
    /**
     * Converts an array of entities into data array
     * 
     * @param type $entities
     * @return type
     * @throws InvalidArgumentException
     */
    public static function toArray($entities) {
	if (!is_array($entities)) {
	    throw new SynopsyException('Parameter $entities must be an array!');
	}
	$return = [];
	foreach ($entities as $entity) {
	    if (!$entity instanceof Entity) {
		throw new SynopsyException("Each value of the \$entities must be an (sub)instance of Entity class, '$entity' given!");
	    }
	    $return[] = $entity->getData();
	}
	return $return;
    }
    
}
