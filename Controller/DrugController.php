<?php
require_once APPPATH.'/Model/DurgModel.php';

class DrugController{
    public function getDrugs(){
        $drugModel = new DurgModel(); // Khởi tạo model Drug
        $drugs = $drugModel->getAllDrugs();  // Lấy tất cả các thuốc từ model
        
        if(!empty($drugs)) {
            // Nếu có, trả về dữ liệu dưới dạng JSON
            echo json_encode($drugs);
        } else {
            // Nếu không có dữ liệu, trả về thông báo lỗi
            echo json_encode(["message" => "No drugs found."]);
        }
    }
    // Kiểm tra xem có dữ liệu hay không
    




}

?>