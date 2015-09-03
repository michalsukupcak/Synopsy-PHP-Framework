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
 * Class used for construction of UPDATE SQL queries.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Update extends Query {
    
    /**
     * Constructor, begins UPDATE query (UPDATE [table] SET ...).
     * 
     * @param String $table
     * @return Update
     */
    public function __Construct($table) {
	parent::__Construct($table);
	$this->sql = 'UPDATE '.$this->table.' SET ';
	return $this;
    }
    
    /**
     * Appends columns and values that are to be updated to the end of current
     * UPDATE SQL query. Columns and values are supplied as array, where each
     * entry is one column-value pair (key-value).
     * 
     * Example:
     * [
     *     'x' => 3,
     *     'y' => 4
     * ]
     * ->
     * UPDATE [table] SET x = '3', y = '4'
     * 
     * @param type $update
     * @return \Update
     * @throws SynopsyException
     */
    public function data($update) {
	if (!is_array($update) || empty($update)) {
	    throw new SynopsyException('Parameter $update of method Update::data() must be a non-empty array! ');
	}
	$u = '';
	foreach ($update as $column => $value) {
	    $u .= $this->escape($column).' = \''.$this->escape($value).'\',';
	}
	$this->sql .= rtrim($u,',');
	return $this;
    }
    
}