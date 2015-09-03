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

use Synopsy\Db\Database;

/**
 * Abstract model class of.
 * 
 * Main objective of model is to provide means of data retrieval from database
 * for processing in corresponding controller.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
abstract class Model {

    /**
     * Constructor.
     * 
     */
    public function __Construct() {}
        
    /**
     * Calls method Database::executeQuery()
     * 
     * @param Query $qyery
     * @param Boolean $return
     * @return MySQLi_result
     * @throws InvalidArgumentException
     */
    protected function executeQuery($query,$return=false) {
	return Database::executeQuery($query,$return);
    }
        
    /**
     * Calls method Database::fetchAll()
     * 
     * 
     * @param MySQLi_result $result
     * @return type
     * @throws InvalidArgumentException
     */
    protected function fetchAll($result=null) {
	return Database::fetchAll($result);
    }
    
    /**
     * Calls method Database::fetchOne()
     * 
     * 
     * @param MySQLi_result $result
     * @return type
     * @throws InvalidArgumentException
     */
    protected function fetchOne($result=null) {
	return Database::fetchOne($result);
    }
    
    /**
     * Calls method Database::insertId()
     * 
     * @return Integer
     */
    protected function insertId() {
	return Database::insertId();
    }
    
    /**
     * Calls method Database::affectedRows()
     * 
     * @return Integer
     */
    protected function affectedRows() {
	return Database::affectedRows();
    }
    
    /**
     * Calls method Database::changed()
     * 
     * @return Boolean
     */
    protected function changed() {
	return Database::changed();
    }
        
}