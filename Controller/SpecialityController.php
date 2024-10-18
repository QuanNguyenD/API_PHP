<?php
    require_once APPPATH.'/Core/Input.php';
    require_once APPPATH.'/Model/SpecialityModel.php';
    class SpecialityController extends Controller{
        /**
         * process
         */
        
        public function process($id = null){
            $request_method = Input::method();
            //So sánh cả giá trị và loại dữ liệu (3 dấu =)
            if($request_method === 'GET'){
                if ($id !== null) {
                    $this->getById($id); // Truyền $id vào phương thức getDrugById
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
            }

        }


        private function getById($id){
            
            try{
                $SpecialityModel = new SpecialityModel();
                $Speciality = $SpecialityModel->getSpeciality($id);

                if( empty($Speciality) )
                {
                    $this->resp->msg = "Speciality is not available";
                    $this->jsonecho();
                }
                else{
                    echo json_encode($Speciality);
                }
            }catch(Exception $ex){
                $this->resp->msg = $ex->getMessage();
            }
            
            

        }



    }



?>