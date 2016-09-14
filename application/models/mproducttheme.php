<?php 
class MProductTheme extends CI_Model{
	protected $_table="product_theme";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
		if($options == null || $options['task'] == 'admin-item'){
			$this->db->select('pt.id, pt.name')
					 ->order_by('pt.id', 'ASC');
			
			$resultTmp =  $this->db->get($this->_table . ' as pt')->result_array();
				
			$this->db->flush_cache();
			$result = array('Chọn chủ đề');
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
			$this->db->select('COUNT(pt.id) AS totalItem');
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('pt.id = ' . (int)$keywords);
				}else {
					$this->db->where("(pt.name like '%" . $keywords . "%') OR (pt.alias like '%" . $keywords . "%')");
				}
			}
			
			$query = $this->db->get($this->_table . ' as pt');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			$paginator = $arrParam['paginator'];
			$this->db->select('pt.*, u.user_name, COUNT(p.id) as total_item')
					 ->join('product as p', 'p.id_theme = pt.id', 'left')
					 ->join('users AS u', 'u.id = pt.created_by', 'left')
					 ->group_by('pt.id');
			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('pt.id = ' . (int)$keywords);
				}else {
					$this->db->where("(pt.name like '%" . $keywords . "%') OR (pt.alias like '%" . $keywords . "%')");
				}
			}
			
			$result =  $this->db->get($this->_table . ' as pt')->result_array();
			$this->db->flush_cache();
		}elseif($options['task'] == 'public-list'){
			$this->db->select('pt.id, pt.name, pt.alias')
					 ->where('pt.status = 1')
					 ->order_by('pt.order','ASC')
					 ->order_by('pt.id','DESC');
			
			$result =  $this->db->get($this->_table . ' as pt')->result_array();
			$this->db->flush_cache();
			if(!empty($result)){
				foreach($result as &$val) {
					$val['linkMenu'] = rewriteLink('shopping-index-theme', array('alias'=>$val['alias'], 'id'=>$val['id']));
				}
			}
		}
		return $result;
	}
	
	public function getItems($arrParam = null, $options = null){
		if($options['task'] == 'public-info'){
			$this->db->select('pt.*')
					 ->where('pt.id in ('.implode(',', $arrParam['theme_ids']).')')
					 ->where('status = 1');
	
			$resultTmp =  $this->db->get($this->_table . ' as pt')->result_array();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val) {
					$result[$val['id']] = $val;
				}
			}
		}
	
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('pt.*')
					  ->where('pt.id',$arrParam['id']);
	
			$result =  $this->db->get($this->_table . ' as pt')->row_array();
		}elseif($options['task'] == 'public-info') {
			$this->db->select('pt.*')
					 ->where('pt.id',$arrParam['id'])
					 ->where('pt.status = 1');
			
			$result =  $this->db->get($this->_table . ' as pt')->row_array();
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
			
			$data['name']					= 			  stripslashes($arrParam['name']);
			$data['alias'] 					= 			  $alias;
			$data['created']				= 			  @date("Y-m-d H:i:s");
			$data['created_by']     		=			  $arrParam['adminInfo']['id'];
			$data['modified']				= 			  @date("Y-m-d H:i:s");
			$data['modified_by']     		=			  $arrParam['adminInfo']['id'];
			$data['status']	 				= 			  $arrParam['status'];
			$data['excerpt']				= 			  stripslashes($arrParam['excerpt']);
			$data['content']				= 			  stripslashes($arrParam['content']);
			$data['meta_title']				= 			  stripslashes($arrParam['meta_title']);
			$data['meta_description']		= 			  stripslashes($arrParam['meta_description']);
			$data['meta_keywords']			= 			  stripslashes($arrParam['meta_keywords']);
			$data['icon']					= 			  $arrParam['icon'];
			$data['images']					= 			  $arrParam['images'];
			$data['thumb'] 					= 			  getThumb($arrParam['images']);
			$data['file']	 				= 			  $arrParam['file'];
			$data['video']	 				= 			  $arrParam['video'];
			$data['audio']	 				= 			  $arrParam['audio'];
			$data['flash']	 				= 			  $arrParam['flash'];
			$data['order']	 				= 			  $arrParam['order'];

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
			
			$data['name']					= 			  stripslashes($arrParam['name']);
			$data['alias'] 					= 			  $alias;
			$data['modified']				= 			  @date("Y-m-d H:i:s");
			$data['modified_by']     		=			  $arrParam['adminInfo']['id'];
			$data['status']	 				= 			  $arrParam['status'];
			$data['excerpt']				= 			  stripslashes($arrParam['excerpt']);
			$data['content']				= 			  stripslashes($arrParam['content']);
			$data['meta_title']				= 			  stripslashes($arrParam['meta_title']);
			$data['meta_description']		= 			  stripslashes($arrParam['meta_description']);
			$data['meta_keywords']			= 			  stripslashes($arrParam['meta_keywords']);
			$data['icon']					= 			  $arrParam['icon'];
			$data['images']					= 			  $arrParam['images'];
			$data['thumb'] 					= 			  getThumb($arrParam['images']);
			$data['file']	 				= 			  $arrParam['file'];
			$data['video']	 				= 			  $arrParam['video'];
			$data['audio']	 				= 			  $arrParam['audio'];
			$data['flash']	 				= 			  $arrParam['flash'];
			$data['order']	 				= 			  $arrParam['order'];
	
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
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete-muti'){
			$cid = explode(',', $arrParam['cid']);
			if(!empty($cid) && isset($arrParam['cid'])){
				$ids = implode(',', $cid);
				$this->db->where('id IN (' . $ids . ')');
				$this->db->delete($this->_table);
				$this->db->flush_cache();
				
				$this->db->select('COUNT(id) as totalItem')
						 ->where('id_theme in ('.$ids.')');
				
				$query = $this->db->get('product');
				$totalItem = $query->row()->totalItem;
				$this->db->flush_cache();
				
				if($totalItem>0) {
					$this->load->model("MProduct");
					$this->load->model("MProductCatDetail");
					
					$product_ids = $this->MProduct->getIds(array('theme_ids'=>$ids), array('task'=>'by-theme'));
					$this->MProduct->deleteItem(array('theme_ids'=>$ids), array('task'=>'by-theme'));
					$this->MProductCatDetail->deleteItem(array('product_ids'=>$product_ids), array('task'=>'by-product'));
				}
			}
	
		}
	}
}