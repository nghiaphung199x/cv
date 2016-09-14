<?php 
class MUser extends CI_Model{
	protected $_table="users";
	public function __construct(){
		parent::__construct();
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options == null) {
			$ssFilter  = $arrParam['ssFilter'];
			$user_id = $arrParam['adminInfo']['id'];
			$this->db->select('COUNT(u.id) as total')
					 ->where('u.id != ' . $user_id)
			 		 ->where('u.phanloai = 0');
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				$this->db->where('u.user_name LIKE \'%' . $keywords . '%\'');
			}
			
			if($ssFilter['group_id']>0){
				$this->db->where('u.group_id', $ssFilter['group_id']);
			}
			
			$query = $this->db->get($this->_table . ' as u');
			$result = $query->row()->total;
			
			$this->db->flush_cache();
		}elseif($options['task'] == 'all') {
			$this->db->select('COUNT(u.id) as total');
			$query = $this->db->get($this->_table . ' as u');
			$result = $query->row()->total;
				
			$this->db->flush_cache();
		}

		return $result;
	}
	
	public function getItems($arrParam = null, $options = null){
		if($options == null) {
			$this->db->select("id as user_id, user_name")
					 ->from($this->_table)
					 ->where('id IN ('.implode(',', $arrParam['user_ids']).')');
			
			$query = $this->db->get();
			
			$resultTmp = $query->result_array();
			$this->db->flush_cache();
			$result = array();
			
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val) {
					$result[$val['user_id']] = $val;
				}
			}
		}
		
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$paginator = $arrParam['paginator'];
		$ssFilter  = $arrParam['ssFilter'];
		
		$user_id = $arrParam['adminInfo']['id'];
		
		if($options == null) {
			$this->db->select("id, user_name as name")
					->from($this->_table)
					->order_by("id",'DESC');
			 
			if(!empty($arrParam['keywords'])){
				$keywords = trim($arrParam['keywords']);
				$keywordsArr = explode(' ', $keywords);
				foreach($keywordsArr as $keyword) {
					$where[] = "user_name LIKE '%$keyword%' OR name LIKE '%$keyword%'";
				}
			
				$where = implode(' OR ', $where);
				$this->db->where($where);
			}
			
			$this->db->limit(8);
			
			$query = $this->db->get();

			$result = $query->result_array();
			$this->db->flush_cache();
		}elseif($options['task'] == 'admin-list'){
			$this->db->select('u.user_name, u.status, u.email, u.id, g.group_name');
			$this->db->select("DATE_FORMAT(u.created, '%d/%m/%Y') AS created", FALSE)
				     ->join("user_group AS g",'g.id = u.group_id', 'left')
					 ->where('u.id != ' . $user_id)
					 ->where('u.phanloai = 0');

			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'], $ssFilter['order']);
			}
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				$this->db->where('u.user_name LIKE \'%' . $keywords . '%\'');
			}
			
			if($ssFilter['group_id']>0){
				$this->db->where('u.group_id', $ssFilter['group_id']);
			}
			
			$result =  $this->db->get($this->_table . ' as u')->result_array();
			
			$this->db->flush_cache();
		}

		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit' || $options['task'] == 'public'){
			$this->db->select('*, g.group_name');
			$this->db->select("DATE_FORMAT(u.birthday,'%d/%m/%Y') AS birthday", FALSE)
					 ->join("user_group AS g",'u.group_id = g.id', 'left')
					 ->where('u.id', (int)$arrParam['id']);
				
			$result = $this->db->get($this->_table . ' as u')->row_array();
			
			$this->db->flush_cache();
		}elseif($options['task'] == 'delete'){
			$cid  = $arrParam['cid'];
			$this->db->where('id in ('.$cid.')');
			$result = $this->db->get($this->_table)->result_array();
			
			$this->db->flush_cache();
		}
		
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-add') {
			$birthday 			= '0000-00-00';
			if(!empty($arrParam['birthday']))
				$birthday = date('Y-d-m', strtotime(str_replace('.', '/', $arrParam['birthday'])));
			
			$data['user_name'] 			= 		stripslashes($arrParam['user_name']);
			$data['alias'] 				= 		stripslashes($arrParam['alias']);
			$data['password'] 			= 		md5 ($arrParam['password'] . 'namnv2729');
			$data['email'] 				= 		$arrParam['email'];
			$data['group_id'] 			= 		$arrParam['group_id'];
			$data['name'] 				=		$arrParam['name'];
			$data['birthday'] 			= 		$birthday;
			$data['status'] 			= 		$arrParam['status'];
			$data['sign'] 				= 		stripslashes($arrParam['sign']);
			$data['created'] 			=		@date("Y-m-d H:i:s");
			$data['created_ip'] 		= 		$_SERVER['REMOTE_ADDR'];
			$data['user_avatar'] 		= 		$arrParam['user_avatar'];
			
			$this->db->insert($this->_table,$data);
			
			$this->db->flush_cache();
			
		}elseif($options['task'] == 'admin-edit'){
			$birthday 			= '0000-00-00';
			if(!empty($arrParam['birthday'])) 
				$birthday = date('Y-d-m', strtotime(str_replace('.', '/', $arrParam['birthday'])));
			
			$this->db->where("id",$arrParam['id']);
			$data['user_name'] = stripslashes($arrParam['user_name']);
			$data['alias'] = stripslashes($arrParam['alias']);
			if(!empty($arrParam['password']))
				$data['password'] 	= md5 ($arrParam['password'].'namnv2729');
			
			$data['email'] 		= 	$arrParam['email'];
			$data['group_id'] 	= 	$arrParam['group_id'];
			$data['name'] 		= 	$arrParam['name'];
			$data['birthday']	= 	$birthday;
			$data['status'] 	=	$arrParam['status'];
			$data['sign'] 	    = 	stripslashes($arrParam['sign']);
			$data['visited_date'] = 	@date("Y-m-d H:i:s");
			$data['visited_ip']   = 	$_SERVER['REMOTE_ADDR'];
			$data['user_avatar']  = 	$arrParam['user_avatar'];

			$this->db->update($this->_table,$data);
			
			$this->db->flush_cache();
	
		}elseif($options['task'] == 'admin-password'){
			$this->db->where("id",$arrParam['id']);
			$data['password'] = md5 ($arrParam['password_new'] . 'namnv2729');
			
			$this->db->update($this->_table,$data);
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
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete-muti'){
			$cid = explode(',', $arrParam['cid']);
			if(!empty($cid) && isset($arrParam['cid'])){
				$rows = $this->getItem($arrParam, array('task'=>'delete'));
				if(count($rows)>0) {
					foreach($rows as $val) {
						$upload_dir = FILE_PATH . '/users';
						@unlink($upload_dir . '/orignal/' . $val['user_avatar']);
						@unlink($upload_dir . '/img100x100/' . $val['user_avatar']);
						@unlink($upload_dir . '/img450x450/' . $val['user_avatar']);
					}
				}

				$this->db->where('id in ('.$arrParam['cid'].')');
				$this->db->delete($this->_table);
				
				$this->db->flush_cache();
			}

		}
	}
	
	public function checklogin($user_name, $password) {
		$hash_password = md5 ($password . 'namnv2729' );
		$this->db->select('id, user_name')
				 ->where('user_name LIKE \''.$user_name.'\'')
				 ->where('password LIKE \''.$hash_password.'\'');
				 
	   $result =  $this->db->get($this->_table)->row_array();
	   
	   return $result;
	}
}