<?php
require_once APPPATH.'/Model/AppointmentModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class AppointmentQueueController extends Controller{
    public function process(){
        $AuthUser = $this->getVariable("AuthUser");
        $Route = $this->getVariable("Route");
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($headers['authorization'])) {
            $jwt =$headers['authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }

        // if ($jwt) {
        //     try {
        //         $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        //         // Lưu thông tin người dùng vào biến hoặc session
        //         $_SESSION['AuthUser'] = $decoded; 
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
        if(!isset($jwt)){
            header("Location: " . APPURL . "/login");
            exit;
        }

        
        $request_method = Input::method();
        if($request_method === 'GET'){
            //Chỉnh lại thành Admin
            $request = Input::get("request");
                switch ($request) 
                {
                    case 'all':
                        $this->getAll();
                        break;
                    case 'queue':
                        $this->getQueue();
                    default:
                        $this->getAll();
                        break;
                }
        }
        elseif($request_method === 'GET'){

            $this->arrange();
        }




    }

    private function getAll(){
        $this->resp->result = 0;
        // $AuthUser = $this->getVariable("AuthUser");
        $data = [];
        $msg = "All appointments";
        /////////
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

        $type           = Input::get("type") ? strtolower(Input::get("type") ): "all"; 
        $order          = Input::get("order");
        $search         = Input::get("search");
        $length         = Input::get("length") ? (int)Input::get("length") : 10;
        $start          = Input::get("start") ? (int)Input::get("start") : 0;
        $doctor_id         = Input::get("doctor_id");// Only ADMIN & SUPPORTER can use this filter.
        $room           = Input::get("room");// Only ADMIN & SUPPORTER can use this filter.
        $date           = Input::get("date");
        $status         = Input::get("status");

        $appoiMd = new AppointmentModel(); 
        $query = $appoiMd->appoinmentQueue();

        try{
            
            $valid_roles = ["admin", "supporter"];
            $role_validation = in_array($AuthUser->role, $valid_roles);
            if( $role_validation || !$AuthUser->role)
            {
                
                $query->where(TB_PREFIX.TB_APPOINTMENTS.".doctor_id", "=", $doctor_id);
            }
            else
            {
                
                $query->where(TB_PREFIX.TB_APPOINTMENTS.".doctor_id", "=", $AuthUser->id);
            }
            $search_query = trim( (string)$search );
            if($search_query){
                $query->where(function($q) use($search_query)
                {
                    $q->where(TB_PREFIX.TB_APPOINTMENTS.".patient_name", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".patient_phone", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".patient_reason", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".status", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_APPOINTMENTS.".date", 'LIKE', $search_query.'%');
                }); 
            }
            if( $date )
            {
                $query->where(TB_PREFIX.TB_APPOINTMENTS.".date", "=", $date);
                $msg .= " at ".$date;
            }
            $valid_roles = ["admin", "supporter"];
            $role_validation = in_array($AuthUser->role, $valid_roles);
            if( $doctor_id && $role_validation )
            {
                $query->where(TB_PREFIX.TB_APPOINTMENTS.".doctor_id", "=", $doctor_id);

                $msg .= " - doctor ID: ".$doctor_id;
                $Doctor = Controller::model("Doctor", $doctor_id);
                if( $Doctor->isAvailable() )
                {
                    $msg .= " - ".$Doctor->get("name");
                }
            }

            if( $room )
            {
                $query->leftJoin(TB_PREFIX.TB_DOCTORS,
                                    TB_PREFIX.TB_DOCTORS.".id", "=", TB_PREFIX.TB_APPOINTMENTS.".doctor_id")
                    ->where(TB_PREFIX.TB_DOCTORS.".room_id", "=", $room);
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
                ////////
                $query->orderBy($column_name, $sort);
                
            }
            else 
            {
                $query->orderBy("id", "desc");
            }
            $query->limit($length ? $length : 10)
                        ->offset($start ? $start : 0);

            $result = $query->get();
            foreach($result as $element)
            {
                $data[] = array(
                    "position" => (int)$element->position,
                    "numerical_order" => (int)$element->numerical_order,
                    "id" => (int)$element->id,
                    "patient_id" => (int)$element->patient_id,
                    "patient_name" => $element->patient_name,
                    "doctor_id" => (int)$element->doctor_id,
                    "appointment_time" => $element->appointment_time,
                    "status" => $element->status,
                );
            }

            ////////////////////////////
            $this->resp->result = 1;
            $this->resp->msg = $msg;
            $this->resp->quantity = count($result);
            $this->resp->data = $data;





        }catch(Exception $ex)
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();

    }
    private function getQueue(){

        $this->resp->result = 0;
        
        $date = Date("d-m-Y");
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
        $AuthUser = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));

        if( $AuthUser->role == "member")
        {
            $doctor_id = $AuthUser->id;
        }
        else
        {
            $doctor_id = Input::post("doctor_id");
            if( $doctor_id )
            {
                $this->resp->msg = "Missing doctor ID";
                $this->jsonecho();
            }
        }
        $appoiMd = new AppointmentModel();
        $queryNormal = $appoiMd->appoimentQueue2($date,$doctor_id);
        $resultNormal = $queryNormal->get();

        print_r("current ".$resultNormal[0]->id);
        print_r("\n next ".$resultNormal[1]->id);







    }

    private function arrange(){
        $this->resp->result = 0;
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
        $AuthUser = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));

        $valid_roles = ["admin", "supporter"];
        $role_validation = in_array($AuthUser->role, $valid_roles);
        if( !$role_validation )
        {
            $this->resp->msg = "Only ".implode(', ', $valid_roles)." can arrange appointments";
            $this->jsonecho();
        }
        $required_fields = ["doctor_id", "queue"];
        foreach($required_fields as $field)
        {
            if( !Input::post($field) )
            {
                $this->resp->msg = "Missing field: ".$field;
                $this->jsonecho();
            }
        }
        $doctor_id = Input::post("doctor_id");
        $type = Input::post("type");
        $queue = Input::post("queue");
        $date = Date("d-m-Y");// by default, date is today.

        $Doctor = Controller::model("Doctor", $doctor_id);
        if( !$Doctor->isAvailable() )
        {
            $this->resp->msg = "Doctor is not available !";
            $this->jsonecho();
        }
        if( $Doctor->get("active") != 1)
        {
            $this->resp->msg = "This doctor account was deactivated. No need this action !";
            $this->jsonecho();
        }
        if( is_array($queue) != 1)
        {
            $this->resp->msg = "Queue's format is not valid.";
            $this->jsonecho();
        }
        $appoiMd = new AppointmentModel();
        $query = $appoiMd->appoimentQueue3($doctor_id, $date);

        $result = $query->get();

        try 
        {
        $position = 1;
        foreach($queue as $element)
        {
            $Appointment = Controller::model("Appointment", (int)$element);
            if( $Appointment->get("doctor_id") != $doctor_id )
            {
                continue;
            }
            $Appointment->set("position", $position)
                    ->save();
            $position++; 
        }

        $this->resp->result = 1;
        $this->resp->msg = "Appointments have been updated their positions";
        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();



    }





}



?>