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

use Synopsy\Sql\Query;

/**
 * Class used for construction of custom SQL queries! (Not for daily use!)
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Sql extends Query {

    /**
     * Constructor.
     * 
     * @param String $sql
     * @return Sql
     */
    public function __Construct($sql) {
	parent::__Construct('empty');
	$this->sql = $sql;
	return $this;
    }

    
}