<?php


class Router
{
    private $routes;
    

    public function __construct()
    {
        $routesPath = ROOT.'/config/routes.php';
        $this->routes = include($routesPath);
    }
     //Получить строку запроса
    private function getURI()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            return  trim($_SERVER['REQUEST_URI'],'/');
        }
        
       

    }
    public function run()
    {
        //Получить строку запроса
        $uri = $this->getURI();
        $uri = str_replace ('PHP_MVC_/','',$uri);
        // var_dump($uri);
        //проверить наличие такого запроса в routers
        foreach ($this->routes as $uriPatern=>$path) {
            // Сравниваем $uriPatern и $uri
            if (preg_match("~\b$uriPatern\b~",$uri)){

                // echo '<br>Запрос пользователя '.$uri;
                // echo '<br>совпадение '. $uriPatern;
                // echo '<br>кто обрабатывает '.$path.'<br>';
               
                if ($path != 'site/index'){
                //получаем внутренний путь из внешнего согласно правилу.
                    $internalRouter = preg_replace("~\b$uriPatern\b~",$path, $uri);
                    // var_dump($internalRouter);
                    
                    $segments = explode('/', $internalRouter);
                    // var_dump($segments);
                    // $dirName = array_shift($segments);
                    $controllerName = array_shift($segments).'Controller';
                    // echo $controllerName;
                    $controllerName = ucfirst($controllerName);
                    
                    $actionName = 'action'.ucfirst(array_shift($segments));
                    $parameters = $segments ;

                    // print_r($parameters);
                    // echo $controllerName;
                    // echo $actionName;
                

                    //Подключить файл класса контроллера
                    $controllerFile = ROOT.'/controllers/'.$controllerName.'.php';
                    // var_dump($controllerFile);
                }else {
                        $segments = explode('/', $path);
                        $controllerName = array_shift($segments).'Controller';
                        $controllerName = ucfirst($controllerName);
                        
                        $actionName = 'action'.ucfirst(array_shift($segments));
                        $parameters = $segments ;

                        // print_r($parameters);
                        // echo $controllerName;
                        // echo $actionName;
                    

                        //Подключить файл класса контроллера
                        $controllerFile = ROOT.'/controllers/'.$controllerName.'.php';
                    }
                    // var_dump($controllerFile);
                if (file_exists($controllerFile)) {
                    include_once($controllerFile);
                }
            
                //Создать обьект, вызвать метод 
                $controllerObject = new $controllerName;
                $result = call_user_func_array(array($controllerObject, $actionName),$parameters);
                    if ($result != null){
                        break;
                    }    
            }
        }
    }   
}