<?php
require APPPATH.'/Core/App.php';
require APPPATH.'/Core/DataEntry.php';
class DrugModel extends DataEntry{
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
        public function getDrug($id){
            return $this->qb->table(TB_PREFIX.TB_DRUGS)->where(TB_PREFIX.TB_DRUGS.".id", "=", $id)->first();
        }





}

?>