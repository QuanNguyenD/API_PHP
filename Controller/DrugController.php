<?php
require_once APPPATH.'/Model/DrugModel.php';

class DrugController extends Controller{

    


    public function getAllDrugs(){
        $drugModel = new DrugModel(); // Khởi tạo model Drug
        $drugs = $drugModel->getAllDrugs();  // Lấy tất cả các thuốc từ model
        
        if(!empty($drugs)) {
            // Nếu có, trả về dữ liệu dưới dạng JSON
            echo json_encode($drugs);
        } else {
            // Nếu không có dữ liệu, trả về thông báo lỗi
            echo json_encode(["message" => "No drugs found."]);
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