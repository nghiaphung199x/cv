<?php 
class MOrder extends CI_Model{
	protected $_table="order";
	public function __construct(){
		parent::__construct();
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			$this->db->select('COUNT(o.id) AS totalItem');
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('o.id = ' . (int)$keywords);
			
				}elseif(preg_match('#^(min:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('o.total_all >=' . (int)$keywords);
			
				}elseif(preg_match('#^(max:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('o.total_all <=' . (int)$keywords);
			
				}else {
					$this->db->where("(o.fullname like '%".$keywords."%' OR o.address like '%".$keywords."%')");
				}
			}
				
			if(!empty($ssFilter['tungay'])) {
				$tungay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['tungay'])));
				$this->db->where('o.created >= \''.$tungay.'\'');
			}
			
			if(!empty($ssFilter['denngay'])) {
				$denngay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['denngay'])));
				$this->db->where('o.created <= \''.$denngay.'\'');
			}
			
			$query = $this->db->get($this->_table . ' as o');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}
		return $result;
		
	}
	
	public function listItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'admin-list'){
			$paginator = $arrParam['paginator'];
			$this->db->select('o.*');
			$this->db->select("DATE_FORMAT(o.created, '%d/%m/%Y %h:%i %p') AS created", FALSE);
			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('o.id = ' . (int)$keywords);
						
				}elseif(preg_match('#^(min:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('o.total_all >=' . (int)$keywords);
						
				}elseif(preg_match('#^(max:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('o.total_all <=' . (int)$keywords);
						
				}else {
					$this->db->where("(o.fullname like '%".$keywords."%' OR o.address like '%".$keywords."%')");
				}
			}
			
			if(!empty($ssFilter['tungay'])) {
				$tungay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['tungay'])));
				$this->db->where('o.created >= \''.$tungay.'\'');
			}
				
			if(!empty($ssFilter['denngay'])) {
				$denngay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['denngay'])));
				$this->db->where('o.created <= \''.$denngay.'\'');
			}
			
			$result =  $this->db->get($this->_table . ' as o')->result_array();
			$this->db->flush_cache();
			
		}
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('o.*')
					 ->where('o.id',$arrParam['id']);
	
			$result =  $this->db->get($this->_table . ' as o')->row_array();
			$this->db->flush_cache();
			if(!empty($result)) {
				$this->db->select('od.id_pcd, p.name as product_name, od.price, od.quantity, pc.name as cat_name, p.thumb, p.id as product_id')
						 ->join('product_category_detail as pcd', 'od.id_pcd = pcd.id', 'left')
						 ->join('product as p','pcd.id_product = p.id','left')
						 ->join('product_category as pc', 'pcd.id_category = pc.id','left')
						 ->where('od.id_order',$arrParam['id'])
						 ->order_by('od.id_pcd','DESC');
				
				$result['order_detail'] =  $this->db->get('order_detail as od')->result_array();
			}
				
		}
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'add'){
			$data['fullname']				= 				stripslashes($arrParam['fullname']);
			$data['phone']					= 				stripslashes($arrParam['phone']);
			$data['address']				= 				stripslashes($arrParam['address']);
			$data['id_city'] 				= 				$arrParam['id_city'];
			$data['note']					= 				stripslashes($arrParam['note']);
			$data['status'] 				= 				0;
			$data['order'] 					= 				255;
			$data['coupon'] 				= 				$arrParam['coupon'];
			$data['shipping'] 				= 				$arrParam['shipping'];
			$data['total_all'] 				= 				$arrParam['total_all'];
			$data['created']				= 				@date("Y-m-d H:i:s");
			$data['modified']				= 				@date("Y-m-d H:i:s");
			
			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
				
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-edit'){
			$this->db->where("id",$arrParam['id']);
			$data['fullname'] 			= 		stripslashes($arrParam['fullname']);
			$data['phone'] 				= 		stripslashes($arrParam['phone']);
			$data['address'] 			= 		stripslashes($arrParam['address']);
			$data['id_city'] 			= 		$arrParam['id_city'];
			$data['shipping'] 			= 		str_replace(".","",$arrParam['shipping']);
			$data['status'] 			= 		$arrParam['status'];
			$data['order'] 				= 		$arrParam['order'];
			$data['note']				= 		stripslashes($arrParam['note']);
			$data['total_all']		    = 		$arrParam['total_all'] - $arrParam['current_shipping']+$data['shipping'];
			
			$this->db->update($this->_table,$data);
				
			$this->db->flush_cache();
				
			$lastId = $arrParam['id'];
		}
		return $lastId;
	}
	
	public function changeStatus($arrParam = null, $options = null){
		$cid = $arrParam['cid'];
		if(count($cid) > 0){
			if($arrParam['type'] == 1){
				$status = 1;
			}elseif($arrParam['type'] == 2){
				$status = 2;
			}else
				$status = 0;
	
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
	
	public function deleteItem($arrParam = null, $options = null) {
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