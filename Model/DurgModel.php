<?php
require APPPATH.'/Core/App.php';
class DurgModel{
    protected $db;
    protected $qb;
        /**
		 * Extend parents constructor and select entry
		 * @param mixed $uniqid Value of the unique identifier
		 */
	    public function __construct()
	    {
            $this->db = App::getConnection();
			$this->qb = new \Pixie\QueryBuilder\QueryBuilderHandler($this->db);
	        
	    }
        public function getAllDrugs(){
            return $this->qb->table(TB_PREFIX.TB_DRUGS)->get();
        }





}

?>