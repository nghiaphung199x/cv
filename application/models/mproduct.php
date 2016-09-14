<?php 
class MProduct extends CI_Model{
	protected $_table="product";
	public function __construct(){
		parent::__construct();
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			if($ssFilter['id_cat']>0){
				$this->load->model('MProductCat');
				$arrId = $this->MProductCat->getIds(array($ssFilter['id_cat']));
			}
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
				
			if($ssFilter['id_theme']>0){
				$this->db->where('p.id_theme', $ssFilter['id_theme']);
			}
			
			if(count($arrId)>0) {
				$this->db->join('product_category_detail AS pcd', 'pcd.id_product = p.id', 'left')
						 ->where('pcd.id_category in ('.implode(',', $arrId).')')
						 ->group_by('p.id');
			}
			
			$query = $this->db->get($this->_table . ' as p');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}elseif($options['task'] == 'public-list'){
			$this->db->select('COUNT(DISTINCT(p.id)) as totalItem')
					 ->join('product_category_detail as pcd', 'p.id = pcd.id_product')
					 ->where('p.status = 1');
			
			if(!empty($arrParam['keywords'])){
				$keywords = trim($arrParam['keywords']);
				$this->db->where("(p.name like '%" . $keywords . "%') OR (p.alias like '%" . $keywords . "%')");
			}
			
			if(count($arrParam['catIds'])>0) {
				$this->db->where('id_category in ('.implode(',', $arrParam['catIds']).')');
			}
			
			if($arrParam['theme_id']>0) {
				$this->db->where('id_theme',(int)$arrParam['theme_id']);
			}
			
			$query = $this->db->get($this->_table . ' as p');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			if($ssFilter['id_cat']>0){
				$this->load->model('MProductCat');
				$arrId = $this->MProductCat->getIds(array($ssFilter['id_cat']));
			}
			$paginator = $arrParam['paginator'];
			$this->db->select('p.*, pt.name as theme_name')
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
					$this->db->where('p.id = ' . (int)$keywords);
				}else {
					$this->db->where("(p.name like '%" . $keywords . "%') OR (p.alias like '%" . $keywords . "%')");
				}
			}
			
			if($ssFilter['id_theme']>0){
				$this->db->where('p.id_theme', $ssFilter['id_theme']);
			}
			
			if(count($arrId)>0) {
				$this->db->join('product_category_detail AS pcd', 'pcd.id_product = p.id', 'left')
						 ->where('pcd.id_category in ('.implode(',', $arrId).')')
						 ->group_by('p.id');
			}
			
			$result =  $this->db->get($this->_table . ' as p')->result_array();
			$this->db->flush_cache();
			
		}elseif($options['task'] == 'public-list'){
			$this->db->select('p.id, p.alias, p.name, pcd.price, p.thumb')
					 ->join('product_category_detail as pcd', 'p.id = pcd.id_product')
					 ->where('p.status = 1')
					 ->group_by('p.id')
					 ->order_by('pcd.id','DESC');

			$paginator = $arrParam['paginator'];
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($arrParam['keywords'])){
				$keywords = trim($arrParam['keywords']);
				$this->db->where("(p.name like '%" . $keywords . "%') OR (p.alias like '%" . $keywords . "%')");
			}
			
			if(count($arrParam['catIds'])>0) {
				$this->db->where('id_category in ('.implode(',', $arrParam['catIds']).')');
			}
			
			if($arrParam['theme_id']>0) {
				$this->db->where('id_theme',(int)$arrParam['theme_id']);
			}

			$result =  $this->db->get($this->_table . ' as p')->result_array();
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function getIds($arrParam = null, $options = null) {
		if($options['task'] == 'by-theme') {
			$this->db->select('id')
					 ->where('id_theme in ('.implode(',', $arrParam['theme_ids']).')');
			
			$resultTmp = $this->db->get($this->_table)->result_array();
			$this->db->flush_cache();
			$result = array();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val)
					$result[] = $val['id'];
			}
			
		}
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('p.*, pt.name as theme_name')
				     ->join('product_theme AS pt', 'p.id_theme = pt.id','left')
					 ->where('p.id', $arrParam['id']);

			$result =  $this->db->get($this->_table . ' as p')->row_array();
			$this->db->flush_cache();
			if(!empty($result)) {
				if(!empty($result['picture_multi'])) {
					$result['picture_multi'] = unserialize($result['picture_multi']);
					foreach($result['picture_multi'] as $key => $value) {
						$tmp['picture_multi'] = $value;
						$tmp['orderImage'] = $key;
						$result['picture_multis'][] = $tmp;
					}
				}
				$this->db->select('*, id_category as id_cat')
						 ->where('id_product', $arrParam['id'])
						 ->order_by('id', 'ASC');
				
				$result['products'] =  $this->db->get('product_category_detail')->result_array();
				$this->db->flush_cache();
	
			}
		}elseif($options['task'] == 'public-info') {
			$this->db->select('p.*')
					 ->where('p.id', $arrParam['id'])
					 ->where('p.status = 1');
			
			$result =  $this->db->get($this->_table . ' as p')->row_array();
			$this->db->flush_cache();
			if(!empty($result)) {
				if(!empty($result['picture_multi'])) 
					$result['picture_multi'] = unserialize($result['picture_multi']);
						
				$this->db->select('*,pcd.id as pcd_id, pcd.id_category as id_cat, pc.name as product_cat_name')
						 ->join('product_category as pc', 'pcd.id_category = pc.id')
						 ->where('pcd.id_product', $arrParam['id'])
						 ->where('pcd.status = 1')
						 ->where('pcd.store > 0')
						 ->order_by('pcd.id', 'ASC');

				$result['products'] =  $this->db->get('product_category_detail as pcd')->result_array();
				$this->db->flush_cache();
				if(empty($result['products'])) 
					$result['products'] = array();
			
			}
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			if(isset($arrParam['picture_multi']) && !empty($arrParam['picture'])) {
				$orderImage = $arrParam['orderImage'];
				foreach($orderImage as &$value)
					$value = (int)$value;
				
				asort($orderImage);
				
				foreach($orderImage as $key => $value) {
					$picture_multis[] = $arrParam['picture_multis'][$key];
				}
				
				foreach($picture_multis as $value){
					$pic_multi[] = $value['picture_multi'];
					$pic_multi_thumb[] = getThumb($value['picture_multi']);
				}

				$pic_multi = serialize($pic_multi);
				$pic_multi_thumb = serialize($pic_multi_thumb);
			}
			
			if(empty($arrParam['alias'])){
				$alias 	= rewriteUrl($arrParam['name']);
			}else{
				$alias 	= $arrParam['alias'];
			}
			
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['alias']	 				= 				$alias;
			$data['detail']					= 				stripslashes($arrParam['detail']);
			$data['tags']					= 				stripslashes($arrParam['tags']);
			$data['meta_title']				= 				stripslashes($arrParam['meta_title']);
			$data['meta_description']		= 				stripslashes($arrParam['meta_description']);
			$data['meta_keywords']			= 				stripslashes($arrParam['meta_keywords']);
			$data['picture'] 				= 				$arrParam['picture'];
			$data['thumb'] 					= 				getThumb($arrParam['picture']);
			$data['picture_multi']			= 				$pic_multi;
			$data['thumb_multi']			= 				$pic_multi_thumb;
			$data['created']				= 				@date("Y-m-d H:i:s");
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['created_by']     		=				$arrParam['adminInfo']['id'];
			$data['modified_by']     		=				$arrParam['adminInfo']['id'];
			$data['order'] 					= 				$arrParam['order'];
			$data['id_theme'] 				= 				$arrParam['id_theme'];
			$data['status'] 				= 				$arrParam['statusP'];

			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
			
			$this->db->flush_cache();
			
			if(isset($arrParam['products'])) {
				$data = array();
				foreach($arrParam['products'] as $value) {
					$tmp['id_product'] 		= 	 $lastId;
					$tmp['id_category'] 	= 	 $value['id_cat'];
					$tmp['price'] 			= 	 $value['price'];
					$tmp['status'] 			= 	 $value['status'];
					$tmp['store'] 		    = 	 $value['store'];
					$tmp['sale'] 			= 	 $value['sale'];
					$data[] 				= 	 $tmp;
				}

				$this->db->insert_batch('product_category_detail', $data);
				$this->db->flush_cache();
				
			}
			
		}elseif($options['task'] == 'admin-edit'){
			if(isset($arrParam['picture_multi']) && !empty($arrParam['picture'])) {
				$orderImage = $arrParam['orderImage'];
				foreach($orderImage as &$value)
					$value = (int)$value;
			
				asort($orderImage);
			
				foreach($orderImage as $key => $value) {
					$picture_multis[] = $arrParam['picture_multis'][$key];
				}
			
				foreach($picture_multis as $value){
					$pic_multi[] = $value['picture_multi'];
					$pic_multi_thumb[] = getThumb($value['picture_multi']);
				}
			
				$pic_multi = serialize($pic_multi);
				$pic_multi_thumb = serialize($pic_multi_thumb);
			}
			
			if(empty($arrParam['alias'])){
				$alias 	= rewriteUrl($arrParam['name']);
			}else{
				$alias 	= $arrParam['alias'];
			}
			
			$this->db->where("id",$arrParam['id']);
			
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['alias']	 				= 				$alias;
			$data['detail']					= 				stripslashes($arrParam['detail']);
			$data['tags']					= 				stripslashes($arrParam['tags']);
			$data['meta_title']				= 				stripslashes($arrParam['meta_title']);
			$data['meta_description']		= 				stripslashes($arrParam['meta_description']);
			$data['meta_keywords']			= 				stripslashes($arrParam['meta_keywords']);
			$data['picture'] 				= 				$arrParam['picture'];
			$data['thumb'] 					= 				getThumb($arrParam['picture']);
			$data['picture_multi']			= 				$pic_multi;
			$data['thumb_multi']			= 				$pic_multi_thumb;
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['modified_by']     		=				$arrParam['adminInfo']['id'];
			$data['order'] 					= 				$arrParam['order'];
			$data['id_theme'] 				= 				$arrParam['id_theme'];
			$data['status'] 				= 				$arrParam['statusP'];
			
			$this->db->update($this->_table,$data);
			$this->db->flush_cache();

			if(!empty($arrParam['products'])) {
				$product_category_detail = array();
				foreach($arrParam['products'] as $value) {
					$tmp = array();
					$tmp['id_product'] 		= 		$arrParam['id'];
					$tmp['id_category'] 	= 		$value['id_cat'];
					$tmp['price'] 			= 		str_replace(".","",$value['price']);
					$tmp['store'] 			= 		$value['store'];
					$tmp['sale'] 			= 		$value['sale'];
					$tmp['status'] 			= 		$value['status'];
					$product_category_detail[$tmp['id_product'] . '_' . $tmp['id_category']] = $tmp;
				}
				$result =  $arrParam['old_products'];
				$this->db->flush_cache();
				if(!empty($result)) {
					foreach($result as $key => $value) {
						if(isset($product_category_detail[$value['id_product'] . '_' . $value['id_category']])) {
							$array = $product_category_detail[$value['id_product'] . '_' . $value['id_category']];
							$array['id'] = $value['id'];
							$arrayEdit[] = $array;
							unset($product_category_detail[$value['id_product'] . '_' . $value['id_category']]);
							unset($arrParam['old_products'][$key]);
						}
					}
				}
				if(isset($arrayEdit)) {
					foreach($arrayEdit as $value) {
						$data = array();
						$this->db->where("id",$value['id']);
						
						$data['id_product'] 	= 	$value['id_product'];
						$data['id_category'] 	= 	$value['id_category'];
						$data['price'] 			= 	str_replace(".","",$value['price']);
						$data['store'] 			= 	$value['store'];
						$data['sale'] 			= 	$value['sale'];
						$data['status'] 		= 	$value['status'];
						
						$this->db->update('product_category_detail',$data);
						$this->db->flush_cache();
					}
				}
			
				if(count($arrParam['old_products'])>0) {
					$i = 0;
					foreach($arrParam['old_products'] as $value){
						if($i == 0)
							$this->db->where('(id_product='.$value['id_product'].' AND id_category='.$value['id_category'].')');
						else
							$this->db->or_where('(id_product='.$value['id_product'].' AND id_category='.$value['id_category'].')');
					
						$i++;
					}
						
					$this->db->delete('product_category_detail');
					$this->db->flush_cache();
				}
				
				if(count($product_category_detail)>0) {
					$this->db->insert_batch('product_category_detail', $product_category_detail);
					$this->db->flush_cache();
				}

			}

			$lastId = $arrParam['id'];
	  }

		return $lastId;
	}
	
	public function deleteItem($arrParam = null, $options = null) {
		if($options['task'] == 'admin-delete-muti'){
			$cid = explode(',', $arrParam['cid']);
			if(!empty($cid) && isset($arrParam['cid'])){
				$this->load->model('MProductCatDetail');
				$ids = implode(',', $cid);
				$this->db->where('id IN (' . $ids . ')');
				$this->db->delete($this->_table);
				$this->db->flush_cache();
				
				$this->MProductCatDetail->deleteItem(array('product_ids'=>$cid), array('task'=>'by-product'));
			}
		
		}elseif($options['task'] == 'by-product-ids') {
			$this->db->where('id IN (' . implode(',', $arrParam['ids']) . ')');
			$this->db->delete($this->_table);
				
			$this->db->flush_cache();
		}elseif($options['task'] == 'by-theme') {
			$this->db->where('id_theme IN (' . implode(',', $arrParam['theme_ids']) . ')');
			$this->db->delete($this->_table);
			
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