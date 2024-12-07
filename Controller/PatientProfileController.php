<?php
require_once APPPATH.'/Model/PatientModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class PatientProfileController extends Controller{
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
        if( empty($decoded->role)== false )
        {
            $this->resp->result = 0;
            $this->resp->msg = "You are not logging with PATIENT account so that you are not allowed do this action !";
            $this->jsonecho();
        }
        $request_method = Input::method();
        
        if($request_method === 'GET')
        {
            

            $this->getInformation();
        }
       
        else if( $request_method === 'POST')
        {
            
            $action = Input::post("action");
                switch ($action) {
                    case "personal":
                        $this->changeInformation();
                        break;
                    case "password":
                        $this->changePassword();
                        break;
                    case "avatar":
                        $this->changeAvatar();
                        break;
                    default:
                        $this->resp->result = 0;
                        $this->resp->msg = "Your action is ".$action." & it's not valid. There are valid actions: personal, password & avatar ";
                        $this->jsonecho();
                }
        }
        
    }

    private function getInformation(){
        $this->resp->result = 0;
        ////////
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
        ///////////////
        $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        $AuthUser = Controller::Model("Patient", $decoded->id);
        if( !$AuthUser )
        {
            $this->resp->msg = "There is no authenticated user !";
            $this->jsonecho();
        }
        $this->resp->result = 1;
            $this->resp->msg = "Action successfully !";
            // $this->resp->data = array(
            //     "id" => (int)$AuthUser->id,
            //     "name" => $AuthUser->name,
            //     "gender" => (int)$AuthUser->gender,
            //     "phone" => $AuthUser->phone,
            //     "email" => $AuthUser->email,
            //     "birthday" => $AuthUser->birthday,
            //     "address" => $AuthUser->address,
            //     "avatar" => $AuthUser->avatar,
            //     "create_at" => $AuthUser->create_at,
            //     "update_at" => $AuthUser->update_at
                
            // );
            $this->resp->data = array(
                "id" => (int)$AuthUser->get("id"),
                "name" => $AuthUser->get("name"),
                "gender" => (int)$AuthUser->get("gender"),
                "phone" => $AuthUser->get("phone"),
                "email" => $AuthUser->get("email"),
                "birthday" => $AuthUser->get("birthday"),
                "address" => $AuthUser->get("address"),
                "avatar" => $AuthUser->get("avatar"),
                "create_at" => $AuthUser->get("create_at"),
                "update_at" => $AuthUser->get("update_at")
            );

        $this->jsonecho();



    }

    private function changeInformation(){

        $this->resp->result = 0;

        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
        ///////////////
        $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        $AuthUser = Controller::Model("Patient", $decoded->id);

        $required_fields = ["name", "birthday" , "address"];
        foreach( $required_fields as $field )
        {
            if( !Input::post($field))
            {
                $this->resp->msg = "Missing field: ".$field;
                $this->jsonecho();
            }
        }
        $name = Input::post("name");
        $gender = Input::post("gender") ? Input::post("gender") : 0;

        $birthday = Input::post("birthday");
        $address = Input::post("address");

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $update_at = date("Y-m-d H:i:s");

        /**Step 3 - validation */
        /**Step 3.1 - name validation */
        $name_validation = isVietnameseName($name);
        if( $name_validation == 0 ){
            $this->resp->msg = "Vietnamese name only has letters and space";
            $this->jsonecho();
        }


        /**Step 3.2 - gender validation*/
        $valid_gender = [0,1];
        $gender_validation = in_array($gender, $valid_gender);
        if( !$gender_validation )
        {
            $this->resp->msg = "Gender value is not correct. There are 2 values: 0 is female & 1 is man";
            $this->jsonecho();
        }


        $yearBirthday = (int)substr($birthday, 6);
        $monthBirthday = (int)substr($birthday,3,5);
        $dayBirthday = (int)substr($birthday,0,2);


        $yearToday = (int)date("Y");
        $monthToday = (int)date("m");
        $dayToday = (int)date("d");


        $yearDifference  = $yearToday - $yearBirthday;
        $monthDifference = $monthToday - $monthBirthday;
        $dayDifference   = $dayToday - $dayBirthday;

        $today = date("D, d-m-Y");

        /*Step 3.3 - Case 1 - birthday is not valid*/
        $birthday_validation = checkdate($monthBirthday, $dayBirthday, $yearBirthday);
        if( !$birthday_validation )
        {
            $this->resp->msg = "Your birthday - ".$birthday." - does not exist !";
            $this->jsonecho();
        }
        /*Step 3.3 - Case 2 - yearBirthday(2023) > yearToday(2022)*/
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
                // dayBirthday = 15 but dayToday = 13
                if( $dayDifference < 0)
                {
                    $this->resp->msg = "Today is ".$today." so that birthday is not valid !";
                    $this->jsonecho();
                }
            }
            //Case 3.3. monthBirthday < monthToday
            else
            {
                // do thing
            }
        }
        /*Step 3.3 - Case 4 - yearBirthday < yearToday*/
        else
        {
            //always correct
        }

        /**Step 3.4 - address */
        $address_validation = isAddress($address);
        if( $address_validation == 0)
        {
            $this->resp->msg = "Address only accepts letters, space & number";
            $this->jsonecho();
        }

        /**Step 4 - save */
        try 
        {
            $AuthUser->set("name", $name)
                    ->set("birthday", $birthday)
                    ->set("address", $address)
                    ->set("update_at", $update_at)
                    ->save();
                
            $this->resp->result = 1;
            $this->resp->msg = "Your personal information has been updated successfully !";
            $this->resp->data = array(
                "id" => (int)$AuthUser->get("id"),
                "email" => $AuthUser->get("email"),
                "phone" => $AuthUser->get("phone"),
                "name" => $AuthUser->get("name"),
                "gender" => (int)$AuthUser->get("gender"),
                "birthday" => $AuthUser->get("birthday"),
                "address" => $AuthUser->get("address"),
                "avatar" => $AuthUser->get("avatar"),
                "create_at" => $AuthUser->get("create_at"),
                "update_at" => $AuthUser->get("update_at")
            );
        } 
        catch (\Exception $ex)
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();






    }

    private function changePassword(){
        $this->resp->result = 0;
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
        ///////////////
        $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        $AuthUser = Controller::Model("Patient", $decoded->id);
        if( !$AuthUser )
        {
            $this->resp->msg = "You does not log in !";
            $this->jsonecho();
        }
        $required_field = ["currentPassword", "newPassword", "confirmPassword"];
            foreach($required_field as $field)
            {
                if( !Input::post($field) )
                {
                    $this->resp->msg = "Missing field: ".$field;
                    $this->jsonecho();
                }
            }

            $id = $AuthUser->get("id");
            $currentPassword = Input::post("currentPassword");
            $newPassword     = Input::post("newPassword");
            $confirmPassword = Input::post("confirmPassword");

            /**Step 3 - is the Patient active ? */
            $Patient = Controller::model("Patient", $id);
            if( !$Patient->isAvailable() )
            {
                $this->resp->msg = "This account is not available !";
                $this->jsonecho();
            }


            /**Step 4 - validation */
            $hash = $Patient->get("password");
            if(  !password_verify( $currentPassword, $hash ) )
            {
                $this->resp->msg = "Your current password is incorrect. Try again !";
                $this->jsonecho();
            }
            if (mb_strlen($newPassword) < 6) 
            {
                $this->resp->msg = ("Password must be at least 6 character length!");
                $this->jsonecho();
            } 
            if ($newPassword != $confirmPassword) 
            {
                $this->resp->msg = ("Password confirmation does not equal to new password !");
                $this->jsonecho();
            }

            /**Step 5 - save */
            try 
            {
                $Patient->set("password", password_hash($newPassword, PASSWORD_DEFAULT))
                    ->save();

                $this->resp->result = 1;
                $this->resp->msg = "New password has been updated successfully. Don't forget to login again !";
                $this->resp->data = array(
                    "id" => (int)$AuthUser->get("id"),
                    "email" => $AuthUser->get("email"),
                    "phone" => $AuthUser->get("phone"),
                    "name" => $AuthUser->get("name"),
                    "gender" => (int)$AuthUser->get("gender"),
                    "birthday" => $AuthUser->get("birthday"),
                    "address" => $AuthUser->get("address"),
                    "avatar" => $AuthUser->get("avatar"),
                    "create_at" => $AuthUser->get("create_at"),
                    "update_at" => $AuthUser->get("update_at")
                );
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();


    }

    private function changeAvatar(){
        $this->resp->result = 0;
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
        ///////////////
        $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
        $AuthUser = Controller::Model("Patient", $decoded->id);
        if( !$AuthUser )
        {
            $this->resp->msg = "You does not log in !";
            $this->jsonecho();
        }
        if( !$AuthUser->isAvailable() )
            {
                $this->resp->msg = "This account is not available !";
                $this->jsonecho();
            }


            /**Step 2 - check if file is received or not */
            if (empty($_FILES["file"]) || $_FILES["file"]["size"] <= 0) 
            {
                $this->resp->msg = "Photo is not received !";
                $this->jsonecho();
            }

            
            /**Step 3 - check filename extension */
            $ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
            $allow = ["jpeg", "jpg", "png"];
            if (!in_array($ext, $allow)) 
            {
                $this->resp->msg = ("Only ".join(",", $allow)." files are allowed");
                $this->jsonecho();
            }


            /**Step 4 - upload file */
            $date = new DateTime();
            $timestamp = $date->getTimestamp();
            $name = "avatar_".$AuthUser->get("id")."_".$timestamp;
            $directory = UPLOAD_PATH;


            if (!file_exists($directory)) {
                mkdir($directory);
            }
            
            $filepath = $directory . "/" . $name . "." .$ext;

            if (!move_uploaded_file($_FILES["file"]["tmp_name"], $filepath)) 
            {
                $this->resp->msg = ("Oops! An error occured. Please try again later!");
                $this->jsonecho();
            }
            
            /**Step 6 - update photo name for AuthUser */
            try 
            {
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                $update_at = date("Y-m-d H:i:s");

                $AuthUser->set("avatar", $name . "." .$ext)
                        ->set("update_at", $update_at)
                        ->save();

                $this->resp->result = 1;
                $this->resp->msg = ("Avatar has been updated successfully !");
                $this->resp->url = APPURL."/assets/uploads/".$name . "." .$ext;
                $this->resp->data = array(
                    "id"    => (int)$AuthUser->get("id"),
                    "email" => $AuthUser->get("email"),
                    "phone" => $AuthUser->get("phone"),
                    "name" => $AuthUser->get("name"),
                    "gender" => (int)$AuthUser->get("gender"),
                    "birthday" => $AuthUser->get("birthday"),
                    "address" => $AuthUser->get("address"),
                    "avatar" => $AuthUser->get("avatar"),
                    "create_at" => $AuthUser->get("create_at"),
                    "update_at" => $AuthUser->get("update_at")
                );

            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }

            $this->jsonecho();
    }






}




?>