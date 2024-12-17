<?php
require_once APPPATH.'/Model/ServiceModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class ServiceController extends Controller{

    public function process($id = null){
        $jwt = null;
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $jwt =$headers['Authorization'];
            }
            if (!$jwt && isset($_COOKIE['accessToken'])) {
                $jwt = $_COOKIE['accessToken'];
            }
            if(!isset($jwt)){
                header("Location: " . APPURL . "/login");
                exit;
            }
            
        // if ($jwt) {
        //     try {
        //         $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        //         // Lưu thông tin người dùng vào biến hoặc session
        //         $_SESSION['AuthUser'] = $decoded; 
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
            if ($id !== null) {
                $this->getById($id); // Truyền $id vào phương thức getById
            } else {
                echo json_encode(["message" => "ID is required"]);
            }
        }
        elseif($request_method ==='PUT'){
            $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
            $this->update($id);
        }
        else if( $request_method === 'POST')
        {
            $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
        }
        elseif($request_method ==='DELETE'){
            $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            if($decoded->role !="admin"){
                $this->resp->msg = "You are not admin & you can't do this action !";
                $this->jsonecho();
            }
            $this->delete($id);

        }
        
    }

    private function getByID($id){
        $this->resp->result = 0;
        if(!isset($id)){
            $this->resp->msg = "ID is required !";
            $this->jsonecho();
        }

        try
        {
            $Service = Controller::model("Service", $id);
            if( !$Service->isAvailable() )
            {
                $this->resp->msg = "Service is not available";
                $this->jsonecho();
            }



            $this->resp->result = 1;
            $this->resp->msg = "Action successfully !";
            $this->resp->data = array(
                "id" => (int)$Service->get("id"),
                "name" => $Service->get("name"),
                "image" => $Service->get("image"),
                "description"=> $Service->get("description")
            );
        }
        catch(Exception $ex)
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();


    }

    private function update($id){
        $this->resp->result = 0;
        if( !isset($id) )
        {
            $this->resp->msg = "ID is required !";
            $this->jsonecho();
        }
        
            
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


        /**Step 3 - check exist*/
        $Service = Controller::model("Service", $id);
        if( !$Service->isAvailable() )
        {
            $this->resp->msg = "Service is not available";
            $this->jsonecho();
        }


        /**Step 4 - update */
        try 
        {
            $Service->set("name", $name)
                ->set("description", $description)
                ->save();

            $this->resp->result = 1;
            $this->resp->msg = "Service has been updated successfully";
            $this->resp->data = array(
                "id" => (int)$Service->get("id"),
                "name" => $Service->get("name"),
                "image" => $Service->get("image"),
                "description"=> $Service->get("description")
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
        $Service = Controller::model("Service", $id);
        if( !$Service->isAvailable() )
        {
            $this->resp->msg = "Service is not available";
            $this->jsonecho();
        }

        $serviceMd = new ServiceModel();

        $bongking = $serviceMd->queryBooking($id);
        $result = $bongking->get();

        if( count($result) > 0)
        {
            $this->resp->msg = "This Service can't be deleted because there are ".count($result)." booking have been existed !";
            $this->jsonecho();
        }

        $doctorandservice = $serviceMd->queryBooking($id);
        $result = $doctorandservice->get();

        if( count($result) > 0)
        {
            $this->resp->msg = "This Service can't be deleted because there are ".count($result)." records assigned with doctors !";
            $this->jsonecho();
        }

        try 
        {
            $Service->delete();
            
            $this->resp->result = 1;
            $this->resp->msg = "Service is deleted successfully !";
        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();


    }

}




?>