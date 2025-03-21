<?php
require_once APPPATH.'/Model/DrugModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class DrugsController extends Controller{
    public function process()
        {
            $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
            
        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
                // Lưu thông tin người dùng vào biến hoặc session
                $_SESSION['AuthUser'] = $decoded; 
                //$jsonDecoded = json_encode($decoded, JSON_PRETTY_PRINT);
                //echo $jsonDecoded;
                //$userRole = $decoded->role;
                //echo($userRole);
                
            } catch (Exception $e) {
                // Xử lý lỗi nếu token không hợp lệ
                echo json_encode(["message" => "Token is invalid or expired."]);
                exit;
            }
        } else {
            // Nếu không có token
            header("Location: " . APPURL . "/login");
            exit;
        }
        

        $request_method = Input::method();
        
        if($request_method === 'GET')
        {
            if($decoded->role != "admin" && $decoded->role != "supporter" && $decoded->role != "menber"){
                $this->resp->result = 0;
                $this->resp->msg= "you are not admin you can not do this action";
                $this->jsonecho();
            }
            $this->getAllDrugs();

            
        }
        else if( $request_method === 'POST')
        {
            if($decoded->role != "admin" && $decoded->role != "supporter" && $decoded->role != "menber"){
                $this->resp->result = 0;
                $this->resp->msg= "you are not admin you can not do this action";
                $this->jsonecho();
            }
            //$this->newFlow();
        }
    }

    public function getAllDrugs(){

        $this->resp->result = 0;
        $data = [];

        try{

            $drModel = new DrugModel();
            $dreg = $drModel -> getAllDrugs();
            



        }catch(Exception $ex){
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();  




        // $drugModel = new DrugModel(); // Khởi tạo model Drug
        // $drugs = $drugModel->getAllDrugs();  // Lấy tất cả các thuốc từ model
        
        // if(!empty($drugs)) {
        //     // Nếu có, trả về dữ liệu dưới dạng JSON
        //     echo json_encode($drugs);
        // } else {
        //     // Nếu không có dữ liệu, trả về thông báo lỗi
        //     echo json_encode(["message" => "No drugs found."]);
        // }
    }





}




?>