
		<div class="gantt_cal_ltitle" style="cursor: pointer;"><span class="gantt_mark">&nbsp;</span>
			<span class="gantt_time">Xử lý tiến độ</span>
		</div>
		<div class="toolbars">
			<ul class="list clearfix">
				<li class="btn-save"><a href="javascript:;" onclick="save_tiendo('xuly');"><i class="fa fa-floppy-o"></i>Lưu</a></li>
				<li class="btn-cancel"><a href="javascript:;" onclick="cancel('quick');"><i class="fa fa-times-circle"></i>Đóng</a></li>
				
			</ul>
		</div>
		<div class="gantt_cal_larea">
			<form method="POST" name="progress_form" id="progress_form" class="form-horizontal">
				<input type="hidden" name="id" value="<?php echo $arrParam['id']; ?>" />
				<div class="clearfix hang" style="margin-bottom: 10px;">
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<label class="col-md-3 col-lg-2 control-label">Phê duyệt</label>
								<div class="col-md-9 col-lg-10">
									<select name="pheduyet" class="form-control">
										<option value="1">Có</option>
										<option value="0">Không</option>
									</select> 
								</div>
							</div>
						</div>
						<div class="col-lg-12">
							<div class="form-group">
								<label class="col-md-3 col-lg-2 control-label">Phản hồi</label>
								<div class="col-md-9 col-lg-10">
									<textarea name="reply" class="form-control"></textarea>
								</div>
							</div>
						</div>
					</div>

				</div>
			</form>
		</div>