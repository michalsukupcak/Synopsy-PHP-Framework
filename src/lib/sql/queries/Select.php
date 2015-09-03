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

namespace Synopsy\Sql\Queries;

use Synopsy\Exceptions\SynopsyException;
use Synopsy\Sql\Query;

/**
 * Class used for construction of SELECT SQL queries.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Select extends Query {

    /**
     * Constructor. begins SELECT query (SELECT ...).
     * 
     * @param String $table
     * @return Select
     */
    public function __Construct($table) {
	parent::__Construct($table);
	$this->sql = 'SELECT ';
	return $this;
    }

    /**
     * Appends list of columns that are to be selected from database to current
     * SQL query. Columns are supplied as function arguments and function must
     * have at least one argument in order to work properly!
     * 
     * Example:
     * (new Select(...))->cols('x','y','z')
     * ->
     * SELECT x,y,z FROM ...
     * 
     * @param String/Array $columns
     * @return Select
     * @throws InvalidArgumentException
     */
    public function cols($columns) {
	$sql = '';
	if (is_array($columns)) {
	    $cols = $columns;
	} else {
	    $cols = func_get_args();
	}
	foreach ($cols as $col) {
	    $sql .= $this->escape($col).',';
	}
	$this->sql .= rtrim($sql,',').' FROM '.$this->table;
	return $this;
    }
    
    /**
     * Appends ORDER BY clause to current SELECT SQL query.
     * 
     * @return Select
     * @throws SynopsyException
     */
    public function orderBy() {
	if (func_num_args() < 1) {
	    throw new SynopsyException('Method Select::orderBy() must have at least one argument!');
	}
	$sql = ' ORDER BY ';
	foreach (func_get_args() as $arg) {
	    if (strlen($arg) > 0) {
		$sql .= $this->escape($arg).',';
	    }
	    $this->sql .= rtrim($sql,',');
	}
	return $this;
    }
    
    /**
     * Appends GROUP BY clause to current SELECT SQL query.
     * 
     * @return Select
     * @throws SynopsyException
     */
    public function groupBy() {
	if (func_num_args() < 1) {
	    throw new SynopsyException('Method Select::groupBy() must have at least one argument!');
	}
	$sql = ' GROUP BY ';
	foreach (func_get_args() as $arg) {
	    if (strlen($arg) > 0) {
		$sql .= $this->escape($arg).',';
	    }
	    $this->sql .= rtrim($sql,',');
	}
	return $this;
    }
    
    /**
     * Appends LIMIT clause to current SELECT SQL query.
     * 
     * @param type $start
     * @param type $stop
     * @return \Select
     */
    public function limit($start,$stop=null) {
	$this->sql .= ' LIMIT '.$this->escape($start).($stop ? ','.$this->escape($stop) : '');
	return $this;
    }
    
}