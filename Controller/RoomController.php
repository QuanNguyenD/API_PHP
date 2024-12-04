<?php
require_once APPPATH.'/Model/RoomModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class RoomController extends Controller{
    private $id;
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

        $request_method = Input::method();

        if($request_method === 'GET')
            {
                if($decoded->role !="admin"){
                    $this->resp->msg = "You are not admin & you can't do this action !";
                    $this->jsonecho();
                }
                if ($id !== null) {
                    $this->getById($id); // Truyền $id vào phương thức getById
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
                
            }
        else if( $request_method === 'PUT')
        {
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
            if ($id !== null) {
                $this->update($id); // Truyền $id vào phương thức getById
            } else {
                echo json_encode(["message" => "ID is required"]);
            }
        }
        elseif($request_method ==='DELETE'){
            
        }

    }

    private function getById($id){
        
        $this->resp->result = 0;
        $AuthUser = $this->getVariable("AuthUser");
        $Route = $this->getVariable("Route");

        try
            {
                $Room = Controller::model("Room", $id);
                if( !$Room->isAvailable() )
                {
                    $this->resp->msg = "Room is not available";
                    $this->jsonecho();
                }



                $this->resp->result = 1;
                $this->resp->msg = "Action successfully !";
                $this->resp->data = array(
                    "id" => (int)$Room->get("id"),
                    "name" => $Room->get("name"),
                    "location" => $Room->get("location")
                );
            }
            catch(Exception $ex)
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
    }

    private function update($id){
        $this->resp->result = 0;
        $AuthUser = $this->getVariable("AuthUser");
        $Route = $this->getVariable("Route");

        $required_fields = ["name", "location"];
        foreach( $required_fields as $field)
        {
            if( !Input::put($field) )
            {
                $this->resp->msg = "Missing field: ".$field;
                $this->jsonecho();
            }
        }

        $name = Input::put("name");
        $location = Input::put("location");



    }



}



?>