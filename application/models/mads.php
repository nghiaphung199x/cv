<?php 
class MAds extends CI_Model{
	protected $_table="ads";
	public function __construct(){
		parent::__construct();
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];

			$this->db->select('COUNT(a.id) AS totalItem');
				
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('a.id = ' . (int)$keywords);
				}else {
					$this->db->where("(a.name like '%" . $keywords . "%') OR (a.alias like '%" . $keywords . "%')");
				}
					
			}
	
			$query = $this->db->get($this->_table . ' as a');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
				
		}
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
	
		if($options['task'] == 'admin-list'){
			$paginator = $arrParam['paginator'];
			$this->db->select('a.*');
	
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
	
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('a.id = ' . (int)$keywords);
				}else {
					$this->db->where("(a.name like '%" . $keywords . "%') OR (a.alias like '%" . $keywords . "%')");
				}
			}

			$result =  $this->db->get($this->_table . ' as a')->result_array();
			$this->db->flush_cache();
		}elseif($options['task'] == 'public-list') {
			$this->load->driver('cache');
			if (!$result = $this->cache->file->get('ads')){
				$this->db->select('a.*')
					     ->where('a.status = 1')
					     ->order_by('a.order','ASC');
					
				$result =  $this->db->get($this->_table . ' as a')->result_array();
				$this->db->flush_cache();
				$this->cache->file->save('ads', $result, 300);
			}
		}
	
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('a.*')
					  ->where('a.id',$arrParam['id']);
	
			$result =  $this->db->get($this->_table . ' as a')->row_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['picture']	 			= 				$arrParam['picture'];
			$data['content']				= 				stripslashes($arrParam['content']);
			$data['order']	 				= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
			$data['created']				= 				@date("Y-m-d H:i:s");
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['created_by']     		=				$arrParam['adminInfo']['id'];
			$data['modified_by']			= 				@date("Y-m-d H:i:s");
			$data['url']					= 				stripslashes($arrParam['url']);
			$data['target']	 				= 				$arrParam['target'];
	
			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
				
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-edit'){

			$this->db->where("id",$arrParam['id']);
			
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['picture']	 			= 				$arrParam['picture'];
			$data['content']				= 				stripslashes($arrParam['content']);
			$data['order']	 				= 				$arrParam['order'];
			$data['status']	 				= 				$arrParam['status'];
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['modified_by']			= 				@date("Y-m-d H:i:s");
			$data['url']					= 				stripslashes($arrParam['url']);
			$data['target']	 				= 				$arrParam['target'];
			
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
}