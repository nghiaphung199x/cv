<?php 
$trangthai_arr = array('Chưa thực hiện', 'Đang thực hiện', 'Hoàn thành', 'Đóng/dừng', 'Không thực hiện');
$prioty_arr    = array('Rất cao', 'Cao', 'Trung bình', 'Thấp', 'Rất thấp');
$task_id   = $item['id'];
$trangthai = $item['trangthai'];
$prioty    = $item['prioty'];
$progress  = $item['progress'] * 100;

$style = '';
if($item['lft'] != $item['rgt'] - 1)
	$style = ' style="width: 100%; margin-right: 0;"';
?>	
		<div class="gantt_cal_ltitle" style="cursor: pointer;"><span class="gantt_mark">&nbsp;</span>
			<span class="gantt_time">Cập nhật tiến độ</span>
		</div>
		<div class="toolbars">
			<ul class="list clearfix">
				<li class="btn-save"><a href="javascript:;" onclick="save_tiendo();"><i class="fa fa-floppy-o"></i>Lưu</a></li>
				<li class="btn-cancel"><a href="javascript:;" onclick="cancel('quick');"><i class="fa fa-times-circle"></i>Đóng</a></li>
				
			</ul>
		</div>
		<div class="gantt_cal_larea">
			<form method="POST" name="task_form" id="progress_form" class="form-horizontal">
				<input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
				<div class="clearfix hang" style="margin-bottom: 10px;">
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<label class="col-md-3 col-lg-2 control-label">Trạng thái</label>
								<div class="col-md-9 col-lg-10">
									<select name="trangthai" class="form-control"<?php echo $style; ?>>
<?php 
				foreach($trangthai_arr as $key => $val) {
?>
										<option value="<?php echo $key; ?>"<?php if($key == $trangthai) echo ' selected'; ?>><?php echo $val; ?></option>
<?php 
				}
?>

									</select> 
<?php  if($item['lft'] == $item['rgt'] - 1){ ?>
									<input type="number" name="progress" value="<?php echo $progress; ?>" class="form-control"/>
<?php  }else {?>

									<input type="hidden" name="progress" value="-1" class="form-control"/>
<?php }?>
		
								</div>
							</div>
	
						</div>
						<div class="col-lg-12">
							<div class="form-group">
								<label class="col-md-3 col-lg-2 control-label">Ưu tiên</label>
								<div class="col-md-9 col-lg-10">
									<select name="prioty" class="form-control">
<?php 
				foreach($prioty_arr as $key => $val) {
?>
										<option value="<?php echo $key; ?>"<?php if($key == $prioty) echo ' selected'; ?>><?php echo $val; ?></option>
<?php 
				}
?>

									</select> 
								</div>
							</div>
	
						</div>
						<div class="col-lg-12">
							<div class="form-group">
								<label class="col-md-3 col-lg-2 control-label">Ghi chú</label>
								<div class="col-md-9 col-lg-10">
									<textarea name="note" class="form-control"></textarea>
								</div>
							</div>
						</div>
					</div>

				</div>
			</form>
		</div>