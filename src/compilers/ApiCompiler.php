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

use Synopsy\Files\Files;
use ReflectionClass;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class ApiCompiler extends AbstractCompiler {

    /**
     * 
     */
    public function compile() {
        $apiFiles = $this->getApiFiles();
        if ($this->filesChanged('apis',$apiFiles)) {
            $apis = [];
            $versions = $this->getApiVersions();
            foreach ($versions as $version) {
                $apis[$version] = [];
            }
            $apiFileTimes = [];
            foreach ($apiFiles as $apiFile) {
                require_once($apiFile);
                $api = $this->getFileName($apiFile);
                $apiFilePath = $this->getApiFilePath($apiFile);
                $apiFileTimes[$api] = $this->getFileTime($apiFile);
                $apiVersion = $this->getApiVersionFromFile($apiFilePath);
                $apiClass = new ReflectionClass($api);
                $apiDoc = $this->removeWhitespaces($apiClass->getDocComment());
                if ($this->isRoutableApi($apiDoc)) {
                    $apiRoute = $this->getApiRoute($apiDoc);
                    $apiMaps = $this->getApiMaps($api,$apiDoc);
                    $apis[$apiVersion][$apiRoute] = [
                        'api' => $apiFilePath,
                        'maps' => $apiMaps,
                        'calls' => []
                    ];
                    $callAnnotationsData = [];
                    $apiMethods = $apiClass->getMethods();
                    foreach ($apiMethods as $apiCall) {
                        $callDoc = $apiCall->getDocComment();
                        if ($this->isRoutableCall($callDoc)) {
                            $callAnnotationsData[$apiCall->name] = [];
                            $callAnnotationsData[$apiCall->name]['routes'] = $this->getCallRoutes($callDoc);
                            $callAnnotationsData[$apiCall->name]['authenticated'] = ($this->isAuthenticatedCall($callDoc));
                            if ($this->isRolesCall($callDoc)) {
                                if (!$callAnnotationsData[$apiCall->name]['authenticated']) {
                                    throw new SynopsyException("Call '$apiCall->name' in Api '$api' has defined @roles annotation, but missing @authenticated annotation!");
                                }
                                $callAnnotationsData[$apiCall->name]['roles'] = $this->getCallRoles($apiCall->name,$api,$callDoc);
                            } else {
                                $callAnnotationsData[$apiCall->name]['roles'] = [];
                            }
                        }
                    }
                    foreach ($callAnnotationsData as $call => $callData) {
                        if (array_key_exists($callData['routes'],$apis[$apiVersion][$apiRoute]['calls'])) {
                            throw new CompilerException("Duplicite route '$apiRoute' in api '$api'!");
                        }
                        $apis[$apiVersion][$apiRoute]['calls'][] = [
                            $callData['routes'] => [
                                'call' => $call,
                                'authenticated' => $callData['authenticated'],
                                'roles' => $callData['roles']
                            ]
                        ];
                    }
                }
            }
            $cachedApis = $this->compileCachedApis($apis);
            $this->setApisCacheFile($cachedApis);
            $this->setCacheFile('apis',$apiFileTimes);
            $this->compilerExecuted();
        }
    }
    
    /* ---------------------------------------------------------------------- */
    /* Controllers */
    
    /**
     * 
     * @param String $doc
     * @return Boolean
     */
    private function isRoutableApi($doc) {
        return preg_match('/@API/',$doc);
    }
        
    /**
     * Retrieves route of an api from docComments of the api.
     * 
     * @param String $doc
     * @return Mixed
     */
    private function getApiRoute($doc) {
        $route = null;
        preg_match('/@Route ([a-zA-Z0-9_\-]+)/',$doc,$route);
        if (isset($route[1])) {
            return $route[1];
        } else {
            return null;
        }
    }

    /**
     * 
     * @param type $api
     * @param type $doc
     * @return null
     * @throws CompilerException
     */
    private function getApiMaps($api,$doc) {
        $apiMaps = [];
        $m = null;
        preg_match_all('/@Map (.+)/',$doc,$m);
        if (!empty($m[0])) {
            foreach ($m[1] as $index => $mapString) {
                $match = null;
                preg_match('/([a-zA-Z0-9]+) ([a-zA-Z0-9_\-\|<>\{\}\/=]+)/',$mapString,$match);
                if (isset($match[1])) {
                    $mapName = $match[1];
                    if (!$mapName) {
                        throw new CompilerException("Missing map name for map '".$m[0][$index]."' in api '$api'!");
                    }
                } else {
                    throw new CompilerException("Missing map name for map '".$m[0][$index]."' in api '$api'!");
                }
                if (isset($match[2])) {
                    $map = $match[2];
                    if (!$map) {
                        throw new CompilerException("Missing map definition for map '".$m[0][$index]."' in api '$api'!");
                    }
                } else {
                    throw new CompilerException("Missing map definition for map '".$m[0][$index]."' in api '$api'!");
                }
                $mapComponents = explode('/',$map);
                $apiMaps[$mapName] = '';
                foreach ($mapComponents as $mapComponent) {
                    if ($mapComponent[0] == '{') {
                        $apiMaps[$mapName] .= rtrim(ltrim($mapComponent,'{'),'}');
                    } elseif ($mapComponent[0] == '<') {
                        $apiMaps[$mapName] .= $mapComponent.'/';
                    } else {
                        throw new CompilerException("Api '$api' has invalid component defined in map '".$m[0][$index]."' - each component must be enclosed in either {} or <> brackets!");
                    }
                }
            }
        }
        return $apiMaps;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Methods */
    
    /**
     * 
     * @param type $doc
     * @return type
     */
    private function isRoutableCall($doc) {
        return preg_match('/@CALL/',$doc);
    }
    
    /**
     * 
     * @param type $controller
     * @param type $method
     * @param type $doc
     * @return null
     * @throws CompilerException
     */
    private function getCallRoutes($doc) {
        $route = null;
        preg_match('/@Route ([a-zA-Z0-9_\-]+)/',$doc,$route);
        if (isset($route[1])) {
            return $route[1];
        } else {
            return null;
        }
    }
    
    private function isAuthenticatedCall($doc) {
        return preg_match('/@Authenticated/',$doc);
    }
    
    private function isRolesCall($doc) {
        return preg_match('/@Roles/',$doc);
    }
    
    private function getCallRoles($call,$api,$doc) {
        $roles = [];
        $c = null;
        preg_match('/@Roles ([a-zA-Z0-9,_\-]+)/',$doc,$c);
        if (isset($c[1])) {
            $r = explode(',',$c[1]);
            foreach ($r as $role) {
                if (!Role::exists($role)) {
                    throw new CompilerException("Role '$role' in call '$call' in Api '$api' is not defined in config.xml file!");
                }
                $roles[] = $role;
            }
        } else {
            throw new CompilerException("@roles annotation for call '$call' in Api '$api' has invalid role definition!");
        }
        return $roles;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Routes.php file */
    
    /**
     * 
     * @param type $apis
     * @return string
     */
    private function compileCachedApis($apis) {
        $cachedApis = "\$return = [\n";
        foreach ($apis as $version => $route) {
            $cachedApis .= "        '$version' => [\n";
            foreach ($route as $aRoute => $aData) {
                $cachedApis .= "            '$aRoute' => [\n"
                        . "                'api' => '$aData[api]',\n"
                        . "                'maps' => [\n";
                foreach ($aData['maps'] as $mapName => $map) {
                    $cachedApis .= "                    '$mapName' => '".rtrim($map,'/')."',\n";
                }
                $cachedApis = rtrim($cachedApis,',');
                $cachedApis .= "                ],\n";
                $cachedApis .= "                'calls' => [\n";
                foreach ($aData['calls'] as $call) {
                    $cRoute = array_keys($call)[0];
                    $cachedApis .= "                    '$cRoute' => [\n";
                    $cachedApis .= "                        'call' => '".$call[$cRoute]['call']."',\n";
                    $cachedApis .= "                        'authenticated' => ".($call[$cRoute]['authenticated'] ? 'true' : 'false').",\n";
                    $cachedApis .= "                        'roles' => [\n";
                    if (is_array($call[$cRoute]['roles'])) {
                        foreach ($call[$cRoute]['roles'] as $role) {
                            $cachedApis .= "                            '$role',\n";
                        }
                    }
                    $cachedApis .= "                        ],\n";
                    $cachedApis .= "                    ],\n";                    
                }
                $cachedApis = rtrim($cachedApis,',');
                $cachedApis .= "                ]\n"
                        . "            ],\n";
            }
            $cachedApis = rtrim($cachedApis,',');
            $cachedApis .= "        ],\n";
        }
        $cachedApis = rtrim($cachedApis,',');
        $cachedApis .= "    ];";
        return $cachedApis;
    }
    
    /**
     * 
     * @param type $cachedRoutes
     * @param type $cachedUrls
     */
    private function setApisCacheFile($cachedRoutes) {
        $apisFile = CACHE.'apis.php';
        $fileContent = "<?php\n"
                . "/*\n"
                . " * Synopsy PHP Framework (c) by Webdesign Studio s.r.o.\n"
                . " * \n"
                . " * Synopsy PHP Framework is licensed under a\n"
                . " * Creative Commons Attribution 4.0 International License.\n"
                . " * \n"
                . " * You should have received a copy of the license along with this\n"
                . " * work. If not, see <http://creativecommons.org/licenses/by/4.0/>.\n"
                . " * \n"
                . " * Any files in this application that are NOT marked with this disclaimer are\n"
                . " * not part of the framework's open-source implementation, the CC 4.0 licence\n"
                . " * does not apply to them and are protected by standard copyright laws!\n"
                . " */\n"
                . "\n"
                . "\n"
                . "use Synopsy\Exceptions\InvalidApiVersionException;\n"
                . "/**\n"
                . " * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>\n"
                . " */\n"
                . "\n"
                . "/**\n"
                . " * List of cached apis from URL to apis & calls.\n"
                . " * \n"
                . " * @return String\n"
                . " */\n"
                . "function cached_apis(\$version) {\n"
                . "    $cachedRoutes\n"
                . "    if (!array_key_exists(\$version,\$return)) {\n"
                . "        throw new InvalidApiVersionException();\n"
                . "    }\n"
                . "    return \$return[\$version];\n"
                . "}\n"
                . "\n";
        $apisFileHandler = fOpen($apisFile,'w');
        fWrite($apisFileHandler,$fileContent);
        fClose($apisFileHandler);
    }
    
    /* ---------------------------------------------------------------------- */
    /* Auxiliary functions */
    
    /**
     * 
     * @return type
     */
    private function getApiVersions() {
        $versions = [];
        $versionFiles = Files::getDirContent(APP.'rest/',false,true);
        foreach ($versionFiles as $versionFile) {
            $version = explode('/',$versionFile);
            $versions[] = array_pop($version);
        }
        return $versions;
    }
    
    /**
     * 
     * @param type $filePath
     * @return type
     */
    private function getApiVersionFromFile($apiFilePath) {
        return explode('/',$apiFilePath)[0];
    }
    
    /**
     * 
     * @param type $apiFile
     * @return type
     */
    private function getApiFilePath($apiFile) {
        return rtrim(explode('/rest/',$apiFile)[1],'.php');
    }
    
    /**
     * 
     * @return type
     */
    private function getApiFiles() {
        $apiFiles = [];
        $afs = Files::getDirContent(APP.'rest/',true);
        foreach ($afs as $file) {
            if (preg_match('~^'.APP.'rest/v([0-9]+)/(.*)Api.php$~',$file)) {
                $apiFiles[] = $file;
            }
        }
        return $apiFiles;
    }
    
}
