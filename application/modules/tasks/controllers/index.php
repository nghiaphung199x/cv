<?php
class Index extends MY_Controller{	//Thong so phan trang
	protected $_paginator = array(
				'per_page' => 10,
				'uri_segment' => 4
			);
	
	public function __construct(){
		parent::__construct();

		$this->_data['arrParam']['paginator'] = $this->_paginator;

		$this->_data['path']="public/index";
		$config_site = $this->_data['siteConfig'];
		$this->_data['imgUrl'] = $this->_data['siteDir'] . '/public/public/images';
	}
	
	public function index(){
		$this->load->library('MY_System_Info');
		$info 		= new MY_System_Info();
		$this->_data['user_info'] = $user_info = $info->getMemberInfo();

		$this->load->view('index/index_view',$this->_data);
	}
	
	public function danhsach() {
		$this->load->model('MTasks');
		$ketqua = $this->MTasks->listItem();

		$result = array('ketqua'=>$ketqua['ketqua'], 'deny'=>$ketqua['deny'], 'drag_task'=>$ketqua['drag_task'], 'links'=>array());
		if(!empty($ketqua['ketqua'])) {
			$this->load->model('MTasksLinks');
			$arrParams['task_ids'] = array_keys($ketqua['ketqua']);
			$links = $this->MTasksLinks->listItem($arrParams, array('task'=>'by-source'));
			$result['links'] = $links;
		}

		echo json_encode($result);
	}

	public function editcongviec() {
		$post = $this->input->post();
		$get  = $this->input->get();
		$this->load->library('MY_System_Info');
		$info 		= new MY_System_Info();
		$this->_data['user_info'] = $user_info = $info->getMemberInfo();

		if(empty($user_info)) {
			$response = array('flag'=>'error', 'message'=>'Bạn cần phải đăng nhập để thực hiện tác vụ.');
			echo json_encode($response);
		}else {
			//quyền chung của user
			$task_permission = array();
			if(!empty($user_info['task_permission'])) {
				$task_permission = $user_info['task_permission'];
				$task_permission = explode(',', $task_permission);
			}
			
			$this->load->model('MTasks');
			$this->load->model('MTasksRelation');
			
			if(!empty($post)) {
				$arrParam = $post;
				
				$this->load->library("form_validation");
				$this->form_validation->set_rules('name', 'Tiêu đề', 'required|max_length[255]');
				$this->form_validation->set_rules('date_start', 'Bắt đầu', 'required');
				$this->form_validation->set_rules('date_end', 'Kết thúc', 'required');
				
				if($this->form_validation->run($this) == FALSE){
					$errors = $this->form_validation->error_array();
					$flagError = true;
				}else {
					// kiểm tra time
					$date_start = str_replace('/', '-', $arrParam['date_start']);
					$arrParam['date_start'] = date('Y-m-d', strtotime($date_start));
					
					$date_end = str_replace('/', '-', $arrParam['date_end']);
					$arrParam['date_end'] = date('Y-m-d', strtotime($date_end));
	
					$datediff = strtotime($arrParam['date_end']) - strtotime($arrParam['date_start']);
					$arrParam['duration'] = floor($datediff/(60*60*24)) + 1;
					if($arrParam['duration'] < 0) {
						$flagError = true;
						$errors['date'] = 'Ngày kết thúc phải sau ngày bắt đầu.';
					}
				}


				if($flagError == false) {
					$this->MTasks->saveItem($arrParam, array('task'=>'edit'));
					$respon = array('flag'=>'true');
				}else {
					$respon = array('flag'=>'false', 'message'=>current($errors));
				}

				echo json_encode($respon);
			}else {
				$item = $this->MTasks->getItem(array('id'=>$get['id']), array('task'=>'public-info', 'brand'=>'detail'));
				$is_xem 	  = $is_implement = $is_create_task = $is_pheduyet = $is_progress = array();
				$is_create_task_parent = $is_pheduyet_parent = $is_progress_parent = array();
				if(!empty($item['is_xem'])) {
					foreach($item['is_xem'] as $val)
						$is_xem[] = $val['user_id'];
					
					$is_xem = array_unique($is_xem);
				}
				
				if(!empty($item['is_implement'])) {
					foreach($item['is_implement'] as $val)
						$is_implement[] = $val['user_id'];
					
					$is_implement = array_unique($is_implement);
				}
				
				if(!empty($item['is_create_task'])) {
					foreach($item['is_create_task'] as $key => $val){
						$is_create_task[] = $val['user_id'];
						$keyArr = explode('-', $key);
						if($keyArr[0] != $get['id'])
							$is_create_task_parent[] = $val['user_id'];
					}
						
					$is_create_task_parent = array_unique($is_create_task_parent);
					$is_create_task 	   = array_unique($is_create_task);
				}
				
				if(!empty($item['is_pheduyet'])) {
					foreach($item['is_pheduyet'] as $key => $val){
						$is_pheduyet[] = $val['user_id'];
						
						$keyArr = explode('-', $key);
						if($keyArr[0] != $get['id'])
							$is_pheduyet_parent[] = $val['user_id'];
					}
					
					$is_pheduyet_parent = array_unique($is_pheduyet_parent);
					$is_pheduyet 		= array_unique($is_pheduyet);
				}
				
				$item['is_pheduyet_parent'] = $is_pheduyet_parent;

				if(!empty($item['is_progress'])) {
					foreach($item['is_progress'] as $key => $val){
						$is_progress[] = $val['user_id'];
						
						$keyArr = explode('-', $key);
						if($keyArr[0] != $get['id'])
							$is_progress_parent[] = $val['user_id'];
					}
					
					$is_progress_parent = array_unique($is_progress_parent);
					$is_progress 		= array_unique($is_progress);
				}
				
				$item['is_pheduyet_parent'] = $is_pheduyet_parent;

				if($item['parent'] > 0){
					$cid 						 = array($item['parent'], $item['project_id']);
					$items 						 = $this->MTasks->getItems(array('cid'=>$cid), array('task'=>'public-info'));
					$this->_data['project_item'] = $items[$item['project_id']];
					$this->_data['parent_item']  = $items[$item['parent']];
					
					$items 		= $this->MTasks->getInfo(array('lft'=>$item['lft'], 'rgt'=>$item['rgt'], 'project_id'=>$item['project_id']), array('task'=>'create-task'));
					$task_ids 	= $items['task_ids'];
					
					$project_relation 	  = $this->MTasksRelation->getItems(array('task_ids'=>$task_ids), array('task'=>'by-multi-task'));
					
					$this->_data['project_relation'] = $project_relation;
				}
	
				$this->_data['item'] = $item;
	
				if($item['parent'] == 0) { // dự án
					if(in_array('update_project', $task_permission)) 
						$this->load->view('index/editform_view',$this->_data);
					elseif(in_array($user_info['id'], $is_implement) && in_array('update_brand_task', $task_permission))
						$this->load->view('index/editform_view',$this->_data);
					elseif(in_array($user_info['id'], $is_implement))
						$this->load->view('index/quickupdate_view',$this->_data);
					elseif(in_array($user_info['id'], $is_xem)) {
						$this->_data['no_comment'] = $this->_data['no_update'] = true;
						$this->load->view('index/detail_view',$this->_data);
					}
								
				}else { // công việc thuộc dự án
					if(in_array('update_all_task', $task_permission))
						$this->load->view('index/editform_view',$this->_data);
					elseif(in_array($user_info['id'], $is_implement) && in_array('update_brand_task', $task_permission))
						$this->load->view('index/editform_view',$this->_data);
					elseif(in_array($user_info['id'], $is_create_task_parent)){
						$this->load->view('index/editform_view',$this->_data);
					}elseif(in_array($user_info['id'], $is_implement))
						$this->load->view('index/quickupdate_view',$this->_data);
					elseif(in_array($user_info['id'], $is_xem) || in_array($user_info['id'], $is_pheduyet_parent)) {
						$this->_data['no_comment'] = $this->_data['no_update'] = true;
						$this->load->view('index/detail_view',$this->_data);
					}
				}

			}
		}
	}
	
	public function quickupdate() {
		$post = $this->input->post();
		$this->load->model('MTasks');
		if(!empty($post)) {
			$arrParam = $post;
			
			$this->load->library("form_validation");
			$this->form_validation->set_rules('date_start', 'Bắt đầu', 'required');
			$this->form_validation->set_rules('date_end', 'Kết thúc', 'required');
			if(isset($post['trangthai'])) {
				$this->form_validation->set_rules('progress', 'Tiến độ', 'required|greater_than[-1]|less_than[101]');
			}
			
			$flagError = false;
			
			if($this->form_validation->run($this) == FALSE){
				$errors = $this->form_validation->error_array();
				$flagError = true;
			}else {
				// kiểm tra time
				$date_start = str_replace('/', '-', $arrParam['date_start']);
				$arrParam['date_start'] = date('Y-m-d', strtotime($date_start));
					
				$date_end = str_replace('/', '-', $arrParam['date_end']);
				$arrParam['date_end'] = date('Y-m-d', strtotime($date_end));
			
				$datediff = strtotime($arrParam['date_end']) - strtotime($arrParam['date_start']);
				$arrParam['duration'] = floor($datediff/(60*60*24)) + 1;
				if($arrParam['duration'] < 0) {
					$flagError = true;
					$errors['date'] = 'Ngày kết thúc phải sau ngày bắt đầu.';
				}
			}

			if($flagError == false) {
				$this->MTasks->saveItem($arrParam, array('task'=>'quick-update'));
				$respon = array('flag'=>'true');
			}else {
				$respon = array('flag'=>'false', 'message'=>current($errors));
			}
			
			echo json_encode($respon);
		}
	}

	public function addcongviec() {
		$post = $this->input->post();
		$get  = $this->input->get();
		$this->load->library('MY_System_Info');
		$info 		= new MY_System_Info();
		$this->_data['user_info'] = $user_info = $info->getMemberInfo();

		$this->load->model('MTasks');
		$this->load->model('MTasksRelation');
		
		if($get['parent'] > 0) {
			$parent_item 	= $this->MTasks->getItem(array('id'=>$get['parent']), array('task'=>'public-info'));
			$parents 		= $this->MTasks->getInfo(array('lft'=>$parent_item['lft'], 'rgt'=>$parent_item['rgt'], 'project_id'=>$parent_item['project_id']), array('task'=>'create-task'));
			$task_ids 		= $parents['task_ids'];
			
			$project_relation 	  = $this->MTasksRelation->getItems(array('task_ids'=>$task_ids), array('task'=>'by-multi-task'));
		}

		if(!empty($post)) {
			if(empty($user_info)) {
				$respon = array('flag'=>'false', 'message'=>'Bạn phải đăng nhập.');
			}else {
				$arrParam = $post;
				$arrParam['user_info'] = $this->_data['user_info'];

				$this->load->library("form_validation");
				$this->form_validation->set_rules('name', 'Tiêu đề', 'required|max_length[255]');
				$this->form_validation->set_rules('progress', 'Tiến độ', 'required|greater_than[-1]|less_than[101]');
				$this->form_validation->set_rules('date_start', 'Bắt đầu', 'required');
				$this->form_validation->set_rules('date_end', 'Kết thúc', 'required');
	
				$flagError = false;
				$task_permission = array();
				if(!empty($user_info['task_permission'])) {
					$task_permission = $user_info['task_permission'];
					$task_permission = explode(',', $task_permission);
				}
					
				if($this->form_validation->run($this) == FALSE){
					$errors = $this->form_validation->error_array();
					$flagError = true;
				}else {
					// kiểm tra time
					$date_start = str_replace('/', '-', $arrParam['date_start']);
					$arrParam['date_start'] = date('Y-m-d', strtotime($date_start));
					
					$date_end = str_replace('/', '-', $arrParam['date_end']);
					$arrParam['date_end'] = date('Y-m-d', strtotime($date_end));
	
					$datediff = strtotime($arrParam['date_end']) - strtotime($arrParam['date_start']);
					$arrParam['duration'] = floor($datediff/(60*60*24));
					if($arrParam['duration'] < 0) {
						$flagError = false;
						$errors['date'] = 'Ngày kết thúc phải sau ngày bắt đầu.';
					}
				}
				
				if($flagError == false) {
					$arrParam['pheduyet'] = 1;
					$this->MTasks->saveItem($arrParam, array('task'=>'add'));
					$respon = array('flag'=>'true');
				}else {
					$respon = array('flag'=>'false', 'message'=>current($errors));
				}
			}

			echo json_encode($respon);

		}else {
			$this->_data['parent'] 				= $get['parent'];
			$this->_data['parent_item'] 		= $parent_item;
			$this->_data['project_relation'] 	= $project_relation;
		
			$this->load->view('index/addform_view',$this->_data);
		}
	}

	public function detail() {
		$post  = $this->input->post();
		if(!empty($post)) {
			$this->load->library('MY_System_Info');
			$info 		= new MY_System_Info();
			$this->_data['user_info'] = $user_info = $info->getMemberInfo();
			
			$this->load->model('MTasks');
			$item = $this->MTasks->getItem(array('id'=>$post['id']), array('task'=>'public-info', 'brand'=>'detail'));
			
			if($item['parent'] > 0){
				$cid 						 = array($item['parent'], $item['project_id']);
				$items 						 = $this->MTasks->getItems(array('cid'=>$cid), array('task'=>'public-info'));
				$this->_data['project_item'] = $items[$item['project_id']];
				$this->_data['parent_item']  = $items[$item['parent']];

				$is_pheduyet_parent = array();
				if(!empty($item['is_pheduyet'])) {
					foreach($item['is_pheduyet'] as $key => $val){
						$is_pheduyet[] = $val['user_id'];
						
						$keyArr = explode('-', $key);
						if($keyArr[0] != $post['id'])
							$is_pheduyet_parent[] = $val['user_id'];
					}
					
					$is_pheduyet_parent = array_unique($is_pheduyet_parent);
					$is_pheduyet 		= array_unique($is_pheduyet);
				}
				
				$item['is_pheduyet_parent'] = $is_pheduyet_parent;
			}

			$this->_data['item'] = $item;
			$this->load->view('index/detail_view',$this->_data);	
		}
	}
	
	public function pheduyet() {
		$post  = $this->input->post();
		if(!empty($post)) {
			$this->load->model('MTasks');
			$this->MTasks->saveItem(array('id'=>$post['id']), array('task'=>'pheduyet'));
			
			$response = array('flag'=>'true');
			echo json_encode($response);
		}
	}
	
	public function link()  {
		$post  = $this->input->post();
		$this->load->library('MY_System_Info');
		$info 		= new MY_System_Info();
		$user_info = $info->getMemberInfo();
		
		if(!empty($post)) {
			$this->load->model('MTasksLinks');
	
			$arrParam = $post;
			$arrParam['user_info'] = $user_info;
			$this->MTasksLinks->saveItem($arrParam, array('task'=>'add'));
			
			$response = array('flag'=>'true');
			echo json_encode($response);
		}
	}
	
	public function delete() {
		$post  = $this->input->post();
		if(!empty($post)) {
			$this->load->model('MTasksLinks');

			$arrParam['id'] = $post['link_id'];
			$this->MTasksLinks->deleteItem($arrParam, array('task'=>'delete'));
		}
	}
	
	public function addtiendo() {
		$this->load->model('MTasks');
		$this->load->model('MTaskProgress');
		$post  = $this->input->post();
		$arrParam = $this->_data['arrParam'];
	
		$this->load->library('MY_System_Info');
		$info 		= new MY_System_Info();
		$arrParam['adminInfo'] = $user_info = $info->getMemberInfo();
		
		if(!empty($post)) {
			$item = $this->MTasks->getItem(array('id'=>$this->_data['arrParam']['task_id']), array('task'=>'public-info', 'brand'=>'detail'));

			$is_progress = $is_progress_parent = $is_implement = array();
			
			if(!empty($item['is_implement'])) {
				foreach($item['is_implement'] as $val)
					$is_implement[] = $val['user_id'];
					
				$is_implement = array_unique($is_implement);
			}
			
			if(!empty($item['is_progress'])) {
				foreach($item['is_progress'] as $key => $val){
					$is_progress[] = $val['user_id'];
				}

				$is_progress 		= array_unique($is_progress);
			}

			$task_permission = array();
			if(!empty($user_info['task_permission'])) {
				$task_permission = $user_info['task_permission'];
				$task_permission = explode(',', $task_permission);
			}
			
			$arrParam['pheduyet'] = 2;
			if(in_array('update_project', $task_permission))
				$arrParam['pheduyet'] = 3;
			elseif(in_array($user_info['id'], $is_implement) && in_array('update_brand_task', $task_permission))
				$arrParam['pheduyet'] = 3;
			elseif(count($is_progress) == 0)
				$arrParam['pheduyet'] = 3;
			
			if($arrParam['pheduyet'] == 3) { // không cần phải gửi request
				// cập nhật tiến độ cho task
				// nếu proress == -1 thì chỉ cập nhật trạng thái + progress, ngược lại handling
				if($arrParam['progress'] == -1) {
					$this->MTasks->saveItem($arrParam, array('task'=>'update-tiendo'));
					$arrParam['key'] = '';
					$arrParam['date_pheduyet'] = @date("Y-m-d H:i:s");
					
					$this->MTaskProgress->saveItem($arrParam, array('task'=>'add'));
				}else {
					$this->MTaskProgress->handling($arrParam, array('task'=>'progress'));
				}
				
				$respon = array('flag'=>'true', 'message'=>'Cập nhật thành công', 'reload'=>'true');
			}else {
				// cập nhật task progress. ko cập nhật task
				$arrParam['key'] = '';
				$arrParam['date_pheduyet'] = '0000-00-00 00:00:00';
					
				$this->MTaskProgress->saveItem($arrParam, array('task'=>'add'));
				$respon = array('flag'=>'true', 'message'=>'Cập nhật tiến độ đang được phê duyệt.');
			}

			echo json_encode($respon);
		}else {
			$this->_data['item'] = $item = $this->MTasks->getItem(array('id'=>$this->_data['arrParam']['task_id']), array('task'=>'public-info'));
			$this->load->view('index/addtiendo_view',$this->_data);
		}
	}
	
	public function edittiendo() {
		$post  = $this->input->post();
		$this->load->model('MTasks');
		$this->load->model('MTaskProgress');
	
		$this->load->library('MY_System_Info');
		$info 		= new MY_System_Info();
		
		$arrParam = $this->_data['arrParam'];
		$arrParam['adminInfo'] = $user_info = $info->getMemberInfo();
		
		$item = $this->MTaskProgress->getItem($arrParam, array('task'=>'public-info'));
		if(!empty($post)) {
			$task_item = $this->MTasks->getItem(array('id'=>$arrParam['task_id']), array('task'=>'public-info', 'brand'=>'detail'));
			
			$this->MTaskProgress->saveItem($arrParam, array('task'=>'edit'));
			
			$respon = array('flag'=>'true', 'message'=>'Cập nhật thành công');
			echo json_encode($respon);
		}else {
			$this->_data['item'] = $item;
			$this->load->view('index/edittiendo_view',$this->_data);
		}
	}
	
	public function countTiendo() {
		$this->load->model('MTaskProgress');
		$post  = $this->input->post();
		if(!empty($post)) {
			$result['request_total']  = $this->MTaskProgress->countItem($this->_data['arrParam'], array('task'=>'request-list'));
			$result['pheduyet_total'] = $this->MTaskProgress->countItem($this->_data['arrParam'], array('task'=>'pheduyet-list'));
			
			echo json_encode($result);
		}
	}
	
	public function xulytiendo() {
		$post  = $this->input->post();
		if(!empty($post)) {
			$this->load->model('MTaskProgress');
			$this->MTaskProgress->saveItem($this->_data['arrParam'], array('task'=>'update-pheduyet'));
			$this->MTaskProgress->handling($this->_data['arrParam']);
			
			$respon = array('flag'=>'true', 'message'=>'Cập nhật thành công', 'reload'=>'true');
			echo json_encode($respon);
		}else 
			$this->load->view('index/xulytiendo_view',$this->_data);
	}
	
	public function addfile() {
		$fileError = array(
				'<p>The filetype you are attempting to upload is not allowed.</p>'=>'File tải lên phải có định dạng jpg|png|pdf|docx|doc|xls|xlsx|zip|zar',
				'<p>The file you are attempting to upload is larger than the permitted size.</p>' => 'File tải lên không được quá 10 Mb'
				);
		$post  = $this->input->post();
		
		$this->load->library('MY_System_Info');
		$info 		= new MY_System_Info();
		
		
		if(!empty($post)) {	
			$arrParam = $this->_data['arrParam'];
			$arrParam['adminInfo'] = $user_info = $info->getMemberInfo();
			$this->load->library("form_validation");
			$this->form_validation->set_rules('name', 'Tên tài liệu', 'required|max_length[255]|is_unique[task_files.name]');
			$this->form_validation->set_rules('file_name', 'Tên file', 'required|max_length[255]|is_unique[task_files.file_name]');
	
			if($this->form_validation->run($this) == FALSE){
				$errors = $this->form_validation->error_array();
				$flagError = true;
			}else {
				if($_FILES["file_upload"]['name'] != ""){
					$upload_dir = FILE_PATH . '/document/';
					$config['upload_path'] = $upload_dir;
					$config['allowed_types'] = 'jpg|png|pdf|docx|doc|xls|xlsx|zip|zar';
					$config['max_size']	= '10240';
					$config['encrypt_name'] = TRUE;
					$config['file_name'] = 'test-1.docx';
			
					$this->load->library('upload', $config);

					if($this->upload->do_upload("file_upload")){
						// đổi tên file vì config file_name ứ hoạt động
						$file_info = $this->upload->data();
						$old_file_name = $file_info['file_name'];
						rename($upload_dir . $old_file_name, $upload_dir . $post['file_name']);

						$arrParam['size'] = $_FILES['file_upload']['size'];

					}else{
						$flagError = true;
						$err = $this->upload->display_errors();
						$errors[] = $fileError[$err];
					}
				}else {
					$flagError = true;
					$errors['file_upload'] = 'Phải tải file lên.';
				}
			}

			if($flagError == true) {
				$respon = array('flag'=>'false', 'message'=>current($errors));
			}else {
				$this->load->model('MTaskFiles');
				$this->MTaskFiles->saveItem($arrParam, array('task'=>'add'));
				
				$respon = array('flag'=>'true', 'message'=>'Cập nhật thành công');
			}
			
			echo json_encode($respon);

		}else
			$this->load->view('index/addfile_view',$this->_data);
	}
	
	public function editfile() {
		$fileError = array(
				'<p>The filetype you are attempting to upload is not allowed.</p>'=>'File tải lên phải có định dạng jpg|png|pdf|docx|doc|xls|xlsx|zip|zar',
				'<p>The file you are attempting to upload is larger than the permitted size.</p>' => 'File tải lên không được quá 10 Mb'
		);
		$post  = $this->input->post();
		
		$this->load->library('MY_System_Info');
		$info 		= new MY_System_Info();
		$this->load->model('MTaskFiles');
		$item		= $this->MTaskFiles->getItem($this->_data['arrParam'], array('task'=>'public-info'));
		if(!empty($post)) {
			$arrParam 			   = $this->_data['arrParam'];
			$arrParam['task_id']   = $item['task_id'];
			$arrParam['adminInfo'] = $user_info = $info->getMemberInfo();
		
			$this->load->library("form_validation");
			$flagError = false; 
			$stringValidate = 'taskfiles-name-' . $arrParam['id'];
			if($_FILES["file_upload"]['name'] != ""){
				$this->form_validation->set_rules('name', 'Tên tài liệu', 'required|max_length[255]|unique_check['.$stringValidate.']');
				$this->form_validation->set_rules('file_name', 'Tên file', 'required|max_length[255]|is_unique[task_files.file_name]');

				if($this->form_validation->run($this) == FALSE){
					$errors = $this->form_validation->error_array();
					$flagError = true;
				}else {
					$upload_dir = FILE_PATH . '/document/';
					// remove file cũ
					@unlink($upload_dir . $item['file_name']);
					
					$config['upload_path'] = $upload_dir;
					$config['allowed_types'] = 'jpg|png|pdf|docx|doc|xls|xlsx|zip|zar';
					$config['max_size']	= '10240';
					$config['encrypt_name'] = TRUE;
					$config['file_name'] = 'test-1.docx';
						
					$this->load->library('upload', $config);
					
					if($this->upload->do_upload("file_upload")){
						// đổi tên file vì config file_name ứ hoạt động
						$file_info = $this->upload->data();
						$old_file_name = $file_info['file_name'];
						rename($upload_dir . $old_file_name, $upload_dir . $post['file_name']);
					
						$arrParam['size'] = $_FILES['file_upload']['size'];
					
					}else{
						$flagError = true;
						$err = $this->upload->display_errors();
						$errors[] = $fileError[$err];
					}
				}
			}else {
				$this->form_validation->set_rules('name', 'Tên tài liệu', 'required|max_length[255]|unique_check['.$stringValidate.']');
				
				if($this->form_validation->run($this) == FALSE){
					$errors = $this->form_validation->error_array();
					$flagError = true;
				}else {
					$arrParam['file_name'] = $item['file_name'];
					$arrParam['size'] 	   = $item['size'];
				}
			}

			if($flagError == true) {
				$respon = array('flag'=>'false', 'message'=>current($errors));
			}else {
				$this->load->model('MTaskFiles');
				$this->MTaskFiles->saveItem($arrParam, array('task'=>'edit'));
				
				$respon = array('flag'=>'true', 'message'=>'Cập nhật thành công');
			}

			echo json_encode($respon);
			
		}else {
			$this->_data['item'] = $item;

			$this->load->view('index/editfile_view',$this->_data);
		}
	}
	
	public function deletefile() {
		$post  = $this->input->post();
		
		if(!empty($post)) {
			$this->load->model('MTaskFiles');
			$this->_data['arrParam']['cid'] = $this->_data['arrParam']['file_ids'];
			
			$this->MTaskFiles->deleteItem($this->_data['arrParam'], array('task'=>'delete-multi'));
		}
	}
	
	public function filelist() {
		$this->load->model('MTaskFiles');
		$post  = $this->input->post();
		
		if(!empty($post)) {
			$config['base_url'] = base_url() . 'tasks/index/filelist';
			$config['total_rows'] = $this->MTaskFiles->countItem($this->_data['arrParam'], array('task'=>'public-list'));
			$config['per_page'] = $this->_paginator['per_page'];
			$config['uri_segment'] = $this->_paginator['uri_segment'];
			$config['use_page_numbers'] = TRUE;
			
			$this->load->library("pagination");
			$this->pagination->initialize($config);
			$this->pagination->createConfig('front-end');
			
			$pagination = $this->pagination->create_ajax();

			$this->_data['arrParam']['start'] = $this->uri->segment(4);
			$items = $this->MTaskFiles->listItem($this->_data['arrParam'], array('task'=>'public-list'));

			$result = array('count'=> $config['total_rows'], 'items'=>$items, 'pagination'=>$pagination);
			
			echo json_encode($result);
		}
	}
	
	public function progresslist() {
		$this->load->model('MTaskProgress');
		$post  = $this->input->post();
		if(!empty($post)) {
			$config['base_url'] = base_url() . 'tasks/index/progresslist';
			$config['total_rows'] = $this->MTaskProgress->countItem($this->_data['arrParam'], array('task'=>'public-list'));
			$config['per_page'] = $this->_paginator['per_page'];
			$config['uri_segment'] = $this->_paginator['uri_segment'];
			$config['use_page_numbers'] = TRUE;

			$this->load->library("pagination");
			$this->pagination->initialize($config);
			$this->pagination->createConfig('front-end');

			$pagination = $this->pagination->create_ajax();
			
			$this->_data['arrParam']['start'] = $this->uri->segment(4);
			$items = $this->MTaskProgress->listItem($this->_data['arrParam'], array('task'=>'public-list'));
			
			$result = array('count'=> $config['total_rows'], 'items'=>$items, 'pagination'=>$pagination);

			echo json_encode($result);
		}
	}
	
	public function requestlist() {
		$this->load->model('MTaskProgress');
		$post  = $this->input->post();
		if(!empty($post)) {
			$config['base_url'] = base_url() . 'tasks/index/progresslist';
			$config['total_rows'] = $this->MTaskProgress->countItem($this->_data['arrParam'], array('task'=>'request-list'));
			$config['per_page'] = $this->_paginator['per_page'];
			$config['uri_segment'] = $this->_paginator['uri_segment'];
			$config['use_page_numbers'] = TRUE;
	
			$this->load->library("pagination");
			$this->pagination->initialize($config);
			$this->pagination->createConfig('front-end');
	
			$pagination = $this->pagination->create_ajax();
				
			$this->_data['arrParam']['start'] = $this->uri->segment(4);
			$items = $this->MTaskProgress->listItem($this->_data['arrParam'], array('task'=>'request-list'));
				
			$result = array('count'=> $config['total_rows'], 'items'=>$items, 'pagination'=>$pagination);
	
			echo json_encode($result);
		}
	}
	
	public function commentlist() {
		$this->load->model('MTaskComment');
		$post  = $this->input->post();
		if(!empty($post)) {
			$config['base_url'] = base_url() . 'tasks/index/commentlist';
		    $config['total_rows'] = $this->MTaskComment->countItem($this->_data['arrParam'], array('task'=>'public-list'));
			$config['per_page'] = $this->_paginator['per_page'];
			$config['uri_segment'] = $this->_paginator['uri_segment'];
			$config['use_page_numbers'] = TRUE;
			
			$this->load->library("pagination");
			$this->pagination->initialize($config);
			$this->pagination->createConfig('front-end');
			
			$pagination = $this->pagination->create_ajax();
				
			$this->_data['arrParam']['start'] = $this->uri->segment(4);
			$items = $this->MTaskComment->listItem($this->_data['arrParam'], array('task'=>'public-list'));
	
			$result = array('items'=>$items, 'pagination'=>$pagination);
			
			echo json_encode($result);
		}
	}
	
	public function addcomment() {
		$this->load->model('MTaskComment');
		$post  	  = $this->input->post();
		$arrParam = $this->_data['arrParam'];

		if(!empty($post)) {
			$this->form_validation->set_rules('content', 'Nội dung', 'required');
			
			if($this->form_validation->run($this) == FALSE){
				$errors = $this->form_validation->error_array();
				
				$response = array('flag'=>'false', 'msg'=>current($errors));
			}else {
				$this->MTaskComment->saveItem($arrParam, array('task'=>'add'));
				$response = array('flag'=>'true', 'msg'=>'Bình luận thành công', 'task_id'=>$arrParam['task_id']);
				
			}
			
			echo json_encode($response);
		}
	}
	 
// 	public function test() {
// 		$arrParam['id'] = 2;
// 		$this->load->model('MTaskProgress');
// 		$items = $this->MTaskProgress->test($arrParam);
// 	}
}