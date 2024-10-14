<?php
    class PatientModel extends DataEntry
	{
        protected $db;
        protected $qb;

        public function __construct($uniqid=0)
	    {
            $this->db = App::getConnection();     
		    $this->qb = new \Pixie\QueryBuilder\QueryBuilderHandler($this->db);
	        parent::__construct();
	        $this->select($uniqid);
	    }
        public function select($uniqid)
	    {
	    	if (is_int($uniqid) || ctype_digit($uniqid)) {
	    		$col = $uniqid > 0 ? "id" : null;
	    	} else if (filter_var($uniqid, FILTER_VALIDATE_EMAIL)) {
	    		$col = "email";
	    	}
			else 
			{
				$col = "phone";
			}

	    	if ($col) {
		    	$query = $this->qb->table(TB_PREFIX.TB_PATIENTS)
			    	      ->where($col, "=", $uniqid)
			    	      ->limit(1)
			    	      ->select("*");
		    	if ($query->count() == 1) {
		    		$resp = $query->get();
		    		$r = $resp[0];

		    		foreach ($r as $field => $value)
		    			$this->set($field, $value);

		    		$this->is_available = true;
		    	} else {
		    		$this->data = array();
		    		$this->is_available = false;
		    	}
	    	}

	    	return $this;
	    }

        /**
         * Kiểm tra xem bệnh nhân có tồn tại hay không dựa trên số điện thoại
         * 
         * @param string $phone Số điện thoại của bệnh nhân
         * @return bool Trả về true nếu bệnh nhân tồn tại, false nếu không
         */

         public function existsByPhone($phone)
        {
            $query = $this->qb->table(TB_PREFIX.TB_PATIENTS)
                        ->where("phone", "=", $phone);
            $result = $query->get();

            return $result;
            // Trả về bản ghi đầu tiên nếu tìm thấy, ngược lại là false
        }


		public function insert()
		{
			// Chèn dữ liệu từ $this->data vào bảng
			$insertId = $this->qb->table(TB_PREFIX . TB_PATIENTS)
								->insert($this->data);

			// Nếu chèn thành công, lấy lại ID và cập nhật trong đối tượng
			if ($insertId) {
				$this->set("id", $insertId);  // Cập nhật ID mới được tạo vào đối tượng
				$this->is_available = true;   // Đánh dấu rằng bản ghi này tồn tại
				return $insertId;
			}

			return false;
		}
		public function save()
		{
    		return $this->isAvailable() ? $this->update() : $this->insert();
		}



    }
  

?>
