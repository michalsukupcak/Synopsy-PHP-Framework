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
 * Class used for construction of DELETE SQL queries.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Insert extends Query {
    
    /**
     * Constructor, begins SQL query (INSERT INTO [table] ...)
     * 
     * @param type $table
     * @return \Insert
     */
    public function __Construct($table) {
	parent::__Construct($table);
	$this->sql = 'INSERT INTO '.$this->table;
	return $this;
    }
 
    /**
     * Appends columns and values that are to be inserted to the end of current
     * INSERT SQL query. Columns and values are supplied as array, where each
     * entry is one column-value pair (key-value).
     * 
     * Example:
     * [
     *     'x' => 3,
     *     'y' => 4
     * ]
     * ->
     * INSERT INTO [table](x,y) VALUES('3','4')
     * 
     * @param type $insert
     * @return \Update
     * @throws SynopsyException
     */
    public function data($insert) {
	if (!is_array($insert) || empty($insert)) {
	    throw new SynopsyException('Parameter $insert of method Insert::data() must be a non-empty array! ');
	}
	$columns = '';
	$values = '';
	foreach ($insert as $column => $value) {
	    $columns .= $this->escape($column).',';
	    $values .= '\''.$this->escape($value).'\',';
	}
	$c = rtrim($columns,',');
	$v = rtrim($values,',');
	$this->sql .= '('.$c.') VALUES('.$v.')';
	return $this;
    }
    
}