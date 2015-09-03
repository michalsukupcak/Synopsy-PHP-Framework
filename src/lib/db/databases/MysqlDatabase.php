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

namespace Synopsy\Db\Databases;

use MySQLi;
use MySQLi_result;
use Synopsy\Db\DatabaseInterface;
use Synopsy\Exceptions\SynopsyException;
use Synopsy\Sql\Query;

/**
 * Class handling opening, storing and closing mysqli connections with database
 * server.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class MysqlDatabase implements DatabaseInterface {
    
    /**
     * Holds mysqli connection instance
     * 
     * @var MySQLi
     */
    private $mysqli = null;
    
    /**
     * Holds result of last query
     * 
     * @var MySQLi_Result
     */
    private $result = null;

    public function __Construct() {}
 
    public function connect($config) {
	$mysqli = new MySQLi(
            $config['host'],
            $config['user'],
            $config['password'],
            $config['database'],
            ($config['port'] ? intval($config['port']) : null)
        );
	if ($mysqli->ping()) {
	    if ($mysqli->set_charset('utf8')) {
		$this->mysqli = $mysqli;
	    } else {
		throw new SynopsyException('Unable to set utf-8 as database connection charset!');
	    }
	} else {
	    throw new SynopsyException('Unable to connect to database server!');
	}
    }
 

    public function disconnect() {
	$this->mysqli->close();
	$this->mysqli = null;
    }
        
    public function executeQuery($query,$return=false) {
	if (!$query instanceof Query) {
	    throw new SynopsyException('Parameter $query of method Database::executeQuery() must be instance of class Query!');
	}
	$sql = $query->getSql();
	$result = $this->mysqli->query($sql);
	if ($result) {
	    if (!$return) {
		$this->result = $result;
	    }
	    return $result;
	} else {
            synopsy_sql_handler($sql,$this->mysqli->error,$this->mysqli->errno);
	    return null;
	}
    }
    
    public function fetchAll($result=null) {
	if ($result) {
	    if (!$result instanceof MySQLi_result) {
		throw new SynopsyException('Parameter $result of method Database::fetchAll() must be instance of class MySQLi_result!');
	    }
	    $return = $result->fetch_all(MYSQLI_ASSOC);
	} else {
	    $return = $this->result->fetch_all(MYSQLI_ASSOC);
	}
        return $return;
	
    }
    
    public function fetchOne($result=null) {
	if ($result) {
	    if (!$result instanceof MySQLi_result) {
		throw new SynopsyException('Parameter $result of method Database::fetchAll() must be instance of class MySQLi_result!');
	    }
	    $return = $result->fetch_all(MYSQLI_ASSOC);
	} else {
	    $return = $this->result->fetch_all(MYSQLI_ASSOC);
	}
        if (is_array($return)) {
            return $return[0];
        } else {
            return null;
        }
    }
    
    public function insertId() {
	return $this->mysqli->insert_id;
    }
    
    public function affectedRows() {
	return $this->mysqli->affected_rows;
    }
    
    public function changed() {
	return ($this->affectedRows() > 0);
    }
    
    public function escape($string) {
	return htmlspecialchars($this->mysqli->real_escape_string($string));
    }
    
    /**
     * Prints SQL error description - error message, error code and SQL query
     * that caused error.
     * 
     * @param String $sql
     */
    private function error($sql) {
	echo '
	    <table style="background: white; border: 3px solid red; font: .8em sans-serif; padding: 5px 10px;">
		<tr>
		    <td colspan="2" style="font-size: 1.4em; font-weight: bold;">SQL error occured!</td>
		</tr>
		'.(IS_LOCALHOST ? '
		    <tr>
			<td style="font-style: italic;">Error message: </td>
			<td>'.$this->mysqli->error.'</td>
		    </tr>
		    <tr>
			<td style="font-style: italic;">Error code: </td>
			<td>'.$this->mysqli->errno.'</td>
		    </tr>
		    <tr>
			<td style="font-style: italic;">SQL command: </td>
			<td>'.$sql.'</td>
		    </tr>
		' : '').'
	    </div>
	';
    }
        
}
