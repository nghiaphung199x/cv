<?php 
class MTaskProgress extends CI_Model{
   
	protected $_table    = 'task_progress';
	protected $_items    = null;
	protected $_task_ids = null;
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
					 ->where('p.id',$arrParam['id'])
					 ->where('p.pheduyet IN (1, 3)');
			
			$query = $this->db->get();
			$result = $query->row_array();

			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'public-list'){
			$ssFilter  = $arrParam['ssFilter'];
			
			$taskTable = $this->model_load_model('MTasks');
			
			$item = $taskTable->getItem(array('id'=>$arrParam['task_id']), array('task'=>'public-info', 'brand'=>'detail'));
			
			$task_ids = $taskTable->getIds(array('lft'=>$item['lft'], 'rgt'=>$item['rgt'], 'project_id'=>$item['project_id']));
				
			$this->_task_ids = $task_ids;

			$this->db -> select('COUNT(p.id) AS totalItem')
					  -> from($this->_table . ' AS p')
					  -> where('p.task_id IN ('.implode(', ', $this->_task_ids).')');
			
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
			$data['user_pheduyet']		= 0;
			$data['date_pheduyet']		= $arrParam['date_pheduyet'];
			$data['user_pheduyet_name'] = '';
		
			$data['key']				= $arrParam['key'];
			
			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
				
			$this->db->flush_cache();
			
			return $lastId;
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

			$ssFilter  = $arrParam['ssFilter'];

			$paginator = $arrParam['paginator'];
			$this->db->select("DATE_FORMAT(p.date_pheduyet, '%d/%m/%Y %H:%i:%s') as created", FALSE);
			$this->db -> select('p.id, p.created_by, p.trangthai, t.name as task_name,p.progress, p.pheduyet, p.key, u.user_name, p.prioty')
					  -> from($this->_table . ' AS p')
					  -> join('tasks as t', 't.id = p.task_id', 'left')
					  -> join('users AS u', 'u.id = p.created_by', 'left')
					  -> where('p.task_id IN ('.implode(', ', $this->_task_ids).')')
					  -> where('p.pheduyet IN (1, 3)')
					  -> order_by('p.date_pheduyet', 'DESC');
	
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}

			$query = $this->db->get();

			$result = $query->result_array();
			$this->db->flush_cache();
	
			if(!empty($result)) {
				$trangthai_arr = array('-1'=>'_','0'=>'Chưa thực hiện', '1'=>'Đang thực hiện', '2'=>'Hoàn thành', '3'=>'Đóng/dừng', '4'=>'Không thực hiện');
				foreach($result as &$val) {
					$val['trangthai'] = $trangthai_arr[$val['trangthai']];
					$val['progress'] = $val['progress'] * 100 . '%';
	
					$val['prioty'] = $prioty_arr[$val['prioty']];
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
						'user_pheduyet'		 => 0,
						'user_pheduyet_name' => '',	
						'date_pheduyet'	     => @date("Y-m-d H:i:s"),
						'key' 			 	 => '',
						);
				
				$this->_items[] = $progressTmp;

				$this->do_progress($level);	
			}
		}
	}
	
	function handling($arrParam = null, $options = null) {
		if($options == null)
			$progress_item = $this->getItem(array('id'=>$arrParam['id']), array('task'=>'public-info'));
		elseif($options['task'] == 'progress')
			$progress_item = $arrParam;
		
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
		if($options['task'] == 'progress')
			$this->db->insert_batch($this->_table, $this->_items);
	}
	
	function model_load_model($model_name)
	{
		$CI =& get_instance();
		$CI->load->model($model_name);
		return $CI->$model_name;
	}
	
}