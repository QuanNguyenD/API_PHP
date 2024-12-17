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
            else if( $request_method === 'PUT')
            {
                $this->update($id);
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
        private function update($id){
            $id = $this->id;
            $jwt = null;
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $jwt =$headers['Authorization'];
            }
            if (!$jwt && isset($_COOKIE['accessToken'])) {
                $jwt = $_COOKIE['accessToken'];
            }
            $AuthUser = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            $this->resp->result = 0;
            $Booking = Controller::model("Booking", $id);
            $Patient = Controller::model("Patient", $Booking->get("patient_id"));
            if( !$Booking->isAvailable() )
            {
                $this->resp->msg = "This booking does not exist !";
                $this->jsonecho();
            }
            $valid_roles = ["processing","verified"];
            $role_validation = in_array($Booking->get("status"), $valid_roles);
            if( !$role_validation )
            {
                $this->resp->result = 0;
                $this->resp->msg = "You don't have permission to do this action. Only booking's status is "
                .implode(', ', $valid_roles)." can do this action !";
                $this->jsonecho();
            }
            $required_fields = ["service_id",
                                "name", "appointment_time", "appointment_date"];
            foreach($required_fields as $field)
            {
                if( !Input::put($field) )
                {
                    $this->resp->msg = "Missing field: ".$field;
                    $this->jsonecho();
                }
            }

            $service_id = Input::put("service_id");
            $booking_name = Input::put("booking_name");

            $booking_phone = Input::put("booking_phone");
            $name = Input::put("name");

            $gender = Input::put("gender") ? Input::put("gender") : 0;
            $birthday = Input::put("birthday");

            $address = Input::put("address");
            $reason = Input::put("reason");

            $appointment_time = Input::put("appointment_time");
            $appointment_date = Input::put("appointment_date");
            $status = $Booking->get("status");

            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $update_at = date("Y-m-d H:i:s");

            $Service = Controller::model("Service", $service_id);
            if( !$Service->isAvailable() )
            {
                $this->resp->msg = "Service is not available";
                $this->jsonecho();
            }

            
    
            

            /**Step 4.4 - Name */
            $name_validation = isVietnameseName($name);
            if( $name_validation == 0 ){
                $this->resp->msg = "( Name ) Vietnamese name only has letters and space";
                $this->jsonecho();
            }

            /**Step 4.5 - Gender */
            $valid_gender = [ 0,1 ];
            $gender_validation = in_array($gender, $valid_gender);
            if( !$gender_validation )
            {
                $this->resp->msg = "Gender is not valid. There are 2 values: 0 is female & 1 is men";
                $this->jsonecho();
            }
            if( $birthday )
            {
                $msg = isBirthdayValid($birthday);
                if( !empty($msg) )
                {
                    $this->resp->msg = $msg;
                    $this->jsonecho();
                }
            }
            
            $input = $appointment_date." ".$appointment_time;
            $output = isAppointmentTimeValid($input);
            if( !empty($output) )
            {
                $this->resp->msg = $output;
                $this->jsonecho();
            }
            $valid_status = ["processing"];
            $status_validation = in_array($status, $valid_status);
            if( !$status_validation )
            {
                $this->resp->msg = "Booking's status is ".$Booking->get("status")." now. Booking is only updated when its status: "
                                    .implode(', ',$valid_status)." !";
                $this->jsonecho();
            }

            try 
            {
                $Booking->set("service_id", $service_id)
                    ->set("booking_name", $booking_name)
                    ->set("booking_phone", $booking_phone)
                    // ->set("name", $name)
                    // ->set("gender", $gender)
                    // ->set("birthday", $birthday)
                    // ->set("address", $address)
                    // ->set("reason", $reason)
                    ->set("appointment_time", $appointment_time)
                    ->set("appointment_date", $appointment_date)
                    ->set("status", $status)
                    ->set("update_at", $update_at)
                    ->save();
                $Patient->set("name", $name)
                ->set("gender",$gender)
                ->set("birthday",$birthday )
                ->set("address", $address)
                ->save();
                
                $this->resp->result = 1;
                $this->resp->msg = "Congratulation, doctor ".$AuthUser->name."! Your booking at "
                                    .$Booking->get("appointment_time")
                                    ." which has been created successfully.";
                $this->resp->data = array(
                    "id" => (int)$Booking->get("id"),
                    "patient_id" => (int)$Booking->get("patient_id"),
                    // "booking_name" => $Booking->get("booking_name"),
                    // "booking_phone" => $Booking->get("booking_phone"),
                    "name" => $Patient->get("name"),
                    "gender" => (int)$Patient->get("gender"),
                    "birthday" => $Patient->get("birthday"),
                    "address" => $Patient->get("address"),
                    
                    "appointment_time" => $Booking->get("appointment_time"),
                    "appointment_date" => $Booking->get("appointment_date"),
                    "status" => $Booking->get("status"),
                    "create_at" => $Booking->get("create_at"),
                    "update_at" => $Booking->get("update_at"),
                    "service" => array(
                        "id"=> (int)$Service->get("id"),
                        "name"=>$Service->get("name")
                    )
                );
            } 
            catch (\Exception $ex) {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();









        }


    }


?>