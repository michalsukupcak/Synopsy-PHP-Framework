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

namespace Synopsy\Compilers;

use ReflectionClass;
use Synopsy\Config\Config;
use Synopsy\Db\Database;
use Synopsy\Files\Files;
use Synopsy\Sql\Queries\Sql;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class EntitiesCompilerOld extends AbstractCompiler {
        
    const COL_RENAME = 'rename';
    const COL_TYPE = 'type';
    const COL_LENGTH = 'length';
    const COL_DEFAULT = 'default';
    const COL_ATTRIBUTES = 'attributes';
    const COL_NULL = 'null';
    const COL_INDEX = 'index';
    const COL_AUTO_INCREMENT = 'autoIncrement';
    
    private $datatypes = [
        // Numeric
        'TINYINT','SMALLINT','MEDIUMINT','INT','BIGINT',
        'DECIMAL','FLOAT','DOUBLE','REAL',
        'BIT','BOOLEAN','SERIAL',
        // Date and time
        'DATE','DATETIME','TIMESTAMP','TIME','YEAR',
        // String
        'CHAR','VARCHAR',
        'TINYTEXT','TEXT','MEDIUMTEXT','LONGTEXT',
        'BINARY','VARBINARY',
        'TINYBLOB','MEDIUMBLOB','BLOB','LONGBLOB',
        'ENUM','SET',
        // Spatial
        'GEOMETRY','POINT','LINESTRING','POLYGON','MULTIPOINT','MULTILINESTRING','MULTIPOLYGON','GEOMETRYCOLLECTION'
    ];
    
    /**
     * 
     */
    public function compile() {
        // Load files
        $entityFiles = array_merge_recursive(Files::getDirContent(APP.'entities/'),Files::getDirContent(SRC.'entities/'));
        // Compile files
        if ($this->filesChanged('entities',$entityFiles)) {
            $this->backupDatabase();
            $entityFileTimes = [];
            $dbTables = $this->getDbTables();
            foreach ($entityFiles as $entityFile) { // Read content of each source file and append it into destination file
                // File information
                $entity = $this->getFileName($entityFile);
                $entityClass = new ReflectionClass($entity);
                // Table data
                $tableDoc = $this->removeWhitespaces($entityClass->getProperty('table')->getDocComment());
                $entityTable = $this->getEntityTable($entityClass,$tableDoc,$entityFile);
                $entityEngine = $this->getEntityEngine($entityClass,$tableDoc);
                // Columns data
                $columnsDoc = $this->removeWhitespaces($entityClass->getProperty('columns')->getDocComment());
                $entityColumns = $this->getEntityColumns($entityClass,$columnsDoc);
                // Execute entity SQLs
                if (in_array($entityTable,$dbTables)) { // Table exists
                    $dbTables = array_diff($dbTables,[$entityTable]); // Delete table from array of existing tables
                    $dbEngine = $this->getDbEngine($entityTable);
                    $dbColumns = $this->getDbColumns($entityTable);
                    if ($entityEngine != $dbEngine) {
                        $this->alterTableEngine($entityTable,$entityEngine);
                    }
                    $previousColumn = null;
                    $entityFileContent = file_get_contents($entityFile);
                    foreach ($entityColumns as $entityColumnName => $entityColumnData) {
                        if (array_key_exists($entityColumnName,$dbColumns)) { // Existing column
                            $dbColumnData = $dbColumns[$entityColumnName];
                            if ($entityColumnData[self::COL_RENAME]) { // If columns is to be renamed, rename and change type and length
                                $newEntityColumnName = $entityColumnData[self::COL_RENAME];
                                $this->changeColumn($entityTable,$entityColumnName,$newEntityColumnName,$entityColumnData);
                                $entityFileContent = preg_replace("/@name $entityColumnName > $newEntityColumnName/","@name $newEntityColumnName",$entityFileContent);
                            } elseif ($this->isColumnDifferent($dbColumnData,$entityColumnData)) {
                                $this->modifyColumn($entityTable,$entityColumnName,$entityColumnData);
                            }
                            if ($dbColumnData[self::COL_INDEX] != $entityColumnData[self::COL_INDEX]) {
                                if ($dbColumnData[self::COL_INDEX] != null) {
                                    $this->dropIndex($entityTable,$entityColumnName);
                                }
                                if ($entityColumnData[self::COL_INDEX]) {
                                    $this->createIndex($entityTable,$entityColumnName,$entityColumnData[self::COL_INDEX]);
                                }
                            }
                            unset($dbColumns[$entityColumnName]); // Delete column from array of existing columns
                        } else { // New column
                            $this->addColumn($entityTable,$entityColumnName,$entityColumnData,$previousColumn);
                        }
                        $previousColumn = $entityColumnName;
                    }
                    $cols = '';
                    foreach ($entityColumns as $columnName => $columnData) {
                        $cols .= "'$columnName',";
                    }
                    $colsTrimmed = rtrim($cols,',');
                    $entityFileContent = preg_replace('/protected \$columns = \[(.){0,}\]/s','protected \$columns = ['.$colsTrimmed.']',$entityFileContent);
                    $entityFileHandler = fOpen($entityFile,'w');
                    fwrite($entityFileHandler,$entityFileContent);
                    fClose($entityFileHandler);   
                    $entityFileTimes[$entity] = $this->getFileTime($entityFile);
                    if (!empty($dbColumns)) { // Drop existing columns that don't have attribute representation
                        $this->dropColumns($entityTable,$dbColumns);
                    }
                } else { // New table
                    $this->createTable($entityTable,$entityEngine,$entityColumns);
                }
            }
            if (!empty($dbTables)) { // Drop existing tables that don't have entity representation
                $this->dropTables($dbTables);
            }
            $this->setCacheFile('entities',$entityFileTimes);
            $this->compilerExecuted();
        }
    }
    
    /* ---------------------------------------------------------------------- */
    /* Backup */
    
    /**
     * 
     */
    private function backupDatabase() {
        $backups = Files::getDirContent(SRC.'compilers/backup/');
        $c = count($backups);
        if ($c > 10) {
            for ($i = 0; $i < ($c-10); $i++) {
                unlink($backups[$i]);
            }
        }
        $databaseConfig = Config::get('database',true);
        $user = (string) $databaseConfig->user;
        $password = (string) $databaseConfig->password;
        $database = (string) $databaseConfig->database;
        $file = SRC.'compilers/backup/'.$database.'-export_'.date('Y-n-d_h-i-s').'.sql';
        shell_exec("mysqldump -u$user -p$password $database > $file");
    }
    
    /* ---------------------------------------------------------------------- */
    /* Database/table information retrieval and parsing */
    
    /**
     * Return an array containing names of tables in database.
     * 
     * @return Mixed
     */
    private function getDbTables() {
        $dbTables = [];
        $databaseConfig = Config::getDatabase();
        Database::executeQuery(new Sql("SHOW TABLES"));
        $ts = Database::fetchAll();
        foreach ($ts as $t) {
            $dbTables[] = $t['Tables_in_'.($databaseConfig['database'])];
        }
        return $dbTables;
    }
    
    /**
     * 
     * @param type $table
     * @return type
     */
    private function getDbEngine($table) {
        $sql = "SHOW TABLE STATUS WHERE Name = '$table'";
        if (Database::executeQuery(new Sql($sql))) {
            return Database::fetchOne()['Engine'];
        } else {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /**
     * 
     * @param type $table
     * @return type
     */
    private function getDbColumns($table) {
        $indexes = [];
        Database::executeQuery(new Sql("SHOW INDEX FROM $table"));
        $i = Database::fetchAll();
        if (is_array($i)) {
            foreach ($i as $index) {
                $x = null;
                if ($index['Key_name'] == 'PRIMARY') {
                    $key = 'PRIMARY';
                } elseif ($index['Index_type'] == 'FULLTEXT') {
                    $key = 'FULLTEXT';
                } elseif ($index['Non_unique'] == '0') {
                    $key = 'UNIQUE';
                } else {
                    $key = 'INDEX';
                }
                $indexes[$index['Column_name']] = $key;
            }
        }
        $columns = [];
        $sql = "DESCRIBE $table";
        if (Database::executeQuery(new Sql($sql))) {
            $description = Database::fetchAll();
            foreach ($description as $d) {  
                $column = [];
                $type = null;
                preg_match('/^('.strtolower(implode('|',$this->datatypes)).')(\([0-9]+\))?( )?(unsigned|unsigned zerofill)?$/',$d['Type'],$type);
                // Type
                $column[self::COL_TYPE] = strtoupper($type[1]);
                // Length
                if (isset($type[2])) {
                    $column[self::COL_LENGTH] = substr($type[2],1,-1);
                } else {
                    $column[self::COL_LENGTH] = null;
                }
                // Default
                if ($d['Default']) {
                    $column[self::COL_DEFAULT] = $d['Default'];
                } else {
                    $column[self::COL_DEFAULT] = null;
                }
                // Attributes
                if (isset($type[4])) {
                    switch ($type[4]) {
                        case 'unsigned':
                            $column[self::COL_ATTRIBUTES] = 'UNSIGNED';
                            break;
                        case 'unsigned zerofill':
                            $column[self::COL_ATTRIBUTES] = 'ZEROFILL';
                            break;
                        default:
                            $column[self::COL_ATTRIBUTES] = null;
                            break;
                        
                    }
                } else {
                    $column[self::COL_ATTRIBUTES] = null;
                }
                // Null
                if ($d['Null'] == 'YES') {
                    $column[self::COL_NULL] = 'NULL';
                } else {
                    $column[self::COL_NULL] = 'NOT NULL';
                }
                // Index
                if (array_key_exists($d['Field'],$indexes)) {
                    $column[self::COL_INDEX] = $indexes[$d['Field']];
                } else {
                    $column[self::COL_INDEX] = null;
                }
                // Auto increment
                if ($d['Extra'] == 'auto_increment') {
                    $column[self::COL_AUTO_INCREMENT] = 'YES';
                } else {
                    $column[self::COL_AUTO_INCREMENT] = null;
                }
                $columns[$d['Field']] = $column;
            }
            return $columns;
        } else {
            throw new CompilerException("Query failed: $sql");
        }
    }

    /**
     * 
     * @param type $class
     * @param type $doc
     * @param type $file
     * @return null
     * @throws CompilerException
     */
    private function getEntityTable($class,$doc,$file) {
        if (!preg_match('/@ENTITY/',$doc)) {
            throw new CompilerException("Property '\$table' for class '".$class->name."' is missing @ENTITY annotation!");
        }
        $table = null;
        preg_match('/@table ([a-zA-Z0-9\_]+)( > ([a-zA-Z0-9\_]+))?/',$doc,$table);
        if ($table[1] == null) {
            throw new CompilerException("Parameter @table for property '\$table' in class '".$class->name."' was not found!");
        }
        if (isset($table[3])) {
            $this->renameTable($table[1],$table[3],$file);
            return $table[3];
        } else {
            return $table[1];
        }
    }
    
    /**
     * 
     * @param type $class
     * @param type $doc
     * @return null
     * @throws CompilerException
     */
    private function getEntityEngine($class,$doc) {
        if (!preg_match('/@ENTITY/',$doc)) {
            throw new CompilerException("Property '\$table' for class '".$class->name."' is missing @ENTITY annotation!");
        }
        $table = null;
        preg_match('/@engine (InnoDB|MyISAM)/',$doc,$table);
        if ($table[1] == null) {
            throw new CompilerException("Parameter @engine for property '\$table' in class '".$class->name."' was not found!");
        }
        return $table[1];
    }
    
    /**
     * 
     * @param type $class
     * @param type $doc
     * @return null
     * @throws CompilerException
     */
    private function getEntityColumns($class,$doc) {
        $datatypeRegex = implode('|',$this->datatypes);
        $columns = [];
        $cols = null;
        preg_match_all('/@ATTRIBUTE\n([@a-zA-Z0-9\_\-\n >,]+?)@END/s',$doc,$cols);
        foreach ($cols[1] as $column) {   
            $name = null;
            $col = [];
            $n = null;
            preg_match('/@name ([a-zA-Z0-9\_]+)( > ([a-zA-Z0-9\_]+))?/',$column,$n);
            if (array_key_exists(1,$n)) {
                 $name = $n[1];
                 if (array_key_exists(3,$n)) {
                     $col[self::COL_RENAME] = $n[3];
                 } else {
                     $col[self::COL_RENAME] = null;
                 }
            } else {
                throw new CompilerException("Parameter @name for entity '$class->name' is required!");
            }
            $t = null;
            preg_match('/@type ('.$datatypeRegex.')/',$column,$t);
            if (array_key_exists(1,$t)) {
                $col[self::COL_TYPE] = $t[1];
            } else {
                throw new CompilerException("Parameter @type for entity '$class->name' is required!");
            }
            $l = null;
            preg_match('/@length ([0-9,]+)/',$column,$l);
            if (array_key_exists(1,$l)) {
                $col[self::COL_LENGTH] = $l[1];
            } else {
                $col[self::COL_LENGTH] = '';
            }
            $d = null;
            preg_match('/@default ([a-zA-Z0-9]+)/',$column,$d);
            if (array_key_exists(1,$d)) {
                $col[self::COL_DEFAULT] = $d[1];
            } else {
                $col[self::COL_DEFAULT] = '';
            }
            $a = null;
            preg_match('/@attributes (UNSIGNED|ZEROFILL)/',$column,$a);
            if (array_key_exists(1,$a)) {
                $col[self::COL_ATTRIBUTES] = $a[1];
            } else {
                $col[self::COL_ATTRIBUTES] = '';
            }
            $nl = null;
            preg_match('/@null (NOT NULL|NULL)/',$column,$nl);
            if (array_key_exists(1,$nl)) {
                $col[self::COL_NULL] = ($nl[1] != 'null' ? $nl[1] : 'NOT NULL');
            } else {
                $col[self::COL_NULL] = 'NOT NULL';
            }
            $i = null;
            preg_match('/@index (PRIMARY|UNIQUE|INDEX|FULLTEXT)/',$column,$i);
            if (array_key_exists(1,$i)) {
                $col[self::COL_INDEX] = $i[1];
            } else {
                $col[self::COL_INDEX] = null;
            }
            $ai = null;
            preg_match('/@auto_increment (YES|NO)/',$column,$ai);
            if (array_key_exists(1,$ai)) {
                $col[self::COL_AUTO_INCREMENT] = $ai[1];
            } else {
                $col[self::COL_AUTO_INCREMENT] = '';
            }
            $columns[$name] = $col;
        }
        return $columns;
    }
    
    /**
     * 
     * @param type $column
     * @return string
     */
    private function getColumnSql($name,$data) {
        $return = " $name $data[type]";
        if ($data[self::COL_LENGTH]) {
            $return .= "($data[length])";
        }
        if ($data[self::COL_DEFAULT]) {
            $return .= "DEFAULT $data[default]";
        }
        if ($data[self::COL_ATTRIBUTES]) {
            $return .= " $data[attributes]";
        }
        if ($data[self::COL_NULL]) {
            $return .= " $data[null]";
        } else {
            $return .= ' NOT NULL';
        }
        if ($data[self::COL_AUTO_INCREMENT]) {
            $return .= ' AUTO_INCREMENT';
        }
        dump($return);
        return $return;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Table operations */
    
    /**
     * 
     * @param type $table
     * @param type $engine
     * @param type $columns
     * @throws CompilerException
     */
    private function createTable($table,$engine,$columns) {
        $primary = null;
        $unique = [];
        $key = [];
        $fulltext = [];
        $sql = "CREATE TABLE $table (";
        foreach ($columns as $name => $data) {
            $sql .= $this->getColumnSql($name,$data).', ';
            if ($data[self::COL_INDEX]) {
                switch ($data[self::COL_INDEX]) {
                    case 'PRIMARY':
                        $primary = $name;
                        break;
                    case 'UNIQUE':
                        $unique[] = $name;
                        break;
                    case 'INDEX':
                        $key[] = $name;
                        break;
                    case 'FULLTEXT':
                        $fulltext[] = $name;
                        break;
                }
            }
        }
        $sql = rtrim($sql,', ');
        if ($primary) {
            $sql .= ", PRIMARY KEY ($primary)";
        }
        if (!empty($unique)) {
            foreach ($unique as $i) {
                $sql .= ", UNIQUE KEY $i ($i)";
            }
        }
        if (!empty($key)) {
            foreach ($key as $i) {
                $sql .= ", KEY $i ($i)";
            }
        }
        if (!empty($fulltext)) {
            foreach ($fulltext as $i) {
                $sql .= ", FULLTEXT KEY $i ($i)";
            }
        }
        $sql .= ") ENGINE=$engine DEFAULT CHARSET=utf8;";
        if (!Database::executeQuery(new Sql($sql))) {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /**
     * Alters table engine.
     * 
     * @param String $table
     * @param String $engine
     * @throws CompilerException
     */
    private function alterTableEngine($table,$engine) {
        $sql = "ALTER TABLE $table ENGINE=$engine";
        if (!Database::executeQuery(new Sql($sql))) {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /**
     * 
     * @param type $oldName
     * @param type $newName
     * @param type $file
     * @throws CompilerException
     */
    private function renameTable($oldName,$newName,$file) {
        $sql = "RENAME TABLE $oldName TO $newName";
        if (Database::executeQuery(new Sql($sql))) {
            $f1 = file_get_contents($file);
            $f2 = preg_replace("/@table $oldName > $newName/","@table $newName",$f1);
            $f3 = preg_replace('/protected \$table = \''.$oldName.'\';/','protected $table = \''.$newName.'\';',$f2);
            $f4 = preg_replace('/public static \$dbTable = \''.$oldName.'\';/','public static $dbTable = \''.$newName.'\';',$f3);
            $fileHandler = fOpen($file,'w');
            fWrite($fileHandler,$f4);
            fClose($fileHandler);
        } else {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /**
     * 
     * @param type $tables
     */
    private function dropTables($tables) {
        foreach ($tables as $table) {
            $sql = "DROP TABLE $table";
            if (!Database::executeQuery(new Sql($sql))) {
                throw new CompilerException("Query failed: $sql");
            }
        }
    }
    
    /* ---------------------------------------------------------------------- */
    /* Column operations */
    
    /**
     * 
     * 
     * @param type $table
     * @param type $name
     * @param type $data
     * @param type $previous
     * @throws CompilerException
     */
    private function addColumn($table,$name,$data,$previous) {
        $columnSql = $this->getColumnSql($name,$data);
        if ($previous != null) {
            $columnSql .= " AFTER $previous";
        }
        $sql = "ALTER TABLE $table ADD $columnSql";
        if (!Database::executeQuery(new Sql($sql))) {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /**
     * 
     * @param type $table
     * @param type $oldColumnName
     * @param type $newColumnName
     * @param type $type
     * @param type $length
     * @throws CompilerException
     */
    private function changeColumn($table,$oldColumnName,$newColumnName,$newColumnData) {
        $columnSql = $this->getColumnSql($newColumnName,$newColumnData);
        $sql = "ALTER TABLE $table CHANGE $oldColumnName $columnSql";
        if (!Database::executeQuery(new Sql($sql))) {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /**
     * 
     * @param type $table
     * @param type $columnName
     * @param type $columnData
     * @throws CompilerException
     */
    private function modifyColumn($table,$columnName,$columnData) {
        $columnSql = $this->getColumnSql($columnName,$columnData);
        $sql = "ALTER TABLE $table MODIFY $columnSql";
        if (!Database::executeQuery(new Sql($sql))) {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /**
     * 
     * @param type $table
     * @param type $columns
     * @throws CompilerException
     */
    private function dropColumns($table,$columns) {
        foreach ($columns as $column => $data) {
            $sql = "ALTER TABLE $table DROP $column";
            if (!Database::executeQuery(new Sql($sql))) {
                throw new CompilerException("Query failed: $sql");
            }
        }
    }
    
    /* ---------------------------------------------------------------------- */
    /* Index operations */
    
    /**
     * 
     * @param type $table
     * @param type $column
     * @param type $index
     * @throws CompilerException
     */
    private function createIndex($table,$column,$index) {
        if ($index == 'INDEX') {
            $i = '';
        } else {
            $i = $index;
        }
        $sql = "CREATE $i INDEX $column ON $table ($column)";
        if (!Database::executeQuery(new Sql($sql))) {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /**
     * 
     * @param type $table
     * @param type $column
     * @throws CompilerException
     */
    private function dropIndex($table,$column) {
        $sql = "DROP INDEX $column ON $table";
        if (!Database::executeQuery(new Sql($sql))) {
            throw new CompilerException("Query failed: $sql");
        }
    }
    
    /* ---------------------------------------------------------------------- */
    /* Auxiliary functions */
        
    /**
     * 
     * @param type $dbColumnData
     * @param type $entityColumnData
     * @return type
     */
    private function isColumnDifferent($dbColumnData,$entityColumnData) {
        return (
            $dbColumnData[self::COL_TYPE] != $entityColumnData[self::COL_TYPE]
            ||
            $dbColumnData[self::COL_LENGTH] != $entityColumnData[self::COL_LENGTH]
            ||
            $dbColumnData[self::COL_DEFAULT] != $entityColumnData[self::COL_DEFAULT]
            ||
            $dbColumnData[self::COL_ATTRIBUTES] != $entityColumnData[self::COL_ATTRIBUTES]
            ||
            $dbColumnData[self::COL_NULL] != $entityColumnData[self::COL_NULL]
            ||
            $dbColumnData[self::COL_AUTO_INCREMENT] != $entityColumnData[self::COL_AUTO_INCREMENT]
        );
    }    
    
}
