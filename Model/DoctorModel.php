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
        public function getAllDoc(){
            return $this->qb->table('tn_doctors')->get();
        }



}



?>