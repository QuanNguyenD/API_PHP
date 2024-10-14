<?php


class LoginController extends Controller{
    public function process()
    {
        $AuthUser = $this->getVariable("AuthUser");
        if ($AuthUser) {
            $this->resp->result = 1;
            $this->resp->msg = "You already logged in";
            
            $this->jsonecho();
        }
            $this -> login();
        
    }

    private function login(){

        $this->resp->result = 0;
        // $type = Input::post("type");
        // $password = Input::post("password");
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $type = $data['type']?? null;
        $password = $data['password'] ?? null;
        
        
        
        
        
        $data = [];
        $payload = [];
        $msg = [];
        $jwt = "";

        if( !$password )
        {
            $this->resp->msg = "Password can not be empty !";
            $this->jsonecho();
        }
        /**Case 1 : if type equals to "patient" => patient is logging */
        if( $type == "patient" )
        {
            $this->loginByPatient();
        }
        /**Case 2 : if type does not equals to "patient" => doctor is logging */
        else
        {
            $this->loginByDoctor();
        }







    }

    /**
     * Đăng nhập với quyền của DOCTOR
     * Đăng nhập với email và mật khẩu
     * 
     * 
     * 
     */
    private function loginByDoctor(){
        /**Step 1 - declare */
        $this->resp->result = 0;
        // $password = Input::post("password");
        // $email = Input::post("email");
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        
        /**Step 2 - is email empty ? */
        if( !$email )
        {
            $this->resp->msg = "Email can not be empty !";
            $this->jsonecho();
        }

        /**Step 3 - does the doctor exist? */
        $Doctor = Controller::model("Doctor", $email);
        if( !$Doctor->isAvailable() ||
            $Doctor->get("active") != 1 || 
            !password_verify($password, $Doctor->get("password")) )
        {
            $this->resp->msg = "The email or password you entered is incorrect !";
            $this->jsonecho();
        }


        $data = array(
            "id"    => (int)$Doctor->get("id"),
            "email" => $Doctor->get("email"),
            "phone" => $Doctor->get("phone"),
            "name" => $Doctor->get("name"),
            "price" => (int)$Doctor->get("price"),
            "role" => $Doctor->get("role"),
            "active" => (int)$Doctor->get("active"),
            "avatar" => $Doctor->get("avatar"),
            "create_at" => $Doctor->get("create_at"),
            "update_at" => $Doctor->get("update_at"),
            "speciality_id" => (int)$Doctor->get("speciality_id"),
            "recovery_token" => $Doctor->get("recovery_token")
        );

        $payload = $data;
        $payload["hashPass"] = md5($Doctor->get("password"));
        $payload["iat"] = time();
        $jwt = Firebase\JWT\JWT::encode($payload, EC_SALT, 'HS256');

        $this->resp->result = 1;
        $this->resp->msg = "Congratulations, doctor ".$Doctor->get("name")." ! You have been logged in successfully.";
        $this->resp->accessToken = $jwt;
        $this->resp->data = $data;
        $this->jsonecho();



    }

    /**
     * Đăng nhập với bệnh nhân
     * 
     * 
     */
    private function loginByPatient()
    {
        /**Step 1 - declare */
        $this->resp->result = 0;
        //$password = Input::post("password");
        //$phone = Input::post("phone");

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $password = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        
        $hashPassword = "";
        $data = [];

        /**Step 2 - is phone number correct format ? */
        if( !$phone )
        {
            $this->resp->msg = "Phone number can not be empty !";
            $this->jsonecho();
        }
        if( strlen($phone) < 10 ){
            $this->resp->msg = "Phone number has at least 10 number !";
            $this->jsonecho();
        }
        $phone_number_validation = isNumber($phone);
        if( !$phone_number_validation ){
            $this->resp->msg = "This is not a valid phone number. Please, try again !";
            $this->jsonecho();
        }

        $Patient = Controller::model("Patient");
        $result = $Patient->existsByPhone($phone); // Gọi phương thức và lưu kết quả vào biến

        /**Step 3 - does the patient exist? */
        // $query = DB::table(TB_PREFIX.TB_PATIENTS)
        //             ->where(TB_PREFIX.TB_PATIENTS.".phone" , "=" , $phone);
        // $result = $query->get();

        /*Step 3 - Case 1 - if this patient does not exist in the database, we will create a new account for this patient*/
        if( count($result) == 0 )
        {
            $Patient = Controller::model("Patient");
            $Patient->set("email", "")
                ->set("phone", $phone)
                ->set("password", password_hash($password, PASSWORD_DEFAULT) )
                ->set("name", $phone)
                ->set("birthday", "")
                ->set("gender", 0)
                ->set("address", "")
                ->set("avatar", "")
                ->set("create_at", date("Y-m-d H:i:s"))
                ->set("update_at", date("Y-m-d H:i:s"))
                ->save();

            $msg = "Welcome to UMBRELLA CORPORATION, ".$Patient->get("name")." !";
            $data = array(
                "id"    => (int)$Patient->get("id"),
                "email" => $Patient->get("email"),
                "phone" => $Patient->get("phone"),
                "name" => $Patient->get("name"),
                "gender" => (int)$Patient->get("gender"),
                "birthday" => $Patient->get("birthday"),
                "address" => $Patient->get("address"),
                "avatar" => $Patient->get("avatar"),
                "create_at" => $Patient->get("create_at"),
                "update_at" => $Patient->get("update_at")
            );

            $hashPassword = $Patient->get("password");
        }
        /**Step 3 - Case 2 - if this patient logins again, we will return JWT token & his/her information except password */
        else 
        {
            /**is password correct ? */
            $hashPassword = $result[0]->password;
            if( !password_verify($password, $hashPassword ) )
            {
                $this->resp->msg = "Your email or password is incorrect. Try again !";
                $this->jsonecho();
            }


            
            /**yes, WELCOME BACK */
            $msg = "Welcome back to UMBRELLA CORPORATION, ".$result[0]->name." !";
            $data = array(
                "id"    => (int)$result[0]->id,
                "email" => $result[0]->email,
                "phone" => $result[0]->phone,
                "name" => $result[0]->name,
                "gender" => (int)$result[0]->gender,
                "birthday" => $result[0]->birthday,
                "address" => $result[0]->address,
                "avatar" => $result[0]->avatar,
                "create_at" => $result[0]->create_at,
                "update_at" => $result[0]->update_at
            );

            // // need update $password again
            // $password = $result[0]->password;
        }




        $payload = $data;
        $payload["hashPass"] = md5($hashPassword);
        $payload["iat"] = time();
        $jwt = Firebase\JWT\JWT::encode($payload, EC_SALT, 'HS256');

        $this->resp->result = 1;
        $this->resp->msg = $msg;
        $this->resp->accessToken = $jwt;
        $this->resp->data = $data;
        $this->jsonecho();
    }


    



}



?>
