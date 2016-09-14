<?php 
class MOrderCoupon extends CI_Model{
	protected $_table="order_coupon";
	public function __construct(){
		parent::__construct();
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'add'){
			$this->db->insert_batch($this->_table, $arrParam);
			$this->db->flush_cache();
		}
		return $lastId;
	}
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'by-order-id'){
			$cid = explode(',', $arrParam['cid']);
			if(!empty($cid) && isset($arrParam['cid'])){
				$ids = implode(',', $cid);
				$this->db->where('id_order IN (' . $ids . ')');
				$this->db->delete($this->_table);
				$this->db->flush_cache();
			}
		}
	}
}