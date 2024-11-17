<?php
require_once APPPATH.'/Model/BookingModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class PatientBookingController extends Controller{
    public function process($id = null){
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
        if( empty($decoded->role)== false )
        {
            $this->resp->result = 0;
            $this->resp->msg = "You are not logging with PATIENT account so that you are not allowed do this action !";
            $this->jsonecho();
        }

        $request_method = Input::method();
        
        if($request_method === 'GET')
        {
            if ($id !== null) {
                $this->getById($id);
            } else {
                echo json_encode(["message" => "ID is required"]);
            }
        }
        
        elseif($request_method ==='DELETE'){
            if ($id !== null) {
                $this->delete($id);
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

    private function delete($id)
    {
            
        $this->resp->result = 0;
        $Route = $this->getVariable("Route");
        $AuthUser = $this->getVariable("AuthUser");
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $update_at = date("Y-m-d H:i:s");

        /*check ID*/
        $Booking = Controller::model("Booking", $id);
        if( !$Booking->isAvailable() )
        {
            $this->resp->msg = "This booking does not exist !";
            $this->jsonecho();
        }
        $headers = getallheaders();
        $jwt =$headers['Authorization'];
        $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        $idPatient= $decoded->id;

        if( $Booking->get("patient_id") != $idPatient )
        {
            $this->resp->msg = "This booking is not available !";
            $this->jsonecho();
        }

        /**if status == cancelled => no need more action*/
        if( $Booking->get("status") == "cancelled" )
        {
            $this->resp->msg = "This booking's status is cancelled. No need any more action !";
            $this->jsonecho();
        }
        
        /**if status == processing or status verified => allow set status to CANCELLED */
        $status = $Booking->get("status");
        $valid_status = ["processing"];
        $status_validation = in_array($status, $valid_status);
        if( !$status_validation )
        {
            $this->resp->msg = "Booking's status is not valid. Booking can be cancelled only when its status: "
                                .implode(', ',$valid_status)." !";
            $this->jsonecho();
        }
        
        /** save change */
        $Booking->set("status", "cancelled")
                ->set("update_at", $update_at)
                ->save();




        $Notification = Controller::model("Notification");
        $Service = Controller::model("Service", $Booking->get("service_id"));
        $serviceName = $Service->get("name");
        $date = $Booking->get("appointment_date");
        $time = $Booking->get("appointment_time");
        
        $notificationMessage = "Lịch hẹn khám ".$serviceName." lúc ".$time." ngày ".$date." đã được hủy bỏ thành công!";
        $Notification->set("message", $notificationMessage)
                ->set("record_id", $Booking->get("id") )
                ->set("record_type", "booking")
                ->set("is_read", 0)
                ->set("patient_id", $AuthUser->get("id"))
                ->set("create_at", $update_at)
                ->set("update_at", $update_at)
                ->save();
        
        $this->resp->result = 1;
        $this->resp->msg = "Booking has been cancelled successfully !";
        $this->jsonecho();
    }

    // private function getAll(){
    //     $this->resp->result = 0;
    //     $headers = getallheaders();
    //     $jwt =$headers['Authorization'];
    //     $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
    //     $id= $decoded->id;
    //     echo($id);

    //         /**Step 2 - get filters */
    //         $order          = Input::get("order");
    //         $search         = Input::get("search");
    //         $length         = Input::get("length") ? (int)Input::get("length") : 10;
    //         $start          = Input::get("start") ? (int)Input::get("start") : 0;
    //         try{
    //         $bookingMd = new BookingModel();

    //         $query = $bookingMd->getAllBooking($id);

    //         }catch(Exception $ex)
    //         {
    //             $this->resp->msg = $ex->getMessage();
    //         }
    // }




}





?>