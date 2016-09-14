// Rewrite Url
function get_alias(){
	//var theForm = document.appForm;
	var str_rewrite = document.getElementById("name").value;
	str_rewrite= str_rewrite.toLowerCase();
	
	str_rewrite= str_rewrite.replace(/à|ả|ã|á|ạ|ă|ằ|ẳ|ẵ|ắ|ặ|â|ầ|ẩ|ẫ|ấ|ậ/g,"a");
	str_rewrite= str_rewrite.replace(/è|ẻ|ẽ|é|ẹ|ê|ề|ể|ễ|ế|ệ/g,"e");
	str_rewrite= str_rewrite.replace(/ì|ỉ|ĩ|í|ị/g,"i");
	str_rewrite= str_rewrite.replace(/ò|ỏ|õ|ó|ọ|ô|ồ|ổ|ỗ|ố|ộ|ơ|ờ|ở|ỡ|ớ|ợ/g,"o");
	str_rewrite= str_rewrite.replace(/ù|ủ|ũ|ú|ụ|ư|ừ|ử|ữ|ứ|ự/g,"u");
	str_rewrite= str_rewrite.replace(/ỳ|ỷ|ỹ|ý/g,"y");
	str_rewrite= str_rewrite.replace(/đ/g,"d");
	str_rewrite= str_rewrite.replace(/!|@|\$|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'| |\"|\“|\”|\&|\#|\[|\]|~/g,"-");
	str_rewrite= str_rewrite.replace(/-+-/g,"-");
	str_rewrite= str_rewrite.replace(/^\-+|\-+$/g,"");

	if(document.getElementById("alias").value == '') {
		document.getElementById("alias").value = str_rewrite;
	}
	
	return str;
}

function number_format(jElement){
	var vMoney = $(jElement).val();
    vMoney = vMoney.replace(/[^\d]+/g,''); // tìm những kí tự ko phải là số rồi chuyển thành khoảng trắng
    var vNewMoney = "";
    
    if(vMoney.length > 3){
    	var x = 1;
		for(var i = vMoney.length - 1; i>=0; i--){
			vNewMoney = vNewMoney + "" + vMoney[i]; //  + "" để tránh trường hợp 2 số cộng với nhau
			if(x%3 == 0){
				vNewMoney = vNewMoney + ".";
			}
			x++;
		}
		var tmp = "";
		for(var i=vNewMoney.length-1; i>=0; i--){ // đảo ngược chuỗi được đảo ngược ở bên trên
			tmp = tmp + "" + vNewMoney[i];
		}
		
		vNewMoney = tmp.replace(/^[\.]/g,''); // xóa dấu chấm thừa ở đầu (nếu có) sau khi đảo ngược
    }else{
		vNewMoney = vMoney;
	}
    
    $(jElement).val(vNewMoney)
}

/*
 * Main menu
 */
if (typeof dd_domreadycheck=="undefined")
	var dd_domreadycheck=false

var ddlevelsmenu={
enableshim: true,

arrowpointers:{
	downarrow: ["/public/templates/admin/system/images/j_arrow_down.png", 9,6],
	rightarrow: ["/public/templates/admin/system/images/j_arrow.png", 6,9],
	showarrow: {toplevel: true, sublevel: true}
},
hideinterval: 0,
effects: {enableswipe: true, enableslide: true, enablefade: true, duration: 250},
httpsiframesrc: "blankaa.htm",

topmenuids: [], //array containing ids of all the primary menus on the page
topitems: {}, //object array containing all top menu item links
subuls: {}, //object array containing all ULs
lastactivesubul: {}, //object object containing info for last mouse out menu item's UL
topitemsindex: -1,
ulindex: -1,
hidetimers: {}, //object array timer
shimadded: false,
nonFF: !/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent), //detect non FF browsers
ismobile:navigator.userAgent.match(/(iPad)|(iPhone)|(iPod)|(android)|(webOS)/i) != null, //boolean check for popular mobile browsers

getoffset:function(what, offsettype){
	return (what.offsetParent)? what[offsettype]+this.getoffset(what.offsetParent, offsettype) : what[offsettype]
},

getoffsetof:function(el){
	el._offsets={left:this.getoffset(el, "offsetLeft"), top:this.getoffset(el, "offsetTop")}
},

getwindowsize:function(){
	this.docwidth=window.innerWidth? window.innerWidth-10 : this.standardbody.clientWidth-10
	this.docheight=window.innerHeight? window.innerHeight-15 : this.standardbody.clientHeight-18
},

gettopitemsdimensions:function(){
	for (var m=0; m<this.topmenuids.length; m++){
		var topmenuid=this.topmenuids[m]
		for (var i=0; i<this.topitems[topmenuid].length; i++){
			var header=this.topitems[topmenuid][i]
			var submenu=document.getElementById(header.getAttribute('rel'))
			header._dimensions={w:header.offsetWidth, h:header.offsetHeight, submenuw:submenu.offsetWidth, submenuh:submenu.offsetHeight}
		}
	}
},

isContained:function(m, e){
	var e=window.event || e
	var c=e.relatedTarget || ((e.type=="mouseover")? e.fromElement : e.toElement)
	while (c && c!=m)try {c=c.parentNode} catch(e){c=m}
	if (c==m)
		return true
	else
		return false
},

addpointer:function(target, imgclass, imginfo, BeforeorAfter){
	var pointer=document.createElement("img")
	pointer.src=imginfo[0]
	pointer.style.width=imginfo[1]+"px"
	pointer.style.height=imginfo[2]+"px"
	if(imgclass=="rightarrowpointer"){
		pointer.style.left=target.offsetWidth-imginfo[2]-2+"px"
	}
	pointer.className=imgclass
	var target_firstEl=target.childNodes[target.firstChild.nodeType!=1? 1 : 0] //see if the first child element within A is a SPAN (found in sliding doors technique)
	if (target_firstEl && target_firstEl.tagName=="SPAN"){
		target=target_firstEl //arrow should be added inside this SPAN instead if found
	}
	if (BeforeorAfter=="before")
		target.insertBefore(pointer, target.firstChild)
	else
		target.appendChild(pointer)
},

css:function(el, targetclass, action){
	var needle=new RegExp("(^|\\s+)"+targetclass+"($|\\s+)", "ig")
	if (action=="check")
		return needle.test(el.className)
	else if (action=="remove")
		el.className=el.className.replace(needle, "")
	else if (action=="add" && !needle.test(el.className))
		el.className+=" "+targetclass
},

addshimmy:function(target){
	var shim=(!window.opera)? document.createElement("iframe") : document.createElement("div") //Opera 9.24 doesnt seem to support transparent IFRAMEs
	shim.className="ddiframeshim"
	shim.setAttribute("src", location.protocol=="https:"? this.httpsiframesrc : "about:blank")
	shim.setAttribute("frameborder", "0")
	target.appendChild(shim)
	try{
		shim.style.filter='progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)'
	}
	catch(e){}
	return shim
},

positionshim:function(header, submenu, dir, scrollX, scrollY){
	if (header._istoplevel){
		var scrollY=window.pageYOffset? window.pageYOffset : this.standardbody.scrollTop
		var topgap=header._offsets.top-scrollY
		var bottomgap=scrollY+this.docheight-header._offsets.top-header._dimensions.h
		if (topgap>0){
			this.shimmy.topshim.style.left=scrollX+"px"
			this.shimmy.topshim.style.top=scrollY+"px"
			this.shimmy.topshim.style.width="99%"
			this.shimmy.topshim.style.height=topgap+"px" //distance from top window edge to top of menu item
		}
		if (bottomgap>0){
			this.shimmy.bottomshim.style.left=scrollX+"px"
			this.shimmy.bottomshim.style.top=header._offsets.top + header._dimensions.h +"px"
			this.shimmy.bottomshim.style.width="99%"
			this.shimmy.bottomshim.style.height=bottomgap+"px" //distance from bottom of menu item to bottom window edge
		}
	}
},

hideshim:function(){
	this.shimmy.topshim.style.width=this.shimmy.bottomshim.style.width=0
	this.shimmy.topshim.style.height=this.shimmy.bottomshim.style.height=0
},


buildmenu:function(mainmenuid, header, submenu, submenupos, istoplevel, dir){
	header._master=mainmenuid //Indicate which top menu this header is associated with
	header._pos=submenupos //Indicate pos of sub menu this header is associated with
	header._istoplevel=istoplevel
	if (istoplevel){
		this.addEvent(header, function(e){
		ddlevelsmenu.hidemenu(ddlevelsmenu.subuls[this._master][parseInt(this._pos)])
		}, "click")
	}
	this.subuls[mainmenuid][submenupos]=submenu
	header._dimensions={w:header.offsetWidth, h:header.offsetHeight, submenuw:submenu.offsetWidth, submenuh:submenu.offsetHeight}
	this.getoffsetof(header)
	submenu.parentNode.style.left=0
	submenu.parentNode.style.top=0
	submenu.parentNode.style.visibility="hidden"
	submenu.style.visibility="hidden"
	this.addEvent(header, function(e){ //mouseover event
		if (ddlevelsmenu.ismobile || !ddlevelsmenu.isContained(this, e)){
			var submenu=ddlevelsmenu.subuls[this._master][parseInt(this._pos)]
			if (this._istoplevel){
				ddlevelsmenu.css(this, "selected", "add")
				clearTimeout(ddlevelsmenu.hidetimers[this._master][this._pos])
			}
			ddlevelsmenu.getoffsetof(header)
			var scrollX=window.pageXOffset? window.pageXOffset : ddlevelsmenu.standardbody.scrollLeft
			var scrollY=window.pageYOffset? window.pageYOffset : ddlevelsmenu.standardbody.scrollTop
			var submenurightedge=this._offsets.left + this._dimensions.submenuw + (this._istoplevel && dir=="topbar"? 0 : this._dimensions.w)
			var submenubottomedge=this._offsets.top + this._dimensions.submenuh
			//Sub menu starting left position
			var menuleft=(this._istoplevel? this._offsets.left + (dir=="sidebar"? this._dimensions.w : 0) : this._dimensions.w)
			if (submenurightedge-scrollX>ddlevelsmenu.docwidth){
				menuleft+= -this._dimensions.submenuw + (this._istoplevel && dir=="topbar" ? this._dimensions.w : -this._dimensions.w)
			}
			submenu.parentNode.style.left=menuleft+"px"
			//Sub menu starting top position
			var menutop=(this._istoplevel? this._offsets.top + (dir=="sidebar"? 0 : this._dimensions.h) : this.offsetTop)
			if (submenubottomedge-scrollY>ddlevelsmenu.docheight){ //no room downwards?
				if (this._dimensions.submenuh<this._offsets.top+(dir=="sidebar"? this._dimensions.h : 0)-scrollY){ //move up?
					menutop+= - this._dimensions.submenuh + (this._istoplevel && dir=="topbar"? -this._dimensions.h : this._dimensions.h)
				}
				else{ //top of window edge
					menutop+= -(this._offsets.top-scrollY) + (this._istoplevel && dir=="topbar"? -this._dimensions.h : 0)
				}
			}
			submenu.parentNode.style.top=menutop+"px"
			if (ddlevelsmenu.enableshim && (ddlevelsmenu.effects.enableswipe==false || ddlevelsmenu.nonFF)){ //apply shim immediately only if animation is turned off, or if on, in non FF2.x browsers
				ddlevelsmenu.positionshim(header, submenu, dir, scrollX, scrollY)
			}
			else{
				submenu.FFscrollInfo={x:scrollX, y:scrollY}
			}
			ddlevelsmenu.showmenu(header, submenu, dir)
			if (e.preventDefault)
				e.preventDefault()
			if (e.stopPropagation)
				e.stopPropagation()
		}
	}, (this.ismobile)? "click" : "mouseover")
	this.addEvent(header, function(e){ //mouseout event
		var submenu=ddlevelsmenu.subuls[this._master][parseInt(this._pos)]
		if (this._istoplevel){
			if (!ddlevelsmenu.isContained(this, e) && !ddlevelsmenu.isContained(submenu.parentNode, e)) //hide drop down div if mouse moves out of menu bar item but not into drop down div itself
				ddlevelsmenu.hidemenu(submenu.parentNode)
		}
		else if (!this._istoplevel && !ddlevelsmenu.isContained(this, e)){
			ddlevelsmenu.hidemenu(submenu.parentNode)
		}

	}, "mouseout")
},

setopacity:function(el, value){
	el.style.opacity=value
	if (typeof el.style.opacity!="string"){ //if it's not a string (ie: number instead), it means property not supported
		el.style.MozOpacity=value
		try{
			if (el.filters){
				el.style.filter="progid:DXImageTransform.Microsoft.alpha(opacity="+ value*100 +")"
			}
		} catch(e){}
	}
},

showmenu:function(header, submenu, dir){
	if (this.effects.enableswipe || this.effects.enablefade){
		if (this.effects.enableswipe){
			var endpoint=(header._istoplevel && dir=="topbar")? header._dimensions.submenuh : header._dimensions.submenuw
			submenu.parentNode.style.width=submenu.parentNode.style.height=0
			submenu.parentNode.style.overflow="hidden"
		}
		if (this.effects.enablefade){
			submenu.parentNode.style.width=submenu.offsetWidth+"px"
			submenu.parentNode.style.height=submenu.offsetHeight+"px"
			this.setopacity(submenu.parentNode, 0) //set opacity to 0 so menu appears hidden initially
		}
		submenu._curanimatedegree=0
		submenu.parentNode.style.visibility="visible"
		submenu.style.visibility="visible"
		clearInterval(submenu._animatetimer)
		submenu._starttime=new Date().getTime() //get time just before animation is run
		submenu._animatetimer=setInterval(function(){ddlevelsmenu.revealmenu(header, submenu, endpoint, dir)}, 10)
	}
	else{
		submenu.parentNode.style.visibility="visible"
		submenu.style.visibility="visible"
	}
},

revealmenu:function(header, submenu, endpoint, dir){
	var elapsed=new Date().getTime()-submenu._starttime //get time animation has run
	if (elapsed<this.effects.duration){
		if (this.effects.enableswipe){
			if (submenu._curanimatedegree==0){ //reset either width or height of sub menu to "auto" when animation begins
				submenu.parentNode.style[header._istoplevel && dir=="topbar"? "width" : "height"]=(header._istoplevel && dir=="topbar"? submenu.offsetWidth : submenu.offsetHeight)+"px"
			}
			submenu.parentNode.style[header._istoplevel && dir=="topbar"? "height" : "width"]=(submenu._curanimatedegree*endpoint)+"px"
			if (this.effects.enableslide){
				submenu.style[header._istoplevel && dir=="topbar"? "top" : "left"]=Math.floor((submenu._curanimatedegree-1)*endpoint)+"px"
			}
		}
		if (this.effects.enablefade){
			this.setopacity(submenu.parentNode, submenu._curanimatedegree)
		}
	}
	else{
		clearInterval(submenu._animatetimer)
		if (this.effects.enableswipe){
			submenu.parentNode.style.width=submenu.offsetWidth+"px"
			submenu.parentNode.style.height=submenu.offsetHeight+"px"
			submenu.parentNode.style.overflow="visible"
			if (this.effects.enableslide){
				submenu.style.top=0;
				submenu.style.left=0;
			}
		}
		if (this.effects.enablefade){
			this.setopacity(submenu.parentNode, 1)
			submenu.parentNode.style.filter=""
		}
		if (this.enableshim && submenu.FFscrollInfo) //if this is FF browser (meaning shim hasn't been applied yet
			this.positionshim(header, submenu, dir, submenu.FFscrollInfo.x, submenu.FFscrollInfo.y)
	}
	submenu._curanimatedegree=(1-Math.cos((elapsed/this.effects.duration)*Math.PI)) / 2
},

hidemenu:function(submenu){
	if (typeof submenu._pos!="undefined"){ //if submenu is outermost DIV drop down menu
		this.css(this.topitems[submenu._master][parseInt(submenu._pos)], "selected", "remove")
		if (this.enableshim)
			this.hideshim()
	}
	clearInterval(submenu.firstChild._animatetimer)
	submenu.style.left=0
	submenu.style.top="-1000px"
	submenu.style.visibility="hidden"
	submenu.firstChild.style.visibility="hidden"
},


addEvent:function(target, functionref, tasktype) {
	if (target.addEventListener)
		target.addEventListener(tasktype, functionref, false);
	else if (target.attachEvent)
		target.attachEvent('on'+tasktype, function(){return functionref.call(target, window.event)});
},

domready:function(functionref){ //based on code from the jQuery library
	if (dd_domreadycheck){
		functionref()
		return
	}
	// Mozilla, Opera and webkit nightlies currently support this event
	if (document.addEventListener) {
		// Use the handy event callback
		document.addEventListener("DOMContentLoaded", function(){
			document.removeEventListener("DOMContentLoaded", arguments.callee, false )
			functionref();
			dd_domreadycheck=true
		}, false )
	}
	else if (document.attachEvent){
		// If IE and not an iframe
		// continually check to see if the document is ready
		if ( document.documentElement.doScroll && window == window.top) (function(){
			if (dd_domreadycheck){
				functionref()
				return
			}
			try{
				// If IE is used, use the trick by Diego Perini
				// http://javascript.nwbox.com/IEContentLoaded/
				document.documentElement.doScroll("left")
			}catch(error){
				setTimeout( arguments.callee, 0)
				return;
			}
			//and execute any waiting functions
			functionref();
			dd_domreadycheck=true
		})();
	}
	if (document.attachEvent && parent.length>0) //account for page being in IFRAME, in which above doesn't fire in IE
		this.addEvent(window, function(){functionref()}, "load");
},


init:function(mainmenuid, dir){
	this.standardbody=(document.compatMode=="CSS1Compat")? document.documentElement : document.body
	this.topitemsindex=-1
	this.ulindex=-1
	this.topmenuids.push(mainmenuid)
	this.topitems[mainmenuid]=[] //declare array on object
	this.subuls[mainmenuid]=[] //declare array on object
	this.hidetimers[mainmenuid]=[] //declare hide entire menu timer
	if (this.enableshim && !this.shimadded){
		this.shimmy={}
		this.shimmy.topshim=this.addshimmy(document.body) //create top iframe shim obj
		this.shimmy.bottomshim=this.addshimmy(document.body) //create bottom iframe shim obj
		this.shimadded=true
	}
	var menubar=document.getElementById(mainmenuid)
	var alllinks=menubar.getElementsByTagName("a")
	this.getwindowsize()
	for (var i=0; i<alllinks.length; i++){
		if (alllinks[i].getAttribute('rel')){
			this.topitemsindex++
			this.ulindex++
			var menuitem=alllinks[i]
			this.topitems[mainmenuid][this.topitemsindex]=menuitem //store ref to main menu links
			var dropul=document.getElementById(menuitem.getAttribute('rel'))
			var shelldiv=document.createElement("div") // create DIV which will contain the UL
			shelldiv.className="ddsubmenustyle"
			dropul.removeAttribute("class")
			shelldiv.appendChild(dropul)
			document.body.appendChild(shelldiv) //move main DIVs to end of document
			shelldiv.style.zIndex=2000 //give drop down menus a high z-index
			shelldiv._master=mainmenuid  //Indicate which main menu this main DIV is associated with
			shelldiv._pos=this.topitemsindex //Indicate which main menu item this main DIV is associated with
			this.addEvent(shelldiv, function(){ddlevelsmenu.hidemenu(this)}, "click")
			var arrowclass=(dir=="sidebar")? "rightarrowpointer" : "downarrowpointer"
			var arrowpointer=(dir=="sidebar")? this.arrowpointers.rightarrow : this.arrowpointers.downarrow
			if (this.arrowpointers.showarrow.toplevel)
				this.addpointer(menuitem, arrowclass, arrowpointer, (dir=="sidebar")? "before" : "after")
			this.buildmenu(mainmenuid, menuitem, dropul, this.ulindex, true, dir) //build top level menu
			shelldiv.onmouseover=function(){
				clearTimeout(ddlevelsmenu.hidetimers[this._master][this._pos])
			}
			this.addEvent(shelldiv, function(e){ //hide menu if mouse moves out of main DIV element into open space
				if (!ddlevelsmenu.isContained(this, e) && !ddlevelsmenu.isContained(ddlevelsmenu.topitems[this._master][parseInt(this._pos)], e)){
					var dropul=this
					if (ddlevelsmenu.enableshim)
						ddlevelsmenu.hideshim()
					ddlevelsmenu.hidetimers[this._master][this._pos]=setTimeout(function(){
						ddlevelsmenu.hidemenu(dropul)
					}, ddlevelsmenu.hideinterval)
				}
			}, "mouseout")
			var subuls=dropul.getElementsByTagName("ul")
			for (var c=0; c<subuls.length; c++){
				this.ulindex++
				var parentli=subuls[c].parentNode
				var subshell=document.createElement("div")
				subshell.appendChild(subuls[c])
				parentli.appendChild(subshell)
				if (this.arrowpointers.showarrow.sublevel)
					this.addpointer(parentli.getElementsByTagName("a")[0], "rightarrowpointer", this.arrowpointers.rightarrow, "before")
				this.buildmenu(mainmenuid, parentli, subuls[c], this.ulindex, false, dir) //build sub level menus
			}
		}
	} //end for loop
	this.addEvent(window, function(){ddlevelsmenu.getwindowsize(); ddlevelsmenu.gettopitemsdimensions()}, "resize")
},

setup:function(mainmenuid, dir){
	this.domready(function(){ddlevelsmenu.init(mainmenuid, dir)})
}

}


/*
 * Submit form
 */
function OnSubmitForm(url)
{ 
	document.appForm.action = url;
	document.appForm.submit();
	return true;
}

function OnSubmitForm2(url)
{ 

	var theForm = document.appForm;
	var number = 0;
	if (theForm.cid.length) {
		for(i=0;i < theForm.cid.length; i++){
			if(theForm.cid[i].checked == true){
			   number++;
			}
		}
		if(number == 0){
			alert('Bạn chưa chọn dữ liệu nào');
			return false;
		}
	} else {
		if (!$('#cid').prop('checked')) {
			alert('Bạn chưa chọn dữ liệu nào');
			return false;
		}
	}
	
	theForm.action = url;
	theForm.submit();
	return true;
}

/*
 * Check all
 */
function checkCheckBox(){
	var theForm = document.appForm;
	if (theForm.elements[i].name=='cid[]') {
        theForm.elements[i].checked = checked;
        if(theForm.elements[i].checked = true){
        	window.alert(this.value);
        }
    }
}

//Popup
function jquery_popup(p_width, p_height, p_content, class_content) {
	$('.box_overlay').remove();
	$('.block_popup').remove();
	var str_html = 	'<div class="box_overlay" onclick="close_popup()"></div>'+
				   	'<div class="block_popup">'+
				   		'<a class="popup_close" href="javascript:void(0);" onclick="close_popup()"></a>'+
						'<div class="block_content '+class_content+'">'+
							p_content +
						'</div>'+
					'</div>';
	$('body').append(str_html);

	if(p_width == 0) {
		p_width = $('.block_popup .' + class_content).width();
	}
	if(p_height == 0) {
		p_height = $('.block_popup .' + class_content).height();
	}
	$('.block_popup').css({'margin-left' : '-' + (p_width/2) + 'px', 'margin-top' : '-' + (p_height/2) + 'px'});
	$('.block_popup .block_content').css({'width' : p_width + 'px', 'height' : p_height + 'px'});
	
	$('.block_popup').fadeIn();
}

function close_popup(){
	if($('#list_dachon').length) {
		if($('#list_dachon #total_item').length) {
			$('#total_' + $('#id_type_lesson').val()).text($('#list_dachon #total_item').text());
		} else {
			$('#total_' + $('#id_type_lesson').val()).text('0');
		}
	}
	$('.block_popup .block_content').html('');
	$('.box_overlay').fadeOut().remove();
	$('.block_popup').fadeOut().remove();
}

//Popup con
function jquery_popup_con(p_width, p_height, p_content, class_content) {
	$('.box_overlay_con').remove();
	$('.block_popup_con').remove();
	var str_html = 	'<div class="block_popup_con">'+
				   		'<a class="popup_close" href="javascript:void(0);" onclick="close_popup_con()"></a>'+
						'<div class="block_content '+class_content+'">'+
							p_content +
						'</div>'+
					'</div>';
	$('.block_popup').append(str_html);

	if(p_width == 0) {
		p_width = $('.block_popup_con .' + class_content).width();
	}
	if(p_height == 0) {
		p_height = $('.block_popup_con .' + class_content).height();
	}
	$('.block_popup_con').css({'margin-left' : '-' + (p_width/2) + 'px', 'margin-top' : '-' + (p_height/2) + 'px'});
	$('.block_popup_con .block_content').css({'width' : p_width + 'px', 'height' : p_height + 'px'});
	
	$('.block_popup_con').fadeIn();
}

function close_popup_con(){
	$('.block_popup_con .block_content').html('');
	$('.box_overlay_con').fadeOut().remove();
	$('.block_popup_con').fadeOut().remove();
}

function popup_media(item, type){
	var urlMedia = $('#' + item).val();
	var urlAjax = base_url + '/default/media/popup';
	var htmlLoad = 	'<div class="box_overlay" onclick="close_popup()"><div class="dangtai"></div></div>';
	$('body').append(htmlLoad);
	$.ajax({
		type: "POST",
		url: urlAjax,
		data: {
			url : urlMedia,
			type: type
		},
		success: function(html){
			jquery_popup(500, 450, html);
	    }
	});
}

function delete_media(item){
	if(confirm('Bạn có muốn xóa thật không?')) {
		$('#' + item).val('');
		$('#view_' + item).remove();
		$('#delete_' + item).remove();
	}
}

function loading(){
	var htmlLoad = 	'<div class="box_overlay_loading" onclick="close_loading()"><div class="dangtai"></div></div>';
	$('body').append(htmlLoad);
}

function close_loading(){
	$('.box_overlay_loading').fadeOut().remove();
}

function dangxuly(){
	var htmlLoad = 	'<div class="dangxuly">Đang xử lý...</div>';
	$('body').append(htmlLoad);
}

function close_dangxuly(){
	$('.dangxuly').fadeOut().remove();
}

function ajaxPage(urlAjax, idItem){
	loading();
	$.ajax({
		type: "GET",
		url: urlAjax,
		data: "",
		success: function(html){
			$('#'+idItem).html(html);
			close_loading();
		}
	});
}

//xóa chi tiết
function popup_xoachitiet(id_pcd, id_order) {
	$('body #popup_control').remove();
	$('body').append('<div id="popup_control" class="popup_control"></div>');
	var width_dialog 	= 450;
	var height_dialog 	= 200;
	var urlAjax 		= base_url + '/shopping/adminControl/xoachitiethd';
	$.ajax({
		type: "GET",
		url: urlAjax,
		data: {
			id_pcd : id_pcd,
			id_order : id_order
		},
		beforeSend: function() {
			loading();
		},
		success: function(html){
			$("#popup_control").html(html);
			if(html == '<div class="no-access">Bạn phải đăng nhập vào hệ thống.</div>'){
				$("#popup_control").dialog({
					modal: true,
					width: 300,
					height: 120,
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
			}else if(html == '<div class="no-access">Bạn không có quyền truy cập vào chức năng này.</div>'){
				$("#popup_control").dialog({
					modal: true,
					width: 300,
					height: 120,
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
			}else {
				$("#popup_control").dialog({
					open: function() {},
					modal: true,
					width: width_dialog,
					height: height_dialog,
					buttons: {
						"Xác nhận xóa": function() {
							loading();
							$.ajax({
								url: urlAjax,
								type: 'POST',
								cache: false,
								async: true,
								data: $("#popupForm").serializeArray(),
								success: function(string){
									close_loading();
									if(string != ''){
										jquery_popup(300, 60, string, 'alert_box');
									} else {
										$('#popup_control').dialog( "close" );
										$('#popup_control').remove();
										window.location.reload();
									}
								},
								error: function (request, status, error) {
									alert(request.responseText); 
								}
							});
							return false;
						},
						"Hủy": function() {
							$( this ).dialog( "close" );
							$('#popup_control').remove();
						}
					},
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
				$(".ui-dialog-titlebar").hide();
			}
			close_loading();
		},
		error: function (request, status, error) {
			alert(request.responseText); // 
		}
		
	});
}

//thêm chi tiết
function themchitiet_hd(id_order) {
	$('body #popup_control').remove();
	$('body').append('<div id="popup_control" class="popup_control"></div>');
	var width_dialog 	= 450;
	var height_dialog 	= 350;
	var urlAjax 		= base_url + '/shopping/adminControl/themchitiethd';
	$.ajax({
		type: "GET",
		url: urlAjax,
		data: {
			id_order : id_order
		},
		beforeSend: function() {
			loading();
		},
		success: function(html){
			$("#popup_control").html(html);
			if(html == '<div class="no-access">Bạn phải đăng nhập vào hệ thống.</div>'){
				$("#popup_control").dialog({
					modal: true,
					width: 300,
					height: 120,
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
			}else if(html == '<div class="no-access">Bạn không có quyền truy cập vào chức năng này.</div>'){
				$("#popup_control").dialog({
					modal: true,
					width: 300,
					height: 120,
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
			}else {
				$("#popup_control").dialog({
					open: function() {},
					modal: true,
					width: width_dialog,
					height: height_dialog,
					buttons: {
						"Xác nhận thêm": function() {
							loading();
							$.ajax({
								url: urlAjax,
								type: 'POST',
								cache: false,
								async: true,
								data: $("#popupForm").serializeArray(),
								success: function(string){
									close_loading();
									if(string != ''){
										jquery_popup(300, 60, string, 'alert_box');
									} else {
										$('#popup_control').dialog( "close" );
										$('#popup_control').remove();
										window.location.reload();
									}
								},
								error: function (request, status, error) {
									alert(request.responseText); 
								}
							});
							return false;
						},
						"Hủy": function() {
							$( this ).dialog( "close" );
							$('#popup_control').remove();
						}
					},
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
				$(".ui-dialog-titlebar").hide();
			}
			close_loading();
		},
		error: function (request, status, error) {
			alert(request.responseText); // 
		}
		
	});
}

function popup_suachitiet(id_pcd,id_order) {
	$('body #popup_control').remove();
	$('body').append('<div id="popup_control" class="popup_control"></div>');
	var width_dialog 	= 450;
	var height_dialog 	= 450;
	var urlAjax 		= base_url + '/shopping/adminControl/suachitiet';
	$.ajax({
		type: "GET",
		url: urlAjax,
		data: {
			id_pcd: id_pcd,
			id_order : id_order
		},
		beforeSend: function() {
			loading();
		},
		success: function(html){
			$("#popup_control").html(html);
			if(html == '<div class="no-access">Bạn phải đăng nhập vào hệ thống.</div>'){
				$("#popup_control").dialog({
					modal: true,
					width: 300,
					height: 120,
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
			}else if(html == '<div class="no-access">Bạn không có quyền truy cập vào chức năng này.</div>'){
				$("#popup_control").dialog({
					modal: true,
					width: 300,
					height: 120,
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
			}else {
				$("#popup_control").dialog({
					open: function() {},
					modal: true,
					width: width_dialog,
					height: height_dialog,
					buttons: {
						"Xác nhận sửa": function() {
							loading();
							$.ajax({
								url: urlAjax,
								type: 'POST',
								cache: false,
								async: true,
								data: $("#popupForm").serializeArray(),
								success: function(string){
									close_loading();
									if(string != ''){
										jquery_popup(300, 60, string, 'alert_box');
									} else {
										$('#popup_control').dialog( "close" );
										$('#popup_control').remove();
										window.location.reload();
									}
								},
								error: function (request, status, error) {
									alert(request.responseText); // ThĂ´ng bĂ¡o lá»—i
								}
							});
							return false;
						},
						"Hủy": function() {
							$( this ).dialog( "close" );
							$('#popup_control').remove();
						}
					},
					close: function () {
						$( this ).dialog( "close" );
						$('#popup_control').remove();
					}
				});
				$(".ui-dialog-titlebar").hide();
			}
			close_loading();
		},
		error: function (request, status, error) {
			alert(request.responseText); // 
		}
		
	});
}

function jplayAudioMedia(name, file, path) {
	$('#jquery_jplayer_' + name).jPlayer({
		ready: function (event) {
			$(this).jPlayer("setMedia", {
				m4a: file,
			});
		},
		play: function() {
			$(this).jPlayer("pauseOthers");
			$(id_video + ' .jp-video-play').css({'display' : 'none'});
		},
		swfPath: path,
		supplied: "m4a, oga",
		wmode: "window",
		cssSelectorAncestor: '#jp_container_' + name,
		smoothPlayBar: true,
		keyEnabled: true
	});
}

function jplayVideoMedia(name, file, path, width, height) {
	var id_video = '#jp_container_' + name;
	$("#play_video_" + name).jPlayer({
		ready: function () {
			$(this).jPlayer("setMedia", {
				m4v: file,
				poster: ""
			});
		},
		swfPath: path,
		supplied: "webmv, ogv, m4v",
		size: {
			width: width + "px",
			height: height + "px",
			cssClass: "jp-video-360p"
		},
		play: function() {
			$(this).jPlayer("pauseOthers");
			$(id_video + ' .jp-video-play').css({'display' : 'none'});
		},
		pause: function() {
			$(id_video + ' .jp-video-play').css({'display' : 'block'});
		},
		click: function(event) {
			if(event.jPlayer.status.paused) {
				$(this).jPlayer("play");
			} else {
				$(this).jPlayer("pause");
			}
		},
		swfPath: "js",
		supplied: "webmv, ogv, m4v",
		cssSelectorAncestor: id_video,
		volume: 0.9,
		smoothPlayBar: true,
		keyEnabled: true
	});
	$(id_video).hover(
		function () {
			$('.jp-interface', this).fadeIn().delay(3000).fadeOut();
			$(this).mousemove(function(){
				if ($('.jp-interface', this).css('display') == 'none') {
					$('.jp-interface', this).fadeIn();
				}
			});
			
		},
		function () {
			$('.jp-interface', this).fadeOut();
		}
	);
	$(id_video).css({'width' : width + 'px', 'height' : height + 'px'});
	var w_interface 	= width - 10;
	$(id_video + ' .jp-interface').css({'width' : w_interface + 'px'});
	var w_play 			= $(id_video + ' .jp-play').outerWidth(true);
	var w_currentTime	= $(id_video + ' .jp-current-time').outerWidth(true);
	var w_duration		= $(id_video + ' .jp-duration').outerWidth(true);
	var w_mute			= $(id_video + ' .jp-mute').outerWidth(true);
	var w_fullScreen	= $(id_video + ' .jp-full-screen').outerWidth(true);
	var w_bar			= w_interface - w_play - w_currentTime - w_duration - w_mute - w_fullScreen - 5;
	$(id_video + ' .jp-bar').css({'width' : w_bar + 'px'});
}

function get_value(value_item, table, column) {
	var html = '';
	if(value_item != '') {
		url_item = base_url + '/daotao/ajax/get-value';
		$.ajax({
			type: "POST",
			cache: false,
			async: false,
			url: url_item,
			data: {
				value: value_item,
				table: table,
				column: column
			},
			success: function(string){
				html = $.parseJSON(string);
			},
			error: function (request, status, error) {
		        alert(request.responseText); // Thông báo lỗi
		    }
		});
	}
	return html;
}

function checkExport(){
	var total = $('.limit b').text();
	if(total > 1000) {
		alert('Dữ liệu bạn xuất quá lớn. Vui lòng lọc theo khoảng thời gian.');
		return false;
	}
	return true;
}

function addHtmlAjax(id_content, url) {
	var input_content = '';
	var i = parseInt($('#total_input').val());
	$(id_content).addClass('dangtai2');
	$.ajax({
		type: "GET",
		url: url,
		success: function(html){		
			input_content = html;
			input_content = input_content.replace(/{i}/gi,i);
			$(id_content).removeClass('dangtai2');
			$(id_content).append(input_content);
			$('.price').autoNumeric('init', { mDec: 0, aDec: ',', aSep: '.'});
		    return false;
		},
		error: function (request, status, error) {
	        alert(request.responseText); // Thông báo lỗi
	    }
	});
	$('#total_input').val(i + 1);
}

function deleteHtml(id_delete, i) {
	if(confirm('Bạn có muốn xóa')) {
		$(id_delete + i).remove();
	}
}

function tai_tinhthanh(obj) {
	var id_country = $(obj).val();
	var id_load = '#city_id';
	var urlAjax = base_url + '/default/ajax/tinhthanh';

	$.ajax({
		url: urlAjax,
		type: 'POST',
		data: {id_country : id_country},
		beforeSend: function() {
			loading();
		},
		success: function(string){
			close_loading();
			parent = $(id_load).parent();
			$(id_load).remove();
			parent.prepend(string);
		}
	});
}

function getTotal(myData){
  var myTotal = 0;
  for (var j = 0; j < myData.length; j++) {
    myTotal += (typeof myData[j] == 'number') ? myData[j] : 0;
  }
  return myTotal;
}

function plotData(id, myData, myLabel, myColor) {
  var canvas;
  var ctx;
  var lastend = 0;
  var myTotal = getTotal(myData);
  var doc;
  canvas = document.getElementById(id);
  var x = (canvas.width)/2;
  var y = (canvas.height)/2;
  var r = 100;
  
  ctx = canvas.getContext("2d");
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  for (var i = 0; i < myData.length; i++) {
    ctx.fillStyle = myColor[i];
    ctx.beginPath();
    ctx.moveTo(x,y);
    ctx.arc(x,y,r,lastend,lastend+(Math.PI*2*(myData[i]/myTotal)),false);
    ctx.lineTo(x,y);
    ctx.fill();
    
    // Now the pointers
    ctx.beginPath();
    var start = [];
    var end = [];
    var last = 0;
    var flip = 0;
    var textOffset = 0;
    var precentage = (myData[i]/myTotal)*100;
    start = getPoint(x,y,r-20,(lastend+(Math.PI*2*(myData[i]/myTotal))/2));
    end = getPoint(x,y,r+20,(lastend+(Math.PI*2*(myData[i]/myTotal))/2));
    if(start[0] <= x)
    {
      flip = -1;
      textOffset = -110;
    }
    else
    {
      flip = 1;
      textOffset = 10;
    }
    ctx.moveTo(start[0],start[1]);
    ctx.lineTo(end[0],end[1]);
    ctx.lineTo(end[0]+120*flip,end[1]);
    ctx.strokeStyle = "#bdc3c7";
    ctx.lineWidth   = 2;
    ctx.stroke();
    // The labels
    ctx.font="17px Arial";
    ctx.fillText(myLabel[i]+" "+precentage.toFixed(2)+"%",end[0]+textOffset,end[1]-4); 
    // Increment Loop
    lastend += Math.PI*2*(myData[i]/myTotal);
    
  }
}
// Find that magical point
function getPoint(c1,c2,radius,angle) {
  return [c1+Math.cos(angle)*radius,c2+Math.sin(angle)*radius];
}

function submitForm(url, type, obj, extAcp) {
	if(type == 'status') {
		var classOption = $(obj).attr('class');
		var patt	= /published/;
		var unPatt	= /unPublished/;
		var checkbox = $(obj).parent().parent().find('input[type="checkbox"]');
		$('.checkboxID').removeAttr("checked");
		checkbox.prop("checked","checked");
		
		if(classOption.match(patt)){
			if(extAcp == 1)
				document.appForm.action = url+'&type=0';
			else
				document.appForm.action = url+'?type=0';
			 document.appForm.submit();
		}else if(classOption.match(unPatt)) {
			if(extAcp == 1)
				document.appForm.action = url+'&type=1';
			else
				document.appForm.action = url+'?type=1';
			document.appForm.submit();
		}
		
	}else if(type == 'sort') {
		$('.checkboxID').each(function() { 
            this.checked = true;               
        });
		document.appForm.action = url;
		document.appForm.submit();
		
	}else if(type == 'remove'){
		var checkbox = $(obj).parent().parent().find('input[type="checkbox"]');
		$('.checkboxID').removeAttr("checked");
		checkbox.prop("checked","checked");
		
		document.appForm.action = url;
		document.appForm.submit();
	}

} 



$(document).ready(function(){
	$('.auto_numberic').autoNumeric('init', { mDec: 0, aDec: ',', aSep: '.', vMax: '10000000000'});
	// Thu nho box
	$('.toggleBlock').click(function () {
		var id = $(this).attr('id');
		if($("div#" + id).is(':hidden')){
			$(this).removeClass('db-down').addClass('db-up');
		}else{
			$(this).removeClass('db-up').addClass('db-down');
		}
		$("div#" + id).slideToggle();
	});
	
	// Tab Ui
	$(function() {
		$( "#tabs" ).tabs();
	});
	
	// Hien thong bao loi
	$("div.listError").each(function (i) {
        $('#view_' + $(this).attr('data')).html($(this).attr('rel'));
    });
	
	//date
	$(".hasDatePicker").datepicker({ 
		dateFormat: 'dd/mm/yy',
		changeMonth: true,
		changeYear: true,
		yearRange: "-100:+2"
	});
	
	$.datepicker.setDefaults($.datepicker.regional['vi']);
	$( "#tungay_validate" ).datepicker({
        changeMonth: true,
        changeYear: true,
       	dateFormat: 'dd/mm/yy',
       	monthNamesShort: ['Tháng 01', 'Tháng 02', 'Tháng 03', 'Tháng 04', 'Tháng 05', 'Tháng 06', 'Tháng 07', 'Tháng 08', 'Tháng 09', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
       	onClose: function( selectedDate ) {
			$( "#denngay_validate" ).datepicker( "option", "minDate", selectedDate );
		}
    });
    $( "#denngay_validate" ).datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd/mm/yy',
        monthNamesShort: ['Tháng 01', 'Tháng 02', 'Tháng 03', 'Tháng 04', 'Tháng 05', 'Tháng 06', 'Tháng 07', 'Tháng 08', 'Tháng 09', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
        onClose: function( selectedDate ) {
			$( "#tungay_validate" ).datepicker( "option", "maxDate", selectedDate );
		}
    });
	
	// Chan keydown enter search
	$('#keywords').keydown(function(event) {
		if (event.keyCode == 13) {
			return false;
		}
	});
	
	$(".auto_init").keypress(function (e){
		var charCode = (e.which) ? e.which : e.keyCode;
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
			return false;
	  	}
	});
});

$(document).ready(function() {
    $('#checkbox').click(function(event) {  //on click 
        if(this.checked) { // check select status
            $('.checkboxID').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"               
            });
        }else{
            $('.checkboxID').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"                       
            });         
        }
    });
    
});