<?php
require_once APPPATH.'/Core/Input.php';
require_once APPPATH.'/Model/AppointmentModel.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
class ChartsController extends Controller{
    public function process(){
            
        $jwt = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $jwt =$headers['Authorization'];
        }
        if (!$jwt && isset($_COOKIE['accessToken'])) {
            $jwt = $_COOKIE['accessToken'];
        }
            
        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key(EC_SALT, 'HS256'));
                // Lưu thông tin người dùng vào biến hoặc session
                $_SESSION['AuthUser'] = $decoded; 
                //$jsonDecoded = json_encode($decoded, JSON_PRETTY_PRINT);
                //echo $jsonDecoded;
                //$userRole = $decoded->role;
                //echo($userRole);
                
            } catch (Exception $e) {
                // Xử lý lỗi nếu token không hợp lệ
                echo json_encode(["message" => "Token is invalid or expired."]);
                exit;
            }
        } else {
            // Nếu không có token
            header("Location: " . APPURL . "/login");
            exit;
        }
        if($decoded->role != "admin" && $decoded->role != "supporter" && $decoded->role != "menber"){
            $this->resp->msg= "you are not admin you can not do this action";
            $this->jsonecho();
        }
        $request_method = Input::method();
        
        if($request_method === 'GET')
            {
                $request = Input::get("request");
                switch ($request) {
                    case "appointmentsinlast7days":
                        $this->appointmentsInLast7Days();
                        break;
                    case "appointmentandbookinginlast7days":
                        $this->appointmentsAndBookingInLast7days();
                        break;
                    default:
                        $this->resp->result = 0;
                        $this->resp->msg = "Your request is invalid!";
                        $this->jsonecho();
                }
            }
            else 
            {
                $this->resp->result = 0;
                $this->resp->msg = "Your request is invalid!";
                $this->jsonecho();
            }
        




    }

    private function appointmentsInLast7Days()
    {
        $this->resp->result = 0;
            date_default_timezone_set('Asia/Ho_Chi_Minh');// set timezone

            $length   = Input::get("length") ? (int)Input::get("length") : 10;
            $start    = Input::get("start") ? (int)Input::get("start") : 0;
            $data = [];

            /**Step 2 - get first day | finish day of a week */
            $date = new \Moment\Moment("now", date_default_timezone_get());
            $from = $date->cloning()->subtractDays(6);
            $to = $date->cloning();
            try 
            {
                $AppoiModel = new AppointmentModel();
                $query = $AppoiModel->appointmentsInLast7($from,$to);


                $result = $query->get();

                for($x = 0; $x<7;$x++)
                {
                    $data[] = array(
                        "date" => $from->format("Y-m-d"),
                        "appointment" => 0
                    );
                    $from->addDays(1);
                }

                
                for($x = 0; $x < count($data); $x++)
                {
                    $date = $data[$x]["date"];
                    foreach($result as $r)
                    {
                        if($r->date == $date)
                        {
                            $data[$x]["appointment"] = (int)$r->quantity;
                        }
                    }
                }

                $this->resp->result = 1;
                $this->resp->quantity = count($data);
                $this->resp->data = $data;
            }
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();




    }
    private function appointmentsAndBookingInLast7days()
    {
        $this->resp->result = 1;
        date_default_timezone_set('Asia/Ho_Chi_Minh');// set timezone
 
        $length   = Input::get("length") ? (int)Input::get("length") : 10;
        $start    = Input::get("start") ? (int)Input::get("start") : 0;
        $data = [];

        /**Step 2 - get first day | finish day of a week */
        $date = new \Moment\Moment("now", date_default_timezone_get());
        $from = $date->cloning()->subtractDays(6);
        $to = $date->cloning();
        try 
            {
                $AppoiModel = new AppointmentModel();
                $query = $AppoiModel->appointmentsInLast7($from,$to);
                

                $result = $query->get();

                for($x = 0; $x<7;$x++)
                {
                    $date = $from->format("Y-m-d");
                     $data[] = array(
                         "date" => $date ,
                         "appointment" => 0,
                         "booking" => 0
                     );
                     $from->addDays(1);
                }

                
                for($x = 0; $x < count($data); $x++)
                {
                    $date = $data[$x]["date"];
                    foreach($result as $r)
                    {
                        if($r->date == $date)
                        {
                            
                            $data[$x]["appointment"] = (int)$r->quantity;
                            $data[$x]["booking"] = $AppoiModel->quantityBookingInDate($date);
                             
                        }
                    }
                }

                $this->resp->result = 1;
                $this->resp->quantity = count($data);
                $this->resp->data = $data;
            }
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
    }


}

?>