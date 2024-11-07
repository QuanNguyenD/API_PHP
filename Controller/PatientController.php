<?php
require_once APPPATH.'/Model/PatientModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

    class PatientController extends Controller{
        public function process(){
            $AuthUser = $this->getVariable("AuthUser");
            $jwt = null;
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $jwt =$headers['Authorization'];
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
                $this->resp->msg = "You are not logging !";
                header("Location: " . APPURL . "/login");
                exit;
            }
            
            $request_method = Input::method();
            if($request_method === 'GET')
            {
                if($decoded->role !="admin"){
                    $this->resp->msg = "You are not admin & you can't do this action !";
                    $this->jsonecho();
                }
                //$this->getAll();
            }
            else if( $request_method === 'POST')
            {
                //$this->save();
            }

        }

        private function getById(){
            
        }



        
    }




?>