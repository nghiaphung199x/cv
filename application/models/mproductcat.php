<?php 
include_once('mnested.php');
class MProductCat extends MNested{
	protected $_table="product_category";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
		if($options == null){
			$this->db->select('pc.id, pc.name, pc.level')
					 ->order_by('lft', 'ASC');
			
			$resultTmp =  $this->db->get($this->_table . ' as pc')->result_array();
			
			$this->db->flush_cache();
			foreach ($resultTmp as $val) {
				$result[$val['id']] = str_repeat('--', $val['level']) . ' ' . $val['name'];
			}
	
		}elseif($options['task'] == 'admin-product'){
			$this->db->select('pc.id, pc.name, pc.level')
					 ->where('pc.id != 1')
					 ->order_by('lft', 'ASC');
				
			$resultTmp =  $this->db->get($this->_table . ' as pc')->result_array();
			
			$this->db->flush_cache();
			$result[0] = 'Chọn danh mục'; 
			foreach ($resultTmp as $val) {
				$result[$val['id']] = str_repeat('--', $val['level']) . ' ' . $val['name'];
			}
			
		}elseif($options['task'] == 'admin-edit'){
			$this->db->select('id')
					 ->where('lft >= ' . (int)$arrParam['lft'])
					 ->where('rgt <= ' . (int)$arrParam['rgt']);
			
			$catIdsTmp = $this->db->get($this->_table)->result_array();

			if(!empty($catIdsTmp)){
				foreach($catIdsTmp as $val)
					$catIds[] = $val['id'];
			}
			$catIds[] = 1;
			
			$this->db->flush_cache();
			
			$this->db->select('pc.id, pc.name, pc.level, pc.status, pc.created_by')
					 ->order_by('pc.lft','ASC');
			
			if(count($catIds)>0) {
				$this->db->where('pc.id NOT IN ('.implode(',', $catIds).')');
			}
			
			$resultTmp = $this->db->get($this->_table . ' as pc')->result_array();
			$this->db->flush_cache();
			
			$result = array();
			if (count($resultTmp)>0) {
				foreach ($resultTmp as $val) {
					$result[$val['id']] = str_repeat('--', $val['level']) . ' ' . $val['name'];
				}
			}
			
		}
		
		return $result;
	}

	public function countItem($arrParam = null, $options = null){
		if ($options['task'] == 'admin-list') {
			$ssFilter  = $arrParam['ssFilter'];
			$this->db->select('COUNT(pc.id) as total')
					 ->where('pc.id != 1');
				
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i',$keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('pc.id = ?', (int)$keywords);
				}else {
					$this->db->where('pc.name LIKE \'%'.$keywords.'%\'');
				}
			}
				
			if($ssFilter['level']>0) {
				$this->db->where('pc.level <= ' . (int)$ssFilter['level']);
			}
	
			$query = $this->db->get($this->_table . ' as pc');
			$ret = $query->row();
			$result = $ret->total;
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			$paginator = $arrParam['paginator'];
			$this->db->select('pc.*, u.user_name, COUNT(pcd.id) as total_item')
					  ->join('product_category_detail as pcd', 'pc.id = pcd.id_category', 'left')
					  ->join('users AS u', 'u.id = pc.created_by', 'left')
					  ->where('pc.id != 1')
					  ->group_by('pc.id')
					  ->order_by('pc.lft','ASC');

			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i',$keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('pc.id = ?', (int)$keywords);
				}else {
					$this->db->where('pc.name LIKE \'%'.$keywords.'%\'');
				}
			}
	
			if($ssFilter['level']>0) {
				$this->db->where('pc.level <= ' . (int)$ssFilter['level']);
			}
				
			$result =  $this->db->get($this->_table . ' as pc')->result_array();
			$this->db->flush_cache();
			
		}elseif($options['task'] == 'public-by-cat') {
			if($arrParam['id'] == 1) {
				$this->db->select('pc.id, pc.name, pc.alias, pc.parent, pc.level')	
						 ->where('pc.id != 1')
						 ->where('pc.status = 1')
						 ->order_by('lft','ASC');
				$resultTmp =  $this->db->get($this->_table . ' as pc')->result_array();
				$this->db->flush_cache();
			}else {
				$item = $this->getItem(array('id'=>$arrParam['id']), array('task'=>'public-info'));
				if(!empty($item)) {
					$this->db->select('pc.id, pc.name, pc.alias, pc.level, pc.parent')
							->where('pc.status = 1')
							->where('pc.lft > '.$item['lft'])
							->where('pc.rgt < '.$item['rgt'])
							->order_by('lft','ASC');
				
					$resultTmp =  $this->db->get($this->_table . ' as pc')->result_array();
					$this->db->flush_cache();
				}
			}

			if(!empty($resultTmp)) {
				foreach($resultTmp as $val){
					$val['linkMenu'] = rewriteLink('shopping-index-category', array('cat_alias'=>$val['alias'], 'cat_id'=>$val['id']));
					$result[$val['id']] = $val;
				}	
			}
		}
	
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('*')
					  ->where('id', $arrParam['id']);
				
			$result = $this->db->get($this->_table)->row_array();
			$this->db->flush_cache();
	
		}elseif($options['task'] == 'public-info') {
			$this->db->select('*')
					 ->where('id', $arrParam['id'])
					 ->where('status = 1');
			
			$result = $this->db->get($this->_table)->row_array();
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
				
			$data['name']  					= 		stripslashes($arrParam['name']);
			$data['alias']					= 		$alias;
			$data['status']					= 		$arrParam['status'];
			$data['created']				= 		@date("Y-m-d H:i:s");
			$data['created_by']				= 		$arrParam['adminInfo']['id'];
			$data['excerpt'] 				= 		stripslashes($arrParam['excerpt']);
			$data['content'] 				= 		stripslashes($arrParam['content']);
			$data['meta_title'] 			= 		stripslashes($arrParam['meta_title']);
			$data['meta_description'] 		= 		stripslashes($arrParam['meta_description']);
			$data['meta_keywords'] 			= 		stripslashes($arrParam['meta_keywords']);
			$data['images'] 				= 		$arrParam['images'];
			$data['thumb'] 					= 		getThumb($arrParam['images']);
			$data['icon']	 				= 		$arrParam['icon'];
			$data['file']	 				= 		$arrParam['file'];
			$data['video']	 				=		$arrParam['video'];
			$data['audio']	 				= 		$arrParam['audio'];
			$data['flash']	 				= 		$arrParam['flash'];
	
			$lastId = $this->insertNode($data,$arrParam['parent']);
			$this->db->flush_cache();
	
		}elseif($options['task'] == 'admin-edit'){
			if(empty($arrParam['alias'])){
				$alias 	= rewriteUrl($arrParam['name']);
			}else{
				$alias 	= $arrParam['alias'];
			}
				
			$data['name'] 					= 	stripslashes($arrParam['name']);
			$data['alias'] 					= 	$alias;
			$data['status'] 				= 	$arrParam['status'];
			$data['modified'] 				=	@date("Y-m-d H:i:s");
			$data['modified_by']			= 	$arrParam['adminInfo']['id'];
			$data['excerpt'] 				= 	stripslashes($arrParam['excerpt']);
			$data['content'] 				= 	stripslashes($arrParam['content']);
			$data['meta_title'] 			= 	stripslashes($arrParam['meta_title']);
			$data['meta_description'] 		= 	stripslashes($arrParam['meta_description']);
			$data['meta_keywords'] 			= 	stripslashes($arrParam['meta_keywords']);
			$data['images'] 				= 	$arrParam['images'];
			$data['thumb'] 					= 	getThumb($arrParam['images']);
			$data['icon'] 					= 	$arrParam['icon'];
			$data['file']	 				= 	$arrParam['file'];
			$data['video']	 				= 	$arrParam['video'];
			$data['audio']	 				= 	$arrParam['audio'];
			$data['flash']	 				= 	$arrParam['flash'];
	
			$this->updateNode($data,(int)$arrParam['id'],$arrParam['parent']);
	
			$lastId 	= $arrParam['id'];
			$this->db->flush_cache();
		}
		return $lastId;
	}
	
	public function getIds($cid){
		$arrID  = array();
		foreach ($cid as $key => $id){
			if(!in_array($id, $arrID)){
				$nodeInfo = $this->getNodeInfo($id);
				$lft = $nodeInfo['lft'];
				$rgt = $nodeInfo['rgt'];
				$this->db->select('pc.id')
						 ->where('pc.lft BETWEEN ' . $lft . ' AND ' . $rgt);
	
				$resultTmp = $this->db->get($this->_table . ' as pc')->result_array();
	
				$this->db->flush_cache();
				$result = array();
				if(!empty($resultTmp)) {
					foreach($resultTmp as $val)
						$result[] = $val['id'];
	
				}
				$arrID = array_merge($arrID,$result);
			}
	
		}
	
		return $arrID;
	
	}
	
	public function getCatIds($arrParam = null, $options = null){
		if($options == null) {
			$this->db->select('id')
					 ->where('lft >= ' . $arrParam['lft'])
					 ->where('rgt <= ' . $arrParam['rgt']);
			
			$resultTmp =  $this->db->get('product_category')->result_array();
			$this->db->flush_cache();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val)
					$result[] = $val['id'];
			}
		}
		
		return $result;
	}
	
	public function changeStatus($arrParam = null, $options = null){
		$cid = $arrParam['cid'];
		if(count($cid) > 0){
			$status = ($arrParam['type'] == 1)?1:0;
			$arrID = $this->getIds($cid);
	
			$data = array('status' => $status);
				
			$this->db->where('id IN (' . implode(',', $arrID) . ')');
			$this->db->update($this->_table,$data);
			$this->db->flush_cache();
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete-muti') {
			$cid = explode(',', $arrParam['cid']);
			$arrID = $this->getIds($cid);
	
			if(count($arrID)>0){
				$this->load->model("MProductCatDetail");
				foreach ($arrID as $key =>$value){
					$this->removeNode($value);
				}
				
				$product_id_arr = $this->MProductCatDetail->getIds(array('catIds'=>$arrID), array('task'=>'by-catIds'));
				$this->MProductCatDetail->deleteItem(array('catIds'=>$arrID), array('task'=>'by-catIds'));
				if(count($product_id_arr)>0) {
					$this->load->model("MProduct");
					$this->MProduct->deleteItem(array('ids'=>$product_id_arr), array('task'=>'by-product-ids'));
				}
	
			}
		}
	}
	
	public function sortItem($arrParam = null, $options = null){
		$cid = $arrParam['cid'];
		$order = $arrParam['ordering'];
		if($options['task'] == 'admin-sort'){
			if(count($cid)>0) {
				$itemOrdering = array();
				foreach ($cid as $key => $val){
					$itemOrdering[$val] = $order[$key];
				}
	
				$this->db->select('pc.id, pc.parent')
						->where('pc.id in ('.implode(',', $cid).')')
						->or_where('pc.level = 0')
						->order_by('pc.lft', 'ASC');
	
				$result =  $this->db->get($this->_table . ' as pc')->result_array();
				$this->db->flush_cache();
	
				$ordering = array();
				foreach ($result as $key => $val){
					$ordering[$val['parent']][$val['id']] = $itemOrdering[$val['id']];
				}
	
				unset($ordering[0]);
	
				foreach ($ordering as $key => $val){
					asort($ordering[$key]);
				}
	
				foreach ($ordering as $key => $ids){
					$parent = $key;
					foreach ($ids as $id => $val){
						$this->moveNode($id,$parent);
					}
				}
			}
	
		}
	}
}