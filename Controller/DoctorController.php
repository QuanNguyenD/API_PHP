<?php
require_once APPPATH.'/Model/DoctorModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

class DoctorController extends Controller{
    public function process($id = null)
    {
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
            header("Location: " . APPURL . "/login");
            exit;
        }

        $request_method = Input::method();
        
        if($request_method === 'GET')
        {
            if ($id !== null) {
                $this->getById($id); // Truyền $id vào phương thức getById
            } else {
                echo json_encode(["message" => "ID is required"]);
            }
        }
        else if( $request_method === 'POST')
        {
            
        }
        elseif($request_method ==='DELETE'){
            
        }

    }

    private function getById($id){
        try{
            $this->resp->result = 0;
            $DoctorModel = new DoctorModel();
            $query = $DoctorModel->getDocById($id);
            
            if( empty($query) )
            {
                $this->resp->msg = "Doctor is not available";
                $this->jsonecho();
            }
            $result = $query;
                if( count($result) > 1 )
                {
                    $this->resp->msg = "Oops, there is an error occurring. Try again !";
                    $this->jsonecho();
                }
                $data = array(
                    "id" => (int)$result[0]->id,
                    "email" => $result[0]->email,
                    "phone" => $result[0]->phone,
                    "name" => $result[0]->name,
                    "description" => $result[0]->description,
                    "price" => (int)$result[0]->price,
                    "role" => $result[0]->role,
                    "avatar" => $result[0]->avatar,
                    "active" => (int)$result[0]->active,
                    "create_at" => $result[0]->create_at,
                    "update_at" => $result[0]->update_at,
                    "speciality" => array(
                        "id" => (int)$result[0]->speciality_id,
                        "name" => $result[0]->speciality_name,
                        "description" => $result[0]->speciality_description
                    ),
                    "room" => array(
                        "id" => (int)$result[0]->room_id,
                        "name" => $result[0]->room_name,
                        "location" => $result[0]->room_location
                    ),
                );

                $this->resp->result = 1;
                $this->resp->msg = "Action successfully !";
                $this->resp->data = $data;
            
        }catch(Exception $ex){
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();
    }

    

    




}




?>