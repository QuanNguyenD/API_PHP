<?php
require_once APPPATH.'/Core/Input.php';
class SignupController extends Controller{

    public function process(){
        $AuthUser = $this->getVariable("AuthUser");

        if ($AuthUser) {
            $this->resp->msg = "You logged in !";
            $this->jsonecho();
        }

        $request_method = Input::method();
        if( $request_method === 'POST')
        {
            $this->signup();
        }
        


    }
    /**
     * Đăng ký tài khoản 
     */
    private function signup(){
        $this->resp->result = 0;
        $required_fields  = [
            "email", 
            "phone", 
            "password", 
            "passwordConfirm", 
            "name",
        ];
        foreach ($required_fields as $field) 
        {
            if (!Input::post($field)) 
            {
                $this->resp->msg = "Missing field: ".$field;
                $this->jsonecho();
            }
        }

        $email = Input::post("email");
        $phone= Input::post("phone");
        $password = Input::post("password");
        $passwordConfirm = Input::post("passwordConfirm");
        $name = Input::post("name");
        $description = Input::post("description");
        $price = Input::post("price") ? (int)Input::post("price"): 100000;
        $role = "member";
        $active = 1;
        $avatar = Input::post("avatar") ? Input::post("avatar"): "";
        $specialityId = 1;
        $roomId =1 ;
        date_default_timezone_set('Asia/Ho_Chi_Minh');

    /**
     * Check output data
     */
        /**
         * Check format email
         */
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->resp->msg = "Email is not correct format. Try again !";
            $this->jsonecho();
        }
        //email duplication
        $Doctor = Controller::model("Doctor", $email);
        if( $Doctor->isAvailable() )
        {
            $this->resp->msg = "This email is used by someone. Try another !";
            $this->jsonecho();
        }
        //password filter
        if (mb_strlen($password) < 6) 
        {
            $this->resp->msg = "Password must be at least 6 character length!";
            $this->jsonecho();
        } 
        else if ($password != $passwordConfirm) 
        {
            $this->resp->msg = "Password confirmation does not equal to password !";
            $this->jsonecho();
        }
        
        

        /**Step 2.5 - phone validation */
        if( strlen($phone) < 10 ){
            $this->resp->msg = "Phone number has at least 10 number !";
            $this->jsonecho();
        }

        $phone_number_validation = isNumber($phone);
        if( !$phone_number_validation ){
            $this->resp->msg = "This is not a valid phone number. Please, try again !";
            $this->jsonecho();
        }



        /**Step 2.6 - price */
        if( $price < 100000 )
        {
            $this->resp->msg = "Price must greater than 100.000 !";
            $this->jsonecho();
        }
        //SAVE
        try 
        {
            $Doctor = Controller::model("Doctor");
            $Doctor->set("email", strtolower($email))
                    ->set("phone", $phone)
                    ->set("password", password_hash($password, PASSWORD_DEFAULT))
                    ->set("name", $name)
                    ->set("description", $description)
                    ->set("price", $price)
                    ->set("role", $role)
                    ->set("active", $active)
                    ->set("avatar", $avatar)
                    ->set("create_at", date("Y-m-d H:i:s"))
                    ->set("update_at", date("Y-m-d H:i:s"))
                    ->set("speciality_id", $specialityId)
                    ->set("room_id", $roomId)
                    ->save();

            $this->resp->result = 1;
            $this->resp->msg = "Doctor account is created successfully !";
            $this->resp->data = array(
                "id" => (int)$Doctor->get("id"),
                "email" => $Doctor->get("email"),
                "phone" => $Doctor->get("phone"),
                "name" => $Doctor->get("name"),
                "description" => $Doctor->get("description"),
                "price" => $Doctor->get("price"),
                "role" => $Doctor->get("role"),
                "active" => (int)$Doctor->get("active"),
                "avatar" => $Doctor->get("avatar"),
                "create_at" => $Doctor->get("create_at"),
                "update_at" => $Doctor->get("update_at"),
                "speciality_id" => (int)$Doctor->get("speciality_id"),
                "room_id" => (int)$Doctor->get("room_id")
            );

            $data = [
                "email" => strtolower($email),
                "phone" => $phone,
                "name" => $name,
                "password" => $password
            ];

            //MyEmail::signup($data);
        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();



    
        

        

        







    }



}



?>