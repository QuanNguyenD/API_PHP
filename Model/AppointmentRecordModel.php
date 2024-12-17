<?php
class AppointmentRecordModel extends DataEntry
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
	    	} else {
	    		$col = "appointment_id";
	    	}


	    	if ($col) {
		    	$query = $this->qb->table(TB_PREFIX.TB_APPOINTMENT_RECORDS)
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
                "appointment_id" => "",
                "reason" => "",
                "description" => "",
                "status_before" => "",
                "status_after" => "",
                "create_at" => "",
                "update_at" => ""
	    	);


	    	foreach ($defaults as $field => $value) {
	    		if (is_null($this->get($field)))
	    			$this->set($field, $value);
	    	}
	    }
        public function insert()
	    {
	    	if ($this->isAvailable())
	    		return false;

	    	$this->extendDefaults();

	    	$id = $this->qb->table(TB_PREFIX.TB_APPOINTMENT_RECORDS)
		    	->insert(array(
		    		"id" => null,
                    "appointment_id" => $this->get("appointment_id"),
		    		"reason" => $this->get("reason"),
                    "description" => $this->get("description"),
                    "status_before" => $this->get("status_before"),
                    "status_after" => $this->get("status_after"),
                    "create_at" => $this->get("create_at"),
                    "update_at" => $this->get("update_at")
		    	));

	    	$this->set("id", $id);
	    	$this->markAsAvailable();
	    	return $this->get("id");
	    }
        public function update()
	    {
	    	if (!$this->isAvailable())
	    		return false;

	    	$this->extendDefaults();

	    	$id = $this->qb->table(TB_PREFIX.TB_APPOINTMENT_RECORDS)
	    		->where("id", "=", $this->get("id"))
		    	->update(array(
                    "appointment_id" => $this->get("appointment_id"),
		    		"reason" => $this->get("reason"),
                    "description" => $this->get("description"),
                    "status_before" => $this->get("status_before"),
                    "status_after" => $this->get("status_after"),
                    "create_at" => $this->get("create_at"),
                    "update_at" => $this->get("update_at")
		    	));

	    	return $this;
	    }
        public function delete()
	    {
	    	if(!$this->isAvailable())
	    		return false;

	    	$this->qb->table(TB_PREFIX.TB_APPOINTMENT_RECORDS)
            ->where("id", "=", $this->get("id"))->delete();
	    	$this->is_available = false;
	    	return true;
	    }

        public function appoinmentRecord(){
        $query = $this->qb->table(TB_PREFIX.TB_APPOINTMENT_RECORDS)
                            ->leftJoin(TB_PREFIX.TB_APPOINTMENTS, 
                                    TB_PREFIX.TB_APPOINTMENTS.".id", "=", TB_PREFIX.TB_APPOINTMENT_RECORDS.".appointment_id")
                            ->leftJoin(TB_PREFIX.TB_DOCTORS,
                                        TB_PREFIX.TB_DOCTORS.".id", "=", TB_PREFIX.TB_APPOINTMENTS.".doctor_id")
                            ->leftJoin(TB_PREFIX.TB_PATIENTS,
                                        TB_PREFIX.TB_PATIENTS.".id", "=", TB_PREFIX.TB_APPOINTMENTS.".patient_id")
                            ->leftJoin(TB_PREFIX.TB_SPECIALITIES,
                                        TB_PREFIX.TB_SPECIALITIES.".id", "=", TB_PREFIX.TB_DOCTORS.".speciality_id")
                            ->select([
                                $this->qb->raw(TB_PREFIX.TB_APPOINTMENT_RECORDS.".*"),

                                $this->qb->raw(TB_PREFIX.TB_APPOINTMENTS.".id as appointment_id"),
                                $this->qb->raw(TB_PREFIX.TB_APPOINTMENTS.".patient_id as patient_id"),
                                $this->qb->raw(TB_PREFIX.TB_PATIENTS.".name as patient_name"),
                                $this->qb->raw(TB_PREFIX.TB_PATIENTS.".birthday as patient_birthday"),
                                $this->qb->raw(TB_PREFIX.TB_APPOINTMENTS.".date as date"),
                                $this->qb->raw(TB_PREFIX.TB_APPOINTMENTS.".status as status"),

                                $this->qb->raw(TB_PREFIX.TB_DOCTORS.".id as doctor_id"),
                                $this->qb->raw(TB_PREFIX.TB_DOCTORS.".name as doctor_name"),

                                $this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".id as speciality_id"),
                                $this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".name as speciality_name"),
                            ]);
            return $query;
    
        }





}


?>