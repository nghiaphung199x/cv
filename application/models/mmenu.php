<?php 
include_once('mnested.php');
class MMenu extends MNested{
	protected $_table="menu";
	
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		if($options == null){
			$this->db->select('id, name, level')
					 ->where('m.status = 1')
					 ->order_by('m.lft', 'ASC');
				
			if(!empty($arrParam['type_menu'])){
				$this->db->where('(m.type_menu = \''.$arrParam['type_menu'].'\' OR m.id = 1)');
			}else{
				$this->db->where('(m.type_menu=\'main_menu\' OR m.id = 1)');
			}
		
			if(!empty($ssFilter['lang_code'])){
				$this->db->where("m.lang_code = '" . $ssFilter['lang_code'] . "'");
			}else{
				$language = $this->session->userdata('language');
				$this->db->where("m.lang_code = '" . $language['lang'] . "'");
			}
			
			$resultTmp =  $this->db->get($this->_table . ' as m')->result_array();	
			if (count($resultTmp)>0) {
				$result = array();
				foreach ($resultTmp as $val) {
					$result[$val['id']] = str_repeat('--', $val['level']) . ' ' . $val['name'];
				}
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
			
			$this->db->flush_cache();
			
			$this->db->select('m.id, m.name, m.level, m.status, m.created_by')
					 ->order_by('m.lft','ASC');
			
			if(!empty($arrParam['type_menu'])){
				$this->db->where('(m.type_menu = \''.$arrParam['type_menu'].'\' OR m.id = 1)');
			}else{
				$this->db->where('(m.type_menu=\'main_menu\' OR m.id = 1)');
			}
			
			if(!empty($ssFilter['lang_code'])){
				$this->db->where("m.lang_code = '" . $ssFilter['lang_code'] . "'");
			}else{
				$language = $this->session->userdata('language');
				$this->db->where("m.lang_code = '" . $language['lang'] . "'");
			}
			
			if(count($catIds)>0) {
				$this->db->where('m.id NOT IN ('.implode(',', $catIds).')');
			}
			
			$resultTmp = $this->db->get($this->_table . ' as m')->result_array();
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
		$ssFilter  = $arrParam['ssFilter'];
		if($options['task'] == 'admin-list') {
			$this->db->select('COUNT(m.id) as total')
					->where('m.id != 1');
			
			if(!empty($arrParam['type_menu'])){
				$this->db->where('(m.type_menu = \''.$arrParam['type_menu'].'\' OR m.id = 1)');
			}else{
				$this->db->where('(m.type_menu=\'main_menu\' OR m.id = 1)');
			}

			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i',$keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('m.id = ?', (int)$keywords);
				}else {
					$this->db->where('m.name LIKE \'%'.$keywords.'%\'');
				}
			}
				
			if($ssFilter['level']>0) {
				$this->db->where('m.level <= ?',(int)$ssFilter['level']);
			}
				
			if(!empty($ssFilter['lang_code'])){
				$this->db->where("m.lang_code = '" . $ssFilter['lang_code'] . "'");
			}else{
				$language = $this->session->userdata('language');
				$this->db->where("m.lang_code = '" . $language['lang'] . "'");
			}
			
			$query = $this->db->get($this->_table . ' as m');
			$ret = $query->row();
			$result = $ret->total;
		}

		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$paginator = $arrParam['paginator'];
		$ssFilter  = $arrParam['ssFilter'];
	
		if($options['task'] == 'admin-list'){
			$this->db->select('m.id, m.name, m.parent, m.level, m.status, m.created_by, m.module_options, m.lang_code, user_name')
					  ->join("users AS u",'u.id = m.created_by', 'left')
					  ->where('m.id != 1')
					  ->order_by('m.lft','ASC');
			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($arrParam['type_menu'])){
				$this->db->where('(m.type_menu = \''.$arrParam['type_menu'].'\' OR m.id = 1)');
			}else{
				$this->db->where('(m.type_menu=\'main_menu\' OR m.id = 1)');
			}
	
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i',$keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('m.id = ?', (int)$keywords);
				}else {
					$this->db->where('m.name LIKE \'%'.$keywords.'%\'');
				}
			}
			
			if($ssFilter['level']>0) {
				$this->db->where('m.level <= ?',(int)$ssFilter['level']);
			}
			if(!empty($ssFilter['lang_code'])){
				$this->db->where("m.lang_code = '" . $ssFilter['lang_code'] . "'");
			}else{
				$language = $this->session->userdata('language');
				$this->db->where("m.lang_code = '" . $language['lang'] . "'");
			}	
	
			$result =  $this->db->get($this->_table . ' as m')->result_array();
	
		}
	
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('*')
					 ->where('id', $arrParam['id']);
				
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
			$data['created']				= 		@date("Y-m-d H:i:s");
			$data['created_by']				= 		$arrParam['adminInfo']['id'];
			$data['modified']				= 		@date("Y-m-d H:i:s");
			$data['modified_by']			= 		$arrParam['adminInfo']['id'];
			$data['module_options']		    = 		$arrParam['module_options'];
			$data['params']		    		= 		serialize($arrParam['params']);
			$data['type_menu']				= 		$arrParam['type_menu'];
			$data['target']					= 		$arrParam['target'];
			$data['status']					= 		$arrParam['status'];
			$data['images'] 				= 		$arrParam['images'];
			$data['thumb'] 					= 		getThumb($arrParam['images']);
	
			$lastId = $this->insertNode($data,$arrParam['parent']);
			$this->db->flush_cache();
	
		}elseif($options['task'] == 'admin-edit'){
			if(empty($arrParam['alias'])){
				$alias 	= rewriteUrl($arrParam['name']);
			}else{
				$alias 	= $arrParam['alias'];
			}
			
			$data['name']  					= 		stripslashes($arrParam['name']);
			$data['alias']					= 		$alias;
			$data['modified']				= 		@date("Y-m-d H:i:s");
			$data['modified_by']			= 		$arrParam['adminInfo']['id'];
			$data['module_options']		    = 		$arrParam['module_options'];
			$data['params']		    		= 		serialize($arrParam['params']);
			$data['type_menu']				= 		$arrParam['type_menu'];
			$data['target']					= 		$arrParam['target'];
			$data['status']					= 		$arrParam['status'];
			$data['images'] 				= 		$arrParam['images'];
			$data['thumb'] 					= 		getThumb($arrParam['images']);
			
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
				$this->db->select('m.id')
						 ->where('m.lft BETWEEN ' . $lft . ' AND ' . $rgt);
	
				$resultTmp = $this->db->get($this->_table . ' as m')->result_array();
	
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
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete-muti') {
			$cid = explode(',', $arrParam['cid']);
			$arrID = $this->getIds($cid);
	
			if(count($arrID)>0){
				foreach ($arrID as $key =>$value){
					$this->removeNode($value);
				}

			}
		}
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
	
	public function sortItem($arrParam = null, $options = null){
		$cid = $arrParam['cid'];
		$order = $arrParam['ordering'];
		if($options['task'] == 'admin-sort'){
			if(count($cid)>0) {
				$itemOrdering = array();
				foreach ($cid as $key => $val){
					$itemOrdering[$val] = $order[$key];
				}
	
				$this->db->select('m.id, m.parent')
						 ->where('m.id in ('.implode(',', $cid).')')
						 ->or_where('m.level = 0')
						 ->order_by('m.lft', 'ASC');
	
				$result =  $this->db->get($this->_table . ' as m')->result_array();
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