<?php


class App{

    private function db(){
        $config = [
            'driver' => DATA_BASE,
            'host' => DB_HOST,
            'database' => DB_NAME,
            'password' => '',
            'username' => USER_NAME,
            'charset' => DB_CHARSET,
            'option' => [
                PDO::ATTR_TIMEOUT => 15,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]


        ];
        $connection = new \Pixie\Connection('mysql',$config);
        return $connection;
        

    }
    //Khi không sử dụng bí danh, 
    //có thể khởi tạo trình xử lý QueryBuilder riêng biệt, 
    //hữu ích cho Dependency Injection và Testing.
    //ví dụ về truy xuất thông tin name = Sana trong bảng my_table
    //Có cách dùng bí danh, mà bị lỗi, chưa biết sửa như thế nào
    //new \Pixie\Connection('mysql', $config, 'QB');-----Ví dụ về dùng bí danh
    // public function test(){
    //     $connection = $this ->db();
    //     $qb = new \Pixie\QueryBuilder\QueryBuilderHandler($connection);

    //     $query = $qb->table('my_table')->where('name', '=', 'Sana');
    // }
    public function process(){

        $this -> db();


    }
    


    
}



