<?php
require_once APPPATH.'/Model/DrugModel.php';
require_once APPPATH.'/Core/Input.php';
class DrugController extends Controller{
// Gọi đến  phương thức này trong routes 
    public function process($id = null)
        {
            $request_method = Input::method();
            if($request_method === 'GET')
            {
                if ($id !== null) {
                    $this->getDrugById($id); // Truyền $id vào phương thức getDrugById
                } else {
                    echo json_encode(["message" => "ID is required"]);
                }
            }
            else if( $request_method === 'POST')
            {
                
            }
        }


    public function getDrugById($id){
        
        
        $drugModel = new DrugModel();
        $drug = $drugModel->getDrug($id);

        if(!empty($drug)){
            echo json_encode($drug);
        }
        else{
            echo json_encode(["message" =>"No drug found"]);
        }


    }
    
    




}

?>