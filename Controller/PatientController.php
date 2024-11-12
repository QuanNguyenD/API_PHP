<?php
require_once APPPATH.'/Model/PatientModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

    class PatientController extends Controller{
        public function process($id = null){
            $AuthUser = $this->getVariable("AuthUser");
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
                $this->resp->msg = "You are not logging !";
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
                if ($id !== null) {
                    $this->getById($id); // Truyền $id vào phương thức getById
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
                
            }
            else if( $request_method === 'PUT')
            {
                if($decoded->role != "admin"){
                    $this->resp->msg= "you are not admin & you can not do this action";
                    $this->jsonecho();
                }
                if($id == null){
                    echo json_encode(["message" => "ID is required"]);
                }
                else{
                    $this->update($id);
                }
                
            }
            elseif( $request_method ==="DELETE"){
                if($decoded->role != "admin"){
                    $this->resp->msg= "you are not admin & you can not do this action";
                    $this->jsonecho();
                }
                if($id == null){
                    echo json_encode(["message" => "ID is required"]);
                }
                else{
                    $this->delete($id);
                }
            }

        }

        private function getById($id){
            $this->resp->result = 0;
            $Patient = new PatientModel();
            $query  = $Patient->getById($id);

            if( empty($query) )
            {
                $this->resp->msg = "Patient is not available";
                $this->jsonecho();
            }
            try
            {
                $result = $query->get();
                if( count($result) == 0 )
                {
                    $this->resp->msg = "Oops, there is an error occurring. Try again !";
                    $this->jsonecho();
                }

                
                $data = array(
                    "id" => (int)$result[0]->id,
                    "email" => $result[0]->email,
                    "phone" => $result[0]->phone,
                    "name" => $result[0]->name,
                    "gender" => (int)$result[0]->gender,
                    "birthday" => $result[0]->birthday,
                    "address" => $result[0]->address,
                    "avatar" => $result[0]->avatar,
                    "create_at" => $result[0]->create_at,
                    "update_at" => $result[0]->update_at
                );

                $this->resp->result = 1;
                $this->resp->msg = "Action successfully !";
                $this->resp->data = $data;
            }
            catch(Exception $ex)
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();



        }

        private function update($id){

            $this->resp->result = 0;
            if(!isset($id)){
                $this->resp->msg="ID is required";
            }

            $Patient = Controller::model("Patient", $id);
            if( !$Patient->isAvailable() )
            {
                $this->resp->msg = "Patient is not available !";
                $this->jsonecho();
            }

            $required_fields = ["name", "phone", "birthday"];
            foreach( $required_fields as $field )
            {
                if( !Input::put($field))
                {
                    $this->resp->msg = "Missing field: ".$field;
                    $this->jsonecho();
                }
            }

            $phone = Input::put("phone");
            $name = Input::put("name");
            $birthday = Input::put("birthday");
            $address = Input::put("address");
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $update_at = date("Y-m-d H:i:s");

            if( strlen($phone) < 10 ){
                $this->resp->msg = "Phone number has at least 10 number !";
                $this->jsonecho();
            }

            $phone_number_validation = isNumber($phone);
            if( !$phone_number_validation ){
                $this->resp->msg = "This is not a valid phone number. Please, try again !";
                $this->jsonecho();
            }

            $yearBirthday = (int)substr($birthday, 0,4);
            $monthBirthday = (int)substr($birthday,5,8);
            $dayBirthday = (int)substr($birthday,8,10);


            $yearToday = (int)date("Y");
            $monthToday = (int)date("m");
            $dayToday = (int)date("d");


            $yearDifference  = $yearToday - $yearBirthday;
            $monthDifference = $monthToday - $monthBirthday;
            $dayDifference   = $dayToday - $dayBirthday;

            $today = date("D, d-m-Y");

            /*birthday is not valid*/
            $birthday_validation = checkdate($monthBirthday, $dayBirthday, $yearBirthday);
            if( !$birthday_validation )
            {
                
                $this->resp->msg = "Your birthday - ".$birthday." - does not exist !";
                echo($monthBirthday);

                $this->jsonecho();
            }
           
            if( $yearDifference < 0)
            {
                $this->resp->msg = "Today is ".$today." so that birthday is not valid !";
                $this->jsonecho();
            }
            /*Step 3.3 - Case 3 - yearBirthday == yearToday*/
            else if( $yearDifference == 0)
            {
                //Case 3.1. monthBirthday > monthToday
                if( $monthDifference < 0  )
                {
                    $this->resp->msg = "Today is ".$today." so that birthday is not valid !";
                    $this->jsonecho();
                }
                //Case 3.2. monthBirthday == monthToday
                else if( $monthDifference == 0)
                {
                    if( $dayDifference < 0)
                    {
                        $this->resp->msg = "Today is ".$today." so that birthday is not valid !";
                        $this->jsonecho();
                    }
                }
            }
            try 
            {
                $Patient->set("phone",$phone)
                        ->set("name", $name)
                        ->set("birthday", $birthday)
                        ->set("address", $address)
                        ->set("update_at", $update_at)
                        ->save();
                    
                $this->resp->result = 1;
                $this->resp->msg = "Patient personal information has been updated successfully !";
                $this->resp->data = array(
                    "id" => (int)$Patient->get("id"),
                    "email" => $Patient->get("email"),
                    "phone" => $Patient->get("phone"),
                    "name" => $Patient->get("name"),
                    "gender" => (int)$Patient->get("gender"),
                    "birthday" => $Patient->get("birthday"),
                    "address" => $Patient->get("address"),
                    "avatar" => $Patient->get("avatar"),
                    "create_at" => $Patient->get("create_at"),
                    "update_at" => $Patient->get("update_at")
                );
            } 
            catch (\Exception $ex)
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();







        }

        private function delete(){
            //do notthing 
            $this->resp->msg = "This action is not allowed !";
            $this->jsonecho();
        }



        
    }




?>