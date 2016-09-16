<?php 
include_once('mnested2.php');
class MTasks extends MNested2{
   
	protected $_table 			= 'tasks';
	protected $_id_admin 		= null;
	protected $_task_permission = null;
	
	public function __construct(){
		parent::__construct();
		$this->load->library('MY_System_Info');
		$info 			 = new MY_System_Info();
		$user_info 		 = $info->getMemberInfo();

		$task_permission = array();
		if(!empty($user_info['task_permission'])) {
			$task_permission = $user_info['task_permission'];
			$task_permission = explode(',', $task_permission);
		}

		$this->_id_admin = $user_info['id'];
		$this->_task_permission  = $task_permission;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'add'){
			if(isset($arrParam['customer'])) {
				$customer_ids = implode(',', $arrParam['customer']);
			}
				
			if($arrParam['trangthai'] == 2 || $arrParam['progress'] == 100) {
				$arrParam['trangthai'] = 2;
				$arrParam['progress'] = 100;
			}

			if($arrParam['parent'] == 0) {
				$data['name']  					= 		stripslashes($arrParam['name']);
				$data['detail'] 				= 		stripslashes($arrParam['detail']);
				$data['percent']				= 		1;
				$data['progress']				= 		$arrParam['progress'] / 100;
				$data['lft']					= 		0;
				$data['rgt']					= 		1;
				$data['level']					= 		0;
				$data['parent']					= 		$arrParam['parent'];
				$data['project_id']				= 		$arrParam['project_id'];
				$data['date_start']				= 		$arrParam['date_start'];
				$data['date_end']				= 		$arrParam['date_end'];
				$data['duration']				= 		$arrParam['duration'];
				$data['created']				= 		@date("Y-m-d H:i:s");
				$data['created_by']				= 		$arrParam['user_info']['id'];
				$data['modified']				= 		@date("Y-m-d H:i:s");
				$data['modified_by']			= 		$arrParam['user_info']['id'];
				$data['trangthai']				= 		$arrParam['trangthai'];
				$data['prioty']					= 		$arrParam['prioty'];
				$data['pheduyet']				= 		1;
				$data['type']					= 		$arrParam['type'];
				$data['project_id']				= 		0;
				$data['customer_ids']			= 		$customer_ids;

				$this->db->insert($this->_table,$data);
				$lastId = $this->db->insert_id();
				
				if($lastId > 0) {
					$this->db->where("id",$lastId);
					$data['project_id']	 		= 			$lastId;
					
					$this->db->update($this->_table,$data);
				}
				
			}else {
				$data['name']  					= 		stripslashes($arrParam['name']);
				$data['detail'] 				= 		stripslashes($arrParam['detail']);
				$data['percent']				= 		$arrParam['percent'] / 100;
				$data['progress']				= 		$arrParam['progress'] / 100;
				$data['date_start']				= 		$arrParam['date_start'];
				$data['date_end']				= 		$arrParam['date_end'];
				$data['duration']				= 		$arrParam['duration'];
				$data['created']				= 		@date("Y-m-d H:i:s");
				$data['created_by']				= 		$arrParam['user_info']['id'];
				$data['modified']				= 		@date("Y-m-d H:i:s");
				$data['modified_by']			= 		$arrParam['user_info']['id'];
				$data['trangthai']				= 		$arrParam['trangthai'];
				$data['prioty']					= 		$arrParam['prioty'];
				$data['pheduyet']				= 		$arrParam['pheduyet'];
				$data['type']					= 		$arrParam['type'];
				$data['project_id']				= 		$arrParam['project_id'];
				$data['customer_ids']			= 		$customer_ids;
		

				$lastId = $this->insertNode($data,$arrParam['parent'], $arrParam['project_id']);
			}
			
			if($lastId > 0) {
				$xemArr = array();
				if(isset($arrParam['xem'])) 
					$xemArr = $arrParam['xem'];

				$implementArr = array();
				if(isset($arrParam['implement'])) 
					$implementArr = $arrParam['implement'];
				
				$create_taskArr = array();
				if(isset($arrParam['create_task']))
					$create_taskArr = $arrParam['create_task'];
				
				$pheduyet_taskArr = array();
				if(isset($arrParam['pheduyet_task']))
					$pheduyet_taskArr = $arrParam['pheduyet_task'];
				
				$progress_taskArr = array();
				if(isset($arrParam['progress_task']))
					$progress_taskArr = $arrParam['progress_task'];
				
	
				if(isset($xemArr)) {
					foreach($xemArr as $user_id) {
						$tmp = array();
						$tmp['task_id'] = $lastId;
						$tmp['user_id'] = $user_id;
						
						$tmp['is_xem'] = 1;
						if(($key = array_search($user_id, $xemArr)) !== false) {
							unset($xemArr[$key]);
						}
			
						if(in_array($user_id, $implementArr)){
							$tmp['is_implement'] = 1;
							if(($key = array_search($user_id, $implementArr)) !== false) {
								unset($implementArr[$key]);
							}
						}else
							$tmp['is_implement'] = 0;

						if(in_array($user_id, $create_taskArr)){
							$tmp['is_create_task'] = 1;
							if(($key = array_search($user_id, $create_taskArr)) !== false) {
								unset($create_taskArr[$key]);
							}
						}else
							$tmp['is_create_task'] = 0;

						if(in_array($user_id, $pheduyet_taskArr)){
							$tmp['is_pheduyet'] = 1;
							if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
								unset($pheduyet_taskArr[$key]);
							}
						}else
							$tmp['is_pheduyet'] = 0;

						if(in_array($user_id, $progress_taskArr)){
							$tmp['is_progress'] = 1;
							if(($key = array_search($user_id, $progress_taskArr)) !== false) {
								unset($progress_taskArr[$key]);
							}
						}else
							$tmp['is_progress'] = 0;
						
						$tmp['created']		= 	 @date("Y-m-d H:i:s");

						$array[] = $tmp;
					}
				}
				
				if(!empty($implementArr)) {
					foreach($implementArr as $user_id) {
						$tmp = array();
						$tmp['task_id'] = $lastId;
						$tmp['user_id'] = $user_id;

						if(in_array($user_id, $xemArr)){
							$tmp['is_xem'] = 1;
							if(($key = array_search($user_id, $xemArr)) !== false) {
								unset($xemArr[$key]);
							}
						}else
							$tmp['is_xem'] = 0;
						
						$tmp['is_implement'] = 1;
						if(($key = array_search($user_id, $implementArr)) !== false) {
							unset($implementArr[$key]);
						}
						
						if(in_array($user_id, $create_taskArr)){
							$tmp['is_create_task'] = 1;
							if(($key = array_search($user_id, $create_taskArr)) !== false) {
								unset($create_taskArr[$key]);
							}
						}else
							$tmp['is_create_task'] = 0;
						
							
						if(in_array($user_id, $pheduyet_taskArr)){
							$tmp['is_pheduyet'] = 1;
							if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
								unset($pheduyet_taskArr[$key]);
							}
						}else
							$tmp['is_pheduyet'] = 0;
						
						if(in_array($user_id, $progress_taskArr)){
							$tmp['is_progress'] = 1;
							if(($key = array_search($user_id, $progress_taskArr)) !== false) {
								unset($progress_taskArr[$key]);
							}
						}else
							$tmp['is_progress'] = 0;
						
						$tmp['created']		= 	 @date("Y-m-d H:i:s");

						$array[] = $tmp;
					}
				}
				
				if(!empty($create_taskArr)) {
					foreach($create_taskArr as $user_id) {
						$tmp = array();
						$tmp['task_id'] = $lastId;
						$tmp['user_id'] = $user_id;
						
						if(in_array($user_id, $xemArr)){
							$tmp['is_xem'] = 1;
							if(($key = array_search($user_id, $xemArr)) !== false) {
								unset($xemArr[$key]);
							}
						}else
							$tmp['is_xem'] = 0;
						
						
						if(in_array($user_id, $implementArr)){
							$tmp['is_implement'] = 1;
							if(($key = array_search($user_id, $implementArr)) !== false) {
								unset($implementArr[$key]);
							}
						}else
							$tmp['is_implement'] = 0;
						
						$tmp['is_create_task'] = 1;
						if(($key = array_search($user_id, $create_taskArr)) !== false) {
							unset($create_taskArr[$key]);
						}
						
						if(in_array($user_id, $pheduyet_taskArr)){
							$tmp['is_pheduyet'] = 1;
							if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
								unset($pheduyet_taskArr[$key]);
							}
						}else
							$tmp['is_pheduyet'] = 0;
						
						if(in_array($user_id, $progress_taskArr)){
							$tmp['is_progress'] = 1;
							if(($key = array_search($user_id, $progress_taskArr)) !== false) {
								unset($progress_taskArr[$key]);
							}
						}else
							$tmp['is_progress'] = 0;
						
						$tmp['created']		= 	 @date("Y-m-d H:i:s");
						
						$array[] = $tmp;
						
					}
				}
				
				if(!empty($pheduyet_taskArr)) {
					foreach($pheduyet_taskArr as $user_id) {
						$tmp = array();
						$tmp['task_id'] = $lastId;
						$tmp['user_id'] = $user_id;
						
						if(in_array($user_id, $xemArr)){
							$tmp['is_xem'] = 1;
							if(($key = array_search($user_id, $xemArr)) !== false) {
								unset($xemArr[$key]);
							}
						}else
							$tmp['is_xem'] = 0;
						
						
						if(in_array($user_id, $implementArr)){
							$tmp['is_implement'] = 1;
							if(($key = array_search($user_id, $implementArr)) !== false) {
								unset($implementArr[$key]);
							}
						}else
							$tmp['is_implement'] = 0;
						
						if(in_array($user_id, $create_taskArr)){
							$tmp['is_create_task'] = 1;
							if(($key = array_search($user_id, $create_taskArr)) !== false) {
								unset($create_taskArr[$key]);
							}
						}else
							$tmp['is_create_task'] = 0;
						
						$tmp['is_pheduyet'] = 1;
						
						if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
							unset($pheduyet_taskArr[$key]);
						}
						
						if(in_array($user_id, $progress_taskArr)){
							$tmp['is_progress'] = 1;
							if(($key = array_search($user_id, $progress_taskArr)) !== false) {
								unset($progress_taskArr[$key]);
							}
						}else
							$tmp['is_progress'] = 0;
						
						$tmp['created']		= 	 @date("Y-m-d H:i:s");
						
						$array[] = $tmp;
					}
				}
				
				if(!empty($progress_taskArr)) {
					foreach($progress_taskArr as $user_id) {
						$tmp = array();
						$tmp['task_id'] = $lastId;
						$tmp['user_id'] = $user_id;
				
						if(in_array($user_id, $xemArr)){
							$tmp['is_xem'] = 1;
							if(($key = array_search($user_id, $xemArr)) !== false) {
								unset($xemArr[$key]);
							}
						}else
							$tmp['is_xem'] = 0;
				
				
						if(in_array($user_id, $implementArr)){
							$tmp['is_implement'] = 1;
							if(($key = array_search($user_id, $implementArr)) !== false) {
								unset($implementArr[$key]);
							}
						}else
							$tmp['is_implement'] = 0;
				
						if(in_array($user_id, $create_taskArr)){
							$tmp['is_create_task'] = 1;
							if(($key = array_search($user_id, $create_taskArr)) !== false) {
								unset($create_taskArr[$key]);
							}
						}else
							$tmp['is_create_task'] = 0;
				
						if(in_array($user_id, $pheduyet_taskArr)){
							$tmp['is_pheduyet'] = 1;
							if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
								unset($pheduyet_taskArr[$key]);
							}
						}else
							$tmp['is_pheduyet'] = 0;
				
						$tmp['is_progress'] = 1;
				
						if(($key = array_search($user_id, $progress_taskArr)) !== false) {
							unset($progress_taskArr[$key]);
						}
						
						$tmp['created']		= 	 @date("Y-m-d H:i:s");
				
						$array[] = $tmp;
					}
				}
				
				if(!empty($array)) {
					$this->db->insert_batch('task_user_relations', $array);
				}
			}

			$this->db->flush_cache();
		}elseif($options['task'] == 'edit') {
			$lastId = $arrParam['id'];
			if(isset($arrParam['customer'])) {
				$customer_ids = implode(',', $arrParam['customer']);
			}
			
			if($arrParam['trangthai'] == 2 || $arrParam['progress'] == 100) {
				$arrParam['trangthai'] = 2;
				$arrParam['progress'] = 100;
			}
			
			$this->db->where("id",$arrParam['id']);

			$data['name']  					= 		stripslashes($arrParam['name']);
			$data['detail'] 				= 		stripslashes($arrParam['detail']);
			$data['date_start']				= 		$arrParam['date_start'];
			$data['date_end']				= 		$arrParam['date_end'];
			$data['duration']				= 		$arrParam['duration'];
			$data['modified']				= 		@date("Y-m-d H:i:s");
			$data['modified_by']			= 		$arrParam['user_info']['id'];
			$data['trangthai']				= 		$arrParam['trangthai'];
			$data['type']					= 		$arrParam['type'];
			$data['customer_ids']			= 		$customer_ids;
			if($arrParam['parent'] != 0)
				$data['percent']				= 		$arrParam['percent'] / 100;
			
			$this->db->update($this->_table,$data);
			$this->db->flush_cache();
			
			$tblRelation = $this->model_load_model('MTasksRelation');
			$tblRelation->deleteItem(array('cid'=>array($arrParam['id'])), array('task'=>'delete-multi'));

			$xemArr = array();
			if(isset($arrParam['xem']))
				$xemArr = $arrParam['xem'];
		
			$implementArr = array();
			if(isset($arrParam['implement']))
				$implementArr = $arrParam['implement'];
		
			$create_taskArr = array();
			if(isset($arrParam['create_task']))
				$create_taskArr = $arrParam['create_task'];
		
			$pheduyet_taskArr = array();
			if(isset($arrParam['pheduyet_task']))
				$pheduyet_taskArr = $arrParam['pheduyet_task'];
			
			$progress_taskArr = array();
			if(isset($arrParam['progress_task']))
				$progress_taskArr = $arrParam['progress_task'];
			
			if(isset($xemArr)) {
				foreach($xemArr as $user_id) {
					$tmp = array();
					$tmp['task_id'] = $lastId;
					$tmp['user_id'] = $user_id;
		
					$tmp['is_xem'] = 1;
					if(($key = array_search($user_id, $xemArr)) !== false) {
						unset($xemArr[$key]);
					}
						
					if(in_array($user_id, $implementArr)){
						$tmp['is_implement'] = 1;
						if(($key = array_search($user_id, $implementArr)) !== false) {
							unset($implementArr[$key]);
						}
					}else
						$tmp['is_implement'] = 0;
		
					if(in_array($user_id, $create_taskArr)){
						$tmp['is_create_task'] = 1;
						if(($key = array_search($user_id, $create_taskArr)) !== false) {
							unset($create_taskArr[$key]);
						}
					}else
						$tmp['is_create_task'] = 0;
		
						
					if(in_array($user_id, $pheduyet_taskArr)){
						$tmp['is_pheduyet'] = 1;
						if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
							unset($pheduyet_taskArr[$key]);
						}
					}else
						$tmp['is_pheduyet'] = 0;
					
					if(in_array($user_id, $progress_taskArr)){
						$tmp['is_progress'] = 1;
						if(($key = array_search($user_id, $progress_taskArr)) !== false) {
							unset($progress_taskArr[$key]);
						}
					}else
						$tmp['is_progress'] = 0;

					$tmp['created']				= 		@date("Y-m-d H:i:s");

					$array[] = $tmp;
				}
			}
		
			if(!empty($implementArr)) {
				foreach($implementArr as $user_id) {
					$tmp = array();
					$tmp['task_id'] = $lastId;
					$tmp['user_id'] = $user_id;
		
					if(in_array($user_id, $xemArr)){
						$tmp['is_xem'] = 1;
						if(($key = array_search($user_id, $xemArr)) !== false) {
							unset($xemArr[$key]);
						}
					}else
						$tmp['is_xem'] = 0;
		
					$tmp['is_implement'] = 1;
					if(($key = array_search($user_id, $implementArr)) !== false) {
						unset($implementArr[$key]);
					}
		
					if(in_array($user_id, $create_taskArr)){
						$tmp['is_create_task'] = 1;
						if(($key = array_search($user_id, $create_taskArr)) !== false) {
							unset($create_taskArr[$key]);
						}
					}else
						$tmp['is_create_task'] = 0;
		
						
					if(in_array($user_id, $pheduyet_taskArr)){
						$tmp['is_pheduyet'] = 1;
						if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
							unset($pheduyet_taskArr[$key]);
						}
					}else
						$tmp['is_pheduyet'] = 0;
					
					if(in_array($user_id, $progress_taskArr)){
						$tmp['is_progress'] = 1;
						if(($key = array_search($user_id, $progress_taskArr)) !== false) {
							unset($progress_taskArr[$key]);
						}
					}else
						$tmp['is_progress'] = 0;

					$tmp['created']				= 		@date("Y-m-d H:i:s");
		
					$array[] = $tmp;
				}
			}
		
			if(!empty($create_taskArr)) {
				foreach($create_taskArr as $user_id) {
					$tmp = array();
					$tmp['task_id'] = $lastId;
					$tmp['user_id'] = $user_id;
		
					if(in_array($user_id, $xemArr)){
						$tmp['is_xem'] = 1;
						if(($key = array_search($user_id, $xemArr)) !== false) {
							unset($xemArr[$key]);
						}
					}else
						$tmp['is_xem'] = 0;
		
		
					if(in_array($user_id, $implementArr)){
						$tmp['is_implement'] = 1;
						if(($key = array_search($user_id, $implementArr)) !== false) {
							unset($implementArr[$key]);
						}
					}else
						$tmp['is_implement'] = 0;
		
					$tmp['is_create_task'] = 1;
					if(($key = array_search($user_id, $create_taskArr)) !== false) {
						unset($create_taskArr[$key]);
					}
		
					if(in_array($user_id, $pheduyet_taskArr)){
						$tmp['is_pheduyet'] = 1;
						if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
							unset($pheduyet_taskArr[$key]);
						}
					}else
						$tmp['is_pheduyet'] = 0;
					
					if(in_array($user_id, $progress_taskArr)){
						$tmp['is_progress'] = 1;
						if(($key = array_search($user_id, $progress_taskArr)) !== false) {
							unset($progress_taskArr[$key]);
						}
					}else
						$tmp['is_progress'] = 0;

					$tmp['created']				= 		@date("Y-m-d H:i:s");
		
					$array[] = $tmp;
				}
			}
		
			if(!empty($pheduyet_taskArr)) {
				foreach($pheduyet_taskArr as $user_id) {
					$tmp = array();
					$tmp['task_id'] = $lastId;
					$tmp['user_id'] = $user_id;
		
					if(in_array($user_id, $xemArr)){
						$tmp['is_xem'] = 1;
						if(($key = array_search($user_id, $xemArr)) !== false) {
							unset($xemArr[$key]);
						}
					}else
						$tmp['is_xem'] = 0;
		
		
					if(in_array($user_id, $implementArr)){
						$tmp['is_implement'] = 1;
						if(($key = array_search($user_id, $implementArr)) !== false) {
							unset($implementArr[$key]);
						}
					}else
						$tmp['is_implement'] = 0;
		
					if(in_array($user_id, $create_taskArr)){
						$tmp['is_create_task'] = 1;
						if(($key = array_search($user_id, $create_taskArr)) !== false) {
							unset($create_taskArr[$key]);
						}
					}else
						$tmp['is_create_task'] = 0;
		
					$tmp['is_pheduyet'] = 1;
					if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
						unset($pheduyet_taskArr[$key]);
					}
					
					if(in_array($user_id, $progress_taskArr)){
						$tmp['is_progress'] = 1;
						if(($key = array_search($user_id, $progress_taskArr)) !== false) {
							unset($progress_taskArr[$key]);
						}
					}else
						$tmp['is_progress'] = 0;

					$tmp['created']				= 		@date("Y-m-d H:i:s");
					
					$array[] = $tmp;
				}
			}
			
			if(!empty($progress_taskArr)) {
				foreach($progress_taskArr as $user_id) {
					$tmp = array();
					$tmp['task_id'] = $lastId;
					$tmp['user_id'] = $user_id;
			
					if(in_array($user_id, $xemArr)){
						$tmp['is_xem'] = 1;
						if(($key = array_search($user_id, $xemArr)) !== false) {
							unset($xemArr[$key]);
						}
					}else
						$tmp['is_xem'] = 0;
			
			
					if(in_array($user_id, $implementArr)){
						$tmp['is_implement'] = 1;
						if(($key = array_search($user_id, $implementArr)) !== false) {
							unset($implementArr[$key]);
						}
					}else
						$tmp['is_implement'] = 0;
			
					if(in_array($user_id, $create_taskArr)){
						$tmp['is_create_task'] = 1;
						if(($key = array_search($user_id, $create_taskArr)) !== false) {
							unset($create_taskArr[$key]);
						}
					}else
						$tmp['is_create_task'] = 0;
			
					if(in_array($user_id, $pheduyet_taskArr)){
						$tmp['is_pheduyet'] = 1;
						if(($key = array_search($user_id, $pheduyet_taskArr)) !== false) {
							unset($pheduyet_taskArr[$key]);
						}
					}else
						$tmp['is_pheduyet'] = 0;
			
					$tmp['is_progress'] = 1;
			
					if(($key = array_search($user_id, $progress_taskArr)) !== false) {
						unset($progress_taskArr[$key]);
					}

					$tmp['created']				= 		@date("Y-m-d H:i:s");
			
					$array[] = $tmp;
				}
			}

			if(!empty($array)) {
				$this->db->insert_batch('task_user_relations', $array);
			}


			$this->db->flush_cache();
			
			
		}elseif($options['task'] == 'pheduyet') {
			$this->db->where("id",$arrParam['id']);
			$data['pheduyet']			= 		1;
				
			$this->db->update($this->_table,$data);
				
			$this->db->flush_cache();
				
			$lastId = $arrParam['id'];
		}elseif($options['task'] == 'update-tiendo') {
			$this->db->where("id",$arrParam['id']);

			$data['trangthai']			= 		$arrParam['trangthai'];
			$data['prioty']				= 		$arrParam['prioty'];
			if($arrParam['progress'] != -1) 
				$data['progress']			= 		$arrParam['progress'] / 100;

			$this->db->update($this->_table,$data);
				
			$this->db->flush_cache();
				
			$lastId = $arrParam['id'];

		}elseif($options['task'] == 'update-progress') {
			$this->db->where("id",$arrParam['id']);

			$data['progress']			= 		$arrParam['progress'] / 100;

			$this->db->update($this->_table,$data);
				
			$this->db->flush_cache();
				
			$lastId = $arrParam['id'];
		}
	}
	
	public function listItem($options = null, $arrParams = null) {
		if($options == null) {
			$flagAll = true;
			if(!(in_array('update_project', $this->_task_permission) && in_array('update_all_task', $this->_task_permission))) 
				$flagAll = false;

			// không có toàn quyền
			if($flagAll == false) {
				// project, task user có liên quan
				$this->db->select("t.project_id, t.parent, r.task_id, r.is_implement, r.is_create_task, r.is_pheduyet, r.is_progress, t.type")
						->from('task_user_relations as r')
						->join('tasks as t', 't.id = r.task_id', 'left')
						->where('r.user_id', $this->_id_admin);
				
				$query = $this->db->get();
				
				$resultTmp = $query->result_array();
				$this->db->flush_cache();
				
				$project_ids = $implement_ids = $create_task_ids = array();
				if(!empty($resultTmp)) {
					foreach($resultTmp as $val) {
						$project_ids[] = $val['project_id'];
						if($val['is_implement'] == 1)
							$implement_ids[] = $val['task_id'];
						
						if($val['is_create_task'] == 1)
							$create_task_ids[] = $val['task_id'];
					}
				}

				$project_ids = array_unique($project_ids);
				
			}

			if(in_array('update_project', $this->_task_permission) || in_array('update_all_task', $this->_task_permission)) {
	    		$this->db->select("DATE_FORMAT(date_start, '%d-%m-%Y') as start_date", FALSE);
	    		$this->db->select("id, name as text, duration, progress, level, parent, type, project_id, lft, rgt, created, pheduyet")
			    		 ->from($this->_table)
			    		 ->order_by("lft",'ASC');
	    		
	    		$query = $this->db->get();
	    		$task_list_tmp = $query->result_array();

			}else {
				if(!empty($project_ids)) {
					$this->db->select("DATE_FORMAT(date_start, '%d-%m-%Y') as start_date", FALSE);
					$this->db->select("id, name as text, duration, progress, level, parent, type, project_id, lft, rgt, created, pheduyet")
							->from($this->_table)
							->where('project_id IN ('.implode(', ', $project_ids).')')
							->order_by("lft",'ASC');
					
					$query = $this->db->get();
					$task_list_tmp = $query->result_array();
				}else
					$task_list_tmp = array();

			}

			$task_list = array();
			if(!empty($task_list_tmp)) {
				foreach($task_list_tmp as $val) {
					if($val['level'] == 0 || $val['level'] == 1) {
						$val['open'] = true;
					}
					
					$val['text'] = $val['text'] . ' ('.($val['progress'] * 100).'%)';
					$val['color'] = '#489ee7';
					$task_list[$val['id']] = $val;
				}
					
			}

			$this->db->flush_cache();

			if(!empty($task_list)) {
				//allow task
				$allow_tasks = $deny_task = $drag_task = $click_task = array();
				if($flagAll == true) {
					$allow_tasks = $drag_task = $click_task = array_keys($task_list);
				}else {
					// cập nhật project
					if(in_array('update_project', $this->_task_permission)) {
						$allow_tasks = $drag_task = $click_task = $project_ids;
					}else
						$deny_task[] = "0";
					
					// quyền cập nhật tất cả các task
					if(in_array('update_all_task', $this->_task_permission)) {
						$tmp = array();
						foreach($resultTmp as $val)	{
							if($val['parent'] != 0 && $val['type'] == 1)
								$tmp[] = $val['id'];
						}
					
						$allow_tasks =  array_merge($allow_tasks, $tmp);
						$drag_task 	 =  array_merge($drag_task, $tmp);
						$click_task  =  array_merge($click_task, $tmp);
					}
					
					// quyền cập nhật trên nhánh
					if(!empty($implement_ids)) {
						foreach($task_list as $task_id => $task_detail) {
							foreach($implement_ids as $t_id) {
								if($task_detail['lft'] >=  $task_list[$t_id]['lft'] && $task_detail['rgt'] <=  $task_list[$t_id]['rgt']){
									if(in_array('update_brand_task', $this->_task_permission)) 
										$allow_tasks[] = $task_detail['id'];
									
									$click_task[] = $task_detail['id'];
								}
							}
						}

					}

					// create_task
					if(!empty($create_task_ids)) {
						foreach($task_list as $task_id => $task_detail) {
							foreach($create_task_ids as $t_id) {
								if($task_detail['lft'] >=  $task_list[$t_id]['lft'] && $task_detail['rgt'] <=  $task_list[$t_id]['rgt']){
									$allow_tasks[] = $task_detail['id'];
								}
							}
						}
					}
		
					foreach($task_list as $value) {
						if(!in_array($value['id'], $allow_tasks))
							$deny_task[] = $value['id'];
					}

					$drag_task = array_unique($drag_task);
				}
				
				if(!empty($deny_task)) {
					foreach($task_list as &$val){
						if(!in_array($val['id'], $click_task))
							$val['color'] = '#cccccc';
					}	
				}
				$result = array('ketqua'=>$task_list, 'deny'=>$deny_task, 'drag_task'=>$drag_task);
			}else {
				$deny_task = array();
				if(!in_array('update_project', $this->_task_permission))
					$deny_task[] = "0";
				
				$result = array('ketqua'=>array(), 'deny'=>$deny_task, 'drag_task'=>array());
			}

			return $result;
			
		}
	}
	
	public function getInfo($arrParam = null, $options = null) {
		if($options['task'] == 'create-task') {
			$this->db->select("t.id, t.created")
					->from($this->_table . ' as t')
					->where('t.lft <= ' . $arrParam['lft'] . ' AND rgt >= ' . $arrParam['rgt'])
					->where('t.project_id',$arrParam['project_id']);
			
			$query = $this->db->get();
			$resultTmp =  $query->result_array();
			$this->db->flush_cache();
			
			if(!empty($resultTmp)) {
				$task_ids = $created = array();
				foreach($resultTmp as $val) {
					$task_ids[] = $val['id'];
					$created[]  = $val['created'];
				}

				$result['task_ids'] = $task_ids;
				$result['created']  = $created;
			}else
				$result = array();
		}
		
		return $result;
	}
	
	public function getIds($arrParam = null, $options = null) {
		if($options == null) {
			$this->db->select("t.id")
					->from($this->_table . ' as t')
					->where('t.lft >= ' . $arrParam['lft'] . ' AND t.rgt <= '.$arrParam['rgt'] )
					->where('t.project_id', $arrParam['project_id']);
			
			$query = $this->db->get();	
			$resultTmp =  $query->result_array();
			

		}elseif($options['task'] == 'up-branch') {
			$this->db->select("t.id")
					 ->from($this->_table . ' as t')
					 ->where('t.lft <= ' . $arrParam['lft'] . ' AND t.rgt >= '.$arrParam['rgt'] )
					 ->where('t.project_id', $arrParam['project_id']);
				
			$query = $this->db->get();
			$resultTmp =  $query->result_array();
		}
		
		$result = array();
		if(!empty($resultTmp)) {
			foreach($resultTmp as $val)
				$result[] = $val['id'];
		}
		
		return $result;
	}
	
	public function getItems($arrParam = null, $options = null) {
		if($options['task'] == 'public-info') {
			$this->db->select("t.*")
					->from($this->_table . ' as t')
					->where('t.id IN ('.implode(', ', $arrParam['cid']).')');
				
			$this->db->select("DATE_FORMAT(t.date_start, '%d/%m/%Y') as date_start", FALSE);
			$this->db->select("DATE_FORMAT(t.date_end, '%d/%m/%Y') as date_end", FALSE);
			
			$query = $this->db->get();
			
			$resultTmp =  $query->result_array();
			$this->db->flush_cache();
			

		}elseif($options['task'] == 'by-project') {
			$this->db->select("t.*")
						->from($this->_table . ' as t')
						->where('t.project_id', $arrParam['project_id']);
			
			$this->db->select("DATE_FORMAT(t.date_start, '%d/%m/%Y') as date_start", FALSE);
			$this->db->select("DATE_FORMAT(t.date_end, '%d/%m/%Y') as date_end", FALSE);
				
			$query = $this->db->get();
				
			$resultTmp =  $query->result_array();
			$this->db->flush_cache();	
		}
		
		$result = array();
		if(!empty($resultTmp)) {
			foreach($resultTmp as $val)
				$result[$val['id']] = $val;
		}
		
		return $result;
	}

	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'public-info') {
			$this->db->select("t.*")
					 ->from($this->_table . ' as t')
					 ->where('t.id',$arrParam['id']);
			
			$this->db->select("DATE_FORMAT(t.date_start, '%d/%m/%Y') as date_start", FALSE);
			$this->db->select("DATE_FORMAT(t.date_end, '%d/%m/%Y') as date_end", FALSE);

			$query = $this->db->get();
				
			$result =  $query->row_array();
			$this->db->flush_cache();
			if($options['brand'] == 'detail' || $options['brand'] == 'full') {
				$customerTable = $this->model_load_model('MCustomers');
				if(!empty($result)) {
					if(!empty($result['customer_ids'])) {
						$cid 		  = explode(',', $result['customer_ids']);
						$tblCustomers = $this->model_load_model('MCustomers');
						
						$result['customers'] = $tblCustomers->getItems(array('cid'=>$cid));
					}

					// tất cả task bao gồm task ở bên trên
					$task_ids = $this->getIds(array('lft'=>$result['lft'], 'rgt'=>$result['rgt'], 'project_id'=>$result['project_id']), array('task'=>'up-branch'));
					
					$this->db->select('r.*')
							->from('task_user_relations as r')
							->where('r.task_id IN ('.implode(',', $task_ids).')')
							->order_by('r.user_id', 'ASC');

					$query = $this->db->get();
					$resultTmp = $query->result_array();
	
					$this->db->flush_cache();
					$user_ids = array();
						
					$user_ids = array($result['created_by']);

					if(!empty($resultTmp)) {
						foreach($resultTmp as $val)
							$user_ids[] = $val['user_id'];
						
						$user_ids = array_unique($user_ids);
						$tblUser  = $this->model_load_model('MUser');
						$users 	  = $tblUser->getItems(array('user_ids'=>$user_ids));
						
						$result['created_by_name'] = $users[$result['created_by']]['user_name'];

						foreach($resultTmp as $val) {	
							$user_id  = $val['user_id'];
							$keywords = $val['task_id'] . '-' . $val['user_id'];

							if($val['is_xem'] == 1) 
								$result['is_xem'][$keywords] = $users[$user_id];

							if($val['is_implement'] == 1) 
								$result['is_implement'][$keywords] = $users[$user_id];
	
							if($val['is_create_task'] == 1)
								$result['is_create_task'][$keywords] = $users[$user_id];

							if($val['is_pheduyet'] == 1)
								$result['is_pheduyet'][$keywords] = $users[$user_id];
							
							if($val['is_progress'] == 1)
								$result['is_progress'][$keywords] = $users[$user_id];
						}
						
					}else {
						$tblUser  = $this->model_load_model('MUser');
						$users 	  = $tblUser->getItems(array('user_ids'=>$user_ids));
						
						$result['created_by_name'] = $users[$result['created_by']]['user_name'];
					}
				}
			}
		}
	
		return $result;
	}

}