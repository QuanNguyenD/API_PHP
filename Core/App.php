<?php

use Gettext\Loader\PoLoader;
use Gettext\Translations;
require_once APPPATH.'/Core/Controller.php';
require_once APPPATH.'/Core/Event.php';
require_once APPPATH.'/Controller/IndexController.php';

class App{

    protected $router;
    protected $controller;
    protected $plugins;

    protected static $routes = [];


    public function __construct()
    {
        $this->controller = new Controller;
    }
    /**
     * Adds a new route to the App:$routes static variable
     * App::$routes will be mapped on a route 
     * initializes on App initializes
     * 
     * Format: ["METHOD", "/uri/", "Controller"]
     * Example: App:addRoute("GET|POST", "/post/?", "Post");
     */
    public static function addRoute()
    {
        $route = func_get_args();
        if ($route) {
            self::$routes[] = $route;
        }
    }


    /**
     * Get App::$routes
     * @return array An array of the added routes
     */
    public static function getRoutes()
    {
        return self::$routes;
    }
    private static function db(){
        $config = [
            'driver' => DATA_BASE,
            'host' => DB_HOST,
            'database' => DB_NAME,
            'password' => '',
            'username' => USER_NAME,
            'charset' => DB_CHARSET,
            'option' => [
                PDO::ATTR_TIMEOUT => 15,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]


        ];
        $connection = new \Pixie\Connection('mysql',$config);
        return $connection;
        

    }
    public static function getConnection() {
        return self::db(); // Gọi phương thức db() từ bên trong lớp
    }
    //Khi không sử dụng bí danh, 
    //có thể khởi tạo trình xử lý QueryBuilder riêng biệt, 
    //hữu ích cho Dependency Injection và Testing.
    //ví dụ về truy xuất thông tin name = Sana trong bảng my_table
    //Có cách dùng bí danh, mà bị lỗi, chưa biết sửa như thế nào
    //new \Pixie\Connection('mysql', $config, 'QB');-----Ví dụ về dùng bí danh
    // public function test(){
    //     $connection = $this ->db();
    //     $qb = new \Pixie\QueryBuilder\QueryBuilderHandler($connection);

    //     $query = $qb->table('my_table')->where('name', '=', 'Sana');
    // }


    /**
     * Analize route and load proper controller
     * @return App
     */
    private function route()
    {
        // Initialize the router
        $router = new AltoRouter();
        $router->setBasePath(BASEPATH);

        // Load plugin/theme routes first
        // TODO: Update router.map in modules to App::addRoute();
        $GLOBALS["_ROUTER_"] = $router;
        \Event::trigger("router.map", "_ROUTER_");
        $router = $GLOBALS["_ROUTER_"];

        // Load internal routes
        //$this->addInternalRoutes();

        // Load global routes
        include APPPATH."/inc/routes.php";
        
        // Map the routes
        $router->addRoutes(App::getRoutes());

        // Match the route
        $match = $router->match();

        if ($match) {
            list($controllerName, $method) = explode('#', $match['target']);
            $controller = new $controllerName();

            if (method_exists($controller, $method)) {
                call_user_func_array([$controller, $method], $match['params']);
            } else {
                header("HTTP/1.0 404 Not Found");
                echo json_encode(['error' => 'Method not found']);
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['error' => 'Route not found',
                            'url' => $_SERVER['REQUEST_URI']]);
            
        }

        $this->controller = new $controller;
        $this->controller->setVariable("Route", $route);
    }

    private function auth()
    {
        $AuthUser = null;
        $headers = apache_request_headers();
        $Authorization = null;

        /**Step 2 - what is type of logging request */
        $keyword = "Doctor";//default
        if(isset($headers['type']))
        {
            $keyword = $headers['type'] ? $headers['type'] : "Doctor";
        }
        if(isset($headers['Type']))
        {
            $keyword = $headers['Type']  ? $headers['Type'] : "Doctor";
        }

        /**Step 3 - Is authorization passed with HTTP request ? */
        if(isset($headers['authorization']))
        {
            $Authorization = $headers['authorization'];
        }
        if(isset($headers['Authorization']))
        {
            $Authorization = $headers['Authorization'];
        }
        /**Step 4a - verify token */
        if(isset($Authorization))
        {
            $matches = array();
            preg_match('/JWT (.*)/', $Authorization, $matches);
    
            if(isset($matches[1])){
                $accessToken = $matches[1];
               
                try {
                    // $decoded = Firebase\JWT\JWT::decode($accessToken, EC_SALT, array('HS256'));
                    $decoded = Firebase\JWT\JWT::decode($accessToken, new Firebase\JWT\Key(EC_SALT, 'HS256'));
                    $AuthenticatedUser = Controller::Model($keyword, $decoded->id);
                    if( $keyword == "Doctor" && $AuthenticatedUser->get("active") != 1 )
                    {
                        return null;
                    }    

                    if (isset($decoded->hashPass) && 
                        $AuthenticatedUser->isAvailable() && 
                        md5($AuthenticatedUser->get("password")) == $decoded->hashPass){
                        $AuthUser = $AuthenticatedUser;
                    }
                } catch (\Exception $th) {
                    return $AuthUser;
                }
            }
        }

        /**Step 4b - if authorization does not set */
        if (Input::cookie("accessToken")) {
            try {
                //$decoded = Firebase\JWT\JWT::decode(Input::cookie("accessToken"), EC_SALT, array('HS256'));
                $decoded = Firebase\JWT\JWT::decode(Input::cookie("accessToken"), new Firebase\JWT\Key(EC_SALT, 'HS256'));
                $AuthenticatedUser = Controller::Model($keyword, $decoded->id);


                if( $keyword == "Doctor" && $AuthenticatedUser->get("active") != 1 )
                {
                    return null;
                }    

                if (isset($decoded->hashPass) && 
                    $AuthenticatedUser->isAvailable() && 
                    md5($AuthenticatedUser->get("password")) == $decoded->hashPass){
                    $AuthUser = $AuthenticatedUser;
                }
            } catch (\Exception $th) {
                return $AuthUser;
            }
            
        }
        return $AuthUser;
    }

    




   
    
    public function process(){

        $this -> db();

        //$AuthUser = $this->auth();
        $this->route();
        //$this->controller->setVariable("AuthUser", $AuthUser);

        

        
        
        

    }
    


    
}



