<?php

class AppointmentModel extends DataEntry{
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
            $query = $this->qb->table(TB_PREFIX.TB_APPOINTMENTS)
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
            "patient_id" => "",
            "booking_id" => "",
            "doctor_id" => "",
            "patient_id" => "",
            "numerical_order" => "",
            "position" => "",
            "appointment_time" => "",
            "date" => "",
            "status" => "",
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

        $id = $this->qb->table(TB_PREFIX.TB_APPOINTMENTS)
            ->insert(array(
                "id" => null,
                "booking_id" => $this->get("booking_id"),
                "doctor_id" => $this->get("doctor_id"),
                "patient_id" => $this->get("patient_id"),
                "numerical_order" => $this->get("numerical_order"),
                "position" => $this->get("position"),
                "date" => $this->get("date"),
                "appointment_time" => $this->get("appointment_time"),
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

        $id = $this->qb->table(TB_PREFIX.TB_APPOINTMENTS)
            ->where("id", "=", $this->get("id"))
            ->update(array(
                "booking_id" => $this->get("booking_id"),
                "doctor_id" => $this->get("doctor_id"),
                "patient_id" => $this->get("patient_id"),
                // "patient_name" => $this->get("patient_name"),
                // "patient_birthday" => $this->get("patient_birthday"),
                // "patient_reason" => $this->get("patient_reason"),
                // "patient_phone" => $this->get("patient_phone"),
                "numerical_order" => $this->get("numerical_order"),
                "position" => $this->get("position"),
                "date" => $this->get("date"),
                "appointment_time" => $this->get("appointment_time"),
                "status" => $this->get("status"),
                "create_at" => $this->get("create_at"),
                "update_at" => $this->get("update_at")
            ));

        return $this;
    }

    public function delete()
    {
        if(!$this->isAvailable())
            return false;

        $this->qb->table(TB_PREFIX.TB_APPOINTMENTS)
        ->where("id", "=", $this->get("id"))->delete();
        $this->is_available = false;
        return true;
    }

    public function getAll(){
        $query = $this->qb->table(TB_PREFIX.TB_APPOINTMENTS)
                        ->leftJoin(TB_PREFIX.TB_DOCTORS, 
                                    TB_PREFIX.TB_DOCTORS.".id", "=", TB_PREFIX.TB_APPOINTMENTS.".doctor_id")
                        ->leftJoin(TB_PREFIX.TB_SPECIALITIES,
                                    TB_PREFIX.TB_SPECIALITIES.".id", "=", TB_PREFIX.TB_DOCTORS.".speciality_id")
                        ->leftJoin(TB_PREFIX.TB_ROOMS,
                                    TB_PREFIX.TB_ROOMS.".id", "=", TB_PREFIX.TB_DOCTORS.".room_id")
                        ->leftJoin(TB_PREFIX.TB_PATIENTS, 
                                    TB_PREFIX.TB_PATIENTS.".id", "=", TB_PREFIX.TB_APPOINTMENTS.".patient_id")
                        ->select([
                            TB_PREFIX.TB_APPOINTMENTS.".*",

                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".id as doctor_id"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".email as doctor_email"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".phone as doctor_phone"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".name as doctor_name"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".description as doctor_description"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".price as doctor_price"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".role as doctor_role"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".active as doctor_active"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".avatar as doctor_avatar"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".create_at as doctor_create_at"),
                            $this->qb->raw(TB_PREFIX.TB_DOCTORS.".update_at as doctor_update_at"),

                            $this->qb->raw(TB_PREFIX.TB_ROOMS.".id as room_id"),
                            $this->qb->raw(TB_PREFIX.TB_ROOMS.".name as room_name"),
                            $this->qb->raw(TB_PREFIX.TB_ROOMS.".location as room_location"),

                            $this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".id as speciality_id"),
                            $this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".name as speciality_name"),
                            $this->qb->raw(TB_PREFIX.TB_SPECIALITIES.".description as speciality_description"),

                            $this->qb->raw(TB_PREFIX.TB_PATIENTS.".name as patient_name"),
                            $this->qb->raw(TB_PREFIX.TB_PATIENTS.".phone as patient_phone"),
                            $this->qb->raw(TB_PREFIX.TB_PATIENTS.".birthday as patient_birthday"),
                            

                            
                        ]);
        return $query;


    }






}

?>