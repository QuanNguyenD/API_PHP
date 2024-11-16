<?php
require_once APPPATH.'/Model/DoctorAndServiceModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class DoctorAndServiceController extends Controller{
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

        $request_method = Input::method();
        
        if($request_method === 'GET')
        {
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
            $this->getAll($id);
        }
        elseif($request_method ==='PUT'){
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
            //$this->create($id);
           
        }
        else if( $request_method === 'POST')
        {
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
            $this->create($id);
        }
        elseif($request_method ==='DELETE'){
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
            $this->delete($id);
            

        }
    }
    private function getAll($id){
        $this->resp->result = 0;
            $Route = $this->getVariable("Route");
            $data = [];


            /**Step 2 - get id */
            if( !isset($id) )
            {
                $this->resp->msg = "Service ID is required !";
                $this->jsonecho();
            }

            /** Step 3 - check service*/
            $Service = Controller::model("Service", $id);
            if( !$Service->isAvailable() )
            {
                $this->resp->msg = "Service is not available !";
                $this->jsonecho();
            }
            try{
                $docanhserviceMd = new DoctorAndServiceModel();
                $query = $docanhserviceMd->doctorwokingservice($id);

                $result = $query->get();
                $quantity = count($result);

                if( $quantity > 0)
                {
                    foreach($result as $element)
                    {
                        $data[] = array(
                            "doctor_and_service_id" => (int)$element->doctor_and_service_id,
                            "id" => (int)$element->doctor_id,
                            "name" => $element->doctor_name,
                            "avatar"=> $element->doctor_avatar,
                            "phone" => $element->doctor_phone,
                            "email" => $element->doctor_email,
                            "speciality" => array(
                                "id" => (int)$element->speciality_id,
                                "name" => $element->speciality_name
                            )
                        );
                    }
                }
                

                $this->resp->result = 1;
                $this->resp->msg = "Action successfully";
                $this->resp->quantity = $quantity;
                $this->resp->service = array(
                    "id" => (int)$Service->get("id"),
                    "name" => $Service->get("name"),
                    "description" => $Service->get("description")
                );
                $this->resp->data = $data;

            }catch(Exception $ex){
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        
    }

    private function create($id){
        /**Step 1 - declare */
        $this->resp->result = 0;

        /**Step 2 - get id - the id of service*/
        if( !isset($id) )
        {
            $this->resp->msg = "Service ID is required !";
            $this->jsonecho();
        }

        /** Step 3 - check service*/
        $service_id = $id;
        $Service = Controller::model("Service", $service_id);
        if( !$Service->isAvailable() )
        {
            $this->resp->msg = "Service is not available !";
            $this->jsonecho();
        }

        $doctor_id = Input::post("doctor_id");
        if( !$doctor_id )
        {
            $this->resp->msg = "Doctor ID is required !";
            $this->jsonecho();
        }
        $Doctor = Controller::model("Doctor", $doctor_id);
        if( !$Doctor->isAvailable() )
        {
            $this->resp->msg = "Doctor is not available";
            $this->jsonecho();
        }
        if( $Doctor->get("active") != 1)
        {
            $this->resp->msg = "Doctor was deactivated !";
            $this->jsonecho(); 
        }



        try 
        {
            $DoctorAndService = Controller::model("DoctorAndService");
            $DoctorAndService->set("service_id", $service_id)
                            ->set("doctor_id", $doctor_id)
                            ->save();
            
            $this->resp->result = 1;
            $this->resp->msg = "Created successfully";
        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();
    }

    private function delete($id){
        /**Step 1 - declare */
        $this->resp->result = 0;


        /**Step 2 - @id is one of ID from table DOCTOR AND SERVICE  */
        if( !isset($id))
        {
            $this->resp->msg = "ID is required !";
            $this->jsonecho();
        }

        /** Step 3 - check service*/
        $doctor_and_service_id = $id;
        $DoctorAndService = Controller::model("DoctorAndService", $doctor_and_service_id);
        if( !$DoctorAndService->isAvailable() )
        {
            $this->resp->msg = "DoctorAndService is not available !";
            $this->jsonecho();
        }

        try 
        {
            $DoctorAndService->delete();

            $this->resp->result = 1;
            $this->resp->msg = "Deleted successfully";
        } 
        catch (Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();
    }
}


?>