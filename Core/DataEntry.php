<?php
    //Quản lý thao tác với dữ liệu
    // Bao gồm các phương thức thao tác với dữ liệu

    class DataEntry{
        
        protected $data;
        protected $is_available;

        //Hàm khởi tạo

        public function __construct()
        {
            $this->data = array();//Khởi tạo mảng data
            $this->is_available = false;

        }

        //Kiểm tra giá trị có sãn hay không 
        public function isAvailable(){
            return $this->is_available;
        }

        /**
         * Để tạo các object từ các data chưa qua sử lý 
         * @return self
         */

        public function markAsAvailable(){
            $this -> is_available = true;
            return $this;
        }

        /**
         * Đặt giá trị cho trường dữ liệu
         * @param string $field Tên của trường dữ liệu
         * @param mixed $value Giá trị của trường dữ liệu
         * @param bool $parse Nếu true thì coi $field là một json
         * 
         */
        public function set($field, $value, $parse = true){
            // Kiểm tra xem $filed có là một chuỗi không, và nó có rỗng không
            if(is_string($field) && $field){
                if($parse){
                    $field = explode(".",$field);
                }
                //Nếu mảng $fields không rỗng và có nhiều hơn một phần tử 
                //thì có cấu trúc lồng nhau.
                if(!empty($field) && count($field)> 1){
                    //Xử lý các trường lồng nhau

                    //Gán column là phần tử đầu tiên trong mảng $field
                    $column = $field[0];
                    //Loại bỏ phần tử đầu tiên trong mảng
                    array_shift($field);
                    // đếm số phần tử còn lại trong mảng và gán vào $total
                    $total = count($field);

                    // biến $newval sẽ được cập nhật sau mỗi lần lặp
                    $newval = $value;
                    for($i= $total-1; $i>=0; $i--){
                        $newval = array($field[$i] => $newval);
                    }

                    $currentval = json_decode($this ->get($column), true);
                    if(!$currentval){
                        $currentval = array();
                    }

                    $this -> data[$column] = json_encode(array_replace_recursive($currentval, $newval));
                }else{
                    $this->data[$field] = $value;
                }
                

            }
            return $this;
        }

        /**
         * Lấy giá trị của trường dữ liệu
         * @param string $field Tên của trường dữ liệu
         * @param bool $parse Nếu đúng thì coi $field như trường json và trả về trường con trong trường hợp tìm thấy hoặc null
         * @return mixed Giá trị của trường dữ liệu đã cho (hoặc null nếu trường không tồn tại)
         */
        public function get($field, $parse = true){
            if(is_string($field) && $field){
                if($parse){
                    $fields = explode(".", $field);
                }
                if(!empty($fields) && count($fields) >1 ){
                    $column = $fields[0];
                    //Kiểm tra xem phần tử $column có tồn tại trong mảng $data hay không.
                    if(isset($this->data[$column]) && is_string($column) && $column){
                        $parsedjson = @json_decode($this->data[$column]);
                        //Kiểm tra xem $parsedjson có là giá trị truthy hay không
                        if ($parsedjson) {

                            //Loại bỏ phần tử đầu tiên trong mảng $fields
                            array_shift($fields);

                            //Lấy giá trị từ JSON đã giải mã
                            $val = $parsedjson;
                            foreach ($fields as $name) {
                                if ($name && isset($val->$name)) {
                                    $val = $val->$name;
                                } else {
                                    $val = null; 
                                    break;
                                }
                            }

                            return is_string($val) ? trim($val) : $val;
                        }
                    }
                }else{
                    if (isset($this->data[$field])) {
                        return is_string($this->data[$field]) ? trim($this->data[$field]) : $this->data[$field];
                    }
                }
            }
            return null;
        }

        /**
         * Select entry with uniqid
         * @param  int|string $uniqid Giá trị bất kỳ của trường duy nhất 
         * @return DataEntry
         */
        public function select($uniqid){
            return $this;
        }
        /**
         * Mở rộng các giá trị mặc định
         * @return DataEntry
         */
        public function extendDefauts(){
            return $this;
        }

        /**
         * Chèn dữ liệu
         */
        public function insert()
        {
            return $this;	
        }

        public function update()
        {
                return $this;
        }

        /**
         * Cập nhật hoặc chèn
         * @return mixed
         */
        public function save()
        {
            return $this->isAvailable() ? $this->update() : $this->insert();
        }

        public function delete()
        {
            return $this;
        }
        /**
        * Cũng là xóa nhưng tên gọi khác
        */
        public function remove()
        {
            return $this->delete();
        }

        /**
        * Làm mới dư liệu
        */
        public function refresh()
        {
            $this->select($this->get("id"));
            return $this;
        }







    }
?>

