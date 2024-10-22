<?php
    require_once APPPATH.'/Core/Input.php';
    require_once APPPATH.'/Model/SpecialityModel.php';
    class SpecialitiesController extends Controller{
        public function process(){
            $request_method = Input::method();
            if($request_method === 'POST'){

            }
            elseif($request_method === 'GET'){


            }




        }
        public function getAllSpeciality(){
            $SpecialityModel = new SpecialityModel();
            
        }




}


?>