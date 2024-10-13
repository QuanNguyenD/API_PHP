<?php
//bắt đầu hoặc tiếp tục một phiên làm việc (session) giữa máy khách (client) và máy chủ (server).
session_start();
/*
// Neu khong dung define
$rootPath = dirname(__FILE__); // Lấy đường dẫn thư mục hiện tại
$appPath = $rootPath . "/app";  // Nối thêm '/app' để tạo đường dẫn đến thư mục 'app'

 VD: require_once $appPath . "/core/Autoloader.php"; // Bao gồm tệp Autoloader.php
*/

//Dung define 

//  define path to root directory of app
define("APPPATH",dirname(__FILE__));
// defife Path to app folder


//VD: require_once APPPATH./"autoload.php";


define("ENVIRONMENT", "production"); // [development|production|installation]

error_reporting(E_ALL);
if (ENVIRONMENT == "installation") {
    header("Location: ./install");
    exit;
} else if (ENVIRONMENT == "development") {
    ini_set('display_errors', 1);
} else if (ENVIRONMENT == "production") {
    ini_set('display_errors', 0);
} else {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Environment is invalid. Please contact developer for more information.';
    exit;
}

// Define Base Path (for routing)
$base_path = trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");
$base_path = $base_path ? "/" . $base_path : "";

define("BASEPATH", $base_path);
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once APPPATH.'/Controller/DrugController.php';
require_once APPPATH.'/Controller/LoginController.php';
require_once APPPATH.'/Controller/DrugsController.php';
require_once APPPATH.'/Controller/DoctorController.php';

require_once APPPATH.'/config/config.php';
require_once APPPATH. '/autoload.php';
require_once APPPATH.'/Core/App.php';
require_once APPPATH."/helper/helpers.php";




$app = new App();
$app ->process();





