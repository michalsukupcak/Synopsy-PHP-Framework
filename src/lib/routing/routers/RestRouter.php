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

namespace Synopsy\Routing\Routers;

use Synopsy\Exceptions\InvalidApiVersionException;
use Synopsy\Exceptions\InvalidUrlException;
use Synopsy\Exceptions\MissingAuthHeaderException;
use Synopsy\Exceptions\UnauthenticatedException;
use Synopsy\Exceptions\UnauthorizedRoleException;
use Synopsy\Exceptions\UndefinedLanguageException;
use Synopsy\Exceptions\SynopsyException;
use Synopsy\Rest\Json;
use Synopsy\Routing\Requests\ApiRequest;
use Synopsy\Routing\RouterInterface;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class RestRouter implements RouterInterface {
    
    /**
     *
     * @var type    
     */
    private $request = null;
    
    /**
     * 
     */
    public function __Construct() {
        require_once(SRC.'lib/routing/requests/ApiRequest.php');
        try {
            $this->request = new ApiRequest();
        } catch (UndefinedLanguageException $e) {
            Json::setHeader();
            die((new Json(false,'ERROR: Missing or unsupported system language in URL parameters!'))->getEncodedJson());
        } catch (InvalidApiVersionException $e) {
            Json::setHeader();
            die((new Json(false,'ERROR: Invalid API version!'))->getEncodedJson());
        } catch (InvalidUrlException $e) {
            Json::setHeader();
            die((new Json(false,'ERROR: Malformed URL! Invalid or missing api/call or other parameters!'))->getEncodedJson());
        } catch (MissingAuthHeaderException $e) {
            Json::setHeader();
            die((new Json(false,'ERROR: Missing x-auth-token header when accessing protected resource!'))->getEncodedJson());
        } catch (UnauthenticatedException $e) {
            Json::setHeader();
            die((new Json(false,'UNAUTHENTICATED: Unauthenticated access to protected resource!'))->getEncodedJson());
        } catch (UnauthorizedRoleException $e) {
            die((new Json(false,'UNAUTHORIZED: Unauthorized access to protected resource!'))->getEncodedJson());
            Json::setHeader();
        }
    }
    
    /**
     * Retrieves controller and method data from request. Creates instance
     * of given controller and calls given method on the controller.
     * 
     */
    public function run() {
        require_once(APP.'rest/'.$this->request->getApiFile());
        $a = $this->request->getApi();
        $api = new $a($this->request);
        $api->{$this->request->getCall()}();
        if (!$api->getJson() instanceof Json) {
            throw new SynopsyException("Method Api::getJson() for current API didn't return an instance of Json class!");
        }
        Json::setHeader();
        echo $api->getJson()->getEncodedJson();
    }
    
}