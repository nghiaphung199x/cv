<?php 
class MDistrict extends CI_Model{
	protected $_table="districts";
	public function __construct(){
		parent::__construct();
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			$this->db->select('COUNT(d.id) AS totalItem');
				
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('d.id = ' . (int)$keywords);
				}else {
					$this->db->where("(d.name like '%" . $keywords . "%') OR (d.alias like '%" . $keywords . "%')");
				}
			}
			
			if($ssFilter['city_id']>0){
				$this->db->join('cities as ci', 'd.city_id = ci.id','left');
				$this->db->where('d.city_id', $ssFilter['city_id']);
			}
				
			if($ssFilter['country_id']>0){
				$this->db->join('cities as ci', 'd.city_id = ci.id','left')
						 ->join('countries AS c', 'c.id = ci.country_id', 'left');
				$this->db->where('ci.country_id', $ssFilter['country_id']);
			}
				
			$query = $this->db->get($this->_table . ' as d');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'admin-list'){
			$paginator = $arrParam['paginator'];
			$this->db->select('d.*, c.name as country_name, ci.name as city_name')
					  ->join('cities as ci', 'd.city_id = ci.id', 'left')
					  ->join('countries AS c', 'c.id = ci.country_id', 'left');
				
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
				
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('d.id = ' . (int)$keywords);
				}else {
					$this->db->where("(d.name like '%" . $keywords . "%') OR (d.alias like '%" . $keywords . "%')");
				}
			}
			
			if($ssFilter['city_id']>0){
				$this->db->where('d.city_id', $ssFilter['city_id']);
			}
				
			if($ssFilter['country_id']>0){
				$this->db->where('ci.country_id', $ssFilter['country_id']);
			}
				
			$result =  $this->db->get($this->_table . ' as d')->result_array();
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			$alias 	= rewriteUrl($arrParam['name']);

			$data['name']					= 				stripslashes($arrParam['name']);
			$data['alias']					=				$alias;
			$data['order']					= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
			$data['city_id']	 			= 				$arrParam['city_id'];
	
			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
	
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-edit'){
			$this->db->where("id",$arrParam['id']);
	
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['alias']					=				$alias;
			$data['order']					= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
			$data['city_id']	 			= 				$arrParam['city_id'];
			$this->db->update($this->_table,$data);
			$this->db->flush_cache();
	
			$lastId = $arrParam['id'];
		}
		return $lastId;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('d.*, ci.country_id')
					 ->join('cities as ci', 'd.city_id = ci.id','left')
					 ->where('d.id',$arrParam['id']);
	
			$result =  $this->db->get($this->_table . ' as d')->row_array();
			$this->db->flush_cache();
		}
	
		return $result;
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
	
	public function sortItem($arrParam = null, $options = null){
		$cid = $arrParam['cid'];
		$order = $arrParam['order'];
		if($options['task'] == 'admin-sort'){
			if(count($cid) > 0){
				foreach ($cid as $key => $val){
					$data = array('order' => $order[$val]);
	
					$this->db->where('id', $val);
					$this->db->update($this->_table,$data);
					$this->db->flush_cache();
				}
			}
		}
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
}