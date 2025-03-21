<?php
require_once APPPATH.'/Model/ServiceModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class ServicesController extends Controller
{
    public function process(){
        $jwt = null;
        $headers = apache_request_headers();
            ////////////
        // if(isset($headers['authorization']))
        // {
        //     $Authorization = $headers['authorization'];
        // }
        // if(isset($headers['Authorization']))
        // {
        //     $Authorization = $headers['Authorization'];
        // }
        // if(!$Authorization){
        //     header("Location: " . APPURL . "/login");
        //     exit;
        // }


        //     //////////
            if (isset($headers['Authorization'])) {
                $jwt =$headers['Authorization'];
            }
            if (isset($headers['authorization'])) {
                $jwt =$headers['authorization'];
            }
            if (!$jwt && isset($_COOKIE['accessToken'])) {
                $jwt = $_COOKIE['accessToken'];
            }
            if(!isset($jwt)){
                header("Location: " . APPURL . "/login");
                exit;
            }
        // if (isset($jwt)) {
        //     try {
        //         $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        //         // Lưu thông tin người dùng vào biến hoặc session
        //         // $_SESSION['AuthUser'] = $decoded; 
        //         //$jsonDecoded = json_encode($decoded, JSON_PRETTY_PRINT);
        //         //echo $jsonDecoded;
        //         //$userRole = $decoded->role;
        //         //echo($userRole);
                
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

        $request_method = Input::method();
        
        if($request_method === 'GET')
        {
            $this->getAll();
        }
        elseif($request_method ==='PUT'){
            // if($decoded->role !="admin"){
            //     $this->resp->msg = "You are not admin & you can't do this action !";
            //     $this->jsonecho();
            // }
           
        }
        else if( $request_method === 'POST')
        {
            $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
            $this->save();
        }
        elseif($request_method ==='DELETE'){
            // if($decoded->role !="admin"){
            //     $this->resp->msg = "You are not admin & you can't do this action !";
            //     $this->jsonecho();
            // }
            

        }
    }


    private function getAll(){

        $this->resp->result = 0;
        $data = [];

        $order          = Input::get("order");
        $search         = Input::get("search");
        $length         = Input::get("length") ? (int)Input::get("length") : 10;
        $start          = Input::get("start") ? (int)Input::get("start") : 0;
        try{

            $serviceMd = new ServiceModel();

            $query = $serviceMd->getAll();

            $search_query = trim( (string)$search );
            if($search_query){
                $query->where(function($q) use($search_query)
                {
                    $q->where(TB_PREFIX.TB_SERVICES.".name", 'LIKE', $search_query.'%');
                }); 
            }

            if( $order && isset($order["column"]) && isset($order["dir"]))
            {
                $type = $order["dir"];
                $validType = ["asc","desc"];
                $sort =  in_array($type, $validType) ? $type : "desc";


                $column_name = trim($order["column"]) != "" ? trim($order["column"]) : "id";
                $column_name = str_replace(".", "_", $column_name);
                
                if(in_array($column_name, ["name"])){
                    $query->orderBy($column_name, $sort);
                }else{
                    $query->orderBy($column_name, $sort);
                }
                
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
                        "image" => $element->image,
                        "description" => $element->description
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

        $required_fields = ["name"];
        foreach( $required_fields as $field)
        {
            if( !Input::post($field) )
            {
                $this->resp->msg = "Missing field: ".$field;
                $this->jsonecho();
            }
        }
        $name = Input::post("name");
        $description = Input::post("description");

        $serviceMd = new ServiceModel();

        $query = $serviceMd->checkName($name);

        $result = $query->get();
        if( count($result) > 0 )
        {
            $this->resp->msg = "This Service exists ! Try another name";
            $this->jsonecho();
        }


        /**Step 4 - create*/
        $Service = Controller::model("Service");
        $Service->set("name", $name)
                ->set("image", 'default_avatar.jpg')
                ->set("description", $description)
                    ->save();
        

        /**Step 5 */
        $this->resp->result = 1;
        $this->resp->msg = "Service is created successfully !";
        $this->resp->data = array(
            "id" => (int)$Service->get("id"),
            "name" => $Service->get("name"),
            "image" => $Service->get("image"),
            "description" => $Service->get("description")
        );
        $this->jsonecho();





    }







}




?>  