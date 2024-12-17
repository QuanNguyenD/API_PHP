<?php
require_once APPPATH.'/Model/AppointmentModel.php';
require_once APPPATH.'/Core/Input.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
    class AppointmentQueueNowController extends Controller{
        public function process(){
            $jwt = null;
            $headers = apache_request_headers();
                
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
            
    
            $request_method = Input::method();
            
            if($request_method === 'GET')
            {
                $this->getQueue();
            }
            
        }


        private function getQueue(){
            $this->resp->result = 0;
            $date = Date("d-m-Y");
            $jwt = null;
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $jwt =$headers['Authorization'];
            }
            if (!$jwt && isset($_COOKIE['accessToken'])) {
                $jwt = $_COOKIE['accessToken'];
            }
            $AuthUser = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
            if( $AuthUser->role == "member")
            {
                $doctor_id = $AuthUser->id;
            }
            else
            {
                $doctor_id = Input::get("doctor_id");
                if( !$doctor_id )
                {
                    $this->resp->msg = "Missing doctor ID";
                    $this->jsonecho();
                }
            }
        }




    }



?>