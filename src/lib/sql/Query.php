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

namespace Synopsy\Sql;

use Synopsy\Db\Database;
use Synopsy\Exceptions\SynopsyException;

/**
 * Abstract class used in construction of SQL queries.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
abstract class Query {
        
    /**
     * Table on which current query should be executed
     * 
     * @var String
     */
    protected $table = '';
    
    /**
     * SQL command for current query
     * 
     * @var String
     */
    protected $sql = '';
    
    /**
     * Determines whether the first WHERE clause in SQl command was called
     * 
     * @var Boolean
     */
    private $where = false;
    
    /**
     * Loads table on which current query should be executed.
     * 
     * @throws SynopsyException
     */
    public function __Construct($table) {
	if ($table == null) {
	    throw new SynopsyException('Parameter $table can\'t be null!');
	}
	$this->table = $table;
    }

    /**
     * Appends an WHERE clause to the end of current query.
     * 
     * @param String $column
     * @param String $operator
     * @param String $value
     * @return \Query
     */
    public function where($column,$operator,$value) {
	if (!$this->where) {
	    $this->sql .= ' WHERE ';
	    $this->where = true;
	}
	$this->sql .= ' '.$this->escape($column).' '.$this->verifyOperator($operator).' \''.$this->escape($value).'\'';
	return $this;
    }

    /**
     * Appends series of WHERE clauses to the end of current query (clauses
     * share same operator and condition between them).
     * 
     * @param Array $where
     * @param String $operator
     * @param String $condition
     * @return \Query
     * @throws SynopsyException
     */
    public function whereArray($where,$operator=null,$condition=null) {
	if (!is_array($where) || empty($where)) {
	    throw new SynopsyException('Parameter $where of method Query::whereArray() must be a non-empty array! ');
	}
	if (!$this->where) {
	    $this->sql .= ' WHERE ';
	    $this->where = false;
	}
	$w = '';
	$o = ($operator ? $operator : '=');
	$c = ($condition ? $condition : 'AND');
	foreach ($where as $column => $value) {
	    $w .= $this->escape($column).' '.$this->verifyOperator($o).' \''.$this->escape($value).'\' '.$this->escape($c).' ';
	}
	$this->sql .= rtrim($w,' '.$this->escape($c).' ');
	return $this;
    }

    /**
     * Appends AND condition to the end of current query.
     * 
     * @return \Query
     */
    public function cAnd() {
	$this->sql .= ' AND ';
	return $this;
    }

    /**
     * Append OR condition to the end of current query.
     * 
     * @return \Query
     */
    public function cOr() {
	$this->sql .= ' OR ';
	return $this;
    }

    /**
     * Appends opening bracket to the end of current query.
     * 
     * @return \Query
     */
    public function bOpen() {
	$this->sql .= ' ( ';
	return $this;
    }

    /**
     * Appends closing bracket to the end of current query.
     * 
     * @return \Query
     */
    public function bClose() {
	$this->sql .= ' ) ';
	return $this;
    }

    /**
     * Returns SQL command of current query.
     * 
     * @return String
     */
    public function getSql() {
	return $this->sql;
    }
        
    /**
     * Calls method Database::escape()
     * 
     * @param String $string
     * @return String
     */
    protected function escape($string) {
	return Database::escape($string);
    }
    
    /**
     * 
     * @param type $operator
     * @return type
     * @throws SynopsyException
     */
    private function verifyOperator($operator) {
        $operators = [
            '=',
            '>',
            '>=',
            '<',
            '<=',
            '<>',
            '!=',
            '<=>',
            'LIKE'
        ];
        if (!in_array($operator,$operators)) {
            throw new SynopsyException("Operator '$operator' is not a suppported MySQL operator!");
        } else {
            return $operator;
        }
    }
    
}