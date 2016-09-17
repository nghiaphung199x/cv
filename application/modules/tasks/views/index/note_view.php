<?php 
	$note = $item['note'];
	if(!empty($item['reply'])) {
		$user_pheduyet_name = $item['user_pheduyet_name'];
		$reply			    = $item['reply'];
		$note = $note . '<br />@'.$user_pheduyet_name . ' : ' . $reply;
	}
?>
		<div class="gantt_cal_ltitle" style="cursor: pointer;"><span class="gantt_mark">&nbsp;</span>
			<span class="gantt_time">Ghi chú</span>
		</div>
		<div class="toolbars">
			<ul class="list clearfix">
				<li class="btn-cancel"><a href="javascript:;" onclick="cancel('quick');"><i class="fa fa-times-circle"></i>Đóng</a></li>
			</ul>
		</div>
		<div class="gantt_cal_larea">
			<form method="POST" name="progress_form" id="progress_form" class="form-horizontal">
				<div class="clearfix hang" style="margin-bottom: 10px;">
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<div class="col-lg-12">
									<textarea name="reply" class="form-control" disabled ><?php echo $note; ?></textarea>
								</div>
							</div>
						</div>
					</div>

				</div>
			</form>
		</div>