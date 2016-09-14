<?php 
class MMember extends CI_Model{
	protected $_table="users";
	public function __construct(){
		parent::__construct();
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'delete'){
			$cid  = $arrParam['cid'];
			$this->db->where('id in ('.$cid.')');
			$result = $this->db->get($this->_table)->result_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
		$ssFilter  = $arrParam['ssFilter'];
		$this->db->select('COUNT(u.id) AS totalItem')
				 ->where('u.phanloai = 1');
		
		if(!empty($ssFilter['keywords'])){
			$keywords = trim($ssFilter['keywords']);
			$this->db->where("(u.user_name LIKE '%" . $ssFilter['keywords'] . "%') OR (u.name LIKE '%" . $ssFilter['keywords'] . "%') OR (u.email LIKE '%" . $ssFilter['keywords'] . "%')");
		}

		$query = $this->db->get($this->_table . ' as u');
		$result = $query->row()->totalItem;
		$this->db->flush_cache();
		
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		$paginator = $arrParam['paginator'];
		$ssFilter  = $arrParam['ssFilter'];

		$user_id = $arrParam['adminInfo']['id'];
	
		if($options['task'] == 'admin-list'){
			$this->db->select('user_name, name, status, email, phone, id');
			$this->db->select("DATE_FORMAT(u.created, '%d/%m/%Y %H:%i:%s') AS created", FALSE)
					  ->where('u.id != ' . (int)$user_id)
					  ->where('u.phanloai = 1');
			
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'], $ssFilter['order']);
			}
			
			if(!empty($ssFilter['keywords'])){
				$keywords = trim($ssFilter['keywords']);
				$this->db->where("(u.user_name LIKE '%" . $ssFilter['keywords'] . "%') OR (u.name LIKE '%" . $ssFilter['keywords'] . "%') OR (u.email LIKE '%" . $ssFilter['keywords'] . "%')");
			}

			$result =  $this->db->get($this->_table . ' as u')->result_array();
			$this->db->flush_cache();
		}
	
		return $result;
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
	
}