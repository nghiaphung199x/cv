<?php 
	$project_id = $item['project_id'];
	$date_start = $item['date_start'];
	$date_end   = $item['date_end'];
	$parent   	= $item['parent'];
	$id  		= $item['id'];
	$pheduyet  	= $item['pheduyet'];
	$progress  	= $item['progress'] * 100;;
	
	$trangthai_arr = array('Chưa thực hiện', 'Đang thực hiện', 'Hoàn thành', 'Đóng/dừng', 'Không thực hiện');
	$prioty_arr    = array('Rất cao', 'Cao', 'Trung bình', 'Thấp', 'Rất thấp');
	
	$btnPheduyet = true;
	if($parent > 0) {
		$title = $item['name'];
		
		//check phê duyệt
		if(!in_array($user_info['id'], $item['is_pheduyet_parent']))
			$btnPheduyet = false;
		
	}else {
		$title = 'Dự án "'.$item['name'].'"';
		$btnPheduyet = false;
	}
	
	if($pheduyet == 1)
		$btnPheduyet = false;
?>
<?php if($arrParam['t'] != 'quick'): ?>
	<div class="gantt_cal_ltitle" style="cursor: pointer;"><span class="gantt_mark">&nbsp;</span>
		<span class="gantt_time"><?php echo $title; ?></span>
	</div>
	<div class="toolbars">
		<ul class="list clearfix">
		    <li class="btn-save"><a href="javascript:;" onclick="edit_congviec();"><i class="fa fa-floppy-o"></i>Lưu</a></li>
<?php if($btnPheduyet == true):?>		
			<li class="btn-pheduyet"><a href="javascript:;" onclick="pheduyet();"><i class="fa fa-gavel"></i>Phê duyệt</a></li>
<?php endif;?>
			<li class="btn-detail"><a href="javascript:;" onclick="detail();"><i class="fa fa-info"></i>Chi tiết</a></li>
			<li class="btn-cancel"><a href="javascript:;" onclick="cancel('full');"><i class="fa fa-times-circle"></i>Đóng</a></li>
		</ul>
	</div>
<?php endif;?>

	<div class="arrord_nav">
		<ul class="list clearfix">
			<li class="active" data-id="progress_manager"><span class="title">Tiến độ</span></li>
		</ul>
	</div>
	<div class="gantt_cal_larea">
		<form method="POST" name="task_form" id="task_form" class="form-horizontal">
			<input type="hidden" name="id" id="task_id" value="<?php echo $id; ?>" />
			<input type="hidden" name="parent" value="<?php echo $parent; ?>" />
			<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
			<input type="hidden" name="type" value="1" />

			<div class="manage-table tabs" id="progress_manager" style="display: block;">
				<div class="control clearfix">		
					<div class="pull-right">
						<div class="buttons-list">
							<div class="pull-right-btn">
							   <a href="javascript:;" id="new-person-btn" onclick="add_tiendo();" class="btn btn-primary btn-lg" title="Cập nhật tiến độ"><span class="">Cập nhật tiến độ</span></a>	
							</div>
						</div>				
					</div>
				</div>
				<div class="panel-heading">
					<h3 class="panel-title">
						<span class="tieude active" data-id="progress_danhsach">Lịch sử tiến độ</span>
						<span id="count_tiendo" title="22 total suppliers" class="badge bg-primary tip-left">0</span>
						
						<span class="tieude" style="margin-left: 10px;" data-id="request_list">Request</span>
						<span id="count_request" title="22 total suppliers" class="badge bg-primary tip-left">0</span>
						
						<span class="tieude" style="margin-left: 10px;" data-id="pheduyet_list">Phê duyệt</span>
						<span id="count_pheduyet" title="22 total suppliers" class="badge bg-primary tip-left">0</span>
					</h3>
				</div>
				<div class="panel-body nopadding table_holder table-responsive table_list" id="progress_danhsach">
					<table class="tablesorter table table-hover sortable_table">
						<thead>
							<tr>
								<th style="width: 20%;">Công việc</th>
								<th style="width: 10%;">Tiến độ</th>
								<th style="width: 15%;">Tình trạng</th>
								<th style="width: 10%;">Ưu tiên</th>						
								<th>Tài khoản</th>
								<th style="width: 15%;">Ngày</th>
							</tr>
						</thead>
						<tbody>	
						</tbody>
					</table>	
				</div>
				<div class="panel-body nopadding table_holder table-responsive table_list" id="request_list" style="display: none;">
					<table class="tablesorter table table-hover sortable_table">
						<thead>
							<tr>
								<th>Công việc</th>
								<th style="width: 5%;">Tiến độ</th>
								<th style="width: 10%;">Tình trạng</th>
								<th style="width: 10%;">Ưu tiên</th>						
								<th style="width: 15%;">Ngày gửi</th>
								<th style="width: 10%;">Người phê duyệt</th>
								<th style="width: 10%;">Ngày phê duyệt</th>
								<th style="width: 10%;"></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>	
				</div>
				<div class="panel-body nopadding table_holder table-responsive table_list" id="pheduyet_list" style="display: none;">
					<table class="tablesorter table table-hover sortable_table">
						<thead>
							<tr>
								<th>Công việc</th>
								<th style="width: 5%;">Tiến độ</th>
								<th style="width: 10%;">Tình trạng</th>
								<th style="width: 10%;">Ưu tiên</th>						
								<th style="width: 10%;">Người gửi</th>
								<th style="width: 10%;">Ngày gửi</th>
								<th style="width: 5%;">Phê duyệt</th>
								<th style="width: 10%;">Phê duyệt</th>
								<th style="width: 20%;"></th>
							</tr>
						</thead>
						<tbody>
						</tbody>	
					</table>
				</div>
			</div>
		</form>
	</div>
<script type="text/javascript">
$( document ).ready(function() {
	load_list('progress', 1);
	countTiendo();
	$('#add_navigation .title').click(function(e){
		$('#add_navigation .active').parent().find('.content').slideUp();
	    $('#add_navigation .active').removeClass('active');
	    $(this).addClass('active');
	    
	    var content_show = $(this).attr('data-id');
	    $('#add_navigation #'+ content_show).slideDown();
	    
	});

	$( "#my-form .arrord_nav ul.list > li" ).click(function() {
		$( "#my-form .arrord_nav ul.list > li" ).removeClass('active');
		var data_id = $(this).attr('data-id');
		 $('#my-form .gantt_cal_larea .tabs').hide();
		 $(this).addClass('active');
		 $('#'+data_id).show();
	});
});
</script>