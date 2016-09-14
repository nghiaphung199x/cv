<?php 
class MAjax extends CI_Model{
	protected $_table="articles";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
		if($options['task'] == 'get-cities'){
			$this->db->select('ci.id, ci.name')
					 ->where('ci.country_id', $arrParam['id_country'])
					 ->order_by('ci.order','ASC');
			
			$resultTmp =  $this->db->get('cities as ci')->result_array();
			$this->db->flush_cache();
			$result[0] = 'Chọn tỉnh/thành phố';
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val) {
					$result[$val['id']] = $val['name'];
				}
			}
		}
		return $result;
	}
	
	public function savePermission($arrParam = null, $options = null){
		if($options['task'] == 'permission-add'){
			$data['name'] = stripslashes($arrParam['name']);
			$data['module'] = stripslashes($arrParam['p_module']);
			$data['controller'] = stripslashes($arrParam['p_controller']);
			$data['action'] = stripslashes($arrParam['p_action']);
			
			$this->db->insert('privileges',$data);
			$this->db->flush_cache();
		}
	}
}