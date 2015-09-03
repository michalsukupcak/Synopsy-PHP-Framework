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

namespace Synopsy\Common;

use Synopsy\Exceptions\SynopsyException;

/**
 * Class for sending HTTP requests and processing HTTP responses.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Http {
    
    /**
     * Instance of CURL class.
     *
     * @var CURL 
     */
    private $curl = null;
    
    /**
     * Header of HTTP response.
     *
     * @var String 
     */
    private $responseHeader = null;
    
    /**
     * Body of HTTP response.
     *
     * @var String 
     */
    private $response = null;
    
    /**
     * Constructor initializes basic CURL settings and options.
     * 
     * @param String $url
     */
    public function __Construct($url) {
	$this->curl = curl_init();
        curl_setopt($this->curl,CURLOPT_URL,$url);
        curl_setopt($this->curl,CURLOPT_HEADER,true);
        curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($this->curl,CURLOPT_CONNECTTIMEOUT,30);
        curl_setopt($this->curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_NONE);
        curl_setopt($this->curl,CURLOPT_HTTP_VERSION,CURLPROTO_HTTP);
    }
    
    /**
     * Sets CURL to use HTTP version 1.0
     * 
     */
    public function useHttpVersion10() {
        curl_setopt($this->curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
    }
    
    /**
     * Sets CURL to use HTTP version 1.1
     * 
     */
    public function usetHttpVersion11() {
        curl_setopt($this->curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
    }
    
    /**
     * Forces CURL to use HTTPS protocol instead of HTTP.
     * 
     */
    public function useHttps() {
        curl_setopt($this->curl,CURLOPT_HTTP_VERSION,CURLPROTO_HTTPS);
    }
    
    /**
     * Executes CURL request as HTTP GET method.
     * 
     * @throws HttpException
     */
    public function get() {
	$this->execute();
    }
    
    /**
     * Executes CURL request as HTTP POST method.
     * 
     * @param Array $parameters
     * @throws HttpException
     */
    public function post($parameters) {
        curl_setopt($this->curl,CURLOPT_POST,true);
        curl_setopt($this->curl,CURLOPT_POSTFIELDS,$parameters);
        $this->execute();
    }
    
    /**
     * Retrieves header of HTTP response.
     * 
     */
    public function responseHeader() {
	return $this->responseHeader;
    }
    
    /**
     * Retrieves body of HTTP response.
     * 
     */
    public function response() {
	return $this->response;
    }
    
    /**
     * Closes CURL connection.
     * 
     */
    public function close() {
        curl_close($this->curl);
    }
    
    /**
     * Executes CURL request.
     * 
     * @throws SynopsyException
     */
    private function execute() {
        $output = curl_exec($this->curl);
        if (!$output) {
            throw new SynopsyException("CURL_EXEC failed!");
        }
        $headerLength = curl_getinfo($this->curl,CURLINFO_HEADER_SIZE);
        $this->responseHeader = substr($output,0,$headerLength-1);
        $this->response = substr($output,$headerLength);
        curl_close($this->curl);
    }
    
}