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

use Synopsy\Config\Role;
use Synopsy\Files\Files;
use Synopsy\Lang\Language;
use ReflectionClass;

/**
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class ControllersCompiler extends AbstractCompiler {
    
    /**
     * 
     */
    public function compile() {
        $controllerFiles = $this->getControllerFiles(); // Load files from /mvc/components directory
        if ($this->filesChanged('controllers',$controllerFiles)) { // Proceed only if there are modified files (different modified file times)
            $routes = []; // All found routes will go here
            $languages = array_keys(Language::getAll());
            foreach ($languages as $languageCode) { // Setup routes array for language codes
                $routes[$languageCode] = [];
            }
            $defaultController = null; // Default controller (@default) will go here
            $controllerFileTimes = []; // Modified file times of controller files
            foreach ($controllerFiles as $controllerFile) { // Read content of each source file and append it into destination file
                require_once($controllerFile);
                $controller = $this->getFileName($controllerFile); // Get filename (HomeController, FormController, ...)
                $controllerFilePath = $this->getControllerFilePath($controllerFile); // Get relative file path (home/HomeController, form/FormController, ...)
                $controllerFileTimes[$controller] = $this->getFileTime($controllerFile); // Save current modified file time
                $controllerClass = new ReflectionClass($controller); // ReflectionClass of current controller class
                $controllerDoc = $this->removeWhitespaces($controllerClass->getDocComment()); // Documentation for current controller class
                if ($this->isRoutableController($controllerDoc)) { // If controller is routable (@CONTROLLER)
                    if ($this->isDefaultController($controllerDoc)) { // If controller is default (@default)
                        if ($defaultController != null) { // If there is already a default controller, throw exception (can only be one)
                            throw new CompilerException("More than one controller has been defined as @default (default = $defaultController, current = $controllerFilePath!");
                        }
                        $defaultController = $controllerFilePath; // Set default controller
                    }
                    $controllerRoutes = $this->getControllerRoutes($controller,$controllerDoc); // Array of language codes form @route-...)
                    $controllerMaps = $this->getControllerMaps($controller,$controllerDoc); // Array of controller maps)
                    $methodAnnotationsData = []; // Methods
                    $controllerMethods = $controllerClass->getMethods();
                    foreach ($controllerMethods as $controllerMethod) { // Iterate through each method in controller
                        $methodDoc = $controllerMethod->getDocComment();
                        if ($this->isRoutableMethod($methodDoc) && $this->hasRoutesMethod($methodDoc)) { // Methods with @METHOD annotation with @route-... annotations
                            $methodAnnotationsData[$controllerMethod->name] = []; // Initialize array
                            $methodAnnotationsData[$controllerMethod->name]['routes'] = $this->getMethodRoutes($controller,$controllerMethod->name,$methodDoc); // Save routes here
                            $methodAnnotationsData[$controllerMethod->name]['authenticated'] = ($this->isAuthenticatedMethod($methodDoc)); // If method has @authenticated annotation: true, else false
                            if ($this->isRolesMethod($methodDoc)) { // If method has @roles annotation
                                if (!$methodAnnotationsData[$controllerMethod->name]['authenticated']) { // Method must be @authenticated if it has @roles annotation, otherwise throw exception
                                    throw new SynopsyException("Method '$controllerMethod->name' in controller '$controller' has defined @roles annotation, but missing @authenticated annotation!");
                                }
                                $methodAnnotationsData[$controllerMethod->name]['roles'] = $this->getMethodRoles($controllerMethod->name,$controller,$methodDoc); // Save method roles
                            } else {
                                $methodAnnotationsData[$controllerMethod->name]['roles'] = [];
                            }
                        } elseif ($controllerMethod->name == 'main') { // Main method (public function main() {...}), doesn't have to have annotations
                            $methodAnnotationsData['main'] = [];
                            $mainRoutes = [];
                            foreach ($languages as $language) {
                                $mainRoutes[$language] = 'main';
                            }
                            $methodAnnotationsData['main']['routes'] = $mainRoutes;
                            $methodAnnotationsData['main']['authenticated'] = ($this->isAuthenticatedMethod($methodDoc));
                            if ($this->isRolesMethod($methodDoc)) {
                                if (!$methodAnnotationsData[$controllerMethod->name]['authenticated']) {
                                    throw new SynopsyException("Method '$controllerMethod->name' in controller '$controller' has defined @roles annotation, but missing @authenticated annotation!");
                                }
                                $methodAnnotationsData['main']['roles'] = $this->getMethodRoles($controllerMethod->name,$controller,$methodDoc);
                            } else {
                                $methodAnnotationsData['main']['roles'] = [];
                            }
                        }
                    }
                    foreach ($languages as $languageCode) {
                        if (array_key_exists($controllerRoutes[$languageCode],$routes[$languageCode])) {
                            throw new CompilerException("Duplicite route '$controllerRoutes[$languageCode]' in controller '$controller'!");
                        }
                        $routes[$languageCode][$controllerRoutes[$languageCode]] = [
                            'controller' => $controllerFilePath,
                            'maps' => $controllerMaps[$languageCode],
                            'methods' => []
                        ];
                        foreach ($methodAnnotationsData as $method => $methodData) {
                            if (array_key_exists($methodData['routes'][$languageCode],$routes[$languageCode][$controllerRoutes[$languageCode]]['methods'])) {
                                throw new CompilerException("Duplicite route '$controllerRoutes[$languageCode]' in controller '$controller'!");
                            }
                            $routes[$languageCode][$controllerRoutes[$languageCode]]['methods'][] = [
                                $methodData['routes'][$languageCode] => [
                                    'method' => $method,
                                    'authenticated' => $methodData['authenticated'],
                                    'roles' => $methodData['roles']
                                ]
                            ];
                        }
                    }
                }
            }
            if (!$defaultController) {
                throw new CompilerException("No controller has been defined as @default!");
            }
            $cachedRoutes = $this->compileCachedRoutes($routes);
            $cachedUrls = $this->compileCachedUrls($routes);
            $this->setRoutesCacheFile($cachedRoutes,$cachedUrls,$defaultController);
            $this->setCacheFile('controllers',$controllerFileTimes);
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
    private function isRoutableController($doc) {
        return preg_match('/@CONTROLLER/',$doc);
    }
    
    /**
     * 
     * @param String $doc
     * @return Boolean
     */
    private function isDefaultController($doc) {
        return preg_match('/@Default/',$doc);
    }
    
    /**
     * Retrieves array of routes from docComments of the controller.
     * 
     * @param String $controller
     * @param String $doc
     * @return Mixed
     * @throws CompilerException
     */
    private function getControllerRoutes($controller,$doc) {
        $routes = [];
        $languageCodes = array_keys(Language::getAll());
        $lr = '';
        foreach ($languageCodes as $languageCode) {
            $lr .= "$languageCode|";
        }
        $languageRegex = rtrim($lr,'|');
        if (preg_match('/@Route-(?!'.$languageRegex.')/',$doc)) {
            throw new CompilerException("There is a route for non-existing language defined for controller '$controller'!");
        }
        foreach ($languageCodes as $languageCode) {
            $c = null;
            preg_match('/@Route-'.$languageCode.' ([a-zA-Z0-9_\-]+)/',$doc,$c);
            if ($c[1]) {
                $routes[$languageCode] = $c[1];
            } else {
                throw new CompilerException("Missing route for language '$languageCode' in controller '$controller'!");
            }
        }
        return $routes;
    }

    /**
     * 
     * @param type $controller
     * @param type $doc
     * @return null
     * @throws CompilerException
     */
    private function getControllerMaps($controller,$doc) {
        $controllerMaps = [];
        $languageCodes = array_keys(Language::getAll());
        foreach ($languageCodes as $languageCode) {
            $controllerMaps[$languageCode] = [];
        }
        $m = null;
        preg_match_all('/@Map (.+)/',$doc,$m);
        if (!empty($m[0])) {
            foreach ($m[1] as $index => $mapString) {
                $match = null;
                preg_match('/([a-zA-Z0-9]+) ([a-zA-Z0-9_\-\|<>\{\}\/=]+)/',$mapString,$match);
                if (isset($match[1])) {
                    $mapName = $match[1];
                    if (!$mapName) {
                        throw new CompilerException("Missing map name for map '".$m[0][$index]."' in controller '$controller'!");
                    }
                } else {
                    throw new CompilerException("Missing map name for map '".$m[0][$index]."' in controller '$controller'!");
                }
                if (isset($match[2])) {
                    $map = $match[2];
                    if (!$map) {
                        throw new CompilerException("Missing map definition for map '".$m[0][$index]."' in controller '$controller'!");
                    }
                } else {
                    throw new CompilerException("Missing map definition for map '".$m[0][$index]."' in controller '$controller'!");
                }
                $mapComponents = explode('/',$map);
                foreach ($languageCodes as $languageCode) {
                    $controllerMaps[$languageCode][$mapName] = '';
                }
                foreach ($mapComponents as $mapComponent) {
                    if ($mapComponent[0] == '{') {
                        $languages = $languageCodes;
                        $componentString = substr($mapComponent,1,-1);
                        $componentItems = explode('|',$componentString);
                        foreach ($componentItems as $item) {
                            $itemComponents = explode('=',$item);
                            $language = $itemComponents[0];
                            if (in_array($language,$languages)) {
                                $languages = array_diff($languages,[$language]);
                                $controllerMaps[$language][$mapName] .= $itemComponents[1].'/';
                            } else {
                                throw new InvalidArgumentException("Detected non-existing language code '$language' in map '".$m[0][$index]."' in controller '$controller'!");
                            }
                        }
                        if (!empty($languages)) {
                            throw new InvalidArgumentException("Some languages are not defined in map '".$m[0][$index]."' in controller '$controller'!");
                        }
                    } elseif ($mapComponent[0] == '<') {
                        foreach ($languageCodes as $languageCode) {
                            $controllerMaps[$languageCode][$mapName] .= $mapComponent.'/';
                        }
                    } else {
                        throw new CompilerException("Controller '$controller' has invalid component defined in map '".$m[0][$index]."' - each component must be enclosed in either {} or <> brackets!");
                    }
                }
            }
        }
        return $controllerMaps;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Methods */
    
    /**
     * 
     * @param type $doc
     * @return type
     */
    private function isRoutableMethod($doc) {
        return preg_match('/@METHOD/',$doc);
    }
    
    /**
     * 
     * @param type $doc
     * @return type
     */
    private function hasRoutesMethod($doc) {
        return preg_match('/@Route/',$doc);
    }
    
    /**
     * 
     * @param type $controller
     * @param type $method
     * @param type $doc
     * @return null
     * @throws CompilerException
     */
    private function getMethodRoutes($controller,$method,$doc) {
        $routes = [];
        $languageCodes = array_keys(Language::getAll());
        $lr = '';
        foreach ($languageCodes as $languageCode) {
            $lr .= "$languageCode|";
        }
        $languageRegex = rtrim($lr,'|');
        if (preg_match('/@Route-(?!'.$languageRegex.')/',$doc)) {
            throw new CompilerException("There is a route for non-existing language defined for method '$method' in controller '$controller'!");
        }
        foreach ($languageCodes as $languageCode) {
            $c = null;
            preg_match('/@Route-'.$languageCode.' ([a-zA-Z0-9_\-]+)/',$doc,$c);
            if (isset($c[1])) {
                $routes[$languageCode] = $c[1];
            } else {
                throw new CompilerException("Missing route for language '$languageCode' in method '$method' in controller '$controller'!");
            }
        }
        return $routes;
    }
    
    private function isAuthenticatedMethod($doc) {
        return preg_match('/@Authenticated/',$doc);
    }
    
    private function isRolesMethod($doc) {
        return preg_match('/@Roles/',$doc);
    }
    
    private function getMethodRoles($method,$controller,$doc) {
        $roles = [];
        $c = null;
        preg_match('/@Roles ([a-zA-Z0-9,_\-]+)/',$doc,$c);
        if (isset($c[1])) {
            $r = explode(',',$c[1]);
            foreach ($r as $role) {
                if (!Role::exists($role)) {
                    throw new CompilerException("Role '$role' in method '$method' in controller '$controller' is not defined in config.xml file!");
                }
                $roles[] = $role;
            }
        } else {
            throw new CompilerException("@roles annotation for method '$method' in controller '$controller' has invalid role definition!");
        }
        return $roles;
    }
    
    /* ---------------------------------------------------------------------- */
    /* Routes.php file */
    
    /**
     * 
     * @param type $routes
     * @return string
     */
    private function compileCachedRoutes($routes) {
        $cachedRoutes = "\$return = [\n";
        foreach ($routes as $languageCode => $route) {
            $cachedRoutes .= "        '$languageCode' => [\n";
            foreach ($route as $cRoute => $cData) {
                $cachedRoutes .= "            '$cRoute' => [\n"
                        . "                'controller' => '$cData[controller]',\n"
                        . "                'maps' => [\n";
                foreach ($cData['maps'] as $mapName => $map) {
                    $cachedRoutes .= "                    '$mapName' => '".rtrim($map,'/')."',\n";
                }
                $cachedRoutes = rtrim($cachedRoutes,',');
                $cachedRoutes .= "                ],\n";
                $cachedRoutes .= "                'methods' => [\n";
                foreach ($cData['methods'] as $method) {
                    $mRoute = array_keys($method)[0];
                    $cachedRoutes .= "                    '$mRoute' => [\n";
                    $cachedRoutes .= "                        'method' => '".$method[$mRoute]['method']."',\n";
                    $cachedRoutes .= "                        'authenticated' => ".($method[$mRoute]['authenticated'] ? 'true' : 'false').",\n";
                    $cachedRoutes .= "                        'roles' => [\n";
                    if (is_array($method[$mRoute]['roles'])) {
                        foreach ($method[$mRoute]['roles'] as $role) {
                            $cachedRoutes .= "                            '$role',\n";
                        }
                    }
                    $cachedRoutes .= "                        ],\n";
                    $cachedRoutes .= "                    ],\n";
                }
                $cachedRoutes = rtrim($cachedRoutes,',');
                $cachedRoutes .= "                ]\n"
                        . "            ],\n";
            }
            $cachedRoutes = rtrim($cachedRoutes,',');
            $cachedRoutes .= "        ],\n";
        }
        $cachedRoutes = rtrim($cachedRoutes,',');
        $cachedRoutes .= "    ];";
        return $cachedRoutes;
    }
    
    /**
     * 
     * @param type $routes
     * @return string
     */
    private function compileCachedUrls($routes) {
        $cachedUrls = "\$return = [\n";
        foreach ($routes as $languageCode => $route) {
            $cachedUrls .= "        '$languageCode' => [\n";
            foreach ($route as $cRoute => $cData) {
                $cachedUrls .= "            '$cData[controller]' => [\n"
                        . "                'route' => '$cRoute',\n"
                        . "                'maps' => [\n";
                foreach ($cData['maps'] as $mapName => $map) {
                    $cachedUrls .= "                    '$mapName' => '".rtrim($map,'/')."',\n";
                }
                $cachedUrls = rtrim($cachedUrls,',');
                $cachedUrls .= "                ],\n";
                $cachedUrls .= "                'methods' => [\n";
                foreach ($cData['methods'] as $method) {
                    $mRoute = array_keys($method)[0];
                    $cachedUrls .= "                    '".$method[$mRoute]['method']."' => '$mRoute',\n";
                }
                $cachedUrls = rtrim($cachedUrls,',');
                $cachedUrls .= "                ]\n"
                        . "            ],\n";
            }
            $cachedUrls = rtrim($cachedUrls,',');
            $cachedUrls .= "        ],\n";
        }
        $cachedUrls = rtrim($cachedUrls,',');
        $cachedUrls .= "    ];";
        return $cachedUrls;
    }
    
    /**
     * 
     * @param type $cachedRoutes
     * @param type $cachedUrls
     */
    private function setRoutesCacheFile($cachedRoutes,$cachedUrls,$defaultController) {
        $routesFile = CACHE.'routes.php';
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
                . "use Synopsy\Exceptions\SynopsyException;\n"
                . "/**\n"
                . " * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>\n"
                . " */\n"
                . "\n"
                . "/**\n"
                . " * List of cached routes from URL to controllers & methods.\n"
                . " * \n"
                . " * @return String\n"
                . " */\n"
                . "function cached_routes(\$languageCode) {\n"
                . "    $cachedRoutes\n"
                . "    if (!array_key_exists(\$languageCode,\$return)) {\n"
                . "        throw new SynopsyException(\"Language code '\$languageCode' is not defined in cached_routes!\");\n"
                . "    }\n"
                . "    return \$return[\$languageCode];\n"
                . "}\n"
                . "\n"
                . "/**\n"
                . " * List of cached controllers and methods to URLs.\n"
                . " * \n"
                . " * @return String\n"
                . " */\n"
                . "function cached_urls(\$languageCode) {\n"
                . "    $cachedUrls\n"
                . "    if (!array_key_exists(\$languageCode,\$return)) {\n"
                . "        throw new SynopsyException(\"Language code '\$languageCode' is not defined in cached_urls!\");\n"
                . "    }\n"
                . "    return \$return[\$languageCode];\n"
                . "}\n"
                . "\n"
                . "/**\n"
                . " * Returns path to default controller.\n"
                . " * \n"
                . " * @return String\n"
                . " */\n"
                . "function default_controller() {\n"
                . "    return '$defaultController';\n"
                . "}\n"
                . "\n"
                . "/**\n"
                . " * Returns default methods name.\n"
                . " * \n"
                . " * @return String\n"
                . " */\n"
                . "function default_method() {\n"
                . "    return 'main';\n"
                . "}\n";
        $routesFileHandler = fOpen($routesFile,'w');
        fWrite($routesFileHandler,$fileContent);
        fClose($routesFileHandler);
    }
    
    /* ---------------------------------------------------------------------- */
    /* Auxiliary functions */
    
    /**
     * 
     * @param type $controllerFile
     * @return type
     */
    private function getControllerFilePath($controllerFile) {
        return rtrim(explode('/mvc/components/',$controllerFile)[1],'.php');
    }
    
    /**
     * 
     * @return type
     */
    private function getControllerFiles() {
        $controllerFiles = [];
        $cfs = Files::getDirContent(APP.'mvc/components/',true);
        foreach ($cfs as $file) {
            if (preg_match('~^'.APP.'mvc/components/(.*)Controller.php$~',$file)) {
                $controllerFiles[] = $file;
            }
        }
        return $controllerFiles;
    }
    
}