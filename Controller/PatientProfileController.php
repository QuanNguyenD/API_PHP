<?php
require_once APPPATH.'/Model/PatientModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class PatientProfileController extends Controller{
    public function process(){
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
        if( empty($decoded->role)== false )
        {
            $this->resp->result = 0;
            $this->resp->msg = "You are not logging with PATIENT account so that you are not allowed do this action !";
            $this->jsonecho();
        }
        $request_method = Input::method();
        
        if($request_method === 'GET')
        {
            

            $this->getInformation();
        }
       
        else if( $request_method === 'POST')
        {
            
            $action = Input::post("action");
                switch ($action) {
                    case "personal":
                        //$this->changeInformation();
                        break;
                    case "password":
                        //$this->changePassword();
                        break;
                    case "avatar":
                        //$this->changeAvatar();
                        break;
                    default:
                        $this->resp->result = 0;
                        $this->resp->msg = "Your action is ".$action." & it's not valid. There are valid actions: personal, password & avatar ";
                        $this->jsonecho();
                }
        }
        
    }

    private function getInformation(){
        $this->resp->result = 0;
        ////////
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
        ///////////////
        $AuthUser = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        if( !$AuthUser )
        {
            $this->resp->msg = "There is no authenticated user !";
            $this->jsonecho();
        }
        $this->resp->result = 1;
            $this->resp->msg = "Action successfully !";
            $this->resp->data = array(
                "id" => (int)$AuthUser->id,
                "name" => $AuthUser->name,
                "gender" => (int)$AuthUser->gender,
                "phone" => $AuthUser->phone,
                "email" => $AuthUser->email,
                "birthday" => $AuthUser->birthday,
                "address" => $AuthUser->address,
                "avatar" => $AuthUser->avatar,
                "create_at" => $AuthUser->create_at,
                "update_at" => $AuthUser->update_at
            );

        $this->jsonecho();



    }






}




?>