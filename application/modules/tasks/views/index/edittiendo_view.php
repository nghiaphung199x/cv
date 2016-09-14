<?php 
$trangthai_arr = array('Chưa thực hiện', 'Đang thực hiện', 'Hoàn thành', 'Đóng/dừng', 'Không thực hiện');
$prioty_arr    = array('Rất cao', 'Cao', 'Trung bình', 'Thấp', 'Rất thấp');

$trangthai  = $item['trangthai'];
$prioty 	= $item['prioty'];
$progress   = $item['progress'] * 100;
$note 		= nl2br($item['note']);
?>	
		<div class="gantt_cal_ltitle" style="cursor: pointer;"><span class="gantt_mark">&nbsp;</span>
			<span class="gantt_time">Cập nhật tiến độ</span>
		</div>
		<div class="toolbars">
			<ul class="list clearfix">
				<li class="btn-save"><a href="javascript:;" onclick="save_tiendo('edit');"><i class="fa fa-floppy-o"></i>Lưu</a></li>
				<li class="btn-cancel"><a href="javascript:;" onclick="cancel('quick');"><i class="fa fa-times-circle"></i>Đóng</a></li>
				
			</ul>
		</div>
		<div class="gantt_cal_larea">
			<form method="POST" name="progress_form" id="progress_form" class="form-horizontal">
				<input type="hidden" name="id" value="<?php echo $item['id']; ?>" />
				<div class="clearfix hang" style="margin-bottom: 10px;">
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<label class="col-md-3 col-lg-2 control-label">Trạng thái</label>
								<div class="col-md-9 col-lg-10">
									<select name="trangthai" class="form-control">
<?php 
				foreach($trangthai_arr as $key => $val) {
?>
										<option value="<?php echo $key; ?>"<?php if($key == $trangthai) echo ' selected'; ?>><?php echo $val; ?></option>
<?php 
				}
?>

									</select> 
									<input type="number" name="progress" value="<?php echo $progress; ?>" class="form-control"/>
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
									<textarea name="note" class="form-control"><?php echo $note; ?></textarea>
								</div>
							</div>
						</div>
					</div>

				</div>
			</form>
		</div>