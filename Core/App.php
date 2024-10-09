<?php

use Gettext\Loader\PoLoader;
use Gettext\Translations;
class App{

    protected $router;
    protected $controller;
    protected $plugins;

    
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














    private function i18n()
    {   
        $Route = $this->controller->getVariable("Route");
        $AuthUser = $this->controller->getVariable("AuthUser");
        $IpInfo = $this->controller->getVariable("IpInfo");

        if ($AuthUser) {
            // Get saved lang code for authorized user.
            $lang = $AuthUser->get("preferences.language");
        } else if (isset($Route->params->lang)) {
            // Direct link or language change
            // Getting lang from route
            $lang = $Route->params->lang;
        } else if (Input::cookie("lang")) {
            // Returninn user (non-auth),
            // Getting lang. from the cookie
            $lang = Input::cookie("lang");
        } else {
            // New user
            // Getting lang. from ip-info
            $lang = Config::get("default_applang");

            if ($IpInfo->languages) {
                foreach ($IpInfo->languages as $l) {
                    foreach (Config::get("applangs") as $al) {
                        if ($al["code"] == $l || $al["shortcode"] == $l) {
                            // found, break loops
                            $lang = $al["code"];
                            break 2;
                        }
                    }
                }
            }
        }


        // Validate found language code
        $active_lang = Config::get("default_applang");
        foreach (Config::get("applangs") as $al) {
            if ($al["code"] == $lang || $al["shortcode"] == $lang) {
                // found, break loop
                $active_lang = $al["code"];
                break;
            }
        }

        define("ACTIVE_LANG", $active_lang);
        @setcookie("lang", ACTIVE_LANG, time()+30 * 86400, "/");

        
        
        
        $loader = new PoLoader();
        // Load app. locale
        $path = APPPATH . "/locale/" . ACTIVE_LANG . "/messages.po";
        if (file_exists($path)) {
            $translations = $loader -> loadFile($path);
            
        }
        //Cái này phải coi lại
        // Load theme locale
        // $path = active_theme("path") . "/locale/" . ACTIVE_LANG . "/messages.po";
        // if (file_exists($path)) {
        //     $translations = Gettext\Translations::fromPoFile($path);
        //     $Translator->loadTranslations($translations);
        // }

        

        //$translations->register(); // Register global functions

        // Set other library locales
        try {
            \Moment\Moment::setLocale(str_replace("-", "_", ACTIVE_LANG));
        } catch (Exception $e) {
            // Couldn't load locale
            // There is nothing to do here,
            // Fallback to default language
        }
    }
    public function process(){

        $this -> db();


    }
    


    
}



