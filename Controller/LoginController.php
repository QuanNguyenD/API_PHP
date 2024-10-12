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
        $type = Input::post("type");
        $password = Input::post("password");
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
            //$this->loginByPatient();
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
        $password = Input::post("password");
        $email = Input::post("email");

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
    



}



?>
