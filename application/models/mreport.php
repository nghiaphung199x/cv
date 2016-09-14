<?php 
class MReport extends CI_Model{
	protected $_table="articles";
	public function __construct(){
		parent::__construct();
	}
	
	public function statistic($arrParam = null, $options = null) {
		$ssFilter  = $arrParam['ssFilter'];
		$this->db->select('SUM(o.total_all) as sum_total, SUM(o.shipping) as sum_shipping, SUM(o.coupon) as sum_coupon');
		
		if($ssFilter['kieu'] == 'thucthu')
			$this->db->where('o.status = 1');
		elseif($ssFilter['kieu'] == 'tamthu')
		$this->db->where('o.status != 0');
		
		if(!empty($ssFilter['tungay'])) {
			$tungay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['tungay'])));
			$this->db->where('o.created >= \''.$tungay.'\'');
		}
		
		if(!empty($ssFilter['denngay'])) {
			$denngay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['denngay'])));
			$this->db->where('o.created <= \''.$denngay.'\'');
		}
		
		$result =  $this->db->get('order as o')->row_array();

		return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
		if($options['task'] == 'revenue'){
			$ssFilter  = $arrParam['ssFilter'];
			$this->db->select('COUNT(od.id_pcd) as totalItem')
					 ->join('order as o', 'od.id_pcd = o.id','left')
					 ->group_by('od.id_pcd');
			
			if($ssFilter['kieu'] == 'thucthu')
				$this->db->where('o.status = 2');
			elseif($ssFilter['kieu'] == 'tamthu')
				$this->db->where('o.status != 0');
		
			if(!empty($ssFilter['tungay'])) {
				$tungay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['tungay'])));
				$this->db->where('o.created >= \''.$tungay.'\'');
			}
				
			if(!empty($ssFilter['denngay'])) {
				$denngay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['denngay'])));
				$this->db->where('o.created <= \''.$denngay.'\'');
			}
			
			$query = $this->db->get('order_detail as od');
			$result = $query->row()->totalItem;
			$this->db->flush_cache();

		}
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'revenue'){
			$ssFilter  = $arrParam['ssFilter'];
			$this->db->select('od.id_pcd, SUM(od.quantity) as sum_quantity, SUM(od.total) as sum_total, p.name as product_name, pc.name as cat_name, p.id as product_id')
					 ->join('order as o', 'o.id = od.id_order','left')
					 ->join('product_category_detail as pcd', 'od.id_pcd = pcd.id','left')
			         ->join('product as p', 'pcd.id_product = p.id','left')
					 ->join('product_category as pc', 'pcd.id_category = pc.id','left')
					 ->group_by('od.id_pcd');
			
			$paginator = $arrParam['paginator'];
			$page = (empty($arrParam['start'])) ? 1 : $arrParam['start'];
			$this->db->limit($paginator['per_page'],($page - 1)*$paginator['per_page']);
			
			if($ssFilter['type'] == 'thucthu')
				$this->db->where('o.status = 1');
			elseif($ssFilter['type'] == 'tamthu')
				$this->db->where('o.status != 0');
			
			if(!empty($ssFilter['col']) && !empty($ssFilter['order'])){
				$this->db->order_by($ssFilter['col'],$ssFilter['order']);
			}
			
			if(!empty($ssFilter['tungay'])) {
				$tungay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['tungay'])));
				$this->db->where('o.created >= \''.$tungay.'\'');
			}
			
			if(!empty($ssFilter['denngay'])) {
				$denngay = date('Y-d-m', strtotime(str_replace('.', '/', $ssFilter['denngay'])));
				$this->db->where('o.created <= \''.$denngay.'\'');
			}
			
			$result =  $this->db->get('order_detail as od')->result_array();
			$this->db->flush_cache();
		}
		return $result;
	}
}