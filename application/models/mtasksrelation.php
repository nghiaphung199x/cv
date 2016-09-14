<?php 
class MTasksRelation extends CI_Model{
   
	protected $_table = 'task_user_relations';
	public function __construct(){
		parent::__construct();
	}
	
	public function getItems($arrParams = null, $options = null) {
		if($options['task'] == 'by-task') {
			$this->db->select('r.user_id, r.is_xem, r.is_implement, r.is_create_task, r.is_pheduyet')
					  ->from($this->_table . ' as r')
					  ->where('r.task_id', $arrParams['task_id']);
			
			$query = $this->db->get();
			$result = $query->result_array();
		}elseif($options['task'] == 'by-multi-task') {
			$this->db->select('r.user_id, r.is_xem, r.is_implement, r.is_create_task, r.is_pheduyet')
				     ->from($this->_table . ' as r')
				     ->where('r.task_id IN ('.implode(', ', $arrParams['task_ids']).')');
				
			$query = $this->db->get();
			$result = $query->result_array();
		}
		
		return $result;
	}
	
	public function deleteItem($arrParam = null, $options = null) {
		if($options['task'] == 'delete-multi'){
			$this->db->where('task_id IN (' . implode(',', $arrParam['cid']) . ')');
			$this->db->delete($this->_table);
			$this->db->flush_cache();
		}
	}
}