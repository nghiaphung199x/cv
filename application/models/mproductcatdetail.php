<?php 
class MProductCatDetail extends CI_Model{
	protected $_table="product_category_detail";
	public function __construct(){
		parent::__construct();
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			if($ssFilter['id_cat']>0){
				$this->load->model('MProductCat');
				$arrId = $this->MProductCat->getIds(array($ssFilter['id_cat']));
			}
			$this->db->select('COUNT(pcd.id) AS totalItem');
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('pcd.id = ' . (int)$keywords);
				}elseif(preg_match('#^(min:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('pcd.price >=' . (int)$keywords);
				}elseif(preg_match('#^(max:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('pcd.price <=' . (int)$keywords);
				}else {
					$this->join('product as p', 'pcd.id_product = p.id','left');
					$this->db->where("(p.name like '%" . $keywords . "%') OR (p.alias like '%" . $keywords . "%')");
				}
			}
			if($ssFilter['id_theme']>0){
				$this->db->where('pcd.id_theme', $ssFilter['id_theme']);
			}
				
			if(count($arrId)>0) {
				$this->db->where('pcd.id_category in ('.implode(',', $arrId).')');
			
			}
			if($arrParam['id_product']>0) {
				$this->db->where('pcd.id_product',$arrParam['id_product']);
			}
			$query = $this->db->get($this->_table . ' as pcd');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
			
		}
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'admin-list'){
			if($ssFilter['id_cat']>0){
				$this->load->model('MProductCat');
				$arrId = $this->MProductCat->getIds(array($ssFilter['id_cat']));
			}
			$paginator = $arrParam['paginator'];
			$this->db->select('pcd.*, pt.name as theme_name, pc.name as category_name, p.name as product_name, p.alias as product_alias, p.id as product_id, p.thumb, pcd.price, pcd.store, pcd.sale')
					 ->join('product_category as pc', 'pcd.id_category = pc.id', 'left')
					 ->join('product as p', 'pcd.id_product = p.id', 'left')
					 ->join('product_theme AS pt', 'pt.id = p.id_theme', 'left');
			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('pcd.id = ' . (int)$keywords);
				}elseif(preg_match('#^(min:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('pcd.price >=' . (int)$keywords);
				}elseif(preg_match('#^(max:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,4);
					$this->db->where('pcd.price <=' . (int)$keywords);
				}else {
					$this->db->where("(p.name like '%" . $keywords . "%') OR (p.alias like '%" . $keywords . "%')");
				}
			}
			
			if($ssFilter['id_theme']>0){
				$this->db->where('pcd.id_theme', $ssFilter['id_theme']);
			}
			
			if(count($arrId)>0) {
				$this->db->where('pcd.id_category in ('.implode(',', $arrId).')');

			}
			
			if($arrParam['id_product']>0) {
				$this->db->where('pcd.id_product',$arrParam['id_product']);
			}
			
			$result =  $this->db->get($this->_table . ' as pcd')->result_array();
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-edit') {
			$this->db->select('pcd.id as pcd_id, p.name as product_name, pc.name as product_cat_name, p.alias, p.thumb, pcd.price, pcd.store, id_product')
					 ->join('product as p', 'pcd.id_product = p.id','left')
				 	 ->join('product_category as pc', 'pcd.id_category = pc.id','left')
					 ->where('pcd.id',$arrParam['id_pcd']);
			
			$result =  $this->db->get($this->_table . ' as pcd')->row_array();
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function getItems($arrParam = null, $options = null){
		$this->db->select('pcd.id as pcd_id, p.name as product_name, pc.name as product_cat_name, p.alias, p.thumb, pcd.price, pcd.store, id_product')
				 ->join('product as p', 'pcd.id_product = p.id','left')
				 ->join('product_category as pc', 'pcd.id_category = pc.id','left')
				 ->where('pcd.id in ('.implode(',', $arrParam['pcd_ids']).')');
		
		$result =  $this->db->get($this->_table . ' as pcd')->result_array();
		$this->db->flush_cache();
		return $result;
	}
	
	public function getIds($arrParam = null, $options = null) {
		if($options['task'] == 'by-catIds') {
			$this->db->select('pcd.id_product')
					 ->where('id_category in ('.implode(',', $arrParam['catIds']).')');
			$resultTmp =  $this->db->get($this->_table . ' as pcd')->result_array();
			
			$this->db->flush_cache();
			$result = array();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val)
					$result[] = $val['id_product'];
			
			}
		}
		return $result;
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
		
		}elseif($options['task'] == 'by-catIds') {
			$this->db->where('id IN (' . implode(',', $arrParam['catIds']) . ')');
			$this->db->delete($this->_table);
			
			$this->db->flush_cache();
		}elseif($options['task'] == 'by-product') {
			$this->db->where('id_product IN (' . implode(',', $arrParam['product_ids']) . ')');
			$this->db->delete($this->_table);
				
			$this->db->flush_cache();
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