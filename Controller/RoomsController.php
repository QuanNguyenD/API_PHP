<?php
require_once APPPATH.'/Model/RoomModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class RoomsController extends Controller{

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
        else if( $request_method === 'POST')
        {
            if($decoded->role != "admin" && $decoded->role != "supporter" && $decoded->role != "menber"){
                $this->resp->msg= "you are not admin you can not do this action";
                $this->jsonecho();
            }
            $this->save();
        }
        
        


    }

    private function getAll(){

        $this->resp->result = 0;
        $AuthUser = $this->getVariable("AuthUser");
        $data = [];

        try{
            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 5;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;
            $speciality_id  = Input::get("speciality_id");

            $roomBd = new RoomModel();
            $query = $roomBd->getAllRoom();


            $search_query = trim( (string)$search );
            if($search_query){
                $query->where(function($q) use($search_query)
                {
                    $q->where(TB_PREFIX.TB_ROOMS.".name", 'LIKE', $search_query.'%')
                    ->orWhere(TB_PREFIX.TB_ROOMS.".location", 'LIKE', $search_query.'%');
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


                
                $query->orderBy(TB_PREFIX.TB_ROOMS.".".$column_name, $sort);
                
            }
            else 
            {
                $query->orderBy("id", "desc");
            } 

            if($speciality_id)
            {
                $query->where(TB_PREFIX.TB_SPECIALITIES.".id", "=", $speciality_id);
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
                    "location" => $element->location,
                    "doctor_quantity" => (int)$element->doctor_quantity
                );
            }


            /**Step 5 - return */
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
    private function save(){
        $this->resp->result = 0;
        $AuthUser = $this->getVariable("AuthUser");
        $data = [];

        $required_fields = ["name", "location"];
        foreach( $required_fields as $field)
        {
            if( !Input::post($field) )
            {
                $this->resp->msg = "Missing field: ".$field;
                $this->jsonecho();
            }
        }
        $name = Input::post("name");
        $location = Input::post("location");

        $RoomMd = new RoomModel();
        $query = $RoomMd->saveRoom($name, $location);

        $result = $query->get();
        if( count($result) > 0 )
        {
            $this->resp->msg = "This room ".$name." at ".$location." exists ! Try another name";
            $this->jsonecho();
        }


        /**Step 4 - create*/
        $Room = Controller::model("Room");
        $Room->set("name", $name)
                ->set("location", $location)
                ->save();
        

        /**Step 5 */
        $this->resp->result = 1;
        $this->resp->msg = "Room is created successfully !";
        $this->resp->data = array(
            "id" => (int)$Room->get("id"),
            "name" => $Room->get("name"),
            "location" => $Room->get("location")
        );
        $this->jsonecho();


    }

    







}


?>