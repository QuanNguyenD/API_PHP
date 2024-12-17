<?php
require_once APPPATH.'/Model/AppointmentRecordModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class AppointmentRecordController extends Controller{
    private $id;
    public function process($id = null)
    {
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
        $valid_roles = ["admin", "member"];
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
            if ($id !== null) {
                //$this->delete($id); // Truyền $id vào phương thức
            } else {
                echo json_encode(["message" => "ID is required"]);
            }
            
        }
        

    }

    private function getById($id){
        $this->resp->result = 0;
        try{
            $appoinmentReCordMd = new AppointmentRecordModel();
            $query = $appoinmentReCordMd->appoinmentRecord();

            $type = Input::get("type") ? Input::get("type") : "id";
                if( $type == "id")
                {
                    $query->where(TB_PREFIX.TB_APPOINTMENT_RECORDS.".id", "=", $id);
                }
                /**Case 2 - get with appointment_id */
                if( $type == "appointment_id")
                {
                    $query->where(TB_PREFIX.TB_APPOINTMENT_RECORDS.".appointment_id", "=", $id);
                }
                $result = $query->get();
                if( count($result) == 0 )
                {
                    $this->resp->msg = "There is no appointment record found by ".$type." so that we CREATE a new one !";
                    $this->jsonecho();
                }
                $element = $result[0];
                $Appointment = Controller::model("Appointment", $element->appointment_id);
                if( !$Appointment )
                {
                    $this->resp->msg = "Appointment does not exist !";
                    $this->jsonecho();
                }
                $this->resp->result = 1;
                $this->resp->msg = "Action successfully !";
                $this->resp->data = array(
                        "id" => (int)$element->id,
                        "reason" => $element->reason,
                        "description" => $element->description,
                        "status_before" => $element->status_before,
                        "status_after" => $element->status_after,
                        "create_at" => $element->create_at,
                        "update_at" => $element->update_at,
                        "appointment" => array(
                            "id" => (int)$element->appointment_id,
                            "patient_id" => (int)$element->patient_id,
                            "patient_name" => $element->patient_name,
                            "patient_birthday" => $element->patient_birthday,
                            "date" => $element->date,
                            "status" => $element->status
                        ),
                        "doctor" => array(
                            "id" => (int)$element->doctor_id,
                            "name" => $element->doctor_name
                        ),
                        "speciality" => array(
                            "id" => (int)$element->speciality_id,
                            "name" => $element->speciality_name
                        )
                );






        }catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();


    }




}



?>