<?php 
require_once APPPATH.'/Model/DoctorModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
    class DoctorsController extends Controller{
        public function process(){
            $AuthUser = $this->getVariable("AuthUser");
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

            // if (!$AuthUser)
            // {
            //     header("Location: ".APPURL."/login");
            //     exit;
            // }
            
            $request_method = Input::method();
            if($request_method === 'GET')
            {
                $this->getAll();
            }
            else if( $request_method === 'POST')
            {
                //$this->save();
            }


            


        }
        private function getAll(){
            $this->resp->result = 0;
            
            $data = [];
            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 10;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;
            $room_id          = Input::get("room_id");// Room_id
            $speciality_id  = Input::get("speciality_id");
            $active         = Input::get("active") ? (int)Input::get("active") : "";
            $service_id     = Input::get("service_id");

            try{
                $docModel = new DoctorModel(); // Khởi tạo model Doc
                $query = $docModel->getAllDoctor();
                
                $search_query = trim((string)$search);
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TB_PREFIX.TB_DOCTORS.".email", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_DOCTORS.".phone", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_DOCTORS.".name", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_DOCTORS.".description", 'LIKE', $search_query.'%');
                    }); 
                }
                if( $order && isset($order["column"]) && isset($order["dir"]))
                {
                    $type = $order["dir"];
                    $validType = ["asc","desc"];
                    $sort =  in_array($type, $validType) ? $type : "desc";


                    $column_name = trim($order["column"]) != "" ? trim($order["column"]) : "id";
                    $column_name = str_replace(".", "_", $column_name);


                    // if(in_array($column_name, ["email", "name", "phone", "speciality_id","create_at", "update_at","price"])){
                    //     $query->orderBy(DB::raw(TABLE_PREFIX.TABLE_DOCTORS.".".$column_name. " * 1"), $sort);
                    // }else{
                    $query->orderBy($column_name, $sort);
                    //}
                }
                else 
                {
                    $query->orderBy("id", "desc");
                }

                if( $room_id)
                {
                    $query->where(TB_PREFIX.TB_DOCTORS.".room_id", "=", $room_id);
                }
                if( $speciality_id )
                {
                    $query->where(TB_PREFIX.TB_SPECIALITIES.".id", "=", $speciality_id);
                }

                if( $active )
                {
                    $query->where(TB_PREFIX.TB_DOCTORS.".active", "=", $active);
                }
                if( $service_id)
                {
                    $query->where(TB_PREFIX.TB_SERVICES.".id", "=", $service_id);
                }

                $res = $query->get();
                $quantity = count($res);

                /**Step 3.4 - length filter * start filter*/
                $query->limit($length)
                    ->offset($start);



                /**Step 4 */
                $result = $query->get();
                foreach($result as $element)
                {
                    $data[] = array(
                        "id" => (int)$element->id,
                        "email" => $element->email,
                        "phone" => $element->phone,
                        "name" => $element->name,
                        "description" => $element->description,
                        "price" => (int)$element->price,
                        "role" => $element->role,
                        "avatar" => $element->avatar,
                        "active" => (int)$element->active,
                        "create_at" => $element->create_at,
                        "update_at" => $element->update_at,
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


                /**Step 5 - return */
                $this->resp->result = 1;
                $this->resp->quantity = $quantity;
                $this->resp->data = $data;
            }
            catch(Exception $ex){
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();


                
        }




    }




?>