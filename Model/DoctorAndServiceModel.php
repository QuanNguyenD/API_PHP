<?php

class DoctorAndServiceModel extends DataEntry{
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
	    	} else {
	    		$col = "name";
	    	}


	    	if ($col) {
		    	$query = $this->qb->table(TB_PREFIX.TB_DOCTOR_AND_SERVICE)
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
                "service_id" => "",
                "doctor_id" => ""
	    	);


	    	foreach ($defaults as $field => $value) {
	    		if (is_null($this->get($field)))
	    			$this->set($field, $value);
	    	}
	    }


	    /**
	     * Insert Data as new entry
	     */
	    public function insert()
	    {
	    	if ($this->isAvailable())
	    		return false;

	    	$this->extendDefaults();

	    	$id = $this->qb->table(TB_PREFIX.TB_DOCTOR_AND_SERVICE)
		    	->insert(array(
		    		"id" => null,
		    		"service_id" => $this->get("service_id"),
                    "doctor_id" => $this->get("doctor_id")
		    	));

	    	$this->set("id", $id);
	    	$this->markAsAvailable();
	    	return $this->get("id");
	    }


	    /**
	     * Update selected entry with Data
	     */
	    public function update()
	    {
	    	if (!$this->isAvailable())
	    		return false;

	    	$this->extendDefaults();

	    	$id = $this->qb->table(TB_PREFIX.TB_DOCTOR_AND_SERVICE)
	    		->where("id", "=", $this->get("id"))
		    	->update(array(
		    		"service_id" => $this->get("service_id"),
                    "doctor_id" => $this->get("doctor_id")
		    	));

	    	return $this;
	    }


	    /**
		 * Remove selected entry from database
		 */
	    public function delete()
	    {
	    	if(!$this->isAvailable())
	    		return false;

            $this->qb->table(TB_PREFIX.TB_DOCTOR_AND_SERVICE)
            ->where("id", "=", $this->get("id"))->delete();
	    	$this->is_available = false;
	    	return true;
	    }

        public function doctorwokingservice($id){
            $query = $this->qb->table(TB_PREFIX.TB_SERVICES)
                ->where(TB_PREFIX.TB_DOCTOR_AND_SERVICE.".service_id", "=", $id)

                ->leftJoin(TB_PREFIX.TB_DOCTOR_AND_SERVICE, 
                           TB_PREFIX.TB_DOCTOR_AND_SERVICE.".service_id", "=", TB_PREFIX.TB_SERVICES.".id")

                ->leftJoin(TB_PREFIX.TB_DOCTORS,
                           TB_PREFIX.TB_DOCTORS.".id", "=", TB_PREFIX.TB_DOCTOR_AND_SERVICE.".doctor_id")

                ->leftJoin(TB_PREFIX.TB_SPECIALITIES,
                        TB_PREFIX.TB_SPECIALITIES.".id", "=", TB_PREFIX.TB_DOCTORS.".speciality_id")
                           
                ->select([
                    $this->qb->raw(TB_PREFIX.TB_DOCTOR_AND_SERVICE.".id as doctor_and_service_id"),
                    $this->qb->raw(TB_PREFIX.TB_DOCTORS.".avatar as doctor_avatar"),
                    $this->qb->raw(TB_PREFIX.TB_DOCTORS.".id as doctor_id"),
                    $this->qb->raw(TB_PREFIX.TB_DOCTORS.".name as doctor_name"),
                    $this->qb->raw(TB_PREFIX.TB_DOCTORS.".phone as doctor_phone"),
                    $this->qb->raw(TB_PREFIX.TB_DOCTORS.".email as doctor_email"),
                    $this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".id as speciality_id"),
                    $this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".name as speciality_name"),
                    

                ]);
            return $query;
        }


}




?>