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

namespace Synopsy\Db;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
interface DatabaseInterface {
        
    /**
     * Opens mysqli connection to database.
     * 
     * @param Config $config
     * @throws DatabaseException
     */
    public function connect($config);
    
    /**
     * Closes mysqli connection with database.
     * 
     */
    public function disconnect();
    
    /**
     * Executes supplied SQL query.
     * 
     * @param Query $query
     * @param Boolean $return
     * @return MySQLi_result
     * @throws DatabaseException
     */
    public function executeQuery($query,$return=false);
    
    /**
     * Returns associative array of DB select results.
     * 
     * 
     * @param MySQLi_result $result
     * @return type
     * @throws DatabaseException
     */
    public function fetchAll($result=null);
    
    /**
     * Returns array of element's parameters of DB select result.
     * 
     * 
     * @param MySQLi_result $result
     * @return type
     * @throws DatabaseException
     */
    public function fetchOne($result=null);
    
    /**
     * Returns newest AUTO_INCREMENT value generated by INSERT SQL query.
     * 
     * @return Integer
     */
    public function insertId();
    
    /**
     * Returns number of rows affected by last SQL query.
     * 
     * @return Integer
     */
    public function affectedRows();
    
    /**
     * Determines if last SQL query modified any data in database.
     * 
     * @return Boolean
     */
    public function changed();
    
    /**
     * Escapes string using mysqli_real_escape_string.
     * 
     * @param String $string
     * @return String
     */
    public function escape($string);
    
}