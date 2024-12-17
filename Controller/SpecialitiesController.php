<?php
    require_once APPPATH.'/Core/Input.php';
    require_once APPPATH.'/Model/SpecialityModel.php';
    use Firebase\JWT\JWT;
    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\Key;

    class SpecialitiesController extends Controller{
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
            if($request_method === 'POST'){
                //Chỉnh lại thành Admin
                $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
                if($decoded->role !="admin"){
                    $this->resp->msg = "You are not admin & you can't do this action !";
                    $this->jsonecho();
                }

                $this->save();
            }
            elseif($request_method === 'GET'){

                $this->getAllSpeciality();
            }




        }
        private function getAllSpeciality(){

            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");
            $data = [];

            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 10;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;
            try{

            
                $SpecialityModel = new SpecialityModel();
                $query = $SpecialityModel->getAllSpeciality1();

                $search_query = trim( (string)$search );
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TB_PREFIX.TB_SPECIALITIES.".name", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_SPECIALITIES.".description", 'LIKE', $search_query.'%');
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
                $res = $query->get();
                $quantity = count($res);

                /**Step 3.3 - length filter * start filter*/
                $query->limit($length)
                    ->offset($start);



                /**Step 4 */
                $result = $query->get();
                foreach($result as $element)
                {
                    $data[] = array(
                        "id" => (int)$element->id,
                        "name" => $element->name,
                        "description" => $element->description,
                        "doctor_quantity" => (int)$element->doctor_quantity,
                        "image" => $element->image
                    );
                }


                /**Step 5 - return */
                $this->resp->result = 1;
                $this->resp->quantity = $quantity;
                $this->resp->data = $data;

            }catch(Exception $ex){
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();

            

            // if(!empty($Speciality)) {
            //     // Nếu có, trả về dữ liệu dưới dạng JSON
            //     echo json_encode($Speciality);
            // } else {
            //     // Nếu không có dữ liệu, trả về thông báo lỗi
            //     echo json_encode(["message" => "No speciality found."]);
            // }

        }

        private function save(){
            $this->resp->result = 0;

            //get required
            $required_fields =["name","description"];
            foreach($required_fields as $field){
                if(!Input::post($field)){
                    $this->resp->msg = "Missing field: ".$field;
                    $this->jsonecho();
                }
            }

            $name = Input::post("name");
            $description = Input::post("description");


            //check duplicate 
            $checkName = Controller::model("Speciality");
            $result = $checkName->checkDuplicate($name);

            if( count($result) > 0 )
            {
                $this->resp->msg = "This speciality exists ! Try another name";
                $this->jsonecho();
            }

            //create

            $Speciality = Controller::model("Speciality");
            $Speciality->set("name", $name)
                    ->set("description", $description)
                    ->set("image", "default_avatar.jpg")
                    ->save();

            $this->resp->result = 1;
            $this->resp->msg = "Speciality is created successfully !";
            $this->resp->data = array(
                "id" => (int)$Speciality->get("id"),
                "name" => $Speciality->get("name"),
                "description" => $Speciality->get("description"),
                "image" => $Speciality->get("image")
            );
            $this->jsonecho();
        }




}


?>