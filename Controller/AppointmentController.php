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
                    $this->getById($id); // Truyền $id vào phương thức getById
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
                
            }
        else if( $request_method === 'PUT')
        {
            
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

        try 
            {
                $Appointment = Controller::model("Appointment", $id);
                if( !$Appointment->isAvailable() )
                {
                    $this->resp->msg = "Appointment is not available";
                    $this->jsonecho();
                }

                $Doctor = Controller::model("Doctor", $Appointment->get("doctor_id"));
                $Speciality = Controller::model("Speciality", $Doctor->get("speciality_id"));
                $Room = Controller::model("Room", $Doctor->get("room_id"));
                $Patient = Controller::model("Patient", $Appointment->get("patient_id"));

                $this->resp->result = 1;
                $this->resp->msg = "Action successfully !";
                $this->resp->data = array(
                    "id" => (int)$Appointment->get("id"),
                    "date" => $Appointment->get("date"),
                    "numerical_order" => (int)$Appointment->get("numerical_order"),
                    //"position" => (int) $Appointment->get("position"),
                    "patient_id" => (int)$Patient->get("id"),
                    "patient_name" => $Patient->get("name"),
                    "patient_phone" => $Patient->get("phone"),
                    "patient_birthday" => $Patient->get("birthday"),
                    // "patient_reason" => $Appointment->get("patient_reason"),
                    // "patient_phone" => $Appointment->get("patient_phone"),
                    "appointment_time" => $Appointment->get("appointment_time"),
                    "status" => $Appointment->get("status"),
                    "create_at" => $Appointment->get("create_at"),
                    "update_at" => $Appointment->get("update_at"),
                    "doctor" => array(
                        "id" => (int)$Doctor->get("id"),
                        "name" => $Doctor->get("name"),
                        "avatar" => $Doctor->get("avatar")
                    ),
                    "speciality" => array(
                        "id" => (int)$Speciality->get("id"),
                        "name" => $Speciality->get("name"),
                        "image" => $Speciality->get("image"),
                        "description" => $Speciality->get("description")
                    ),
                    "room" => array(
                        "id" => (int) $Room->get("id"),
                        "name" => $Room->get("name"),
                        "location" => $Room->get("location")
                    )
                );
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();


    }
    private function update($id)
    {
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
        $AuthUser = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        ////////
        $this->resp->result = 0;
        
        $Route = $this->getVariable("Route");
        $today = (String)Date("Y-m-d");
        

        $Appointment = Controller::model("Appointment", $id);
        $Patient = Controller::model("Patient", $Appointment->get("patient_id"));
        if( !$Appointment->isAvailable() )
        {
            $this->resp->msg = "Appointment is not available";
            $this->jsonecho();
        }

        $invalid_status = ["cancelled", "done"];
        $status_validation = in_array($Appointment->get("status"), $invalid_status);
        if( $status_validation )
        {
            $this->resp->msg = "Appointment's status is ".$Appointment->get("status")." ! You can't do this action !";
            $this->jsonecho();
        }

        $appointment_date = $Appointment->get("date");
            
        $difference = abs(strtotime($today) - strtotime($appointment_date));
        $differenceYear = floor($difference / (365*60*60*24));
        $differenceMonth = floor(($difference - $differenceYear * 365*60*60*24) / (30*60*60*24));
        $differenceDay = floor(($difference - $differenceYear * 365*60*60*24 - $differenceMonth*30*60*60*24)/ (60*60*24));

        if( $differenceDay > 0 )
        {
            $this->resp->msg = "Today is ".$today." but this appointment's is ".$appointment_date." so that you can not do this action";
            $this->jsonecho();
        }

        $required_fields = ["doctor_id", "patient_id"];
        foreach($required_fields as $field)
        {
            if( !Input::put($field) )
            {
                $this->resp->msg = "Missing field: ".$field;
                $this->jsonecho();
            }
        }


        //getdata 
        $doctor_id = Input::put("doctor_id");
        $patient_id = Input::put("patient_id");

        //$patient_name = Input::put("patient_name");
        $patient_birthday = Input::put("patient_birthday");

        $patient_reason = Input::put("patient_reason");
        $patient_phone = Input::put("patient_phone");

        // $numerical_order = "";
        $appointment_time = Input::put("appointment_time") ? Input::put("appointment_time") : "";

        $status = Input::put("status");// default
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $create_at = date("Y-m-d H:i:s");
        $update_at = date("Y-m-d H:i:s");

        $Doctor = Controller::model("Doctor", $doctor_id);
        if( !$Doctor->isAvailable() )
        {
            $this->resp->msg = "Doctor is not available";
            $this->jsonecho();
        }

        $Patient = Controller::model("Patient", $patient_id);
        if( !$Patient->isAvailable() )
        {
            $this->resp->msg = "Patient is not available";
            $this->jsonecho();
        }

        

        if( $patient_birthday )
        {
            $msg = isBirthdayValid($patient_birthday);
            if( !empty($msg) )
            {
                $this->resp->msg = $msg;
                $this->jsonecho();
            }
        }

        if( $patient_phone )
        {
            if( strlen($patient_phone) < 10 )
            {
                $this->resp->msg = "Patient phone number has at least 10 number !";
                $this->jsonecho();
            }
    
            $patient_phone_validation = isNumber($patient_phone);
            if( !$patient_phone_validation )
            {
                $this->resp->msg = "Patient phone number is not a valid phone number. Please, try again !";
                $this->jsonecho();
            }
        }

        if( !empty($appointment_time) )
        {
            $msg = isAppointmentTimeValid($appointment_time);
            if( !empty($msg) )
            {
                $this->resp->msg = $msg;
                $this->jsonecho();
            }

            $date = substr($appointment_time, 0,10);
        }
        $valid_status = ["admin", "supporter"];
        $status_validation = in_array($AuthUser->role, $valid_status);
        if( $status_validation )
        {
            $Appointment->set("doctor_id", $doctor_id);
        }
        try 
            {
                //$Appointment = Controller::model("Appointment");
                $Appointment->set("patient_id", $patient_id)
                        ->set("appointment_time", $appointment_time)
                        ->set("update_at", $update_at)
                        ->save();


                $this->resp->result = 1;
                $this->resp->msg = "Appointment has been updated successfully !";
                $this->resp->data = array(
                    "id" => (int) $Appointment->get("id"),
                    "numerical_order" =>  (int)$Appointment->get("numerical_order"),
                    "date"          => $Appointment->get("date"),
                    "doctor_id" => (int) $Appointment->get("doctor_id"),
                    "patient_id" => (int) $Appointment->get("patient_id"),
                    "appointment_time" => $Appointment->get("appointment_time"),
                    "status" =>  $Appointment->get("status"),
                    "create_at" =>  $Appointment->get("create_at"),
                    "update_at" =>  $Appointment->get("update_at")
                );
            } 
            catch (\Exception $ex) 
            {
                $this->resp->result = $ex->getMessage();
            }
            $this->jsonecho();







    }




}



?>