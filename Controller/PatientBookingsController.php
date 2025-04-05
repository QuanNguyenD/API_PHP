<?php
require_once APPPATH.'/Model/BookingModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
    class PatientBookingsController extends Controller{
        public function process(){
            $jwt = null;
                $headers = getallheaders();
                if (isset($headers['Authorization'])) {
                    $jwt =$headers['Authorization'];
                }
                if (!$jwt && isset($_COOKIE['accessToken'])) {
                    $jwt = $_COOKIE['accessToken'];
                }
                if(!isset($jwt)){
                    header("Location: " . APPURL . "/login");
                    exit;
                }
                
            // if ($jwt) {
            //     try {
            //         $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            //         // Lưu thông tin người dùng vào biến hoặc session
            //         $_SESSION['AuthUser'] = $decoded; 
            //         //$jsonDecoded = json_encode($decoded, JSON_PRETTY_PRINT);
            //         //echo $jsonDecoded;
            //         //$userRole = $decoded->role;
            //         //echo($userRole);
                    
            //     } catch (Exception $e) {
            //         // Xử lý lỗi nếu token không hợp lệ
            //         echo json_encode(["message" => "Token is invalid or expired."]);
            //         exit;
            //     }
            // } else {
            //     // Nếu không có token
            //     header("Location: " . APPURL . "/login");
            //     exit;
            // }
            if( empty($decoded->role)== false )
            {
                $this->resp->result = 0;
                $this->resp->msg = "You are not logging with PATIENT account so that you are not allowed do this action !";
                $this->jsonecho();
            }
            $request_method = Input::method();
            
            if($request_method === 'GET')
            {
                
                $this->getAll();
            }
           
            else if( $request_method === 'POST')
            {
                
                $this->save();
            }
            
        }

        private function getAll(){
            $this->resp->result = 0;
            $headers = getallheaders();
            $jwt =$headers['Authorization'];
            $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            $id= $decoded->id;


            /**Step 2 - get filters */
            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 10;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;
            try{
            $bookingMd = new BookingModel();

            $query = $bookingMd->getAllBooking($id);
            $search_query = trim( (string)$search );
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TB_PREFIX.TB_SERVICES.".booking_name", 'LIKE', $search_query.'%')
                            ->orWhere(TB_PREFIX.TB_SERVICES.".name", 'LIKE', $search_query.'%')
                            ->orWhere(TB_PREFIX.TB_SERVICES.".appointment_time", 'LIKE', $search_query.'%')
                            ->orWhere(TB_PREFIX.TB_SERVICES.".reason", 'LIKE', $search_query.'%');
                    }); 
                }
                if( $order && isset($order["column"]) && isset($order["dir"]))
                {
                    $type = $order["dir"];
                    $validType = ["asc","desc"];
                    $sort =  in_array($type, $validType) ? $type : "desc";


                    $column_name = trim($order["column"]) != "" ? trim($order["column"]) : "id";
                    $column_name = str_replace(".", "_", $column_name);
                    $query->orderBy($column_name, $sort);
                    
                }
                else 
                {
                    $query->orderBy("id", "desc");
                }

                $query->limit($length ? $length : 10)
                    ->offset($start ? $start : 0);



                /**Step 4 */
                $result = $query->get();
                foreach($result as $element)
                {
                    $data[] = array(
                        "id" => (int)$element->id,
                        "patient_id" => (int)$element->patient_id,
                        "name" => $element->patient_name,
                        "gender" => (int)$element->patient_gender,
                        "birthday" => $element->patient_birthday,
                        "address" => $element->patient_address,
                        "appointment_time" => $element->appointment_hour,
                        "appointment_date" => $element->appointment_date,
                        "status" => $element->status,
                        "create_at" => $element->create_at,
                        "update_at" => $element->update_at,
                        "service" => array(
                            "id" => (int)$element->service_id,
                            "name" => $element->service_name,
                            "image" => $element->service_image
                        )
                    );
                }


                /**Step 5 - return */
                $this->resp->result = 1;
                $this->resp->quantity = count($result);
                $this->resp->data = $data;

            }catch(Exception $ex)
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }

        private function save(){
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");
            $headers = apache_request_headers();
            //$jwt ='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTMsImVtYWlsIjoiIiwicGhvbmUiOiIwMTIzNDU2NzkxIiwibmFtZSI6IjAxMjM0NTY3OTEiLCJnZW5kZXIiOjAsImJpcnRoZGF5IjoiIiwiYWRkcmVzcyI6IiIsImF2YXRhciI6IiIsImNyZWF0ZV9hdCI6IjIwMjQtMTEtMjMgMDM6MDg6MDciLCJ1cGRhdGVfYXQiOiIyMDI0LTExLTIzIDAzOjA4OjA3IiwiaGFzaFBhc3MiOiI1NGZhMzgwYzNmOWY5ZTQ0MGVkNTcyZmVkOTUwYTk2OCIsImlhdCI6MTczMjMzMTI4N30.PRhI51Pl5uZvQPkbpfunQuHAv8vDKj5tjp_mWPCq3F4';
            $Authorization = null;
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $Authorization =$headers['Authorization'];
            }
            if (isset($headers['authorization'])) {
                $Authorization =$headers['authorization'];
            }
            //echo($Authorization);
            //$matches = array();
            //preg_match('/JWT (.*)/', $Authorization, $matches);
            //Atention
            //$jwt = $matches[1];
            
            //$decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));

            $decoded = JWT::decode($Authorization, new Key(EC_SALT, 'HS256'));


            //echo($jwt);
            $data = [];
            //echo($decoded->id);

            /**Step 2 - get required data */
            $required_fields = [ "appointment_time", "appointment_date"];
            foreach($required_fields as $field)
            {
                if( !Input::post($field) )
                {
                    $this->resp->msg = "Missing field: ".$field;
                    $this->jsonecho();
                }
            }
            $patient_id = $decoded->id;
            //$doctor_id = (int)Input::post("doctor_id");
            $service_id = Input::post("service_id") != null ? (int)Input::post("service_id") : 1;
            $name = $decoded->name;
            $phone = $decoded->phone;
            $gender = $decoded->gender;
            $birthday = $decoded->birthday;
            $address = $decoded->address;


            $appointment_time = Input::post("appointment_time");
            $appointment_date = Input::post("appointment_date");
            $status = "processing";

            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $create_at = date("Y-m-d H:i:s");
            $update_at = date("Y-m-d H:i:s");

            if( $service_id == 0 /*&& $doctor_id == 0*/)
            {
                $this->resp->msg = "Bạn cần chọn bác sĩ hoặc nhu cầu khám bệnh để tạo lịch hẹn !";
                $this->jsonecho();
            }
            $Service = Controller::model("Service", $service_id);
            if( !$Service->isAvailable() )
            {
                $this->resp->msg = "Service is not available";
                $this->jsonecho();
            }
            $BookingMd = new BookingModel();
            $query = $BookingMd->addBooking($patient_id,$appointment_date,$service_id);
            $result = $query->get();
            if( count($result) > 0)
            {
                $this->resp->msg = "Bạn đã có lịch hẹn với nhu cầu khám ".$Service->get("name")." rồi !";
                $this->jsonecho();
            }

            // if($doctor_id > 0)
            // {
            //     $Doctor = Controller::model("Doctor", $doctor_id);
            //     if( !$Doctor->isAvailable() )
            //     {
            //         $this->resp->msg = "Doctor is not available";
            //         $this->jsonecho();
            //     }
            // }

            // $input = $appointment_date." ".$appointment_time;
            // $output = isAppointmentTimeValid($input);
            // if( !empty($output) )
            // {
            //     $this->resp->msg = $output;
            //     $this->jsonecho();
            // }

            $valid_status = ["processing", "verified", "cancelled"];
            $status_validation = in_array($status, $valid_status);
            if( !$status_validation )
            {
                $this->resp->msg = "Status value is not valid. There are "
                                    .count($valid_status)
                                    ." values accepted: ".implode(', ',$valid_status);
                $this->jsonecho();
            }
            
            try 
            {
                
                $Booking = Controller::model("Booking");
                $Patient = Controller::model("Patient", $patient_id);
                $Booking-> set("service_id", $service_id)
                    ->set("patient_id", $patient_id)
                    // ->set("booking_name", $booking_name)
                    // ->set("booking_phone", $booking_phone)
                    // ->set("name", $name)
                    // ->set("gender", $gender)
                    // ->set("birthday", $birthday)
                    // ->set("address", $address)
                    // ->set("reason", $reason)
                    ->set("appointment_time", $appointment_time)
                    ->set("appointment_date", $appointment_date)
                    ->set("status", $status)
                    ->set("create_at", $create_at)
                    ->set("update_at", $update_at)
                    ->save();
                

                // $Notification = Controller::model("Notification");

                // $serviceName = $Service->get("name");

                // $notificationMessage = "Chúc mừng bạn! Lịch hẹn khám ".$serviceName." lúc ".$appointment_time." ngày ".$appointment_date." đã được tạo thành công!";
                // $Notification->set("message", $notificationMessage)
                //         ->set("record_id", $Booking->get("id") )
                //         ->set("record_type", "booking")
                //         ->set("is_read", 0)
                //         ->set("patient_id", $AuthUser->get("id"))
                //         ->set("create_at", $create_at)
                //         ->set("update_at", $update_at)
                //         ->save();


                $this->resp->result = 1;
                $this->resp->msg = "Congratulation, ".$name."! This booking at "
                                    .$Booking->get("appointment_time")
                                    ." which has been created successfully by you. ";
                $this->resp->data = array(
                    "id" => (int)$Booking->get("id"),
                    // "doctor_id" => (int)$Booking->get("doctor_id"),
                    // "booking_name" => $Booking->get("booking_name"),
                    // "booking_phone" => $Booking->get("booking_phone"),
                    "name" =>$Patient->get("name"),
                    "gender"=>(int)$Patient->get("gender"),
                    "birthday"=>$Patient->get("birthday"),
                    // "name" => $Booking->get("name"),
                    // "gender" => (int)$Booking->get("gender"),
                    // "birthday" => $Booking->get("birthday"),
                    // "address" => $Booking->get("address"),
                    // "reason" => $Booking->get("reason"),
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