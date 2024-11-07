<?php


class DoctorModel extends DataEntry{
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
                
		    	$query = $this->qb->table('tn_doctors')
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
                "description" => "",
                "price" => 0,
				"role" => "admin",
				"active" => "1",
                "avatar" => "",
				"create_at" => date("Y-m-d H:i:s"),
				"update_at" => date("Y-m-d H:i:s"),
                "speciality_id" => "",
				"room_id" => "",
				"recovery_token" => ""
	    	);


	    	foreach ($defaults as $field => $value) {
	    		if (is_null($this->get($field)))
	    			$this->set($field, $value);
	    	}
	    }

        public function getAllDoc(){
            return $this->qb->table('tn_doctors')->get();
        }
		 public function getAllDoctor(){
		 	$query = $this->qb->table(TB_PREFIX.TB_DOCTORS)
			->leftJoin(TB_PREFIX.TB_SPECIALITIES,TB_PREFIX.TB_SPECIALITIES.".id","=",TB_PREFIX.TB_DOCTORS.".speciality_id")
			->leftJoin(TB_PREFIX.TB_ROOMS,TB_PREFIX.TB_ROOMS.".id","=",TB_PREFIX.TB_DOCTORS.".room_id")
			->leftJoin(TB_PREFIX.TB_DOCTOR_AND_SERVICE,TB_PREFIX.TB_DOCTOR_AND_SERVICE.".doctor_id","=", TB_PREFIX.TB_DOCTORS.".id")
			->leftJoin(TB_PREFIX.TB_SERVICES,TB_PREFIX.TB_SERVICES.".id","=",TB_PREFIX.TB_DOCTOR_AND_SERVICE.".service_id")
			->groupBy(TB_PREFIX.TB_DOCTORS.".id")
			->select([
				TB_PREFIX.TB_DOCTORS.".*",
				$this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".id as speciality_id"),
				$this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".name as speciality_name"),
				$this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".description as speciality_description"),
				$this->qb->raw(TB_PREFIX.TB_ROOMS.".id as room_id"),
				$this->qb->raw(TB_PREFIX.TB_ROOMS.".name as room_name"),
				$this->qb->raw(TB_PREFIX.TB_ROOMS.".location as room_location")

			]);

			return $query;
		}

		public function getDocById($id){
			$query = $this->qb->table(TB_PREFIX.TB_DOCTORS)
			->where(TB_PREFIX.TB_DOCTORS.".id","=",$id)
			->leftJoin(TB_PREFIX.TB_SPECIALITIES,TB_PREFIX.TB_SPECIALITIES.".id","=",TB_PREFIX.TB_DOCTORS.".speciality_id")
			->leftJoin(TB_PREFIX.TB_ROOMS,TB_PREFIX.TB_ROOMS.".id","=",TB_PREFIX.TB_DOCTORS.".room_id")
			->groupBy(TB_PREFIX.TB_DOCTORS.".id")
			->select([
				TB_PREFIX.TB_DOCTORS.".*",
				$this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".id as speciality_id"),
				$this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".name as speciality_name"),
				$this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".description as speciality_description"),
				$this->qb->raw(TB_PREFIX.TB_ROOMS.".id as room_id"),
				$this->qb->raw(TB_PREFIX.TB_ROOMS.".name as room_name"),
				$this->qb->raw(TB_PREFIX.TB_ROOMS.".location as room_location")

			]);
			return $query->get();
		}


		public function insert()
		{
			if ($this->isAvailable())
	    		return false;

	    	//$this->extendDefaults();
			// Chèn dữ liệu từ $this->data vào bảng
			$insertId = $this->qb->table(TB_PREFIX .TB_DOCTORS)
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
		public function update(){
			if (!$this->isAvailable())
	    		return false;
			$update = $this->qb->table(TB_PREFIX .TB_DOCTORS)
							->where("id", "=", $this->get("id"))
							->update(array(
								"email" => $this->get("email"),
								"phone" => $this->get("phone"),
								"password" => $this->get("password"),
								"name" => $this->get("name"),
								"description" => $this->get("description"),
								"price" => $this->get("price"),
								"role" => $this->get("role"),
								"active" => $this->get("active"),
								"avatar" => $this->get("avatar"),
								"create_at" => $this->get("create_at"),
								"update_at" => $this->get("update_at"),
								"speciality_id" => $this->get("speciality_id"),
								"room_id" => $this->get("room_id"),
								"recovery_token" => $this->get("recovery_token")
							));

			if ($update) {
				return $update;
			}

				
		}

		public function delete(){
			if(!$this->isAvailable())
	    		return false;
			$this->qb->table(TB_PREFIX .TB_DOCTORS)->where("id", "=", $this->get("id"))->delete();
			$this->is_available = false;
			return true;

		}


		/**
	     * Check if account has administrative privileges
	     * @return boolean 
	     */
	    public function isAdmin()
	    {
	    	if ($this->isAvailable() && 
				in_array($this->get("role"), array("developer", "admin"))) {
	    		return true;
	    	}

	    	return false;
	    }





}



?>