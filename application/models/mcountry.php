<?php 
class MCountry extends CI_Model{
	protected $_table="countries";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
		if($options['task'] == 'get-countries'){
			$this->db->select('c.id, c.name')
					 ->order_by('c.order','ASC');
			
			$resultTmp =  $this->db->get($this->_table . ' as c')->result_array();
			$this->db->flush_cache();
			
			$result[] = 'Chọn quốc gia';
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val) {
					$result[$val['id']] = $val['name'];
				}
			}
		}
		return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];	
			$this->db->select('COUNT(c.id) AS totalItem');
		    if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('c.id = ' . (int)$keywords);
				}else {
					$this->db->where('c.name like \'%'.$keywords.'%\'');
				}
			}

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
	
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
	
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('c.id = ' . (int)$keywords);
				}else {
					$this->db->where('c.name like \'%'.$keywords.'%\'');
				}
			}
				
			$result =  $this->db->get($this->_table . ' as c')->result_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			$data['code']					= 				stripslashes($arrParam['code']);
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['order']					= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
	
			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
				
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-edit'){
			$this->db->where("id",$arrParam['id']);
			$data['code']					= 				stripslashes($arrParam['code']);
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['order']					= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];	
			$this->db->update($this->_table,$data);	
			$this->db->flush_cache();
				
			$lastId = $arrParam['id'];
		}
		return $lastId;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('c.*')
					 ->where('c.id',$arrParam['id']);
	
			$result =  $this->db->get($this->_table . ' as c')->row_array();
			$this->db->flush_cache();
		}
	
		return $result;
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
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete-muti'){
			$cid = explode(',', $arrParam['cid']);
			if(!empty($cid) && isset($arrParam['cid'])){
				$ids = implode(',', $cid);
				$this->db->where('id IN (' . $ids . ')');
				$this->db->delete($this->_table);
				$this->db->flush_cache();
				
				$this->db->select('id')
						 ->where('country_id in ('.$ids.')');

				$resultItem =  $this->db->get('cities')->result_array();
				if(!empty($resultItem)) {
					foreach($resultItem as $val) {
						$city_ids[] = $val['id'];
					}
					$this->db->where('country_id IN (' . $ids . ')');
					$this->db->delete('cities');
					$this->db->flush_cache();
					
					$this->db->where('city_id IN (' . implode(',', $city_ids) . ')');
					$this->db->delete('districts');
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