<?php
class BookingModel extends DataEntry{
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
        $col = "id";


        if ($col) {
            $query = $this->qb->table(TB_PREFIX.TB_BOOKINGS)
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
				"doctor_id" => "",
                "patient_id" => "",
				"service_id" => "",
				"booking_name" => "",
				"booking_phone" => "",
				"name" => "",
				"gender" => "",
				"birthday" => "",
				"address" => "",
				"reason" => "",
				"appointment_date" => "",
				"appointment_time" => "",
				"status" => "",
				"create_at" => "",
				"update_at" => ""
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

	    	$id = $this->qb->table(TB_PREFIX.TB_BOOKINGS)
		    	->insert(array(
		    		"id" => null,
		    		"patient_id" => $this->get("patient_id"),
					"service_id" => $this->get("service_id"),
					// "booking_name" => $this->get("booking_name"),
					// "booking_phone" => $this->get("booking_phone"),
					// "name" => $this->get("name"),
					// "gender" => $this->get("gender"),
					// "birthday" => $this->get("birthday"),
					// "address" => $this->get("address"),
					// "reason" => $this->get("reason"),
                    "appointment_date" => $this->get("appointment_date"),
                    "appointment_hour" => $this->get("appointment_time"),
					"status" => $this->get("status"),
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

	    	$id = $this->qb->table(TB_PREFIX.TB_BOOKINGS)
	    		->where("id", "=", $this->get("id"))
		    	->update(array(
					// "doctor_id" => $this->get("doctor_id"),
					"patient_id" => $this->get("patient_id"),
					"service_id" => $this->get("service_id"),
					// "booking_name" => $this->get("booking_name"),
					// "booking_phone" => $this->get("booking_phone"),
					// "name" => $this->get("name"),
					// "gender" => $this->get("gender"),
					// "birthday" => $this->get("birthday"),
					// "address" => $this->get("address"),
					// "reason" => $this->get("reason"),
                    "appointment_date" => $this->get("appointment_date"),
                    "appointment_hour" => $this->get("appointment_time"),
					"status" => $this->get("status"),
					"create_at" => $this->get("create_at"),
					"update_at" => $this->get("update_at")
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

	    	$this->qb->table(TB_PREFIX.TB_BOOKINGS)
            ->where("id", "=", $this->get("id"))->delete();
	    	$this->is_available = false;
	    	return true;
	    }

        public function getAllBooking($id){
            $query = $this->qb->table(TB_PREFIX.TB_BOOKINGS)
                        ->where(TB_PREFIX.TB_BOOKINGS.".patient_id", "=", $id)
                        ->leftJoin(TB_PREFIX.TB_SERVICES, 
                                    TB_PREFIX.TB_SERVICES.".id","=", TB_PREFIX.TB_BOOKINGS.".service_id")
                        ->leftJoin(TB_PREFIX.TB_PATIENTS,
                                    TB_PREFIX.TB_PATIENTS.".id","=",TB_PREFIX.TB_BOOKINGS.".patient_id")
                        ->select([
                            TB_PREFIX.TB_BOOKINGS.".*",
                            $this->qb->raw(TB_PREFIX.TB_SERVICES.".id as service_id"),
                            $this->qb->raw(TB_PREFIX.TB_SERVICES.".name as service_name"),
                            $this->qb->raw(TB_PREFIX.TB_SERVICES.".image as service_image"),
                            $this->qb->raw(TB_PREFIX.TB_PATIENTS.".name as patient_name"),
                            $this->qb->raw(TB_PREFIX.TB_PATIENTS.".gender as patient_gender"),
                            $this->qb->raw(TB_PREFIX.TB_PATIENTS.".birthday as patient_birthday"),
                            $this->qb->raw(TB_PREFIX.TB_PATIENTS.".address as patient_address")

                        ]);
            return $query;
        }

        public function addBooking($idPatient,$appointment_date,$idService){
            $query = $this->qb->table(TB_PREFIX.TB_BOOKINGS)
            ->where(TB_PREFIX.TB_BOOKINGS.".patient_id", "=", $idPatient)
            ->where(TB_PREFIX.TB_BOOKINGS.".status", "=", "processing")
            ->where(TB_PREFIX.TB_BOOKINGS.".appointment_date", "=", $appointment_date)
            ->where(TB_PREFIX.TB_BOOKINGS.".service_id", "=", $idService);

            return $query;
        }


}


?>