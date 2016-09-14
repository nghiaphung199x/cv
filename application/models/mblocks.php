<?php 
class MBlocks extends CI_Model{
	protected $_table="blocks";
	public function __construct(){
		parent::__construct();
	}
	
	public function itemInSelectboxCat($arrParam = null, $options = null) {
		if($options['task'] == 'public-search'){
			$this->db->select('pc.id, pc.name')
					 ->where('pc.level = 1')
					 ->where('pc.status = 1')
					 ->order_by('pc.lft','ASC');

			$resultTmp =  $this->db->get('product_category as pc')->result_array();
			$this->db->flush_cache();
			$result[0] = 'Chọn danh mục';
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val)
					$result[$val['id']] = $val['name'];
			}	
		}
		return $result;
	}
	
	public function listMenu($arrParam = null, $options = null) {
		if($options['task'] == 'menu-list') {
			$this->db->select('*')
					 ->where('status = 1')
					 ->where('type_menu LIKE \'main_menu\'')
					 ->order_by('lft','ASC');
			$resultTmp =  $this->db->get('menu')->result_array();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $val) {
					$params 				= 	  unserialize($val['params']);
					$val['linkMenu']		= 	  getLinkMenu($val['module_options'], $val['alias'], $params);
					$val['params']			= 	  $params;
					$result[$val['id']]     =     $val;
				}
			}
		
		}
		return $result;
	}
	
	public function listProduct($arrParam = null, $options = null) {
		if($options['task'] == 'theme-home') {
			$limit = $arrParam['limit'] - 1;
			$theme_ids = implode(',', $arrParam['theme_ids']);
			$order = $arrParam['order'];
			$sql = 'SELECT  id, name, alias, thumb, id_theme
					FROM    (
		            SELECT  *,
		                    (SELECT COUNT(1) FROM product WHERE id_theme = p.id_theme AND id < p.id) CountLess
		            FROM    product p
        			) sub
					WHERE   sub.CountLess <= '.$limit.'
					AND id_theme in ('.$theme_ids.')
					ORDER BY id_theme '.$order.', id DESC
			';
			
			$query = $this->db->query($sql);
			$resultTmp =  $query->result_array();
			if(!empty($resultTmp)) {
				foreach($resultTmp as $item) {
					$result[$item['id_theme']][] = $item;
				}
			}
		}
		return $result;
	}
	
	public function getProducts($arrParam = null, $options = null) {
		if($options['task'] == 'product-rela') {
			$this->db->select('p.id, p.alias, p.name, pcd.price, p.thumb')
					->join('product_category_detail as pcd', 'p.id = pcd.id_product')
					->where('p.status = 1')
					->where('id_theme', $arrParam['id_theme'])
					->group_by('p.id')
					->order_by('pcd.id','RANDOM')
					->limit(5);
				
			$result =  $this->db->get('product as p')->result_array();

			$this->db->flush_cache();
		}
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-info' || $options['task'] == 'admin-edit'){
			$this->db->select('b.*')
					  ->where('b.id',$arrParam['id']);
	
			$result =  $this->db->get($this->_table . ' as b')->row_array();
			$this->db->flush_cache();
		}elseif($options['task'] == 'public-info') {
			$this->db->select('b.*')
				     ->where('b.id',$arrParam['id'])
					 ->where('b.status = 1');
			
			$result =  $this->db->get($this->_table . ' as b')->row_array();
			$this->db->flush_cache();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		if($options['task'] == 'admin-edit'){
			$this->db->where("id",$arrParam['id']);
				
			$data['params']			= 		serialize($arrParam['pars']);
			$data['content']		= 		$arrParam['content'];
			$data['status']			=       $arrParam['status'];
			
			$this->db->update($this->_table,$data);
			
			$this->db->flush_cache();
			
			$lastId = $arrParam['id'];
		}
		return $lastId;
	}
}