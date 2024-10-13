<?php
require_once APPPATH.'/Model/DoctorModel.php';
require_once APPPATH.'/Core/Input.php';

class DoctorController extends Controller{
    public function process()
        {
            $request_method = Input::method();
            
            if($request_method === 'GET')
            {
                $this->getAllDoc();
            }
            else if( $request_method === 'POST')
            {
                
            }
    }

    public function getAllDoc(){
        $docModel = new DoctorModel(); // Khởi tạo model Doc
        $docs = $docModel->getAllDoc();  // Lấy tất cả bác sĩ từ model
        
        if(!empty($docs)) {
            // Nếu có, trả về dữ liệu dưới dạng JSON
            echo json_encode($docs);
        } else {
            // Nếu không có dữ liệu, trả về thông báo lỗi
            echo json_encode(["message" => "No doc found."]);
        }
    }





}




?>