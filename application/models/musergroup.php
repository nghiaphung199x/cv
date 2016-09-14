<?php
class MUserGroup extends CI_Model{
	protected $_table="user_group";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
	
		$this->db->select('id, group_name');
		$resultTmp = $this->db->get($this->_table)->result_array();
		
		$this->db->flush_cache();
		if(!empty($resultTmp)) {
			foreach($resultTmp as $value) {
				$result[$value['id']] = $value['group_name'];
		
			}
		}else
			$result = array();
		
		$result[0] = ' -- Select an Item -- ';
		ksort($result);
		
		return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		
		$this->db->select('COUNT(g.id) as total');
		if(!empty($ssFilter['keywords'])){
			$keywords = trim($ssFilter['keywords']);
			if(preg_match('#^(id:){1}#i', $keywords)){
				$keywords = (int)substr($keywords,3);
				$this->db->where('g.id', (int)$keywords);
			}else {
				$this->db->where('g.group_name LIKE \'%' . $keywords . '%\'');
			}
			
		}
		
		$query = $this->db->get($this->_table . ' as g');
		$result = $query->row()->total;
		
		$this->db->flush_cache();
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		$paginator = $arrParam['paginator'];

		if($options['task'] == 'admin-list'){
			$this->db->select('g.id, g.group_name, g.group_acp, g.status, g.order, COUNT(u.id) as members')
					 ->join("users as u",'g.id = u.group_id', 'left')
					 ->group_by('g.id');
			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);

			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'], $ssFilter['order']);
			}
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				if(preg_match('#^(id:){1}#i', $keywords)){
					$keywords = (int)substr($keywords,3);
					$this->db->where('g.id', (int)$keywords);
				}else {
					$this->db->where('g.group_name LIKE \'%' . $keywords . '%\'');
				}
					
			}
			
			$result =  $this->db->get($this->_table . ' as g')->result_array();
			
			$this->db->flush_cache();
		}
		
		return $result;
	}

	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->where('id',$arrParam['id']);
			$result = $this->db->get($this->_table)->row_array();
			
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-permission'){
			$this->db->select('privilege_id')
					 ->where('group_id', (int)$arrParam['id'])
					 ->where('status = 1');
				
			$resultTmp =  $this->db->get('user_group_privileges')->result_array();
			
			$this->db->flush_cache();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val)
					$result[] = $val['privilege_id'];
			}
				
		}elseif($options['task'] == 'admin-files'){
			$this->db->where('group_id',(int)$arrParam['id']);
			$result = $this->db->get('user_files')->row_array();
			
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function privileges($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			$this->db->select('*');
			$result = $this->db->get('privileges')->result_array();
			
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-module') {
			$this->db->select('pr.module')
					 ->group_by('pr.module');
			
			$resultTmp = $this->db->get('privileges as pr')->result_array();
			
			$this->db->flush_cache();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val)
					$result[] = $val['module'];
			}
		}
		
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add'){
			
			$data['group_name'] 		= 		stripslashes($arrParam['group_name']);
			$data['avatar'] 			= 		$arrParam['avatar'];
			$data['ranking'] 			= 		$arrParam['ranking'];
			$data['group_acp'] 			=	 	$arrParam['group_acp'];
			$data['group_default'] 		= 		$arrParam['group_default'];
			$data['created'] 			=		@date("Y-m-d H:i:s");
			$data['created_by'] 		= 		$arrParam['adminInfo']['id'];
			$data['status'] 			= 		$arrParam['status'];
			$data['order'] 				= 		$arrParam['order'];
			if(isset($arrParam['fullAccess']) == 'on'){
				$data['permission'] 	= 'Full Access';
			}else{
				$data['permission'] 	= 'Limit Access';
			}
			
			$this->db->insert($this->_table,$data);
			$id = $this->db->insert_id();
			
			$this->db->flush_cache();
			

			if(!isset($arrParam['fullAccess']) && count($arrParam['privileges']) > 0){
				foreach ($arrParam['privileges'] AS $key => $val){
					$bind = array(
							'privilege_id' => $val,
							'group_id' => $id,
							'status' => 1
					);
					
					$this->db->insert('user_group_privileges',$bind);
					
					$this->db->flush_cache();
				}
			}
			
			$array_keys = array('disabled', 'denyZipDownload', 'denyExtensionRename', 'files_upload', 'files_delete', 'files_copy', 'files_move', 'files_rename', 'dirs_create', 'dirs_delete', 'dirs_rename');
			foreach($array_keys as $key)
				if(empty($arrParam[$key]))
					$arrParam[$key] = 0;
						
			$bind = array(
					'group_id' => $id,
					'disabled' => $arrParam['disabled'],
					'denyZipDownload' => $arrParam['denyZipDownload'],
					'denyExtensionRename' => $arrParam['denyExtensionRename'],
					'files_upload' => $arrParam['files_upload'],
					'files_delete' => $arrParam['files_delete'],
					'files_copy' => $arrParam['files_copy'],
					'files_move' => $arrParam['files_move'],
					'files_rename' => $arrParam['files_rename'],
					'dirs_create' => $arrParam['dirs_create'],
					'dirs_delete' => $arrParam['dirs_delete'],
					'dirs_rename' => $arrParam['dirs_rename'],
			);
			
			$this->db->insert('user_files',$bind);
			
			$this->db->flush_cache();
			
		}elseif($options['task'] == 'admin-edit'){
			$this->db->where("group_id",$arrParam['id']);
			$this->db->delete('user_group_privileges');
			
			$this->db->flush_cache();
			
			$this->db->where("group_id",$arrParam['id']);
			$this->db->delete('user_files');
			
			$this->db->flush_cache();
			
			$this->db->where("id",$arrParam['id']);
			
			$data['group_name'] 		= 		stripslashes($arrParam['group_name']);
			$data['avatar'] 			= 		$arrParam['avatar'];
			$data['ranking']		 	= 		$arrParam['ranking'];
			$data['group_acp'] 			= 		$arrParam['group_acp'];
			$data['group_default'] 		= 		$arrParam['group_default'];
			$data['modified'] 			= 		@date("Y-m-d H:i:s");
			$data['modified_by'] 		= 		$arrParam['adminInfo']['id'];
			$data['status'] 			= 		$arrParam['status'];
			$data['order'] 				= 		$arrParam['order'];
			if(isset($arrParam['fullAccess']) == 'on'){
				$data['permission'] 	= 	'Full Access';
			}else{
				$data['permission'] 	= 'Limit Access';
			}
			
			$this->db->update($this->_table,$data);
			
			$this->db->flush_cache();
			
			$id = $arrParam['id'];

			if(!isset($arrParam['fullAccess']) && count($arrParam['privileges']) > 0){
				foreach ($arrParam['privileges'] AS $key => $val){
					$bind = array(
							'privilege_id' => $val,
							'group_id' => $id,
							'status' => 1
					);
						
					$this->db->insert('user_group_privileges',$bind);
					
					$this->db->flush_cache();
				
				}
			}		
			
			$array_keys = array('disabled', 'denyZipDownload', 'denyExtensionRename', 'files_upload', 'files_delete', 'files_copy', 'files_move', 'files_rename', 'dirs_create', 'dirs_delete', 'dirs_rename');
			foreach($array_keys as $key)
				if(empty($arrParam[$key]))
				$arrParam[$key] = 0;
			
			$bind = array(
					'group_id' => $id,
					'disabled' => $arrParam['disabled'],
					'denyZipDownload' => $arrParam['denyZipDownload'],
					'denyExtensionRename' => $arrParam['denyExtensionRename'],
					'files_upload' => $arrParam['files_upload'],
					'files_delete' => $arrParam['files_delete'],
					'files_copy' => $arrParam['files_copy'],
					'files_move' => $arrParam['files_move'],
					'files_rename' => $arrParam['files_rename'],
					'dirs_create' => $arrParam['dirs_create'],
					'dirs_delete' => $arrParam['dirs_delete'],
					'dirs_rename' => $arrParam['dirs_rename'],
			);
				
			$this->db->insert('user_files',$bind);
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
	
	public function changeStatusAcp($arrParam = null, $options = null){
		if($arrParam['id'] > 0){
			if($arrParam['type'] == 1){
				$status = 1;
			}else{
				$status = 0;
			}

			$data = array('group_acp' => $status);
			$this->db->where('id', $arrParam['id']);
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
			$cid = $arrParam['cid'];
			if(!empty($cid) && isset($arrParam['cid'])){
				$newArr = explode(',', $cid);
				if (($key = array_search(1, $newArr)) !== false) {
					unset($newArr[$key]);
				}
	
				if (($key = array_search(2, $newArr)) !== false) {
					unset($newArr[$key]);
				}
	
				if (($key = array_search(3, $newArr)) !== false) {
					unset($newArr[$key]);
				}
	
				if (($key = array_search(4, $newArr)) !== false) {
					unset($newArr[$key]);
				}
	
				if(count($newArr)>0) {
					$ids = implode(',', $newArr);
					
					$this->db->where('id in ('.$ids.')');
					$this->db->delete($this->_table);
					
					$this->db->flush_cache();
					
					$table = 'user_group_privileges';
					$this->db->where('group_id in ('.$ids.')');
					$this->db->delete($table);
					
					$this->db->flush_cache();
					
					$table = 'user_files';
					$this->db->where('group_id in ('.$ids.')');
					$this->db->delete($table);
					
					$this->db->flush_cache();
	
					$table = 'users';
					$this->db->where('group_id in ('.$ids.')');
					$this->db->delete($table);		

					$this->db->flush_cache();
				}
	
			}
	
		}
	}
	
}	