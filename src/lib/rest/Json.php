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

namespace Synopsy\Rest;

use Synopsy\Exceptions\SynopsyException;

/**
 * 
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Json {
    
    /**
     *
     * @var type 
     */
    private $result = false;
    
    /**
     *
     * @var type 
     */
    private $message = null;

    /**
     *
     * @var type 
     */
    private $data = [];
    
    /**
     * 
     * @param type $result
     * @param type $message
     * @param type $data
     */
    public function __Construct($result,$message,$data=[]) {
        $this->setResult($result);
        $this->setMessage($message);
        $this->setData($data);
    }

    /**
     * 
     * @param type $result
     * @throws SynopsyException
     */
    public function setResult($result) {
	if ($result === null) {
            throw new SynopsyException('Please provide a $result value (boolean true/false)!');
        } elseif (!is_bool($result)) {
            throw new SynopsyException('Variable $result must have a boolean value (true/false) only!');
        }
        $this->result = $result;
    }

    /**
     * 
     * @param type $message
     * @throws SynopsyException
     */
    public function setMessage($message) {
        if ($message == null) {
            throw new SynopsyException('Please provide a response message!');
        }
        $this->message = $message;
    }

    /**
     * 
     * @param type $data
     */
    public function setData($data) {
        $this->data = $data;
    }
    
    /**
     * 
     * @return type
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * 
     * @return type
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * 
     * @return type
     */
    public function getData() {
        return $this->data;
    }

    /**
     * 
     * @return type
     */
    public function getEncodedJson() {
        return json_encode([
            'result' => $this->result,
            'message' => $this->message,
            'data' => $this->data
        ]);
    }

    /**
     * 
     */
    public static function setHeader() {
        header('Content-Type: application/json');
    }
    
}

