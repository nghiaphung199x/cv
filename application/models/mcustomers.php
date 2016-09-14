<?php 
class MCustomers extends CI_Model{
   
	protected $_table = 'customers';
	public function __construct(){
		parent::__construct();
	}
	
	public function getItems($arrParams = null, $options = null) {
		if($options == null) {
			$this->db->select("id, name")
					->from($this->_table)
					->where('id IN ('.implode(',', $arrParams['cid']).')')
					->where('status = 1')
					->order_by("name",'DESC');
			
			$query = $this->db->get();

			$result = $query->result_array();
			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function listItem($arrParams = null, $options = null) {
        if($options == null) {
            $this->db->select("id, name")
            	      ->from($this->_table)
            	      ->where('status = 1')
            		  ->order_by("id",'DESC');
             
            if(!empty($arrParams['keywords'])){
                $keywords = trim($arrParams['keywords']);
                $keywordsArr = explode(' ', $keywords);
                foreach($keywordsArr as $keyword) {
                    $where[] = "name LIKE '%$keyword%'";
                }
                
                $where = implode(' OR ', $where);
                $this->db->where($where);
            }
            
            $this->db->limit(8);
 
            $query = $this->db->get();
           
     
            $result = $query->result_array();
            $this->db->flush_cache();
        }
    
        return $result;
    }

}