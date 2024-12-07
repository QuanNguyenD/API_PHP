<?php
require_once APPPATH.'/Model/AppointmentModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class AppointmentController extends Controller{
    public function process($id = null)
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
                $userRole = $decoded->id;
                //echo($userRole);
                //self::$sharedVariable = $userRole;
                
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
        $valid_roles = ["admin", "supporter", "member"];
        $role_validation = in_array($decoded->role, $valid_roles);
        ////
        if( !$decoded->role || !$role_validation )
        {
            $this->resp->result = 0;
            $this->resp->msg = "You do not have permission to do this action !";
            $this->jsonecho();
        }

        $request_method = Input::method();

        if($request_method === 'GET')
            {
                
                if ($id !== null) {
                    //$this->getById($id); // Truyền $id vào phương thức getById
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
                
            }
        else if( $request_method === 'PUT')
        {
            
            if ($id !== null) {
                //$this->update($id); // Truyền $id vào phương thức getById
            } else {
                echo json_encode(["message" => "ID is required"]);
            }
        }
        elseif($request_method ==='DELETE'){
            
        }

    }




}



?>