<?php
require_once APPPATH.'/Model/BookingModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
    class BookingController extends Controller{
        private $id;
        public function process($id = null){
            $this->id = $id;
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
                if($decoded->role != "admin" && $decoded->role != "supporter"){
                    $this->resp->msg= "you are not admin & you can not do this action";
                    $this->jsonecho();
                }
                if ($id !== null) {
                    $this->getById($id);
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
            }
            
            elseif($request_method ==='DELETE'){
                if ($id !== null) {
                    //$this->delete($id);
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
                
            }
            


        }

    private function getById($id){
            $this->resp->result = 0;

        $Booking = Controller::model("Booking", $id);
        if( !$Booking->isAvailable() )
        {
            $this->resp->msg = "Booking does not exist";
            $this->jsonecho();
        }
        $headers = getallheaders();
        $jwt =$headers['Authorization'];
        $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        $idPatient= $decoded->id;
        
        if( $Booking->get("patient_id") != $idPatient )
        {
            $this->resp->msg = "This booking is not available";
            $this->jsonecho();
        }


        $Service = Controller::model("Service", $Booking->get("service_id"));
        $Patient = Controller::model("Patient", $Booking->get("patient_id"));
        if( !$Service->isAvailable() )
        {
            $this->resp->msg = "Service does not exist";
            $this->jsonecho();
        }

        /**Step 4 - return */
        try
        {

            $this->resp->result = 1;
            $this->resp->msg = "Action successfully !";
            $this->resp->data = array(
                "id" => (int)$Booking->get("id"),
                "patient_id" => (int)$Booking->get("patient_id"),
                // "booking_name" => $Booking->get("booking_name"),
                // "booking_phone" => $Booking->get("booking_phone"),
                "name" =>$Patient->get("name"),
                "gender"=>$Patient->get("gender"),
                "birthday"=>$Patient->get("birthday"),
                // "name" => $Booking->get("name"),
                // "gender" => (int)$Booking->get("gender"),
                // "birthday" => $Booking->get("birthday"),
                // "address" => $Booking->get("address"),
                // "reason" => $Booking->get("reason"),
                "appointment_date" => $Booking->get("appointment_date"),
                "appointment_time" => $Booking->get("appointment_time"),
                "status" => $Booking->get("status"),
                "create_at" => $Booking->get("create_at"),
                "update_at" => $Booking->get("update_at"),
                "service" => array(
                    "id" => (int)$Service->get("id"),
                    "name" => $Service->get("name"),
                    "image" => $Service->get("image")
                )
            );
        }
        catch(Exception $ex)
        {
            $this->resp->msg = $ex->getMessage();
        }
            $this->jsonecho();


        }


    }


?>