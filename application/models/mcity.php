<?php 
class MCity extends CI_Model{
	protected $_table="cities";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'get-cities'){
			$this->db->select('ci.id, ci.name')
					 ->order_by('ci.order','ASC');

			if($ssFilter['country_id']>0 || $arrParam['country_id']>0) {
				if($ssFilter['country_id']>0)
					$country_id = $ssFilter['country_id'];
				else 
					$country_id = $arrParam['country_id'];
				
				$this->db->where('ci.country_id', $country_id);
			}
			
			$resultTmp =  $this->db->get($this->_table . ' as ci')->result_array();
			$this->db->flush_cache();
			$result[0] = 'Chọn tỉnh/thành phố';
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val)
					$result[$val['id']] = $val['name'];
			}

		}elseif($options['task'] == 'public-select') {
			$this->db->select('ci.id, ci.name')
					 ->where('ci.status = 1')
					 ->order_by('ci.order','ASC');
			
			$resultTmp =  $this->db->get($this->_table . ' as ci')->result_array();
			$this->db->flush_cache();
			$result[0] = 'Vui lòng chọn tỉnh/thành phố';
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val)
					$result[$val['id']] = $val['name'];
			}
		}
		
		return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			$this->db->select('COUNT(ci.id) AS totalItem');
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('ci.id = ' . (int)$keywords);
				}else {
					$this->db->where("(ci.name like '%" . $keywords . "%') OR (ci.alias like '%" . $keywords . "%')");
				}
			}
			
			if($ssFilter['country_id']>0){
				$this->db->where('ci.country_id', $ssFilter['country_id']);
			}
			
			$query = $this->db->get($this->_table . ' as ci');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'admin-list'){
			$paginator = $arrParam['paginator'];
			$this->db->select('ci.*, c.name as country_name')
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
					$this->db->where('ci.id = ' . (int)$keywords);
				}else {
					$this->db->where("(ci.name like '%" . $keywords . "%') OR (ci.alias like '%" . $keywords . "%')");
				}
			}
			
			if($ssFilter['country_id']>0){
				$this->db->where('ci.country_id', $ssFilter['country_id']);
			}
			
			$result =  $this->db->get($this->_table . ' as ci')->result_array();
			$this->db->flush_cache();
		}	
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('ci.*')
					 ->where('ci.id',$arrParam['id']);
	
			$result =  $this->db->get($this->_table . ' as ci')->row_array();
			$this->db->flush_cache();
		}elseif($options['task'] == 'public-info') {
			$this->db->select('ci.*')
					 ->where('ci.id',$arrParam['id'])
					 ->where('ci.status = 1');
			
			$result =  $this->db->get($this->_table . ' as ci')->row_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			$alias 	= rewriteUrl($arrParam['name']);
			
			$data['code']					= 				stripslashes($arrParam['code']);
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['alias']					=				$alias;
			$data['shipping']				=				str_replace(".","",$arrParam['shipping']);
			$data['order']					= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
			$data['country_id']	 			= 				$arrParam['country_id'];
	
			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
				
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-edit'){
			$this->db->where("id",$arrParam['id']);
		
			$data['code']					= 				stripslashes($arrParam['code']);
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['alias']					=				$alias;
			$data['shipping']				=				str_replace(".","",$arrParam['shipping']);
			$data['order']					= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
			$data['country_id']	 			= 				$arrParam['country_id'];
			
			$this->db->update($this->_table,$data);	
			$this->db->flush_cache();
				
			$lastId = $arrParam['id'];
		}
		return $lastId;
	}
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete-muti'){
			$cid = explode(',', $arrParam['cid']);
			if(!empty($cid) && isset($arrParam['cid'])){
				$ids = implode(',', $cid);
				$this->db->where('id IN (' . $ids . ')');
				$this->db->delete($this->_table);
				$this->db->flush_cache();
	
				$this->db->where('city_id IN (' . $ids . ')');
				$this->db->delete('districts');
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