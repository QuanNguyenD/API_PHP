<?php
require_once APPPATH.'/Model/BookingModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
//uesd by Doctor
class BookingsController extends Controller{
    
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
                    $this->resp->msg= "you are not admin you can not do this action";
                    $this->jsonecho();
                }
                $this->getAll();

                
            }
            
            elseif($request_method ==='DELETE'){
                
                
            }
            


        }

        private function getAll(){

            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");
            $data = [];

            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 10;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;
            $appointment_date = Input::get("appointment_date");
            $service_id     = Input::get("service_id");
            $status         = Input::get("status");

            try{
                $bkModel = new BookingModel();
                $query = $bkModel->getAllBookingForDoc();

                $search_query = trim( (string)$search );
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TB_PREFIX.TB_SPECIALITIES.".booking_name", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_SPECIALITIES.".booking_phone", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_SPECIALITIES.".name", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_SPECIALITIES.".address", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_SPECIALITIES.".reason", 'LIKE', $search_query.'%');
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


                if($service_id)
                {
                    $query->where(TB_PREFIX.TB_BOOKINGS.".service_id", "=", $service_id);
                }
                if($status)
                {
                    $query->where(TB_PREFIX.TB_BOOKINGS.".status", "=", $status);
                }
                if($appointment_date)
                {
                    $query->where(TB_PREFIX.TB_BOOKINGS.".appointment_date", "=", $appointment_date);
                }

                $res = $query->get();
                $quantity = count($res);

                /**Step 3.3 - length filter * start filter*/
                $query->limit($length)
                    ->offset($start);

                $result = $query->get();

                foreach($result as $element)
                {
                    $data[] = array(
                        "id" => (int)$element->id,
                        //"doctor_id" => (int)$element->doctor_id, 
                        "patient_id" => (int)$element->patient_id,
                        // "booking_name" => $element->booking_name,
                        // "booking_phone" => $element->booking_phone,
                        "name" => $element->patient_name,
                        "gender" => (int)$element->patient_gender,
                        "birthday" => $element->patient_birthday,
                        "address" => $element->patient_address,
                        // "reason" => $element->reason,
                        "appointment_date" => $element->appointment_date,
                        "appointment_time" => $element->appointment_hour,
                        "status" => $element->status,
                        "create_at" => $element->create_at,
                        "update_at" => $element->update_at,
                        "service" => array(
                            "id" => (int)$element->service_id,
                            "name" => $element->service_name
                        )
                        );
                    }

                $this->resp->result = 1;
                $this->resp->quantity = $quantity;
                $this->resp->data = $data;





            }
            catch(Exception $ex)
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();



        }




}

?>