<?php 
class MTaskProgress extends CI_Model{
   
	protected $_table = 'task_progress';
	protected $_items = null;
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
	
	public function getItem($arrParam = null, $options = null) {
		if($options['task'] == 'public-info') {
			$this->db->select('p.*')
					 ->from($this->_table . ' AS p')
					 ->where('p.id',$arrParam['id']);
			
			$query = $this->db->get();
			$result = $query->row_array();

			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'public-list'){
			$ssFilter  = $arrParam['ssFilter'];

			$this->db -> select('COUNT(p.id) AS totalItem')
					  -> from($this->_table . ' AS p')
					  -> where('p.task_id', $arrParam['task_id']);
			
			$query = $this->db->get();
			
			$result = $query->row()->totalItem;
			
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null) {
		if($options['task'] == 'add') {
			$data['task_id'] 			= $arrParam['task_id'];
			$data['trangthai'] 			= $arrParam['trangthai'];
			$data['prioty'] 			= $arrParam['prioty'];
			$data['progress'] 			= $arrParam['progress'] / 100;
			$data['pheduyet'] 			= $arrParam['pheduyet'];
			$data['note']				= stripslashes($arrParam['note']);
			$data['reply']				= '';
			$data['created']			= @date("Y-m-d H:i:s");
			$data['created_by']     	= $arrParam['adminInfo']['id'];
			$data['modified']			= @date("Y-m-d H:i:s");
			$data['modified_by']    	= $arrParam['adminInfo']['id'];
			$data['user_pheduyet']		= 0;
			$data['user_pheduyet_name'] = '';
			
			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
				
			$this->db->flush_cache();
			
			return $lastId;
		}elseif($options['task'] == 'edit') {
			$this->db->where("id",$arrParam['id']);
			
			if($arrParam['trangthai'] == 2 || $arrParam['progress'] == 100) {
				$arrParam['trangthai'] = 2;
				$arrParam['progress'] = 100;
			}
	
			$data['trangthai'] 			= $arrParam['trangthai'];
			$data['prioty'] 			= $arrParam['prioty'];
			$data['progress'] 			= $arrParam['progress'] / 100;
			$data['note']				= stripslashes($arrParam['note']);
			$data['modified']			= @date("Y-m-d H:i:s");
			$data['modified_by']    	= $arrParam['adminInfo']['id'];

			$this->db->update($this->_table,$data);
			
			$this->db->flush_cache();
		}elseif($options['task'] == 'update-pheduyet') {
			$this->db->where("id",$arrParam['id']);
			
			$data['pheduyet'] 			= $arrParam['pheduyet'];
			$data['reply']				= stripslashes($arrParam['reply']);
			
			$this->db->update($this->_table,$data);
				
			$this->db->flush_cache();
		}
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'public-list'){
			$prioty_arr    = array('Rất cao', 'Cao', 'Trung bình', 'Thấp', 'Rất thấp');
			
			$taskTable = $this->model_load_model('MTasks');

			$item = $taskTable->getItem(array('id'=>$arrParam['task_id']), array('task'=>'public-info', 'brand'=>'detail'));
	
			$task_ids = $taskTable->getIds(array('lft'=>$item['lft'], 'rgt'=>$item['rgt'], 'project_id'=>$item['project_id']));

			$ssFilter  = $arrParam['ssFilter'];

			$paginator = $arrParam['paginator'];
			$this->db->select("DATE_FORMAT(p.modified, '%d/%m/%Y %H:%i:%s') as modified", FALSE);
			$this->db -> select('p.id, p.created_by, p.trangthai, t.name as task_name,p.progress, p.pheduyet, p.note, u.user_name, p.prioty')
					  -> from($this->_table . ' AS p')
					  -> join('tasks as t', 't.id = p.task_id', 'left')
					  -> join('users AS u', 'u.id = p.modified_by', 'left')
					  -> where('p.task_id IN ('.implode(', ', $task_ids).')')
					  -> order_by('p.modified', 'DESC');
	
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}

			$query = $this->db->get();

			$result = $query->result_array();
			$this->db->flush_cache();
	
			if(!empty($result)) {
				// quyền
				$is_implement = $is_create_task_parent = $is_progress = array();
				if(!empty($item['is_implement'])) {
					foreach($item['is_implement'] as $val)
						$is_implement[] = $val['user_id'];
						
					$is_implement = array_unique($is_implement);
				}
					
				if(!empty($item['is_create_task'])) {
					foreach($item['is_create_task'] as $key => $val){
						$is_create_task[] = $val['user_id'];
						$keyArr = explode('-', $key);
						if($keyArr[0] != $item['id'])
							$is_create_task_parent[] = $val['user_id'];
					}
						
					$is_create_task_parent = array_unique($is_create_task_parent);
				}
					
				if(!empty($item['is_progress'])) {
					foreach($item['is_progress'] as $key => $val){
						$is_progress[] = $val['user_id'];
					}
						
					$is_progress 		= array_unique($is_progress);
				}
				// end quyền

				// check toàn quyền trên nhánh
				$flag = false;
				
				if(in_array('update_project', $this->_task_permission))
					$flag = true;
				elseif(in_array($this->_id_admin, $is_implement) && in_array('update_brand_task', $this->_task_permission))
					$flag = true;
				elseif(in_array($this->_id_admin, $is_create_task_parent) && $this->_id_admin == $val['created_by']) 
					$flag = true;
				
				// check xem có phải là level cuối ko
				
				$trangthai_arr = array('-1'=>'_','0'=>'Chưa thực hiện', '1'=>'Đang thực hiện', '2'=>'Hoàn thành', '3'=>'Đóng/dừng', '4'=>'Không thực hiện');
				foreach($result as &$val) {
					$val['user_name'] = ($val['user_name'] == NULL) ? '_' : $val['user_name'];
					$val['trangthai'] = $trangthai_arr[$val['trangthai']];
					$val['progress'] = $val['progress'] * 100 . '%';
					if($val['pheduyet'] == 2)
						$val['pheduyet'] = '<i class="fa fa-clock-o" title="Pending"></i>';
					elseif($val['pheduyet'] == 1)
						$val['pheduyet'] = '<i class="fa fa-check" title="Phê duyệt"></i>';
					elseif($val['pheduyet'] == 0)
						$val['pheduyet'] = '<i class="fa fa-ban" title="Không phê duyệt"></i>';
					else
						$val['pheduyet'] = '_';
					
					$val['note'] = nl2br($val['note']);
					if(!empty($val['reply'])) {
						$reply = '<strong>'.$val['user_pheduyet_name'].'</strong>'.nl2br($val['reply']);
						$val['note'] = $val['note'] . '<br />' . $reply;
					}
					
					$val['prioty'] = $prioty_arr[$val['prioty']];
					
					if($flag == true) { // có toàn quyền trên nhánh
						$val['per_xuly'] = 0;
						if($val['pheduyet'] == 0){
							$val['per_xuly'] = 1;
						}

					}else {
						$val['per_xuly']  = 0;

						if(in_array($this->_id_admin, $is_progress) && $val['pheduyet'] == 0)
							$val['per_xuly'] = 1;
					}
				}
			}
				
		}
		return $result;
	}
	
	function do_progress($level) {
		$taskTable = $this->model_load_model('MTasks');
		if(!empty($level)) {
			$last_level = end($level);
			array_pop($level );

			$parent_id = $last_level[0]['parent'];
			if($parent_id > 0) {
				$new_parent_progress = 0;
				foreach($last_level as $task) {
					$new_parent_progress = $new_parent_progress + $task['percent'] * $task['progress'];
				}
					
				$new_parent_progress = $new_parent_progress * 100;
				//update parent
				$taskTable->saveItem(array('id'=>$parent_id, 'progress'=>$new_parent_progress), array('task'=>'update-progress'));
	
				// cập nhật lại parent trong level
				if(!empty($level)) {
					foreach($level as &$l) {
						foreach($l as &$task) {
							if($parent_id == $task[id]) { 
								$task['progress'] = $new_parent_progress / 100;
								$parent_item = $task;
							}
						}
					}
				}
				
				// progress data
				$progressTmp = array(
						'task_id' 			 => $parent_item['id'],
						'trangthai' 		 => $parent_item['trangthai'],
						'prioty' 			 => $parent_item['prioty'],
						'progress' 			 => $parent_item['progress'],
						'pheduyet'			 => 3,
						'note' 				 => '',
						'reply' 			 => '',
						'created'			 => @date("Y-m-d H:i:s"),
						'created_by'		 => 0,
						'modified'			 => @date("Y-m-d H:i:s"),
						'modified_by'		 => 0,
						'user_pheduyet'		 => 0,
						'user_pheduyet_name' => '',	
						);
				
				$this->_items[] = $progressTmp;

				$this->do_progress($level);	
			}
		}
	}
	
	function handling($arrParam) {
		$progress_item = $this->getItem(array('id'=>$arrParam['id']), array('task'=>'public-info'));
		
		$taskTable = $this->model_load_model('MTasks');
		$task = $taskTable->getItem(array('id'=>$progress_item['task_id']), array('task'=>'public-info'));
		$task_items = $taskTable->getItems(array('project_id'=>$task['project_id']), array('task'=>'by-project'));
		
		foreach($task_items as $task_id => $task) {
			if($task_id == $progress_item['task_id']){ 
				$task['progress']  = $progress_item['progress'] * 100;
				$task['prioty']    = $progress_item['prioty'];
				$task['trangthai'] = $progress_item['trangthai'];
				
				$taskTable->saveItem($task, array('task'=>'update-tiendo'));
				
				$task['progress']  = $progress_item['progress'];
			}
			$level[$task['level']][] = $task;
		}

		$this->do_progress($level);
		
		// cập nhật progress
		$this->db->insert_batch($this->_table, $this->_items);
	}
	
	function model_load_model($model_name)
	{
		$CI =& get_instance();
		$CI->load->model($model_name);
		return $CI->$model_name;
	}
	
}