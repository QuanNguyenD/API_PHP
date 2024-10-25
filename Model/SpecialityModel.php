<?php

    class SpecialityModel extends DataEntry{

        protected $db;
        protected $qb;
        public function __construct($uniqid = 0)
        {
            $this->db = App::getConnection();
            $this->qb = new \Pixie\QueryBuilder\QueryBuilderHandler($this->db);
            parent:: __construct();
            $this -> select($uniqid);
        }

        public function select($uniqid)
	    {
	    	if (is_int($uniqid) || ctype_digit($uniqid)) {
	    		$col = $uniqid > 0 ? "id" : null;
	    	} else {
	    		$col = "name";
	    	}


	    	if ($col) {
		    	$query = $this->qb->table(TB_PREFIX.TB_SPECIALITIES)
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
         * Extends  defaut value
         */

         public function extendDefauts()
         {
            $defaults = array(
                "name" => "",
                "description" =>"",
                "image" =>""
            );

            foreach($defaults as $field => $value){
                if(is_null($this->get($field)))
                    $this->set($field,$value);
            }
         }

        /**
         * Thêm data
         */
        public function insert()
        {
            //Đã có trong CSDL
            if($this ->isAvailable())
                return false;
            $this -> extendDefauts();

            $id = $this->qb->table(TB_PREFIX.TB_SPECIALITIES)
            ->insert(array(
                "id" => null,
                "name" => $this->get("name"),
                "description" => $this->get("description"),
                "image" => $this->get("image")
            ));

            $this->set("id", $id);
            $this->markAsAvailable();
            return $this->get("id");
        }

        /**
         * Update data
         * 
        */
        public function update()
        {
            if(!$this -> isAvailable())
                return false;

            $this -> extendDefauts();
            $id = $this->qb->table(TB_PREFIX.TB_SPECIALITIES)
                ->where("id", "=", $this->get("id"))->update(
                array(
                    "name" => $this->get("name"),
                    "description" => $this->get("description"),
                    "image"=> $this->get("image")
                )
            );

            return $this;
        }

        /**
         * Datele
         * 
         */
         public function dalete(){
            if(!$this -> isAvailable())
                return false;

            $this->qb->table(TB_PREFIX.TB_SPECIALITIES)->where(
                "id","=",$this->get("id")
            )->delete();
            $this->is_available = false;
            return true;

        }
        /**
         * GetSpecialityById
         * 
         */
        public function getSpeciality($id){
            return $this->qb->table(TB_PREFIX.TB_SPECIALITIES)->where(TB_PREFIX.TB_SPECIALITIES.".id", "=", $id)->first();
        }
        /**
         * Get All Speciality
         */
        public function getAllSpeciality(){
            return $this->qb->table(TB_PREFIX.TB_SPECIALITIES)->get();
        }

        //check duplicate 
        public function checkDuplicate($name){
            $query = $this->qb->table(TB_PREFIX.TB_SPECIALITIES)
                            ->where(TB_PREFIX.TB_SPECIALITIES.".name","=",$name);
            $result = $query->get();
            return $result;                
        }
        


    }



?>