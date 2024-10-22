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
                // $jsonDecoded = json_encode($decoded, JSON_PRETTY_PRINT);
                // echo $jsonDecoded;
                // $userRole = $decoded->name;
                // echo($userRole);
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
                    $this->getById($id); // Truyền $id vào phương thức getDrugById
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
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



    }



?>