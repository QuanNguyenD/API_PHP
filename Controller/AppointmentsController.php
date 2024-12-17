<?php
require_once APPPATH.'/Model/AppointmentModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class AppointmentsController extends Controller{
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
        

        $request_method = Input::method();
        
        if($request_method === 'GET')
        {
            if($decoded->role != "admin" && $decoded->role != "supporter" && $decoded->role != "menber"){
                $this->resp->result = 0;
                $this->resp->msg= "you are not admin you can not do this action";
                $this->jsonecho();
            }
            $this->getAll();

            
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

    private function getAll(){
        $this->resp->result = 0;
        $data = [];

        $order          = Input::get("order");
        $search         = Input::get("search");
        $length         = Input::get("length") ? (int)Input::get("length") : 5;
        $start          = Input::get("start") ? (int)Input::get("start") : 0;
        $doctor_id         = (int)Input::get("doctor_id");// Only ADMIN & SUPPORTER can use this filter.
        $room_id           = (int)Input::get("room_id");// Only ADMIN & SUPPORTER can use this filter.
        $date           = Input::get("date");
        $status         = Input::get("status");
        $speciality_id     = (int)Input::get("speciality_id");
        $start          = Input::get("start");

        try{
            $appointmentModel = new AppointmentModel();
            $query = $appointmentModel->getAll();
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
            $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            $valid_roles = ["admin", "supporter"];
            $role_validation = in_array($decoded->role, $valid_roles);

            if( !$role_validation )
            {
                $query->where(TB_PREFIX.TB_APPOINTMENTS.".doctor_id", "=", $decoded->id);
            }

            $search_query = trim( (string)$search );
            if($search_query){
                $query->where(function($q) use($search_query)
                {
                    $q->where(TB_PREFIX.TB_APPOINTMENTS.".patient_name", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".patient_phone", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".patient_reason", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".status", 'LIKE', $search_query.'%');
                }); 
            }

            $search_query = trim( (string)$search );
            if($search_query){
                $query->where(function($q) use($search_query)
                {
                    $q->where(TB_PREFIX.TB_APPOINTMENTS.".patient_name", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".patient_phone", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".patient_reason", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".status", 'LIKE', $search_query.'%');
                }); 
            }

            $valid_roles = ["admin", "supporter"];
            $role_validation = in_array($decoded->role, $valid_roles);
            if( $doctor_id && $role_validation )
            {
                $query->where(TB_PREFIX.TB_APPOINTMENTS.".doctor_id", "=", $doctor_id);
            }

            if( $date )
            {
                $query->where(TB_PREFIX.TB_APPOINTMENTS.".date", "=", $date);
            }

           
            if( $room_id )
            {
                $query->where(TB_PREFIX.TB_DOCTORS.".room_id", "=", $room_id);
            }

            if( $speciality_id )
            {
                $query->where(TB_PREFIX.TB_SPECIALITIES.".id", "=", $speciality_id);
            }

            $valid_status = ["processing", "done", "cancelled"];
            $status_validation = in_array($status, $valid_status);
            if( $status && $status_validation )
            {
                $query->where(TB_PREFIX.TB_APPOINTMENTS.".status", "=", $status);
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
            $res = $query->get();
            $quantity = count($res);

            $query->limit($length ? $length : 5)
                    ->offset($start ? $start : 0);

                    $result = $query->get();
                    foreach($result as $element)
                    {
                        $data[] = array(
                            "id" => (int)$element->id,
                            "date" => $element->date,
                            "booking_id" => (int)$element->booking_id,
                            "numerical_order" => (int)$element->numerical_order,
                            "position" => (int)$element->position,
                            "patient_id" => (int)$element->patient_id,
                            "patient_name" => $element->patient_name,
                            "patient_phone" => $element->patient_phone,
                            "patient_birthday" => $element->patient_birthday,
                            // "patient_reason" => $element->patient_reason,
                            // "patient_phone" => $element->patient_phone,
                            "appointment_time" => $element->appointment_time,
                            "status" => $element->status,
                            "create_at" => $element->create_at,
                            "update_at" => $element->update_at,
                            "doctor" => array(
                                "id" => (int)$element->doctor_id,
                                "email" => $element->doctor_email,
                                "phone" => $element->doctor_phone,
                                "name" => $element->doctor_name,
                                "description" => $element->doctor_description,
                                "price" => $element->doctor_price,
                                "role" => $element->doctor_role,
                                "avatar" => $element->doctor_avatar,
                                "active" => (int)$element->doctor_active,
                                "create_at" => $element->doctor_create_at,
                                "update_at" => $element->doctor_update_at,
                            ),
                            "speciality" => array(
                                "id" => (int)$element->speciality_id,
                                "name" => $element->speciality_name,
                                "description" => $element->speciality_description
                            ),
                            "room" => array(
                                "id" => (int)$element->room_id,
                                "name" => $element->room_name,
                                "location" => $element->room_location
                            )
                        );
                    }
        
        
                    
                    $this->resp->result = 1;
                    $this->resp->quantity = $quantity;
                    $this->resp->data = $data;


        }catch(Exception $ex)
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();

        






    }
}

?>