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

use Synopsy\Rest\Storage;
use Synopsy\Sql\Queries\Delete;

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class DemoStorage extends Storage {
    
    public function deleteAllTokens() {
        $this->executeQuery(new Delete(UserToken::$dbTable));
    }
    
    public function addNewToken($token) {
        $userToken = new UserToken();
        $userToken->set('id',0);
        $userToken->set('user_id',1);
        $userToken->set('timestamp',time());
        $userToken->set('token',$token);
        $userToken->save();
    }
    
}