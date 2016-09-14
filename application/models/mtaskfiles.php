<?php 
class MTaskFiles extends CI_Model{
   
	protected $_table = 'task_files';
	public function __construct(){
		parent::__construct();
	}
	
	
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'public-list'){
			$ssFilter  = $arrParam['ssFilter'];

			$this->db -> select('COUNT(f.id) AS totalItem')
					  -> from($this->_table . ' AS f')
					  -> where('f.task_id', $arrParam['task_id']);
			
			$query = $this->db->get();
			
			$result = $query->row()->totalItem;
			
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'add'){
			$data['task_id'] 				= 				$arrParam['task_id'];
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['file_name']				= 				stripslashes($arrParam['file_name']);
			$data['size'] 					= 				$arrParam['size'];
			$data['excerpt']				= 				stripslashes($arrParam['excerpt']);

			$data['created']				= 				@date("Y-m-d H:i:s");
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['created_by']     		=				$arrParam['adminInfo']['id'];
			$data['modified_by']     		=				$arrParam['adminInfo']['id'];

			$this->db->insert($this->_table,$data);
			$lastId = $this->db->insert_id();
				
			$this->db->flush_cache();
			
		}elseif($options['task'] == 'edit'){
			$this->db->where("id",$arrParam['id']);
			
			$data['task_id'] 				= 				$arrParam['task_id'];
			$data['name']					= 				stripslashes($arrParam['name']);
			$data['file_name']				= 				stripslashes($arrParam['file_name']);
			$data['size'] 					= 				$arrParam['size'];
			$data['excerpt']				= 				stripslashes($arrParam['excerpt']);
			
			$data['modified']				= 				@date("Y-m-d H:i:s");
			$data['modified_by']     		=				$arrParam['adminInfo']['id'];
			
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
			$this->db->select("DATE_FORMAT(f.created, '%d/%m/%Y %H:%i:%s') as created", FALSE);
			$this->db->select("DATE_FORMAT(f.modified, '%d/%m/%Y %H:%i:%s') as modified", FALSE);
			$this->db -> select('f.id,f.name, f.file_name, f.size, f.created_by, f.modified_by')
					  -> from($this->_table . ' AS f')
					  -> where('f.task_id', $arrParam['task_id']);
			
	
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
				
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}

			$query = $this->db->get();

			$result = $query->result_array();
			$this->db->flush_cache();
		
			if(!empty($result)) {
				$upload_dir = base_url() . 'public/files/document/';
				$userTable = $this->model_load_model('MUser');
				foreach($result as $val) {
					$user_ids[] = $val['created_by'];
					$user_ids[] = $val['modified_by'];
				}
				
				$user_ids = array_unique($user_ids);
				$user_infos = $userTable->getItems(array('user_ids'=>$user_ids));
				
				foreach($result as &$val) {
					$val['created_name']  = $user_infos[$val['created_by']]['user_name'];
					$val['modified_name'] = $user_infos[$val['modified_by']]['user_name'];
					$val['link']		  = $upload_dir . $val['file_name'];
				}
					
			}
				
		}
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'public-info'){
			$this->db->select('f.*')
					 ->from($this->_table . ' as f')
					 ->where('f.id',$arrParam['id']);
	
			$query = $this->db->get();
			$result = $query->row_array();
			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function getItems($arrParam = null, $options = null){
		if($options['task'] == 'public-info'){
			$this->db->select('f.*')
					->from($this->_table . ' as f')
					->where('f.id IN ('.implode(', ', $arrParam['cid']).')');
		
			$query = $this->db->get();
			$result = $query->result_array();
			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'delete-multi'){
			$items = $this->getItems($arrParam, array('task'=>'public-info'));
			if(!empty($items)) {
				foreach($items as $val) {
					$ids[] 		  = $val['id'];
					$file_names[] = $val['file_name'];
				}		
				
				$this->db->where('id IN (' . implode(', ', $ids) . ')');
				$this->db->delete($this->_table);
				
				$this->db->flush_cache();
				
				// xóa file
				$upload_dir = FILE_PATH . '/document/';
				foreach($file_names as $file_name)
					@unlink($upload_dir . $file_name);
			}
		}
	}
	
	function model_load_model($model_name)
	{
		$CI =& get_instance();
		$CI->load->model($model_name);
		return $CI->$model_name;
	}
	
}