<?php 
class MContact extends CI_Model{
	protected $_table="contact";
	public function __construct(){
		parent::__construct();
	}
	
	public function countItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'admin-list' || $options['task'] == 'all'){
			$this->db->select('COUNT(c.id) AS totalItem');
			
			if(!empty($ssFilter['keywords'])){
				$keywords = '%' . $ssFilter['keywords'] . '%';
				$this->db->where("(c.name LIKE '$keywords' OR c.address LIKE '$keywords' OR c.tel LIKE '$keywords' OR c.fax LIKE '$keywords' OR c.phone LIKE '$keywords' OR c.email LIKE '$keywords' OR c.ip LIKE '$keywords')");

			}

			if(!empty($ssFilter['lang_code'])){
				$this->db->where("c.lang_code = '" . $ssFilter['lang_code'] . "'");
			}
	
			$query = $this->db->get($this->_table . ' as c');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-on'){
			$this->db->select('COUNT(c.id) AS totalItem')
					 ->where('c.status = 1');
			
			$query = $this->db->get($this->_table . ' as c');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
			
		}elseif($options['task'] == 'admin-off'){
			$this->db->select('COUNT(c.id) AS totalItem')
					 ->where('c.status = 0');
			
			$query = $this->db->get($this->_table . ' as c');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}
		
		
		return $result;	
	}
	
	public function listItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'admin-list'){
			$paginator = $arrParam['paginator'];
			$this->db->select('c.*');
			$this->db->select("DATE_FORMAT(c.created, '%d/%m/%Y') AS created", FALSE);
			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
	
			if(!empty($ssFilter['keywords'])){
				$keywords = '%' . $ssFilter['keywords'] . '%';
				$this->db->where("(c.name LIKE '$keywords' OR c.address LIKE '$keywords' OR c.tel LIKE '$keywords' OR c.fax LIKE '$keywords' OR c.phone LIKE '$keywords' OR c.email LIKE '$keywords' OR c.ip LIKE '$keywords')");
			
			}
			
			if(!empty($ssFilter['lang_code'])){
				$this->db->where("c.lang_code = '" . $ssFilter['lang_code'] . "'");
			}
				
			$result =  $this->db->get($this->_table . ' as c')->result_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('c.*')
					 ->where('c.id', (int)$arrParam['id']);
			
			$result =  $this->db->get($this->_table . ' as c')->row_array();
			$this->db->flush_cache();
		}

		return $result;
	}
	
	public function changeStatus($arrParam = null, $options = null){
		$cid = $arrParam['cid'];
		if(count($cid) > 0){
			if($arrParam['type'] == 1){
				$status = 1;
			}else{
				$status = 0;
			}
	
			$id = implode(',', $cid);
			$data = array('status' => $status);
			$this->db->where('id IN (' . $id . ')');
			$this->db->update($this->_table,$data);
			$this->db->flush_cache();
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete-muti'){
			$cid = explode(',', $arrParam['cid']);
			if(!empty($cid) && isset($arrParam['cid'])){
				$ids = implode(',', $cid);
				$this->db->where('id IN (' . $ids . ')');
				$this->db->delete($this->_table);
	
				$this->db->flush_cache();
			}
	
		}
	}
}