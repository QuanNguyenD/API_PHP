<?php
    require_once APPPATH.'/Core/Input.php';
    require_once APPPATH.'/Model/SpecialityModel.php';
    use Firebase\JWT\JWT;
    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\Key;
    class SpecialityController extends Controller{
        /**
         * process
         */
        
        public function process($id = null){
            $AuthUser = $this->getVariable("AuthUser");
            $Route = $this->getVariable("Route");
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
            //So sánh cả giá trị và loại dữ liệu (3 dấu =)
            if($request_method === 'GET'){
                if ($id !== null) {
                    $this->getById($id); // Truyền $id vào phương thức getById
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
            }
            elseif($request_method === 'PUT'){
                if($decoded->role !="admin"){
                    $this->resp->msg = "You are not admin & you can't do this action !";
                    $this->jsonecho();
                }
                $this->update($id);
            }
            elseif($request_method ==='DELETE'){
                if($decoded->role !="member"){
                    $this->resp->msg = "You are not admin & you can't do this action !";
                    $this->jsonecho();
                }
                $this->delete($id);
            }
            


        }


        private function getById($id){
            
            try{
                $SpecialityModel = new SpecialityModel();
                $Speciality = $SpecialityModel->getSpeciality($id);
                
                if( empty($Speciality) )
                {
                    $this->resp->msg = "Speciality is not available";
                    $this->jsonecho();
                }
                else{
                    echo json_encode($Speciality);
                }
            }catch(Exception $ex){
                $this->resp->msg = $ex->getMessage();
            }
            
            

        }

        private function update($id){
            $this->resp->result = 0;
            $required_fields = ["name", "description"];
            foreach( $required_fields as $field)
            {
                if( !Input::put($field) )
                {
                    $this->resp->msg = "Missing field: ".$field;
                    $this->jsonecho();
                }
            }
            $name = Input::put("name");
            $description = Input::put("description");

            //check exist
            $Speciality = Controller::model("Speciality", $id);
            if( !$Speciality->isAvailable() )
            {
                $this->resp->msg = "Speciality is not available";
                $this->jsonecho();
            }
            try 
            {
                $Speciality->set("name", $name)
                    ->set("description", $description)
                    ->save();

                $this->resp->result = 1;
                $this->resp->msg = "Updated successfully";
                $this->resp->data = array(
                    "id" => (int)$Speciality->get("id"),
                    "name" => $Speciality->get("name"),
                    "description" => $Speciality->get("description"),
                    "image" => $Speciality->get("image")
                );
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();

            




        }

        private function delete($id){
            $this->resp->result = 0;
            if(!isset($id)){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }
            if($id ==1){
                $this->resp->msg = "This is the default speciality & it can't be deleted !";
                $this->jsonecho();
            }
            $Speciality = Controller::model("Speciality", $id);
            if( !$Speciality->isAvailable() )
            {
                $this->resp->msg = "Speciality is not available";
                $this->jsonecho();
            }

            $SpecialityModel = new SpecialityModel();
            $query = $SpecialityModel->CheckDoctor($id);

            $result = $query->get();

            if( count($result) > 0)
            {
                $this->resp->msg = "This speciality can't be deleted because there are ".count($result)." doctors in it";
                $this->jsonecho();
            }
            try 
            {
                $Speciality->delete();
                
                $this->resp->result = 1;
                $this->resp->msg = "Speciality is deleted successfully !";
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();



        }



    }



?>