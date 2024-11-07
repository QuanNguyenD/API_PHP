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

		public function extendDefaults()
	    {
	    	$defaults = array(
	    		"email" => "",
                "phone" => "",
	    		"password" => "",
	    		"name" => "",
				"gender" => 0,
                "birthday" => "",
                "address" => "",
                "avatar" => "",
				"create_at" => date("Y-m-d H:i:s"),
				"update_at" => date("Y-m-d H:i:s")
	    	);


	    	foreach ($defaults as $field => $value) {
	    		if (is_null($this->get($field)))
	    			$this->set($field, $value);
	    	}
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

		public function update()
		{
			if(!$this->isAvailable()){
				return false;
			}
			$this->extendDefaults();
			//$update = $this->qb->table(TB_PREFIX.TB_PATIENTS)->where("id","=",$this->get("id"))->update($this->data);
			$update = $this->qb->table(TB_PREFIX.TB_PATIENTS)
	    		->where("id", "=", $this->get("id"))
		    	->update(array(
		    		"email" => $this->get("email"),
		    		"phone" => $this->get("phone"),
                    "password" => $this->get("password"),
                    "name" => $this->get("name"),
					"gender" => $this->get("gender"),
                    "birthday" => $this->get("birthday"),
                    "address" => $this->get("address"),
                    "avatar" => $this->get("avatar"),
                    "create_at" => $this->get("create_at"),
                    "update_at" => $this->get("update_at")
		    	));
			return $update;
		}

		public function delete()
	    {
	    	if(!$this->isAvailable())
	    		return false;

	    	$this->qb->table(TB_PREFIX.TB_PATIENTS)->where("id", "=", $this->get("id"))->delete();
	    	$this->is_available = false;
	    	return true;
	    }
		


	    /**
	     * Check if user is expired
	     * @return boolean true on expired
	     */
	    public function isExpired()
	    {
	    	if ($this->isAvailable()) {
	    		$ed = new DateTime($this->get("expire_date"));
	    		$now = new DateTime();
	    		if ($ed > $now) {
	    			return false;
	    		}
	    	}

	    	return true;
	    }


	    /**
	     * get date-time format preference
	     * @return null|string 
	     */
	    public function getDateTimeFormat()
	    {
	    	if (!$this->isAvailable()) {
	    		return null;
	    	}

	    	$date_format = $this->get("preferences.dateformat");
	    	$time_format = $this->get("preferences.timeformat") == "24"
	    	             ? "H:i" : "h:i A";
	    	return $date_format . " " . $time_format;
	    }


	    /**
	     * Check if user's (primary) email is verified or not
	     * @return boolean 
	     */
	    public function isEmailVerified()
	    {
	    	if (!$this->isAvailable()) {
	    		return false;
	    	}

	    	if ($this->get("data.email_verification_hash")) {
	    		return false;
	    	}

	    	return true;
	    }


	    

	    /**
	     * Set the user's (primary) email address as verified
	     */
	    public function setEmailAsVerified()
	    {
	    	if (!$this->isAvailable()) {
	    		return false;
	    	}

	    	$data = json_decode($this->get("data"));
	    	if (isset($data->email_verification_hash)) {
		    	unset($data->email_verification_hash);
		    	$this->set("data", json_encode($data))
		    	     ->update();
	    	}

	    	return true;
	    }

		public function getAll(){
			return $this->qb->table(TB_PREFIX.TB_PATIENTS)->select("*");
		}

		public function getById($id){
			$query = $this->qb->table(TB_PREFIX.TB_PATIENTS)->where(TB_PREFIX.TB_PATIENTS.".id","=",$id)->select("*");
			return $query;

		}



    }
  

?>
