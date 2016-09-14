<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN " "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<base href="<?php echo base_url();?>" />
	<title>Quản lý công việc</title>
	<link rel="icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon"/>
	<link rel="stylesheet" href="<?php echo base_url();?>public/tasks/css/reset.css" type="text/css" />
	
	<link rel="stylesheet" href="http://hstatic.net/0/0/global/design/css/bootstrap.3.3.1.css">

	<link href="https://hstatic.net/0/0/global/design/plugins/font-awesome/4.5.0/css/font-awesome.min.css" media="screen" rel="stylesheet" type="text/css">
	
	<link rel="stylesheet" href="<?php echo base_url();?>public/scripts/tasks/codebase/dhtmlxgantt.css" type="text/css" />

	<link rel="stylesheet" href="http://staging.4biz.vn/assets/css/forms.css?1459224675" type="text/css" />
	<link rel="stylesheet" href="http://staging.4biz.vn/assets/css/custom.css?1459224675" type="text/css" />
	<link rel="stylesheet" href="http://staging.4biz.vn/assets/css/basic-tables.css?1459224675" type="text/css" />

	<link rel="stylesheet" href="<?php echo base_url();?>public/tasks/css/style.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo base_url();?>public/tasks/css/responsive.css" type="text/css" media="screen" />
	
	<script type="text/javascript" src="<?php echo base_url() ?>public/tasks/js/jquery.min.js" ></script>
	
	<script src="http://hstatic.net/0/0/global/design/js/bootstrap.min.js"></script>



    <script src="<?php echo base_url();?>public/scripts/tasks/codebase/dhtmlxgantt.js" type="text/javascript" charset="utf-8"></script>

    <script type="text/javascript" src="<?php echo base_url() ?>public/tasks/js/form.js" ></script>
    <script type="text/javascript" src="<?php echo base_url() ?>public/tasks/js/task.js" ></script>
    


    <script type="text/javascript">var base_url = "<?php echo base_url(); ?>";</script>
</head>
<body>
	<span style="font-weight: bold;">Tài khoản: <?php echo $user_info['user_name']; ?></span>
	<div id="gantt_here" style='width:100%; height: 500px; margin-top: 50px;'></div>
	<div id="my-form" class="gantt_cal_light" style=""></div>
	<div id="quick-form" class="gantt_cal_light" style=""></div>
	<div>
		<input type="hidden" name="start_date_original" id="start_date_original" />
		<input type="hidden" name="start_date_drag" id="start_date_drag" />
		<input type="hidden" name="end_date_drag" id="end_date_drag" />
	</div>
</body>
</html>