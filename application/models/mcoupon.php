<?php 
class MCoupon extends CI_Model{
	protected $_table="coupon";
	public function __construct(){
		parent::__construct();
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
					
				}elseif(preg_match('#^(min:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('c.price >=' . (int)$keywords);
					
				}elseif(preg_match('#^(max:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('c.price <=' . (int)$keywords);
					
				}else {
					$this->db->where("c.name like '%".$keywords."%'");
				}
			}
				
			if(!empty($ssFilter['tungay'])) {
				$tungay = date('Y-m-d', strtotime(str_replace('.', '/', $ssFilter['tungay'])));
				$this->db->where('c.created >= \''.$tungay.'\'');
			}
				
			if(!empty($ssFilter['denngay'])) {
				$denngay = date('Y-m-d', strtotime(str_replace('.', '/', $ssFilter['denngay'])));
				$this->db->where('c.created <= \''.$denngay.'\'');
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
			$this->db->select("DATE_FORMAT(c.created, '%d/%m/%Y %h:%i %p') AS created", FALSE);
				
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
					
				}elseif(preg_match('#^(min:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('c.price >=' . (int)$keywords);
					
				}elseif(preg_match('#^(max:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('c.price <=' . (int)$keywords);
					
				}else {
					$this->db->where("c.name like '%".$keywords."%'");
				}
			}
			
			if(!empty($ssFilter['tungay'])) {
				$tungay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['tungay'])));
				$this->db->where('c.created >= \''.$tungay.'\'');
			}
			
			if(!empty($ssFilter['denngay'])) {
				$denngay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['denngay'])));
				$this->db->where('c.created <= \''.$denngay.'\'');
			}
	
			$result =  $this->db->get($this->_table . ' as c')->result_array();
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			$data['code'] 			= 		ramdom();
			$data['price']			= 		$arrParam['price'];
			$data['content']		= 		stripslashes($arrParam['content']);
			$data['status']			= 		$arrParam['status'];
			$data['order']			= 		$arrParam['order'];
			$data['created']		= 		@date("Y-m-d H:i:s");

			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
	
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-edit') {
			$this->db->where("id",$arrParam['id']);
			$data['code'] 			= 		stripslashes($arrParam['code']);
			$data['price']			= 		$arrParam['price'];
			$data['content']		= 		stripslashes($arrParam['content']);
			$data['status']			= 		$arrParam['status'];
			$data['order']			= 		$arrParam['order'];
			
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
			
		}elseif($options['task'] == 'public-info') {
			$this->db->select('c.*')
					 ->where('c.code LIKE \''.$arrParam['code'].'\'')
					 ->where('c.status = 1');
			
			$result =  $this->db->get($this->_table . ' as c')->row_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function getItems($arrParam = null, $options = null){
		if($options == null) {
			foreach($arrParam['codes'] as &$val) {
				$val = "'".$val."'";
			}
			$this->db->select('c.*')
					->where('c.code in ('.implode(',', $arrParam['codes']).')')
					->where('c.status = 1');
			
			$resultTmp =  $this->db->get($this->_table . ' as c')->result_array();
			$this->db->flush_cache();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val) {
					$result[$val['code']]  = $val;
				}
			}
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