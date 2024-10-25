<?php 
    class DoctorsController extends Controller{
        public function process(){
            $AuthUser = $this->getVariable("AuthUser");

            if (!$AuthUser)
            {
                header("Location: ".APPURL."/login");
                exit;
            }
            
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
            $AuthUser = $this->getVariable("AuthUser");
            $data = [];

                
        }




    }




?>