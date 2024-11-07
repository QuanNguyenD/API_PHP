<?php
require_once APPPATH.'/Model/PatientModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
    class PatientsController extends Controller{

        public function process(){
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
                $this->getAll();
            }
            else if( $request_method === 'POST')
            {
                //$this->save();
            }

        }

        private function getAll(){
            $this->resp->result = 0;

            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 10;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;

            try{
                $patientModel = new PatientModel();
                $query = $patientModel->getAll();

            
                $search_query = trim( (string)$search );
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TB_PREFIX.TB_PATIENTS.".email", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_PATIENTS.".phone", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_PATIENTS.".name", 'LIKE', $search_query.'%')
                        ->orWhere(TB_PREFIX.TB_PATIENTS.".address", 'LIKE', $search_query.'%');
                    }); 
                }
                /**Step 3.2 - order filter */
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
                $query->limit($length ? $length : 10)
                    ->offset($start ? $start : 0);



                /**Step 4 */
                $result = $query->get();
                foreach($result as $element)
                {
                    $data[] = array(
                        "id" => (int)$element->id,
                        "email" => $element->email,
                        "phone" => $element->phone,
                        "name" => $element->name,
                        "gender" => (int)$element->gender,
                        "birthday" => $element->birthday,
                        "address" => $element->address,
                        "avatar" => $element->avatar,
                        "create_at" => $element->create_at,
                        "update_at" => $element->update_at
                    );
                }


                /**Step 5 - return */
                $this->resp->result = 1;
                $this->resp->quantity = count($result);
                $this->resp->data = $data;
            }
            catch(Exception $ex){
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
            





        }





    }


?>

