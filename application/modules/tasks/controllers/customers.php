<?php
class Customers extends MY_Controller{	//Thong so phan trang
	protected $_paginator = array(
				'per_page' => 10,
				'uri_segment' => 4
			);
	
	public function __construct(){
		parent::__construct();
		
		//Lay thong tin so phan tu tren mot trang
		if(isset($this->_data['arrParam']['limitPage'])){
			$ssFilter['limitPage'] = $this->_data['arrParam']['limitPage'];
			$this->_paginator['per_page'] = $ssFilter['limitPage'];
		
		}elseif(!empty($ssFilter['limitPage'])){
			$this->_paginator['per_page'] = $ssFilter['limitPage'];
		}
		
		$this->_data['arrParam']['paginator'] = $this->_paginator;

		$this->_data['path']="public/index";
		$config_site = $this->_data['siteConfig'];
		$this->_data['imgUrl'] = $this->_data['siteDir'] . '/public/public/images';
		
	}

	public function danhsach() {
		$post = $this->input->post();
		if(!empty($post)) {
			$this->load->model('MCustomers');
			$result = $this->MCustomers->listItem($post);
			
			echo json_encode($result);
		}
	}
	

}