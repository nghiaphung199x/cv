<?php 
class MPermission extends CI_Model{
	protected $_table="privileges";
	public function __construct(){
		parent::__construct();
	}
	
	
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$ssFilter  = $arrParam['ssFilter'];
			$this->db->select('COUNT(p.id) as total');
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'], $ssFilter['order']);
			}
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				$this->db->where('p.name LIKE \'%' . $keywords . '%\'');
			
			}
			if($ssFilter['p_module'] != 'all'){
				$this->db->where("p.module LIKE '" . $ssFilter['p_module'] . "'");
			}
			
			$query = $this->db->get($this->_table . ' as p');
			$result = $query->row()->total;
			
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-list'){
			$paginator = $arrParam['paginator'];
			$ssFilter  = $arrParam['ssFilter'];
	
			$this->db->select('*');
			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'], $ssFilter['order']);
			}
				
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				$this->db->where('p.name LIKE \'%' . $keywords . '%\'');
					
			}
			if($ssFilter['p_module'] != 'all'){
				$this->db->where("p.module LIKE '" . $ssFilter['p_module'] . "'");
			}
			
			$result =  $this->db->get($this->_table . ' as p')->result_array();
			
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-list-all'){
			$this->db->select('*');
			$result =  $this->db->get($this->_table)->result_array();
			
			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('id, name, p.module as p_module, p.controller as p_controller, p.action as p_action')
					 ->where('p.id', (int)$arrParam['id']);
			
			$result =  $this->db->get($this->_table . ' as p')->row_array();
			
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add') {
			$data['name'] 		= 	stripslashes($arrParam['name']);
			$data['module'] 	= 	stripslashes($arrParam['p_module']);
			$data['controller'] = 	stripslashes($arrParam['p_controller']);
			$data['action'] 	= 	stripslashes($arrParam['p_action']);
				
			$this->db->insert($this->_table,$data);
				
			$this->db->flush_cache();
				
		}elseif($options['task'] == 'admin-edit'){
			$this->db->where("id",$arrParam['id']);
			$data['name'] 		= 	stripslashes($arrParam['name']);
			$data['module'] 	= 	stripslashes($arrParam['p_module']);
			$data['controller'] = 	stripslashes($arrParam['p_controller']);
			$data['action'] 	= 	stripslashes($arrParam['p_action']);
			
			$this->db->update($this->_table,$data);
			
			$this->db->flush_cache();
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete-muti'){
			$cid = explode(',', $arrParam['cid']);
			if(!empty($cid) && isset($arrParam['cid'])){
				$ids = implode(',', $cid);
				$this->db->where('id in ('.$ids.')');
				$this->db->delete($this->_table);
				
				$this->db->flush_cache();
				
				$this->db->where('privilege_id in ('.$ids.')');
				$this->db->delete('user_group_privileges');
				
				$this->db->flush_cache();
			}
	
		}
	}
}