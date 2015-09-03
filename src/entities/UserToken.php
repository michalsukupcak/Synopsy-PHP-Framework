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

use Synopsy\Orm\Entity;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class UserToken extends Entity {
    
    /**
     * @ENTITY
     * @Table __user_tokens
     * @Engine InnoDB
     */
    protected $table = '__user_tokens';
    public static $dbTable = '__user_tokens';
    
    /**
     * @ATTRIBUTE
     * @Field id
     * @Type int(10) unsigned
     * @Null NO
     * @Extra auto_increment
     * @Key PRIMARY
     * @END
     *
     * @ATTRIBUTE
     * @Field user_id
     * @Type int(10) unsigned
     * @Null NO
     * @Key INDEX
     * @END
     * 
     * @ATTRIBUTE
     * @Field timestamp
     * @Type int(10) unsigned
     * @Null NO
     * @END
     * 
     * @ATTRIBUTE
     * @Field token
     * @Type varchar(255)
     * @Null NO
     * @Key INDEX
     * @END
     */
    protected $columns = ['id','user_id','timestamp','token'];
    
}