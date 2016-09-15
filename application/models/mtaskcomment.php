<?php 
class MTaskComment extends CI_Model{
   
	protected $_table 	 = 'task_comment';
	protected $_id_admin = null;
	public function __construct(){
		parent::__construct();
		
		$this->load->library('MY_System_Info');
		$info 			 = new MY_System_Info();
		$user_info 		 = $info->getMemberInfo();
		
		$this->_id_admin = $user_info['id'];
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'public-list'){
			$ssFilter  = $arrParam['ssFilter'];

			$this->db -> select('COUNT(c.id) AS totalItem')
					  -> from($this->_table . ' AS c')
					  -> where('c.task_id', $arrParam['task_id']);
			
			$query = $this->db->get();

			$result = $query->row()->totalItem;
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'add'){
			$data['user_id'] 				= 				$this->_id_admin;
			$data['task_id'] 				= 				$arrParam['task_id'];
			$data['content']				= 				stripslashes($arrParam['content']);
			$data['created']				= 				@date("Y-m-d H:i:s");
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['modified_by']     		=				$this->_id_admin;

			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();

			$this->db->flush_cache();
			
		}elseif($options['task'] == 'edit'){
			$this->db->where("id",$arrParam['id']);

			$data['content']				= 				stripslashes($arrParam['content']);
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['modified_by']     		=				$this->_id_admin;

			$this->db->update($this->_table,$data);
			
			$this->db->flush_cache();
			
			$lastId = $arrParam['id'];
		}
		
		return $lastId;
	}
	
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'public-list'){
			$ssFilter  = $arrParam['ssFilter'];

			$paginator = $arrParam['paginator'];
			$this->db->select("DATE_FORMAT(c.created, '%d/%m/%Y %H:%i:%s') as created", FALSE);
			$this->db->select("DATE_FORMAT(c.modified, '%d/%m/%Y %H:%i:%s') as modified", FALSE);
			$this->db -> select('c.id, u.user_name as username, c.content, u.user_avatar, u.name')
					  -> from($this->_table . ' AS c')
					  -> join('users AS u', 'c.user_id = u.id', 'left')
					  -> where('c.task_id', $arrParam['task_id'])
					  -> order_by('c.id DESC');
			
	
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			$query = $this->db->get();

			$result = $query->result_array();
			$this->db->flush_cache();

			if(!empty($result)) {
				foreach($result as &$val){
					$val['content'] = nl2br($val['content']);
				}
			}
		}
		return $result;
	}
	
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'delete-multi'){
// 			$items = $this->getItems($arrParam, array('task'=>'public-info'));
// 			if(!empty($items)) {
// 				foreach($items as $val) {
// 					$ids[] 		  = $val['id'];
// 					$file_names[] = $val['file_name'];
// 				}		
				
// 				$this->db->where('id IN (' . implode(', ', $ids) . ')');
// 				$this->db->delete($this->_table);
				
// 				$this->db->flush_cache();
				
// 				// xóa file
// 				$upload_dir = FILE_PATH . '/document/';
// 				foreach($file_names as $file_name)
// 					@unlink($upload_dir . $file_name);
// 			}
		}
	}
	
	function model_load_model($model_name)
	{
		$CI =& get_instance();
		$CI->load->model($model_name);
		return $CI->$model_name;
	}
	
}