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
 * Sample annotation for a table:
 * @ENTITY
 * @Table users
 * @Engine InnoDB
 * 
 * Annotation description:
 * @ENTITY
 * @Table   - name of the table, allowed characters: a-z, A-Z, 0-9 and _ (underscore)
 * @Engine  - engine of the table, allowed values: MyISAM and InnoDB
 * 
 * --------------------------------------------------------------------------- *
 * 
 * Sample annotation for a column:
 * @ATTRIBUTE
 * @Field id
 * @Type int(10)
 * @Null NO
 * @Default NULL
 * @Key PRIMARY
 * @Extra auto_increment
 * @Comment Primary key for the table
 * @END
 *  
 * Annotation description:
 * @Field      - name of the column
 * @Type       - datatype of column, must be a valid mysql database datatype with length value
 * @Null       - defines if column can have NULL value, allowed values: YES, NO
 * @Default    - default value of column, can be either NULL or a value
 * @Key        - type of key for current column, allowed types: PRIMARY, UNIQUE, INDEX, FULLTEXT
 * @Extra      - extra attributes (eg.: auto_increment)
 * @Comment    - comment for column
 * 
 * --------------------------------------------------------------------------- *
 * 
 * KNOWN ISSUE:
 * Compiler doesn't play well with '@Extra auto_increment' annotation, causing
 * SQL errors in special cases when changing this annotation from one column
 * to another. There only possible workaround at this point:
 * - When making changes to annotations that affect auto_increment column,
 *   remove auto_increment annotation at the first step and add it back after
 *   reloading the compiler for first time.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class EntitiesCompiler extends AbstractCompiler {
    
    /**
     * 
     */
    const DEBUG = true;
        
    /**
     * 
     */
    public function compile() {
        $entityFiles = array_merge_recursive(Files::getDirContent(APP.'entities/'),Files::getDirContent(SRC.'entities/')); // Load files
        if ($this->filesChanged('entities',$entityFiles)) { // Compile files
        
            // Store new file times for entity classes for future comparison
            $entityFileTimes = [];
            
            $this->backupDatabase();
            $entityClasses = $this->getEntityClasses($entityFiles); // Get ReflectionClass instances for each entity class
            $entityAnnotations = $this->getEntityAnnotations($entityClasses); // Get Entity annotations from ReflectionClasses of entities
            
            // Fix empty $table and $dbTable attributes
            $this->fixEntityAttributes($entityClasses,$entityAnnotations);
            
            // Rename tables from annotations
            $this->renameTables($entityAnnotations);
                       
            // Work with tables
            $entityTables = $this->getEntityTables($entityAnnotations); // Get tables from entity annotations
            $dbTables = $this->getDbTables(); // Get tables from database
            $deleteTables = array_diff($dbTables,$entityTables); // Get tables to delete (tables in database but not in entity files)            
            $newTables = array_diff($entityTables,$dbTables); // Get new tables (tables in entity files but not in database)
            $existingTables = array_diff($entityTables,$deleteTables); // Get tables that exist in database after delete
            
            // Delete old tables from database
            $this->deleteTables($deleteTables);
            
            // Create new tables from entities
            $this->createTables($newTables);
            
            // Fix table engines
            $this->fixTableEngines($existingTables,$entityAnnotations);
            
            // Go through all existing entities
            foreach ($entityClasses as $entityClass) {
                
                // Work with table columns
                $entityColumns = $this->getEntityColumns($entityClass); // Get entity columns from annotations
                $entityColumnNames = array_keys($entityColumns); // Get entity column names
                $dbColumns = $this->getDbColumns($entityClass); // Get table columns from database
                $dbColumnNames = array_keys($dbColumns); // Get table columns names
                $deleteColumns = array_diff($dbColumnNames,$entityColumnNames); // Get columns to delete
                $newColumns = array_diff($entityColumnNames,$dbColumnNames); // Get columns to create
                
                // Delete old columns
                $this->deleteColumns($entityClass,$deleteColumns);
                
                // Create new columns
                $this->createColumns($entityClass,$newColumns,$entityColumns);
                                
                // Add newly created columns do $dbColumnNames and $dbColumns variables
                foreach ($newColumns as $newColumn) {
                    $dbColumnNames[] = $newColumn;
                    $dbColumns[$newColumn] = $entityColumns[$newColumn];
                }
                                                
                // Go through all existing columns and compare them
                foreach ($entityColumns as $columnName => $entityColumnData) {
                    $dbColumnData = $dbColumns[$columnName];
                    
                    // Fix column definitions
                    $this->fixColumn($entityClass,$entityColumnData,$dbColumnData);
                    
                    // Rename column
                    $this->renameColumn($entityClass,$entityColumnData);
                    
                }
                
                // Work with indices
                $entityIndexes = $this->getEntityIndexes($entityColumns);
                $entityIndexNames = array_keys($entityIndexes);
                $dbIndexes = $this->getDbIndexes($entityClass);
                $dbIndexNames = array_keys($dbIndexes);
                $deleteIndexes = array_diff($dbIndexNames,$entityIndexNames);
                $newIndexes = array_diff($entityIndexNames,$dbIndexNames);
                
                // Delete old indexes
                $this->deleteIndexes($entityClass,$deleteIndexes,$dbIndexes);
                
                // Create new indexes
                $this->createIndexes($entityClass,$newIndexes,$entityIndexes);
                                
                // Add newly created indexes do $dbIndexNames and $dbIndexes variables
                foreach ($newIndexes as $newIndex) {
                    $dbIndexNames[] = $newIndex;
                    $dbIndexes[$newIndex] = $entityIndexes[$newIndex];
                }
                
                // Go through all existing indexes
                foreach ($entityIndexes as $indexName => $entityIndexData) {
                    $dbIndexData = $dbIndexes[$indexName];
                    
                    // Fix indexes
                    $this->fixIndex($entityClass,$entityIndexData,$dbIndexData);
                    
                }
                
                // Fix protected $columns array values based on annotations in entity file
                $this->fixEntityColumns($entityClass,$entityColumnNames);
                
                // Store file modification tim
                $entityFileTimes[$entityClass->name] = $this->getFileTime($entityClass->getFileName());
                
            }
            
            // Post-compilation file caching and output
            $this->setCacheFile('entities',$entityFileTimes);
            $this->compilerExecuted();
        }
    }
    
    /* ---------------------------------------------------------------------- */
    /* Backup */
    
    /**
     * Creates SQL dump backup and deletes backups older than 10 compiler
     * executions.
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
    /* Entities and tables */
    
    /**
     * Returns ReflectionClass instace for each entity in entity files.
     * 
     * @param Array $entityFiles
     * @return Array
     */
    private function getEntityClasses($entityFiles) {
        $entityClasses = [];
        
        // List through entity files
        foreach ($entityFiles as $entityFile) {
            $e = explode('/',$entityFile);
            $entityClassName = explode('.',array_pop($e))[0];
            $entityClasses[] = new ReflectionClass($entityClassName);
        }
        return $entityClasses;
    }
    
    /**
     * Returns names of tables based on annotations of protected $table
     * attribute in entity classes.
     * 
     * @param Array $entityClasses
     * @return Array
     */
    private function getEntityAnnotations($entityClasses) {
        $entityAnnotations = [];
        
        // List through entity classes
        foreach ($entityClasses as $entityClass) {
            $docComment = $entityClass->getProperty('table')->getDocComment(); // Get docComments for $table attribute
            if ($docComment) { // Continue only if docComments are present
                
                // Table parameters
                $tableName = null;
                $tableRename = null;
                $tableEngine = null;
                
                if (!preg_match('/@ENTITY/',$docComment)) {
                    throw new CompilerException("Annotation @ENTITY for entity '$entityClass->name' is missing!");
                }
                
                // Get Table annotation
                $t = [];
                preg_match('/@Table ([a-zA-Z0-9\_]+)( > ([a-zA-Z0-9\_]+))?/',$docComment,$t);
                if (!isset($t[1])) {
                    throw new CompilerException("Annotation @Table for attribute \$table in entity '$entityClass->name' is missing or is in invalid format! Allowed format: ([a-zA-Z0-9\_]+)( > ([a-zA-Z0-9\_]+))");
                } else {
                    $tableName = $t[1];
                    if (isset($t[3])) {
                        $tableRename = $t[3];
                    }
                }
                
                // Get @Engine annotation
                $e = [];
                preg_match('/@Engine (InnoDB|MyISAM)/',$docComment,$e);
                if (!isset($e[1])) {
                    throw new CompilerException("Annotation @Engine for attribute \$table in entity '$entityClass->name' is missing or has invalid value! Allowed values: InnoDB, MyISAM.");
                } else {
                    $tableEngine = $e[1];
                }
                
                // Save annotation data to entity name
                $entityAnnotations[$entityClass->name] = [
                    'tableName' => $tableName,
                    'tableRename' => $tableRename,
                    'tableEngine' => $tableEngine
                ];
                
            } else {
                throw new CompilerException("Attribute \$table in entity '$entityClass->name' has no docComment annotations defined!");
            }
        }
        
        return $entityAnnotations;
    }
    
    /**
     * Checks values for protected $table and public statc $dbTable attributes
     * and fixes them to comply with annotations if conflicts arise.
     * 
     * @param Array $entityClasses
     * @param Array $entityAnnotations
     */
    private function fixEntityAttributes($entityClasses,$entityAnnotations) {
        foreach ($entityClasses as $entityClass) {
            
            // Get protected $table property
            $tableProperty = $entityClass->getProperty('table');
            $tableProperty->setAccessible(true);
            $table = $tableProperty->getValue(new $entityClass->name());
            $tableProperty->setAccessible(false);
            
            // Get public static $dbTable property
            $className = $entityClass->name;
            $dbTable = $className::$dbTable;
                        
            // Get @Table annotation
            $annotation = $entityAnnotations[$entityClass->name]['tableName'];
                        
            // Compare properties and fix names if conflicts arise
            if ($annotation != $table || $annotation != $dbTable) {
                $f1 = file_get_contents($entityClass->getFileName());
                $f2 = preg_replace('/protected \$table = \''.$table.'\';/',"protected \$table = '$annotation';",$f1);
                $f3 = preg_replace('/public static \$dbTable = \''.$dbTable.'\';/',"public static \$dbTable = '$annotation';",$f2);
                $fileHandler = fOpen($entityClass->getFileName(),'w');
                fWrite($fileHandler,$f3);
                fClose($fileHandler);
            }
            
        }
    }
    
    /**
     * Renames entity attributes and database table to new names supplied in
     * $entityAnnotations parameter.
     * 
     * @param Array $entityAnnotations
     * @throws CompilerException
     */
    private function renameTables($entityAnnotations) {
        foreach ($entityAnnotations as $entityName => $entityAnnotation) {
            if ($entityAnnotation['tableRename']) { // Continue only for tables with renames
                
                // Sql rename only for tables that already exist
                if ($this->tableExists($entityAnnotation['tableName'])) {
                    if ($this->tableExists($entityAnnotation['tableRename'])) { // Check if new name (TableRename) is free (if taken, throw exception
                        throw new CompilerException("New name '$entityAnnotation[tableRename]' for table '$entityAnnotation[tableName]' in entity '$entityName' is alredy taken by a different table, please choose a different table name!");
                    }
                    Database::executeQuery(new Sql("RENAME TABLE $entityAnnotation[tableName] TO $entityAnnotation[tableRename]")); // Rename table
                }
                
                // Entity file rename
                $entityClass = new ReflectionClass($entityName);
                $f1 = file_get_contents($entityClass->getFileName());
                $f2 = preg_replace('/@Table '.$entityAnnotation['tableName'].' > '.$entityAnnotation['tableRename'].'/',"@Table $entityAnnotation[tableRename]",$f1);
                $f3 = preg_replace('/protected \$table = \''.$entityAnnotation['tableName'].'\';/',"protected \$table = '$entityAnnotation[tableRename]';",$f2);
                $f4 = preg_replace('/public static \$dbTable = \''.$entityAnnotation['tableName'].'\';/',"public static \$dbTable = '$entityAnnotation[tableRename]';",$f3);
                $fileHandler = fOpen($entityClass->getFileName(),'w');
                fWrite($fileHandler,$f4);
                fClose($fileHandler);
                
            }
        }
    }
    
    /**
     * Returns array list of table names from entity files.
     * 
     * @param Array $entityAnnotations
     * @return Array
     */
    private function getEntityTables($entityAnnotations) {
        $entityTables = [];
        foreach ($entityAnnotations as $entityAnnotation) {
            $entityTables[] = $entityAnnotation['tableName'];
        }
        return $entityTables;
    }
    
    /**
     * Return array list of table names from database.
     * 
     * @return Array
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
     * Drop tables from database.
     * 
     * @param Array $tableNames
     */
    private function deleteTables($tableNames) {
        foreach ($tableNames as $tableName) {
            Database::executeQuery(new Sql("DROP TABLE IF EXISTS $tableName"));
        }
    }
    
    /**
     * Create new (almost) empty tables in database.
     * 
     * @param Array $tableNames
     */
    private function createTables($tableNames) {
        foreach ($tableNames as $tableName) {
            Database::executeQuery(new Sql("CREATE TABLE IF NOT EXISTS $tableName (id INT(10) NOT NULL) DEFAULT CHARSET=utf8"));
        }
    }
    
    /**
     * Compares table engines of tables in database with engines from entity
     * annotations and fixes database table engines if there is a conflict.
     * 
     * @param Array $existingTables
     * @param Array $entityAnnotations
     */
    private function fixTableEngines($existingTables,$entityAnnotations) {
        $annotationEngines = [];
        foreach ($entityAnnotations as $entityAnnotation) {
            $annotationEngines[$entityAnnotation['tableName']] = $entityAnnotation['tableEngine'];
        }
        foreach ($existingTables as $existingTable) {
            Database::executeQuery(new Sql("SHOW TABLE STATUS WHERE name = '$existingTable'"));
            $engine = Database::fetchOne()['Engine'];
            if ($engine != $annotationEngines[$existingTable]) {
                Database::executeQuery(new Sql("ALTER TABLE $existingTable ENGINE = $annotationEngines[$existingTable]"));
            }
        }
    }
        
    /**
     * Retrieves all column attributes from entity docComment annotations.
     * 
     * @param ReflectionClass $entityClass
     * @return Array
     * @throws CompilerException
     */
    private function getEntityColumns($entityClass) {
        $columns = [];
        $docComment = $entityClass->getProperty('columns')->getDocComment();
        $attrs = null;
        $hasAutoincrement = false;
        $hasPrimaryKey = false;

        // Match all attributes
        preg_match_all('/@ATTRIBUTE(.+?)@END/s',$docComment,$attrs);
        if (empty($attrs[1])) {
            throw new CompilerException("Attribute \$columns in entity '$entityClass->name' has no docComment annotations defined!");
        }
        
        // Attribute counter
        $i = 1;
        
        // List through all attributes
        foreach ($attrs[1] as $attribute) {
                        
            // Field
            // * REQUIRED
            $f = [];
            preg_match('/@Field ([a-zA-Z0-9\_]+)( > ([a-zA-Z0-9\_]+))?/',$attribute,$f);
            if (!isset($f[1])) {
                throw new CompilerException("Annotation @Field is missing for attribute #$i in entity '$entityClass->name'!");
            }
            $field = $f[1];
            if (isset($f[3])) {
                $rename = $f[3];
            } else {
                $rename = null;
            }
            if (array_key_exists($field,$columns)) { // Check if column name is free (if not, throw exception)
                throw new CompilerException("Column name '$field' for attribute #$i in entity '$entityClass->name' already exists, use different name!");
            }
                        
            // Type
            // * REQUIRED
            $t = [];
            preg_match('/@Type (.+)/',$attribute,$t);
            if (!isset($t[1])) {
                throw new CompilerException("Attribute '$field' is missing annotation '@Type' in entity '$entityClass->name'!");
            }
            $type = trim($t[1]);
            
            // Null
            // * REQUIRED
            $n = [];
            preg_match('/@Null (YES|NO)/',$attribute,$n);
            if (!isset($n[1])) {
                throw new CompilerException("Attribute '$field' is missing annotation '@Null' in entity '$entityClass->name'!");
            }
            $null = trim($n[1]);
            
            // Default
            $d = [];
            preg_match('/@Default (.+)/',$attribute,$d);
            if (isset($d[1])) {
                $default = $d[1];
            } else {
                $default = null;
            }
            
            // Key
            $k = [];
            preg_match('/@Key (PRIMARY|UNIQUE|INDEX|FULLTEXT)/',$attribute,$k);
            if (isset($k[1])) {
                $key = $k[1];
                if ($key == 'PRIMARY') {
                    if ($hasPrimaryKey) {
                        throw new CompilerException("Entity '$entityClass->name' has multiple '@Key PRIMARY' annnotations! Only one primary key is allowed!");
                    }
                    $hasPrimaryKey = true;
                }
            } else {
                $key = '';
            }
                        
            // Extra
            $e = [];
            preg_match('/@Extra (.+)/',$attribute,$e);
            if (isset($e[1])) {
                $extra = $e[1];
                if ($extra == 'auto_increment') {
                    if ($hasAutoincrement) {
                        throw new CompilerException("Entity '$entityClass->name' has multiple '@Extra auto_increment' annotations! Only one auto_increment is allowed!");
                    }
                    $hasAutoincrement = true;
                    if ($key != 'PRIMARY' && $key != 'UNIQUE' && $key != 'INDEX') {
                        throw new CompilerException("Attribute '$field' has annotation '@Extra auto_increment' but no @Key (PRIMARY, UNIQUE or INDEX) defined! You need to define a key if you wan't to use auto_increment!");
                    }
                    
                }
            } else {
                $extra = '';
            }
            
            // Comment
            $c = [];
            preg_match('/@Comment (.+)/',$attribute,$c);
            if (isset($c[1])) {
                $comment = trim($c[1]);
            } else {
                $comment = '';
            }
                        
            // Set column data
            $columns[$field] = [
                'Field' => $field,
                'FieldRename' => $rename,
                'Type' => $type,
                'Null' => $null,
                'Default' => $default,
                'Key' => $key,
                'Extra' => $extra,
                'Comment' => $comment
            ];
         
            $i++;   
        }
        
        return $columns;
    }
    
    /**
     * Get list of all columns in table using SHOW FULL COLUMNS SQL query.
     * 
     * @param ReflectionClass $entityClass
     * @return Array
     */
    private function getDbColumns($entityClass) {
        $entityName = $entityClass->name;
        $table = $entityName::$dbTable;
        $columns = [];
        Database::executeQuery(new Sql("SHOW FULL COLUMNS FROM $table"));
        $cols = Database::fetchAll();
        foreach ($cols as $col) {
            $columns[$col['Field']] = $col;
        }
        return $columns;
    }
    
    /**
     * Deletes array of columns from database table.
     * 
     * @param ReflectionClass $entityClass
     * @param Array $columns
     */
    private function deleteColumns($entityClass,$columns) {
        $entityName = $entityClass->name;
        $table = $entityName::$dbTable;
        foreach ($columns as $column) {
            Database::executeQuery(new Sql("ALTER TABLE $table DROP $column"));
        }
    }
    
    /**
     * Creates new columns into database table base on data from entity columns
     * annotation
     * 
     * @param ReflectionClass $entityClass
     * @param Array $columns
     * @param Array $entityColumns
     */
    private function createColumns($entityClass,$columns,$entityColumns) {
        $entityName = $entityClass->name;
        $table = $entityName::$dbTable;
        foreach ($columns as $column) {
            // Get index of previous column in entityColumns array (used to decide after which column should the new one be placed)
            $entityColumnKeys = array_keys($entityColumns);
            $index = count($entityColumnKeys) - 1;
            foreach ($entityColumnKeys as $i => $entityColumnKey) {
                if ($entityColumnKey == $column) {
                    $index = $i - 1;
                    break;
                }
            }
            // If index is not negative, use AFTER syntax to define position, otherwise append the the beginning of the table
            if ($index >= 0) {
                $position = " AFTER $entityColumnKeys[$index]";
            } else {
                $position = ' FIRST';
            }            
            // Check if column is defined as primary key
            if ($entityColumns[$column]['Key'] == 'PRIMARY') {
                $primary = true;
            } else {
                $primary = false;
            }
            // Create SQL syntax from column data
            $sql = $this->getColumnSql($entityColumns[$column],null,$primary);
            // Add new column
            Database::executeQuery(new Sql("ALTER TABLE $table ADD $sql $position"));
        }
    }
    
    /**
     * Fixes column byt checking data from entity annotation and comparing them
     * with data from database column table. If there is a mismatch in data,
     * modify column to comply with entity annotation definition.
     * 
     * @param ReflectionClass $entityClass
     * @param array $entityColumn
     * @param array $dbColumn
     */
    private function fixColumn($entityClass,$entityColumn,$dbColumn) {
        $entityName = $entityClass->name;
        $table = $entityName::$dbTable;
        // Compare all attributes of column and decide if it needs to be modified
        if (
            $entityColumn['Type'] != $dbColumn['Type']
            ||
            $entityColumn['Null'] != $dbColumn['Null']
            ||
            $entityColumn['Default'] != $dbColumn['Default']
            ||
            $entityColumn['Extra'] != $dbColumn['Extra']
            ||
            $entityColumn['Comment'] != $dbColumn['Comment']
        ) {
            // Check if column is defined as primary key
            if ($entityColumn['Key'] == 'PRIMARY') {
                // Check if table has already a defined primary key
                Database::executeQuery(new Sql("SHOW INDEXES FROM $table WHERE Key_name = 'PRIMARY'"));
                // Set primary to true only if table has no primary keys defined at this point
                $primary = !Database::changed();
            } else {
                $primary = false;
            }
            // If column has been modified, alter table with new column definition
            $sql = $this->getColumnSql($entityColumn,null,$primary);
            Database::executeQuery(new Sql("ALTER TABLE $table MODIFY $sql"));
        }        
    }
    
    /**
     * Renames column to new name, specified in parameter FieldRename from
     * entity column annotations.
     * 
     * If rename in database has been succesful, write changed name into entity 
     * class file.
     * 
     * @param ReflectionClass $entityClass
     * @param array $entityColumn
     */
    private function renameColumn($entityClass,$entityColumn) {
        $entityName = $entityClass->name;
        $table = $entityName::$dbTable;
        if ($entityColumn['FieldRename']) {
            $sql = $this->getColumnSql($entityColumn,$entityColumn['FieldRename']);
            if (Database::executeQuery(new Sql("ALTER TABLE $table CHANGE $entityColumn[Field] $sql"))) {
                $f1 = file_get_contents($entityClass->getFileName());
                $f2 = preg_replace("/@Field $entityColumn[Field] > $entityColumn[FieldRename]/","@Field $entityColumn[FieldRename]",$f1);
                $fileHandler = fOpen($entityClass->getFileName(),'w');
                fWrite($fileHandler,$f2);
                fClose($fileHandler);
            }
        }
    }
    
    /**
     * Writes current column names to the the protected $columns attribute of
     * entity class file.
     * 
     * @param ReflectionClass $entityClass
     * @param array $entityColumns
     */
    private function fixEntityColumns($entityClass,$entityColumns) {
        $c = '';
        foreach ($entityColumns as $column) {
            $c .= "'$column',";
        }
        $cols = rtrim($c,',');
        $f1 = file_get_contents($entityClass->getFileName());
        $f2 = preg_replace('/protected \$columns = \[(.*)\];/',"protected \$columns = [$cols];",$f1);
        $fileHandler = fOpen($entityClass->getFileName(),'w');
        fWrite($fileHandler,$f2);
        fClose($fileHandler);   
    }
    
    /**
     * Retrieves list of indexes from entity columns and converts them to data
     * comparable with index data from database.
     * 
     * @param array $entityColumns
     * @return array
     */
    private function getEntityIndexes($entityColumns) {
        $entityIndexes = [];
        foreach ($entityColumns as $entityColumn) {
            if ($entityColumn['Key'] != '') { // Only run for columns that have defined @Key annotation
                if ($entityColumn['FieldRename']) {
                    $indexName = $entityColumn['FieldRename'];
                } else {
                    $indexName = $entityColumn['Field'];
                }
                // Assign key attributes according to key type
                switch ($entityColumn['Key']) {
                    case 'PRIMARY':
                        $keyName = 'PRIMARY';
                        $nonUnique = 0;
                        $indexType = 'BTREE';
                        break;
                    case 'UNIQUE':
                        $keyName = $indexName;
                        $nonUnique = 0;
                        $indexType = 'BTREE';
                        break;
                    case 'INDEX':
                        $keyName = $indexName;
                        $nonUnique = 1;
                        $indexType = 'BTREE';
                        break;
                    case 'FULLTEXT':
                        $keyName = $indexName;
                        $nonUnique = 1;
                        $indexType = 'FULLTEXT';
                        break;
                }
                // Save key attributes
                $entityIndexes[$indexName] = [
                    'KEYTYPE' => $entityColumn['Key'],
                    'Non_unique' => $nonUnique,
                    'Key_name' => $keyName,
                    'Column_name' => $entityColumn['Field'],
                    'Index_type' => $indexType
                ];
            }
        }
        return $entityIndexes;
    }
    
    /**
     * Retrieves list of all indexes for given database table.
     * 
     * @param ReflectionClass $entityClass
     * @return array
     */
    private function getDbIndexes($entityClass) {
        $dbIndexes = [];
        $entityName = $entityClass->name;
        $table = $entityName::$dbTable;
        Database::executeQuery(new Sql("SHOW INDEXES FROM $table"));
        $indexes = Database::fetchAll();
        foreach ($indexes as $indexData) {
            if ($indexData['Key_name'] == 'PRIMARY') {
                $keytype = 'PRIMARY';
            } elseif ($indexData['Non_unique'] == '0') {
                $keytype = 'UNIQUE';
            } elseif ($indexData['Index_type'] == 'FULLTEXT') {
                $keytype = 'FULLTEXT';
            } else {
                $keytype = 'INDEX';
            }
            $indexData['KEYTYPE'] = $keytype;
            $dbIndexes[$indexData['Column_name']] = $indexData;
        }
        return $dbIndexes;
    }
    
    /**
     * 
     * @param type $entityClass
     * @param type $indexNames
     * @param type $dbIndexes
     */
    private function deleteIndexes($entityClass,$indexNames,$dbIndexes) {
        $entityName = $entityClass->name;
        $table = $entityName::$dbTable;
        foreach ($indexNames as $indexName) {
            $this->deleteIndex($table,$indexName,$dbIndexes[$indexName]);
        }
    }
        
    /**
     * Deletes index from database.
     * 
     * @param string $table
     * @param string $indexName
     * @param array $indexData
     */
    private function deleteIndex($table,$indexName,$indexData) {
        if ($indexData['KEYTYPE'] == 'PRIMARY') {
            $sql = "ALTER TABLE $table DROP PRIMARY KEY";
        } else {
            $sql = "DROP INDEX $indexName ON $table";
        }
        Database::executeQuery(new Sql($sql));
    }
    
    /**
     * 
     * @param type $entityClass
     * @param type $indexNames
     * @param type $entityIndexes
     */
    private function createIndexes($entityClass,$indexNames,$entityIndexes) {
        $entityName = $entityClass->name;
        $table = $entityName::$dbTable;
        foreach ($indexNames as $indexName) {
            $this->createIndex($table,$indexName,$entityIndexes[$indexName]);
        }
    }
        
    /**
     * 
     * @param type $table
     * @param type $indexName
     * @param type $indexData
     */
    private function createIndex($table,$indexName,$indexData) {
        switch ($indexData['KEYTYPE']) {
            case 'PRIMARY':
                $sql = "ALTER TABLE $table ADD PRIMARY KEY ($indexName)";
                break;
            case 'UNIQUE':
                $sql = "CREATE UNIQUE INDEX $indexName ON $table ($indexName)";
                break;
            case 'INDEX':
                $sql = "CREATE INDEX $indexName ON $table ($indexName)";
                break;
            case 'FULLTEXT':
                $sql = "CREATE FULLTEXT INDEX $indexName ON $table ($indexName)";
                break;
        }
        Database::executeQuery(new Sql($sql));
    }

    /**
     * Fixes index to comply with definition from entity class annotation. Fix 
     * consists of deleting the index and creating new one.
     * 
     * @param ReflectionClass $entityClass
     * @param string $entityIndex
     * @param array $dbIndex
     */
    private function fixIndex($entityClass,$entityIndex,$dbIndex) {
        if ($entityIndex['KEYTYPE'] != $dbIndex['KEYTYPE']) {
            $entityName = $entityClass->name;
            $table = $entityName::$dbTable;
            $this->deleteIndex($table,$entityIndex['Column_name'],$dbIndex);
            $this->createIndex($table,$entityIndex['Column_name'],$entityIndex);
        }
    }
    
    /* ---------------------------------------------------------------------- */
    /* Helper functions */
    
    /**
     * Checks if a table with given table name already exists.
     * 
     * @param string $tableName
     * @return boolean
     */
    private function tableExists($tableName) {
        Database::executeQuery(new Sql("SHOW TABLES LIKE '$tableName'"));
        return Database::changed();
    }
    
    /**
     * Retrieves column data from annotations and creates corresponding SQL
     * syntax.
     * 
     * @param array $column
     * @param string $columnName
     * @return string
     */
    private function getColumnSql($column,$columnName=null,$primaryKey=false) {
        $sql = '';
        if ($columnName) {
            $sql .= $columnName;
        } else {
            $sql .= $column['Field'];
        }
        $sql .= ' '.$column['Type'];
        if ($column['Null'] == 'NO') {
            $sql .= ' NOT NULL';
        } else {
            $sql .= ' NULL';
        }
        if ($column['Default'] !== null) {
            $sql .= ' DEFAULT '.$column['Default'];
        }
        if ($primaryKey) {
            $sql .= ' PRIMARY KEY';
        }
        if ($column['Extra']) {
            $sql .= ' '.$column['Extra'];
        }
        $sql .= ' COMMENT \''.$column['Comment'].'\'';
        return $sql;
    }
       
}