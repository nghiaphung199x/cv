	var taskId = null;
	var frame_id = null;
	var type = null;
	var deny_items = new Array();
	var drag_task = new Array();
	
	$( document ).ready(function() {
		// xử lý checkbox
		$('body').on('click','.manage-table .check_tatca',function(){
			 var checkbox = $(this).closest('th').find('input[type="checkbox"]'); 
			
			 if (checkbox.prop('checked') == true){ 
				 checkbox.prop('checked', false);
				 $(this).parents('.table').find('td input[type="checkbox"]').prop('checked', false);
			  }else{
				  checkbox.prop('checked', true);
				  $(this).parents('.table').find('td input[type="checkbox"]').prop('checked', true);
			  }
	    });

		$('body').on('click','.manage-table tbody tr td.cb',function(){
			 var checkbox = $(this).closest('tr').find('input[type="checkbox"]');
			 var manage_tab = checkbox.closest('.manage-table');
			 var manage_tab_id = manage_tab.attr('id');

			 if (checkbox.prop('checked')==true){ 
				  checkbox.prop('checked', false);
			 }else
				  checkbox.prop('checked', true);
			 
			 // xử lý phân quyền với các task
			 var cb_progress = $(".progress_checkbox:checked");
			 if(cb_progress.length == 0) {
				 $('#progress_manager .button').hide();
			 }else {
				 if(cb_progress.length == 1) {
					 var per_xuly   = checkbox.attr('data-xuly');
					 if(per_xuly == 1)
						 $('#btn_edit_xuly').show(); 
				 }else {
					 $('#btn_edit_xuly').hide(); 
				 }
			 }
			 
	    });
		
		var array_list = ['progress', 'file'];
		
		// phân trang file, progress
		$.each( array_list, function( key, keyword ) {
			if(keyword == 'progress') {
				var manager_div = 'progress_manager';
			}else if(keyword == 'file') {
				var manager_div   = 'file_manager';
			}
			
			$('body').on('click','#'+manager_div+' .pagination a',function(){
				var page = $(this).attr('data-page');
				
				load_list(keyword, page);
			});
		});
		
		// comment
		$('body').on('click','#btnComment',function(){
			 comment();
			 return false; 
	    });
		
		// progress
		$('body').on('click','#progress_manager .panel-title span.tieude',function(){
			$('#progress_manager .panel-title span.tieude').removeClass('active');
			var content_id = $(this).attr('data-id');
			$('#progress_manager .table_list').hide();
			
			console.log($('#'+content_id).html());
			$('#'+content_id).css('display', 'block');
				
			$(this).addClass('active');
			
			if(content_id == 'progress_danhsach') {
				load_list('progress', 1);
			}else if(content_id == 'request_list')
				load_list('request', 1);
			else if(content_id == 'pheduyet_list'){
				load_list('pheduyet', 1);
			}
	
		});
	});
	
	function load_task() {
		$.ajax({
			type: "POST",
			url: base_url + 'tasks/index/danhsach',
			data: {
			},
			success: function(string){
			   var result = $.parseJSON(string);
			   var data 	  = new Array();
			   var deny_items = new Array();
			   if(jQuery.isEmptyObject(result.ketqua) == false) {
				   $.each(result.ketqua, function(i, value) {
					   data[data.length] = value;
					});	   
				   
			   }else
				   data = new Array();

			   var task = new Object();
			   task.data = data;
			   task.links = result.links;
			   
			   gantt.config.columns = [
			 				          {name:"text",       label:"Dự án/Công việc",  width: 350, tree:true},
			 						  {name:"start_date", label:"Bắt đầu",   align: "center" },
			 						  {name:"duration",   label:"Số ngày",   align: "center" },
			 						  {name:"add",        label:"",          width:44 }
			 				   ];

		
			   gantt.init("gantt_here");
			   
			   gantt.parse(task);

			   drag_task = result.drag_task;
			  
			   if(result.deny.length) {
				   deny_items = result.deny;
				   $.each(deny_items, function(i, task_id) {
					   if(task_id != "0")
						   $('.gantt_row[task_id="'+task_id+'"] .gantt_last_cell').hide();
					   else
						   $('#gantt_here [column_id="add"]').remove();
				   });
			   }

				gantt.templates.grid_row_class = function( start, end, task ){
					task_id = task.id;
					if ($.inArray(task_id, deny_items) != -1)
					{
						 return "nested_task"
					}
					
				    return "";
				};
				// link
				gantt.attachEvent("onBeforeLinkAdd", function(id,link){
					var task_id = link.source;
					
					if($.inArray(task_id, deny_items) == -1){
						$.ajax({
							type: "POST",
							url: base_url + 'tasks/index/link',
							data: {
								source   : link.source,
								target   : link.target,
								type     : parseInt(link.type),
							},
							success: function(string){
								var res = $.parseJSON(string);
								if(res.flag == 'true')
									gantt.alert("Cập nhật thành công.");
						    }
						});
						return true;
					}
					else{
						gantt.alert({
						    text:"Bạn không có quyền với chức năng này.",
						    title:"Error!",
						    ok:"Yes",
						    callback:function(){}
						});
						return false;
					}
				});
				
				gantt.attachEvent("onBeforeLinkDelete", function(id,item){
				    var task_id = item.source;
					if($.inArray(task_id, deny_items) == -1){
						$.ajax({
							type: "POST",
							url: base_url + 'tasks/index/delete',
							data: {
								link_id   : id,
	
							},
							success: function(string){
								//console.log(string);
						    }
						});
						return true;
					}
					else{
						gantt.alert({
						    text:"Bạn không có quyền với chức năng này.",
						    title:"Error!",
						    ok:"Yes",
						    callback:function(){}
						});
						return false;
					}
				});
				
				//drag
				gantt.attachEvent("onBeforeTaskDrag", function(id, mode, task){
					 if(mode == 'move' || mode == 'resize') {
						if ($.inArray(id, deny_items) == -1){
							var task  =gantt.getTask(id);
							return true;
						}
						else{
							gantt.alert({
							    text:"Bạn không có quyền với chức năng này.",
							    title:"Error!",
							    ok:"Yes",
							    callback:function(){}
							});
							return false;
						}
					}else if(mode == 'progress')
						return false;

				});

				gantt.attachEvent("onTaskDrag", function(id, mode, task, original){
					var start_date_original = new Date(task.start_date);
					var end_date_original   = new Date(task.end_date);
					var start_hour 		    = start_date_original.getHours();
					var end_hour 			= end_date_original.getHours();

					if(start_hour >= 12){
						start_date_original.setDate(start_date_original.getDate() + 1);

					}
					var start_date = start_date_original.getFullYear() + '-' + (start_date_original.getMonth() + 1) + '-' + start_date_original.getDate();
					
					
					if(end_hour < 12){
						end_date_original.setDate(end_date_original.getDate() - 1);
					}

					var end_date = end_date_original.getFullYear() + '-' + (end_date_original.getMonth()+1) + '-' + end_date_original.getDate();
					
					$('#start_date_original').val(original.start_date);
					$('#start_date_drag').val(start_date);
					$('#end_date_drag').val(end_date);
														
				    return true;
				});		
					
				gantt.attachEvent("onAfterTaskDrag", function(id, mode, e){
				    var start_date = $('#start_date_drag').val();
				    var end_date   = $('#end_date_drag').val();
				    
				    var res_start = start_date.split("-");
				    var res_end   = end_date.split("-");
				    
				    var new_start_date = res_start[2] + '/' + res_start[1] + '/' + res_start[0];
				    var new_end_date   = res_end[2] + '/' + res_end[1] + '/' + res_end[0];

				    gantt.confirm({
				        text: 'Cập nhật "'+new_start_date+' đến '+new_end_date+'"',
				        ok:"Đồng ý", 
				        cancel:"Hủy bỏ",
				        callback: function(result){
				        	if(result == true) {
								$.ajax({
									type: "POST",
									url: base_url + 'tasks/index/quickupdate',
									data: {
										id 		   : id,
										date_start : start_date,
										date_end   : end_date,
									},
									success: function(string){
										var res = $.parseJSON(string);
										if(res.flag == 'true')
											gantt.alert("Cập nhật thành công.");
											
								    }
								});
				        	}else{
				        		
				        		//task.start_date = $('#start_date_original').val();
				        		//gantt.refreshData();
				        	}

				        }
				    });
				});
				
				
		    }
		});	
	}
	
	
	$( document ).ready(function() {
		load_task();
		gantt.showLightbox = function(id) {
		    taskId = id;

		    var task   = gantt.getTask(id);
		    var parent = parseInt(task.parent);

		    if(task.$new == true){
		    	type = 'new';
		    	url = base_url + 'tasks/index/addcongviec';
		    } else
			    url = base_url + 'tasks/index/editcongviec';
		    
		    parent = task.parent;
			$.ajax({
				type: "GET",
				url: url,
				data: {
					id : id,
					parent: parent
				},
				success: function(html){
				   if(type == 'new') {
					   create_layer();
					   $('#my-form').removeClass('quickInfo');
					   $('#my-form').html(html);
					   $('#my-form').show();
					   
				   }else {
					   if(html != '') {   
						   create_layer();
						   $('#my-form').removeClass('quickInfo');
						   $('#my-form').html(html);
						   if ( $( "#my-form input[name='quickInfo']" ).length ) {
							   $('#my-form').addClass('quickInfo');
						   }
						   
						   $('#my-form').show();
					   }else {
						   gantt.alert({
							    text: 'Bạn không có quyền với chức năng này.', title:"Error!",
							    ok:"Yes", callback:function(){}
							});
					   }
				   }
				   
				   var frame_array = ['customer_list', 'xem_list', 'implement_list', 'create_task_list', 'pheduyet_task_list', 'progress_list'];
				   $.each(frame_array, function( index, value ) {
					  css_form(value);
					  press(value);
				   });
			    }
			});
		};
		
		gantt.templates.quick_info_date = function(start, end, task){
		       return gantt.templates.task_time(start, end, task);
		};

	});
	
	function press(frame_id) {
	   if($('#'+frame_id).length) {
		   var typingTimer;                
		   var doneTypingInterval = 1000;  

		   $('#'+frame_id+' .quick_search').on('keyup', function () {
			   clearTimeout(typingTimer);
			   typingTimer = setTimeout(function(){
				   doneTyping(frame_id)
			    },doneTypingInterval);

			 });

		   //on keydown, clear the countdown 
		   $('#'+frame_id+' .quick_search').on('keydown', function () {
		   	  clearTimeout(typingTimer);
		   });
	   }

	}

		function doneTyping(frame_id) {
			if(frame_id == 'customer_list')
				var url = base_url + 'tasks/customers/danhsach';
			else {
				var url = base_url + 'tasks/users/danhsach';
			}

			$('#'+frame_id+' .result').html('');
			$('#'+frame_id+' .result').hide();
			var keywords = $.trim($('#'+frame_id+' .quick_search').val());
			//console.log(keywords);
			if (keywords) {
				$.ajax({
					type: "POST",
					url: url,
					data: {
						keywords : keywords
					},
					success: function(string){
						array = $.parseJSON(string);
						css_form(frame_id)
						if(array.length) {
							var html = new Array();
							$.each(array, function( index, value ) {
								html[html.length] = '<li><a href="javascript:;" data-id="'+value.id+'" data-name="'+value.name+'" onclick="add_item(this, \''+frame_id+'\');">'+value.name+'</a></li>';
							});

							html = html.join('')
							html = '<ul class="list">'+html+'</ul>'; 
		
							$('#'+frame_id+' .result').html(html);
							$('#'+frame_id+' .result').show();
						}
				    }
				});
			}
		}

		function foucs(obj) {
			$(obj).find('.quick_search').focus();
		}
		
		function edit() {
			var task_id = $('#task_id').val();
			var parent = $('#parent').val();

			var url = base_url+'tasks/index/editcongviec?t=quick'

			$.ajax({
				type: "GET",
				url: url,
				data: {
					id 		   : task_id,
					parent 	   : parent,
				},
				success: function(string){
					$('#my-form .arrord_nav').remove();
					$('#my-form .gantt_cal_larea').remove();
					$('#my-form').append(string);	
					
					$('#my-form .btn-save').html('<a href="javascript:;" onclick="edit_congviec();"><i class="fa fa-floppy-o"></i>Lưu</a>');

				    var frame_array = ['customer_list', 'xem_list', 'implement_list', 'create_task_list', 'pheduyet_task_list', 'progress_list'];
				    $.each(frame_array, function( index, value ) {
					   css_form(value);
					   press(value);
				    });
			    }
			});
		}
		
		function pheduyet() {
		    gantt.confirm({
		        text: 'Phê duyệt cho công việc này?',
		        ok:"Đồng ý", 
		        cancel:"Hủy bỏ",
		        callback: function(result){
		        	if(result == true) {
		    			var task_id = $('#task_id').val();
		    			$.ajax({
		    				type: "POST",
		    				url: base_url + 'tasks/index/pheduyet',
		    				data: {
		    					id 		   : task_id,
		    				},
		    				success: function(string){
		    					var res = $.parseJSON(string);
								if(res.flag == 'true'){
									gantt.alert("Cập nhật thành công");
									$('#my-form').html('');
									$('#my-form').hide();
									load_task();
									close_layer();
								}
									
		    			    }
		    			});
		        	}

		        }
		    });
		}
		
		function detail() {
			var task_id = $('#task_id').val();
			
			$.ajax({
				type: "POST",
				url: base_url + 'tasks/index/detail?task=quick',
				data: {
					id 		   : task_id,
				},
				success: function(string){
					$('#my-form .arrord_nav').remove();
					$('#my-form .gantt_cal_larea').remove();
					$('#my-form').append(string);	
					
					$('#my-form .btn-save').html('<a href="javascript:;" onclick="edit();"><i class="fa fa-edit"></i>Sửa</a>');
			    }
			});
		}

		function add_item(obj, frame_id) {
			var item_name = $(obj).attr('data-name');
			var item_id   = $(obj).attr('data-id');
			var array = new Array();
			array['customer_list'] 	    = 'customer';
			array['xem_list'] 		    = 'xem';
			array['implement_list']     = 'implement';
			array['create_task_list']   = 'create_task';
			array['pheduyet_task_list'] = 'pheduyet_task';
			array['progress_list'] 		= 'progress_task';

			var detect_element 	 = $(obj).parents('.result').prev();
			var result_frame   	 = $(obj).parents('.result');
			var class_name 	 	 = array[frame_id];
			
			var html = '<span class="item"><input type="hidden" name="'+class_name+'[]" class="'+class_name+'" id="'+class_name+'_'+item_id+'" value="'+item_id+'"><a>'+item_name+'</a>&nbsp;&nbsp;<span class="x" onclick="delete_item(this);"></span></span>';
			$( html ).insertBefore( detect_element );
			result_frame.hide();
			detect_element.val('');
			detect_element.focus();
		}

		function delete_item(obj) {
			$(obj).parents('span.item').remove();
		}

		function css_form(obj_id) {
			  if($('#'+obj_id).length) {
				   var top = $("#"+obj_id+" .quick_search").offset().top - $("#"+obj_id).offset().top + 20;
				   var left = $("#"+obj_id+" .quick_search").offset().left - $("#"+obj_id).offset().left;
	
				   var styles = {
				      left : left + "px",
				      top : top + 'px'
				   };
				   
				   $("#"+obj_id+" .result").css( styles );	
			  }
		}
		
		function create_layer(type) {
			if(type == 'quick')
				var classLayer = 'overlay';
			else
				var classLayer = 'dhx_modal_cover';
			
			if($('.'+classLayer).length)
				$('.'+classLayer).css('display', 'inline-block');
			else {
				$( "body" ).append( '<div class="'+classLayer+'" style="display: inline-block;"></div>' );

			}
		}
		
		function close_layer(type) {
			if(type == 'quick')
				var classLayer = 'overlay';
			else
				var classLayer = 'dhx_modal_cover';

			$('.'+classLayer).remove();
		}
		
		function cancel(typeP, type) {
			if(typeP == 'quick') {
				$('#quick-form').html('');
				$('#quick-form').hide();	
				
				close_layer('quick');
			}else {
				$('#my-form').html('');
				$('#my-form').hide();
				close_layer();
		
				if(type == 'new'){
			    	gantt.deleteTask(taskId);
			    }
			}

		}

		function add_congviec() {
			var checkOptions = {
			        url : base_url+'tasks/index/addcongviec',
			        dataType: "json",  
			        success: congviecData
			    };
		    $("#task_form").ajaxSubmit(checkOptions); 
		    return false; 
		}
		
		function edit_congviec() {
			if($('#my-form input[name="quickInfo"]').length)
				var url = base_url+'tasks/index/quickupdate';
			else
				var url = base_url+'tasks/index/editcongviec'
			var checkOptions = {
			        url : url,
			        dataType: "json",  
			        success: taskData
			    };
		    $("#task_form").ajaxSubmit(checkOptions); 
		    return false; 
		}
		
		function taskData(data) {
			if(data.flag == 'false') {
				gantt.alert({
				    text: data.message,
				    title:"Error!",
				    ok:"Yes",
				    callback:function(){}
				});
			}else {
				gantt.alert("Cập nhật thành công.");
				$('#my-form').html('');
				$('#my-form').hide();

				load_task();
				close_layer();
			}
		}
		
		function congviecData(data) {
			if(data.flag == 'false') {
				gantt.alert({
				    text: data.message,
				    title:"Error!",
				    ok:"Yes",
				    callback:function(){}
				});
			}else {
				gantt.alert("Cập nhật thành công.");
				$('#my-form').html('');
				$('#my-form').hide();
				gantt.deleteTask(taskId);
				load_task();
				close_layer();
			}
		}
		
		function countTiendo() {
			var task_id = $('#task_id').val();
			var url = base_url + 'tasks/index/countTiendo';
			$.ajax({
				type: "POST",
				url: url,
				data: {
					task_id : task_id
				},
				success: function(string){
					var result = $.parseJSON(string);
					//console.log(result);
					$('#count_tiendo').text(result.tiendo_total);
					$('#count_request').text(result.request_total);
					$('#count_pheduyet').text(result.pheduyet_total);
			    }
			});
		}
		
		function add_tiendo() {
			var task_id = $('#task_id').val();
			var url = base_url + 'tasks/index/addtiendo'
			$.ajax({
				type: "GET",
				url: url,
				data: {
					task_id : task_id
				},
				success: function(html){
					  $('#quick-form').html(html);
					  $('#quick-form').show();
					  create_layer('quick');
			    }
			});
		}
		
		function edit_tiendo() {
			var checkbox = $(".progress_checkbox:checked");
			var progress_id = checkbox.val();
			var url = base_url + 'tasks/index/edittiendo';

			$.ajax({
				type: "GET",
				url: url,
				data: {
					id : progress_id
				},
				success: function(html){
					  $('#quick-form').html(html);
					  $('#quick-form').show();
					  create_layer('quick');
			    }
			});
		}
		
		function xuly_tiendo(id) {
			var url = base_url + 'tasks/index/xulytiendo';
			$.ajax({
				type: "GET",
				url: url,
				data: {
					id : id
				},
				success: function(html){
					  $('#quick-form').html(html);
					  $('#quick-form').show();
					  create_layer('quick');
			    }
			});
		}
		
		function note(id) {
			var url = base_url + 'tasks/index/note';
			$.ajax({
				type: "POST",
				url: url,
				data: {
					id : id
				},
				success: function(html){
					  $('#quick-form').html(html);
					  $('#quick-form').show();
					  create_layer('quick');
			    }
			});
		}
		
		function save_tiendo(task) {
			if(task == 'edit')
				var url = base_url + 'tasks/index/edittiendo';
			else if(task == 'xuly')
				var url = base_url + 'tasks/index/xulytiendo';
			else
				var url = base_url + 'tasks/index/addtiendo';
			
			var checkOptions = {
			        url : url,
			        dataType: "json",  
			        success: tiendoData
			    };
		    $("#progress_form").ajaxSubmit(checkOptions); 
		    return false; 
		}
		
		function tiendoData(data) {
			if(data.flag == 'false') {
				gantt.alert({
				    text: data.message,
				    title:"Error!",
				    ok:"Yes",
				    callback:function(){}
				});
			}else {
				gantt.alert(data.message);
				$('#quick-form').html('');
				$('#quick-form').hide();
				
				var content_id = $('#progress_manager span.tieude.active').attr('data-id');
				if(content_id == 'progress_danhsach')
					load_list('progress', 1);
				else if(content_id == 'request_list')
					load_list('request', 1);
				else if(content_id == 'pheduyet_list')
					load_list('pheduyet', 1);
				
				countTiendo();
				if(data.reload == 'true')
					load_task();
				
				close_layer('quick');
				
				$('#progress_manager .button').hide();
			}
		}
		
		function delete_file() {
			var checkbox = $(".file_checkbox:checked");
			var url = base_url + 'tasks/index/editfile';
			
			if(checkbox.length) {
				var file_ids = new Array();
				$(checkbox).each(function( index ) {
					file_ids[file_ids.length] = $(this).val();
				});
				
			    gantt.confirm({
			        text: 'Xóa tài liệu',
			        ok:"Đồng ý", 
			        cancel:"Hủy bỏ",
			        callback: function(result){
			        	if(result == true) {
							$.ajax({
								type: "POST",
								url: base_url + 'tasks/index/deletefile',
								data: {
									file_ids   : file_ids,
								},
								success: function(string){
									gantt.alert("Cập nhật thành công.");
									load_list('file', 1);
							    }
							});
			        	}
			        }
			    });
				
			}else {
				gantt.alert({
				    text: 'Chọn ít nhất một bản ghi',
				    title:"Lỗi!",
				    ok:"Đóng",
				    callback:function(){}
				});
			}
		}
		
		function add_file() {
			var task_id = $('#task_id').val();
			var url = base_url + 'tasks/index/addfile'
			$.ajax({
				type: "GET",
				url: url,
				data: {
					task_id : task_id
				},
				success: function(html){
					  $('#quick-form').html(html);
					  $('#quick-form').show();
					  create_layer('quick');
			    }
			});
		}
		
		function edit_file() {
			var checkbox = $(".file_checkbox:checked");
			var url = base_url + 'tasks/index/editfile';
			
			if(checkbox.length == 1) {
				$(checkbox).each(function( index ) {
					 file_id = $(this).val();
				});

				$.ajax({
					type: "GET",
					url: url,
					data: {
						id : file_id,
					},
					success: function(string){
						  $('#quick-form').html(string);
						  $('#quick-form').show();
						  create_layer('quick');
				    }
				});
			}else {
				gantt.alert({
				    text: 'Chọn một bản ghi.',
				    title:"Lỗi!",
				    ok:"Đóng",
				    callback:function(){}
				});
			}
		}
		
		function save_file(task) {
			if(task == 'edit') 
				var url = base_url + 'tasks/index/editfile'
			else 
				var url = base_url + 'tasks/index/addfile'

			var checkOptions = {
			        url : url,
			        dataType: "json",  
			        success: fileData
			    };
		    $("#file_form").ajaxSubmit(checkOptions); 
		    return false; 
		}
		
		function fileData(data) {
			if(data.flag == 'false') {
				gantt.alert({
				    text: data.message,
				    title:"Error!",
				    ok:"Yes",
				    callback:function(){}
				});
			}else {
				gantt.alert("Cập nhật thành công.");
				$('#quick-form').html('');
				$('#quick-form').hide();

				load_list('file', 1);
				close_layer('quick');
			}
		}
		
		function load_pagination(pagination, template) {
			if(jQuery.type(pagination) == 'object') {
				var string = new Array();
				$.each( pagination, function( key, page ) {
					if(template == 'comment') {
						if(key == 'prev')
							string[string.length] = '<li><a class="none fn-prev fn-page" data-page="'+page+'" href="javascript:;">&lt;</a></li>';
						else if(key == 'next')
							string[string.length] = '<li><a class="fn-next fn-page" data-page="'+page+'" href="javascript:;">&gt;</a></li>';
						else if(key == 'current')
							string[string.length] = '<li><a class="fn-page active" data-page="'+page+'" href="javascript:;">'+page+'</a></li>';
						else 
							string[string.length] = '<li><a class="fn-page" data-page="'+page+'" href="javascript:;">'+page+'</a></li>';
						
					}else {
						if(key == 'prev')
							string[string.length] = '<a href="javascript:;" data-page="'+page+'">&lt;</a>';
						else if(key == 'next')
							string[string.length] = '<a href="javascript:;" data-page="'+page+'">&gt;</a>';
						else if(key == 'current')
							string[string.length] = '<strong>'+page+'</strong>';
						else 
							string[string.length] = '<a href="javascript:;" data-page="'+page+'">'+page+'</a>';
					}
				});
				
				string = string.join("");
				
				if(template == 'comment') {
					string = '<ul>'+string+'</ul>';
				}else
					string = '<div class="text-center"><div class="pagination hidden-print alternate text-center">' + string + '</div></div>';

				return string;
			}else
				return '';
		}
		
		function load_template_file(items) {
			 var string = new Array();
			 $.each(items, function( index, value ) {
				  var id      		= value.id;
				  var name 			= value.name;
				  var link 			= value.link;
				  var file_name 	= value.file_name;
				  var size 			= value.size;
				  var progress  	= value.progress;
				  var created_name 	= value.created_name;
				  var created 		= value.created;
				  var modified_name = value.modified_name;
				  var modified 		= value.modified;

				  string[string.length] = '<tr style="cursor: pointer;">'
												+'<td class="center cb"><input type="checkbox" id="file_'+id+'" class="file_checkbox" value="'+id+'"><label for="file_'+id+'"><span></span></label></td>'
												+'<td class="cb">'+name+'</td>'
												+'<td><a href="'+link+'" class="download"><i class="fa fa-download" aria-hidden="true"></i></a>'+file_name+'</td>'
												+'<td class="center cb">'+size+' Kb</td>'
												+'<td class="center cb">'+created+'</td>'
												+'<td class="center cb">'+created_name+'</td>'
												+'<td class="center cb">'+modified+'</td>'
												+'<td class="center cb">'+modified_name+'</td>'
											+'</tr>';

			 });
			 
			 string = string.join("");
			 
			 return string;	
		}
		
		function load_template_request(items) {
			 var string = new Array();
			 $.each(items, function( index, value ) {
				  var id      				= value.id;
				  var task_name      		= value.task_name;
				  var progress      		= value.progress;
				  var trangthai      		= value.trangthai;
				  var prioty      			= value.prioty;
				  var created      		    = value.created;
				  var user_pheduyet_name    = value.user_pheduyet_name;
				  var date_pheduyet      	= value.date_pheduyet;
				  
				  string[string.length] = '<tr>'
												+'<td>'+task_name+'</td>'
												+'<td class="center">'+progress+'</td>'
												+'<td class="center">'+trangthai+'</td>'
												+'<td class="center">'+prioty+'</td>'
												+'<td class="center">'+created+'</td>'
												+'<td class="center">'+user_pheduyet_name+'</td>'
												+'<td class="center">'+date_pheduyet+'</td>'
												+'<td class="center">'
													+'<a href="javascript:;" onclick="note('+id+');">Ghi chú</a>'
												+'</td>'
											+'</tr>';
			 });
			 
			 string = string.join("");
			 
			 return string;
		}
		
		function load_template_pheduyet(items) {
			 var string = new Array();
			 $.each(items, function( index, value ) {
				  var id      			= value.id;
				  var date_pheduyet     = value.date_pheduyet;
				  var created      		= value.created;
				  var task_name      	= value.task_name;
				  var progress          = value.progress;
				  var trangthai      	= value.trangthai;
				  var prioty    		= value.prioty;
				  var user_name      	= value.user_name;
				  var pheduyet      	= value.pheduyet;
				  
				  if(value.is_xuly == true)
					  var control = '<a href="javascript:;" onclick="note('+id+');">Ghi chú</a> | <a href="javascript:;" onclick="xuly_tiendo('+id+');">Phê duyệt</a>';
				  else
					  var control = '<a href="javascript:;" onclick="note('+id+');">Ghi chú</a>';

				  string[string.length] = '<tr>'
												+'<td>'+task_name+'</td>'
												+'<td class="center">'+progress+'</td>'
												+'<td class="center">'+trangthai+'</td>'
												+'<td class="center">'+prioty+'</td>'
												+'<td class="center">'+user_name+'</td>'
												+'<td class="center">'+created+'</td>'
												+'<td class="center">'+pheduyet+'</td>'
												+'<td class="center">'+date_pheduyet+'</td>'
												+'<td class="center">'
													+control
												+'</td>'
											+'</tr>';
			 });
			 
			 string = string.join("");
			 
			 return string;
		}
		
		function load_template_progress(items) {
			 var string = new Array();
			 $.each(items, function( index, value ) {
				  var id      	= value.id;
				  var user_id 	= value.created_by;
				  var user_name = value.user_name;
				  var created 	= value.created;
				  var progress  = value.progress;
				  var trangthai = value.trangthai;
				  var pheduyet 	= value.pheduyet;
				  var note 		= value.note;
				  
				  var prioty 	 = value.prioty;
				  var task_name  = value.task_name;
				  var task_name  = value.task_name;
				  	  
				  user_name = '<span style="font-weight: bold">'+user_name+'</span>';
				  string[string.length] = '<tr style="cursor: pointer;">'		
												+'<td class="cb">'+task_name+'</td>'
												+'<td class="center cb">'+progress+'</td>'
												+'<td class="center cb">'+trangthai+'</td>'
												+'<td class="center cb">'+prioty+'</td>'
												+'<td class="center cb">'+user_name+'</td>'
												+'<td class="center cb">'+created+'</td>'
											+'</tr>	';
			 });
			 
			 string = string.join("");
			 
			 return string;
		}
		
		function load_tempate_comment(items) {
			 var string = new Array();
			 $.each(items, function( index, value ) {
				  var id      	= value.id;
				  var username 	= value.username;
				  var name 		= value.name;
				  var content 	= value.content;
				  var created 	= value.created;

				  string[string.length] = 
		 				  '<li class="item-comment">' 
		 					+'<a target="_blank" rel="nofollow" href="javascript:;" class="thumb-user" title="'+name+'">' 
		 						+'<img class="fn-thumb" width="50" src="http://s120.avatar.zdn.vn/avatar_files/3/b/b/e/caonaman369_120_1.jpg" alt="'+name+'">' 
		 					+'</a>' 
		 					+'<div class="post-comment">' 
		 						+'<a target="_blank" rel="nofollow" class="fn-link" href="http://me.zing.vn/u/caonaman369" title="'+name+'">'+username+'</a>' 
		 						+'<p class="fn-content">'+content+'</p>' 
		 						+'<span class="fn-time">'+created+'</span>' 
		 					+'</div>' 
		 				 +'</li>' ; 

			 });

			 string = string.join("");	
			 return string;
		}
		
		function load_comment(task_id, page) {
			var url = base_url + 'tasks/index/commentlist/'+page;
			$.ajax({
				type: "POST",
				url: url,
				data: {
					task_id : task_id,
				},
				success: function(string){
					var result = $.parseJSON(string);
					var items = result.items;
					if(items.length) {
						var html_string = load_tempate_comment(items);
						var pagination = load_pagination(pagination);
						
						$('#commentList').html(html_string);	
						$('#commentList').html(html_string);	
					}
			    }
			});
		}

		function load_list(keyword, page) {
			var task_id = $('#task_id').val();
			if(keyword == 'progress') {
				var url	        = base_url + 'tasks/index/progresslist/'+page;
				var manager_div = 'progress_danhsach';
				var count_span  = 'count_tiendo';
			}else if(keyword == 'file') {
				var url 		  = base_url + 'tasks/index/filelist/'+page;
				var manager_div   = 'file_manager';
				var count_span 	  = 'count_tailieu';
			}else if(keyword == 'request') {
				var url 		  = base_url + 'tasks/index/requestlist/'+page;
				var manager_div   = 'request_list';
				var count_span 	  = 'count_request';
			}else if(keyword == 'pheduyet') {
				var url 		  = base_url + 'tasks/index/pheduyetlist/'+page;
				var manager_div   = 'pheduyet_list';
				var count_span 	  = 'count_pheduyet';
			}

			$.ajax({
				type: "POST",
				url: url,
				data: {
					task_id : task_id,
				},
				success: function(string){
					 var result = $.parseJSON(string);
					 var items = result.items; 
					 var pagination = result.pagination;

					 if(items.length) {
						 if(keyword == 'progress') {
							 var html_string = load_template_progress(items);
							 var pagination = load_pagination(pagination);

						 }else if(keyword == 'file') {
							 var html_string = load_template_file(items);
							 var pagination = load_pagination(pagination);
						 }else if(keyword == 'request') {
							 var html_string = load_template_request(items);
							 var pagination = load_pagination(pagination);
						 }else if(keyword == 'pheduyet') {
							 var html_string = load_template_pheduyet(items);;
							 var pagination = load_pagination(pagination);
						 }
		
						 $('#'+manager_div+' .table tbody').html(html_string);
						 if($('#'+manager_div+' .text-center').length)
							 $('#'+manager_div+' .text-center').replaceWith( pagination );
						 else
							 $('#'+manager_div).append(pagination);
						 
						 $('#'+count_span).text(result.count);
					 }
			    }
			});
		}
		
		function comment() {
			var checkOptions = {
					url : base_url + 'tasks/index/addcomment',
			        dataType: "json",  
			        success: commentData
			    };
		    $("#task_comment").ajaxSubmit(checkOptions); 
		}
		
		function commentData(data) {
			gantt.alert(data.msg);
			if(data.flag == 'true') {
				load_comment(data.task_id, 1);
			}
			$('#comment_content').val('');
		}
		