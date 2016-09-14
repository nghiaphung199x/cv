<?php 
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
		<div class="gantt_cal_ltitle" style="cursor: pointer;"><span class="gantt_mark">&nbsp;</span>
			<span class="gantt_time"><?php echo $title; ?></span>
		</div>
		<div class="toolbars">
			<ul class="list clearfix">
				<li class="btn-save"><a href="javascript:;" onclick="edit_congviec();"><i class="fa fa-floppy-o"></i>Lưu đóng</a></li>
				<li class="btn-detail"><a href="javascript:;" onclick="detail();"><i class="fa fa-info"></i>Chi tiết</a></li>
				<li class="btn-cancel"><a href="javascript:;" onclick="cancel();"><i class="fa fa-times-circle"></i>Hủy bỏ</a></li>
				<li class="btn-delete"><a href="javascript:;"><i class="fa fa-times"></i>Xóa</a></li>
<?php if($btnPheduyet == true):?>		
				<li class="btn-pheduyet right"><a href="javascript:;"><i class="fa fa-gavel"></i>Phê duyệt</a></li>
<?php endif;?>	
			</ul>
		</div>
		<div class="gantt_cal_larea" style="height: 98px; overflow: auto;">
			<input type="hidden" name="quickInfo" />
			<form method="POST" name="task_form" id="task_form">
				<input type="hidden" name="id" id="task_id" value="<?php echo $id; ?>" />
				<div class="row clearfix col-50">
					<div class="label">Bắt đầu</div>
					<div class="tcontent"><input type="text" name="date_start" value="<?php echo $date_start; ?>"></div>
				</div>
				<div class="row clearfix col-50"">
					<div class="label">Kết thúc</div>
					<div class="tcontent"><input type="text" name="date_end" value="<?php echo $date_end; ?>"></div>
				</div>
				<div class="row clearfix col-50">
					<div class="label">Trạng thái</div>
					<div class="tcontent">
						<select class="select" name="trangthai" style="width: 140px;">
<?php 
		foreach($trangthai_arr as $key => $value) {
?>
							<option value="<?php echo $key; ?>"<?php if($key == $item['trangthai']) echo ' selected'; ?>><?php echo $value; ?></option>
<?php 
		}
?>

						</select> 
						<input type="number" name="progress" style="width: 32px;" value="<?php echo $progress; ?>" />
					</div>
				</div>
				<div class="row clearfix col-50">
					<div class="label">Ưu tiên</div>
					<div class="tcontent">
						<select class="select" name="trangthai">
<?php 
		foreach($prioty_arr as $key => $value) {
?>
							<option value="<?php echo $key; ?>"<?php if($key == $item['prioty']) echo ' selected'; ?>><?php echo $value; ?></option>
<?php 
		}
?>
						</select> 
					</div>
				</div>

				
			</form>
		</div>