<?php 
class MOrderDetail extends CI_Model{
	protected $_table="order_detail";
	public function __construct(){
		parent::__construct();
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'add'){
			$this->db->insert_batch($this->_table, $arrParam);
			$this->db->flush_cache();
			
		}elseif($options['task'] == 'admin-edit') {
			$this->db->where("id_pcd",$arrParam['id_pcd'])
					 ->where('id_order', $arrParam['id_order']);
			
			$data['price'] 			= 		str_replace(".","",$arrParam['price']);
			$data['quantity'] 		= 		$arrParam['quantity'];
			
			$this->db->update($this->_table,$data);
			$this->db->flush_cache();
			$lastId = $arrParam['id'];
			
			$this->load->model('MOrder');
			$order_details = $this->getItems(array('id_order'=>$arrParam['id_order']), array('task'=>'by-order'));
			$order = $this->MOrder->getItem(array('id'=>$arrParam['id_order']), array('task'=>'admin-edit'));
			if(!empty($order_details) && !empty($order)) {
				$data = array();
				$total_all = 0;
				$coupon = $order['coupon'];
				$shipping = $order['shipping'];
				foreach($order_details as $val)
					$total_all = $total_all + $val['price']*$val['quantity'];
				
				$total_all = $total_all + $shipping - $coupon;

				$this->db->where("id",$arrParam['id_order']);
				$data['total_all'] 	= 	$total_all;
				
				$this->db->update('order',$data);
				$this->db->flush_cache();
			}
		}elseif($options['task'] == 'admin-add') {
			$this->load->model('MOrder');
			$order_details = $this->getItems(array('id_order'=>$arrParam['id_order']), array('task'=>'by-order'));
			if(!empty($order_details)) {
				$flag = false;
				foreach($order_details as $val) {
					if($arrParam['id_pcd'] == $val['id_pcd']) {
						$quantity = $val['quantity'];
						$flag = true;
						break;
					}
				}
				if($flag == true) {
					$this->db->where("id_pcd",$arrParam['id_pcd'])
							 ->where('id_order', $arrParam['id_order']);
					
					$data['quantity'] 		= 		$quantity+$arrParam['quantity'];
						
					$this->db->update($this->_table,$data);
					$this->db->flush_cache();
				}else {
					$data['id_order']		= 		$arrParam['id_order'];
					$data['id_pcd']			= 		$arrParam['id_pcd'];
					$data['quantity']		= 		$arrParam['quantity'];
					$data['price'] 			= 		str_replace(".","",$arrParam['price']);
					
					$this->db->insert($this->_table,$data);
					$this->db->flush_cache();
				}
				
				$order_details = $this->getItems(array('id_order'=>$arrParam['id_order']), array('task'=>'by-order'));
				$order = $this->MOrder->getItem(array('id'=>$arrParam['id_order']), array('task'=>'admin-edit'));
				if(!empty($order) && !empty($order_details)) {
					$data = array();
					$total_all = 0;
					$coupon = $order['coupon'];
					$shipping = $order['shipping'];
					foreach($order_details as $val)
						$total_all = $total_all + $val['price']*$val['quantity'];
				
					$total_all = $total_all + $shipping - $coupon;
				
					$this->db->where("id",$arrParam['id_order']);
					$data['total_all'] 	= 	$total_all;
				
					$this->db->update('order',$data);
					$this->db->flush_cache();
				}	
			}
		}
		return $lastId;
	}
	
	public function getItems($arrParam = null, $options = null){
		if($options['task'] == 'by-order') {
			$this->db->select()
					 ->where('id_order',$arrParam['id_order']);
			
			$result =  $this->db->get($this->_table)->result_array();
			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-edit') {
			$this->db->select('od.id_order, od.id_pcd, p.name as product_name, pc.name as cat_name, od.price, od.quantity')
					 ->join('product_category_detail as pcd','pcd.id = od.id_pcd','left')
					 ->join('product as p', 'p.id=pcd.id_product','left')
					 ->join('product_category as pc','pcd.id_category = pc.id','left')
					 ->where('od.id_pcd',$arrParam['id_pcd'])
				     ->where('od.id_order',$arrParam['id_order']);
			
			$result =  $this->db->get($this->_table . ' as od')->row_array();
			$this->db->flush_cache();
	
		}
		return $result;
	}
	
	public function deleteItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-delete'){
			$this->db->where('id_order',$arrParam['id_order'])
					 ->where('id_pcd', $arrParam['id_pcd']);
			$this->db->delete($this->_table);
			$this->db->flush_cache();
			
			$this->load->model('MOrder');
			$order_details = $this->getItems(array('id_order'=>$arrParam['id_order']), array('task'=>'by-order'));
			$order = $this->MOrder->getItem(array('id'=>$arrParam['id_order']), array('task'=>'admin-edit'));
			if(!empty($order_details) && !empty($order)) {
				$data = array();
				$total_all = 0;
				$coupon = $order['coupon'];
				$shipping = $order['shipping'];
				foreach($order_details as $val)
					$total_all = $total_all + $val['price']*$val['quantity'];
			
				$total_all = $total_all + $shipping - $coupon;
			
				$this->db->where("id",$arrParam['id_order']);
				$data['total_all'] 	= 	$total_all;
			
				$this->db->update('order',$data);
				$this->db->flush_cache();
			}
		}elseif($options['task'] == 'by-order-id'){
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