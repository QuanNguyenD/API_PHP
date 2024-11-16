<?php
class ServiceModel extends DataEntry{
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
        if(is_int($uniqid) || ctype_digit($uniqid)){
            $col = $uniqid > 0 ? "id" : null;

        }
        else{
            $col = "name";
        }

        if($col){
            $query = $this->qb->table(TB_PREFIX.TB_SERVICES)->where($col,"=",$uniqid)->limit(1)->select("*");
            if($query->count() ==1){
                $resp = $query->get();
                $r = $resp[0];

                foreach ($r as $field => $value){
                    $this-> set($field, $value);

                }
                $this->is_available = true;
            }else {
                $this->data = array();
                $this->is_available = false;
            }
        }
        return $this;

        
    }
    public function extendDefaults()
    {
        $defaults = array(
            "name" => "",
            "image" => "",
            "description" => ""
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

        $id = $this->qb->table(TB_PREFIX.TB_SERVICES)
            ->insert(array(
                "id" => null,
                "name" => $this->get("name"),
                "image" => $this->get("image"),
                "description" => $this->get("description")
            ));

        $this->set("id", $id);
        $this->markAsAvailable();
        return $this->get("id");
    }

    public function save()
    {
        return $this->isAvailable() ? $this->update() : $this->insert();
    }

    public function update()
	    {
        if (!$this->isAvailable())
            return false;

        $this->extendDefaults();

        $id = $this->qb->table(TB_PREFIX.TB_SERVICES)
            ->where("id", "=", $this->get("id"))
            ->update(array(
                "name" => $this->get("name"),
                "image" => $this->get("image"),
                "description" => $this->get("description")
            ));

        return $this;
	}



    public function queryBooking($id){
        $queryBooking = $this->qb->table(TB_PREFIX.TB_BOOKINGS)
                    ->where(TB_PREFIX.TB_BOOKINGS.".service_id", "=", $id);
        return $queryBooking;
    }

    public function queryDoctorAndService($id){
        $queryDoctorAndService = $this->qb->table(TB_PREFIX.TB_DOCTOR_AND_SERVICE)
                    ->where(TB_PREFIX.TB_DOCTOR_AND_SERVICE.".service_id", "=", $id);
        return $queryDoctorAndService;
    }
    public function delete()
    {
        if(!$this->isAvailable())
            return false;

        $this->qb->table(TB_PREFIX.TB_SERVICES)
        ->where("id", "=", $this->get("id"))->delete();
        $this->is_available = false;
        return true;
    }

    public function getAll(){

        $queryGetAll = $this->qb->table(TB_PREFIX.TB_SERVICES)->select("*");
        return $queryGetAll;
    }

    public function checkName($name){
        $query = $this->qb->table(TB_PREFIX.TB_SERVICES)
                ->where(TB_PREFIX.TB_SERVICES.".name", "=", $name);
        return $query;
    }










}




?>