<?php 
class MPage extends CI_Model{
	protected $_table="page";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
		if($options == null){
			$this->db->select('p.*');
			$resultTmp =  $this->db->get($this->_table . ' as p')->result_array();
			$this->db->flush_cache();
			
			$result[0] = 'Chọn Page';
			if (count($resultTmp)>0) {
				foreach ($resultTmp as $val) {
					$result[$val['id']] = $val['name'];
				}
			}
		}
		return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			
			$this->db->select('COUNT(p.id) AS totalItem');
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('p.id = ' . (int)$keywords);
				}else {
					$this->db->where("(p.name like '%" . $keywords . "%') OR (p.alias like '%" . $keywords . "%')");
				}
			}

			$query = $this->db->get($this->_table . ' as p');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
				
		}
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'admin-list'){
			$paginator = $arrParam['paginator'];
			$this->db->select('p.*');
	
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
	
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('p.id = ' . (int)$keywords);
				}else {
					$this->db->where("(p.name like '%" . $keywords . "%') OR (p.alias like '%" . $keywords . "%')");
				}
			}

			$result =  $this->db->get($this->_table . ' as p')->result_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('p.*')
					 ->where('p.id',$arrParam['id']);
	
			$result =  $this->db->get($this->_table . ' as p')->row_array();
			$this->db->flush_cache();
		}elseif($options['task'] == 'public-info') {
			$this->db->select('p.*')
					  ->where('p.id',$arrParam['id'])
					  ->where('p.status = 1');
			
			$result =  $this->db->get($this->_table . ' as p')->row_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			if(empty($arrParam['alias'])){
				$alias 	= rewriteUrl($arrParam['name']);
			}else{
				$alias 	= $arrParam['alias'];
			}
				
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['alias'] 					= 				$alias;
			$data['excerpt']				= 				stripslashes($arrParam['excerpt']);
			$data['author']					= 				stripslashes($arrParam['author']);
			$data['content']				= 				stripslashes($arrParam['content']);
			$data['created']				= 				@date("Y-m-d H:i:s");
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['created_by']     		=				$arrParam['adminInfo']['id'];
			$data['order']	 				= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
			$data['tags']					= 				stripslashes($arrParam['tags']);
			$data['meta_title']				= 				stripslashes($arrParam['meta_title']);
			$data['meta_description']		= 				stripslashes($arrParam['meta_description']);
			$data['meta_keywords']			= 				stripslashes($arrParam['meta_keywords']);
			$data['icon']					= 				$arrParam['icon'];
			$data['images']					= 				$arrParam['images'];
			$data['thumb'] 					= 				getThumb($arrParam['images']);
			$data['file']	 				= 				$arrParam['file'];
			$data['video']	 				= 				$arrParam['video'];
			$data['audio']	 				= 				$arrParam['audio'];
			$data['flash']	 				= 				$arrParam['flash'];
				
			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
				
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-edit'){
			if(empty($arrParam['alias'])){
				$alias 	= rewriteUrl($arrParam['name']);
			}else{
				$alias 	= $arrParam['alias'];
			}
			
			$this->db->where("id",$arrParam['id']);
			
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['alias'] 					= 				$alias;
			$data['excerpt']				= 				stripslashes($arrParam['excerpt']);
			$data['author']					= 				stripslashes($arrParam['author']);
			$data['content']				= 				stripslashes($arrParam['content']);
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['modified_by']     		=				$arrParam['adminInfo']['id'];
			$data['order']	 				= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
			$data['tags']					= 				stripslashes($arrParam['tags']);
			$data['meta_title']				= 				stripslashes($arrParam['meta_title']);
			$data['meta_description']		= 				stripslashes($arrParam['meta_description']);
			$data['meta_keywords']			= 				stripslashes($arrParam['meta_keywords']);
			$data['icon']					= 				$arrParam['icon'];
			$data['images']					= 				$arrParam['images'];
			$data['thumb'] 					= 				getThumb($arrParam['images']);
			$data['file']	 				= 				$arrParam['file'];
			$data['video']	 				= 				$arrParam['video'];
			$data['audio']	 				= 				$arrParam['audio'];
			$data['flash']	 				= 				$arrParam['flash'];
	
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