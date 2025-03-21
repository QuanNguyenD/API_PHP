<?php
require APPPATH.'/Core/App.php';
require APPPATH.'/Core/DataEntry.php';
class DrugModel extends DataEntry{
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
                $query = $this -> qb -> table(TB_PREFIX.TB_DRUGS)->where($col,"=",$uniqid)->limit(1) ->select("*");
                if($query -> count() ==1){
                    $resp = $query ->get();
                    $r = $resp[0];
                    foreach($r as $field => $value){
                        $this-> set ($field, $value);
                    }
                    $this ->is_available = true;
                }
                else{
                    $this->data = array();
                    $this ->is_available = false;
                }

            }
            return $this;
        }

        public function extendDefaults()
        {
           $defaults = array(
            "name" => "",
            
           ); 
           foreach($defaults as $field =>$value){
                if(is_null($this -> get($field)))
                    $this ->set($field, $value);

           }
        }

        public function insert(){
            if($this->isAvailable())
                return false;
            $this->extendDefaults();

            $id = $this -> qb -> table(TB_PREFIX.TB_DRUGS)
                ->insert(array(
                    "id" => null,
                    "name" => $this -> get("name"),
                ));

            $this->set("id", $id);
            $this->markAsAvailable();
            return $this->get("id");

        }

        public function save(){
            return $this->isAvailable() ? $this->update() : $this->insert();
        }

        public function update(){
            if(!$this->isAvailable())
                return false;

            $this->extendDefaults();

            $id = $this->qb->table(TB_PREFIX.TB_DRUGS)
                            ->where("id", "=", $this->get("id"))
                            ->update(array(
                                "name" => $this->get("name")
                            ));
            return $id;
        }

        public function getAllDrugs(){
            return $this->qb->table(TB_PREFIX.TB_DRUGS)->get();
        }
        public function getDrug($id){
            return $this->qb->table(TB_PREFIX.TB_DRUGS)->where(TB_PREFIX.TB_DRUGS.".id", "=", $id)->first();
        }






}

?>