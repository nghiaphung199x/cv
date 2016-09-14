<?php
class MLanguages extends CI_Model{
	protected $_table="languages";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
		if($options == null){
			$this->db->select('lang_code,title');
			$this->db->order_by('id','desc');
			$resultTmp = $this->db->get($this->_table)->result_array();	
			$this->db->flush_cache();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $value) {
					$result[$value['lang_code']] = $value['title'];
				
				}
			}else
				$result = array();
		}
		
		return $result;
	}
	
}	