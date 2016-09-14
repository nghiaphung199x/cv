<?php
@session_start();
$user_files = $_SESSION['info']['acl']['media_files'];
$_disabled = true;
if(count($user_files) > 0 && $user_files['disabled'] == 1){
	$_disabled = false;
}
$_denyZipDownload = false;
if($user_files['denyZipDownload'] == 1){
	$_denyZipDownload = true;
}

$_denyExtensionRename = true;
if($user_files['denyExtensionRename'] == 1){
	$_denyExtensionRename = false;
}

$_files_upload = false;
if($user_files['files_upload'] == 1){
	$_files_upload = true;
}

$_files_delete = false;
if($user_files['files_delete'] == 1){
	$_files_delete = true;
}

$_files_copy = false;
if($user_files['files_copy'] == 1){
	$_files_copy = true;
}

$_files_move = false;
if($user_files['files_move'] == 1){
	$_files_move = true;
}

$_files_rename = false;
if($user_files['files_rename'] == 1){
	$_files_rename = true;
}

$_dirs_create = false;
if($user_files['dirs_create'] == 1){
	$_dirs_create = true;
}

$_dirs_delete = false;
if($user_files['dirs_delete'] == 1){
	$_dirs_delete = true;
}

$_dirs_rename = false;
if($user_files['dirs_rename'] == 1){
	$_dirs_rename = true;
}


$_CONFIG = array(

	//De gia tri True neu khong cho phep upload. (Kiem tra dieu kien dang nhap moi cho phep upload)
	'disabled' => $_disabled,
	//De true cho phep nen cac tạp tin thanh file zip. Hosting can phai ho tro
    'denyZipDownload' => $_denyZipDownload,
	//De true de khong cho phep kiem tra phien ban update khi vao xem muc About
    'denyUpdateCheck' => true,
	//De true de khong cho phep doi ten phan mo rong cua tap tin
    'denyExtensionRename' => $_denyExtensionRename,
	//Giao dien cua trinh quan ly. (dark)
    'theme' => "oxygen",
	//Duong dan URL den thu muc luu file
    'uploadURL' => "../../files/editor-upload",
    //Thiet lap nay duoc su dung neu KCFinder khong the tu dong lay duong dan cua tap tin
    'uploadDir' => "",

    'dirPerms' => 0755,
    'filePerms' => 0644,

	//Xac dinh quyen cua nguoi dung voi cac tap tin va thu muc
    'access' => array(

        'files' => array(
            'upload' => $_files_upload,
            'delete' => $_files_delete,
            'copy' => $_files_copy,
            'move' => $_files_move,
            'rename' => $_files_rename
        ),

        'dirs' => array(
            'create' => $_dirs_create,
            'delete' => $_dirs_delete,
            'rename' => $_dirs_rename
        )
    ),

	//Cac dinh dang mo rong se bị cam khi upload hay doi ten dinh dang
    'deniedExts' => "exe com msi bat php phps phtml php3 php4 cgi pl",

	//Dinh dang lai kieu cho cac tap tin
    'types' => array(

		'files' => array(),
	 
		'flash' => array(
			'type' => "swf",
			'denyZipDownload' => false
		),
	 
		'images' => array(
			'type' => "*img",
			'thumbWidth' => 300,
			'thumbHeight' => 300
		)
    ),

    'filenameChangeChars' => array(
    	'à'=>'a','ả'=>'a','ã'=>'a','á'=>'a','ạ'=>'a','ă'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ắ'=>'a','ặ'=>'a','â'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ấ'=>'a','ậ'=>'a',
    	'À'=>'a','Ả'=>'a','Ã'=>'a','Á'=>'a','Ạ'=>'a','Ă'=>'a','Ằ'=>'a','Ắ'=>'a','Ẵ'=>'a','Ẳ'=>'a','Ặ'=>'a','Â'=>'a','Ầ'=>'a','Ẩ'=>'a','Ẫ'=>'a','Ấ'=>'a','Ậ'=>'a',
    	'đ'=>"d",'Đ'=>"d",
    	'è'=>'e','ẻ'=>'e','ẽ'=>'e','é'=>'e','ẹ'=>'e','ê'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ế'=>'e','ệ'=>'e',
    	'È'=>'e','Ẻ'=>'e','Ẽ'=>'e','É'=>'e','Ẹ'=>'e','Ê'=>'e','Ề'=>'e','Ể'=>'e','Ễ'=>'e','Ế'=>'e','Ệ'=>'e',
    	'ì'=>'i','ỉ'=>'i','ĩ'=>'i','í'=>'i','ị'=>'i',
    	'Ì'=>'i','Ỉ'=>'i','Ĩ'=>'i','Í'=>'i','Ị'=>'i',
    	'ò'=>'o','ỏ'=>'o','õ'=>'o','ó'=>'o','ọ'=>'o','ô'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ố'=>'o','ộ'=>'o','ơ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ớ'=>'o','ợ'=>'o',
    	'Ò'=>'o','Ỏ'=>'o','Õ'=>'o','Ó'=>'o','Ọ'=>'o','Ô'=>'o','Ồ'=>'o','Ổ'=>'o','Ỗ'=>'o','Ố'=>'o','Ộ'=>'o','Ơ'=>'o','Ờ'=>'o','Ở'=>'o','Ỡ'=>'o','Ớ'=>'o','Ợ'=>'o',
    	'ù'=>'u','ủ'=>'u','ũ'=>'u','ú'=>'u','ụ'=>'u','ư'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ứ'=>'u','ự'=>'u',
    	'Ù'=>'u','Ủ'=>'u','Ũ'=>'u','Ú'=>'u','Ụ'=>'u','Ư'=>'u','Ừ'=>'u','Ử'=>'u','Ữ'=>'u','Ứ'=>'u','Ự'=>'u',
    	'ỳ'=>'y','ỷ'=>'y','ỹ'=>'y','ý'=>'y',
    	'Ỳ'=>'y','Ỷ'=>'y','Ỹ'=>'y','Ý'=>'y',
        ' ' => "-",'  ' => "-",'   ' => "-",'     ' => "-",
        '!'=>"-",'@'=>"-",'$'=>"-",'%'=>"-",'^'=>"-",'*'=>"-",'('=>"-",')'=>"-",'+'=>"-",'='=>"-",'<'=>"-",'>'=>"-",'?'=>"-",'/'=>"-",','=>"-",
        ':'=>"-",'\''=>"-",'\"'=>"-",'&'=>"-",'#'=>"-",'['=>"-",']'=>"-",'\\'=>"-","~"=>"-","_"=>"-","{"=>"-","}"=>"-","`"=>"-",
        ';' => "-"
    ),

    'dirnameChangeChars' => array(
        'à'=>'a','ả'=>'a','ã'=>'a','á'=>'a','ạ'=>'a','ă'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ắ'=>'a','ặ'=>'a','â'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ấ'=>'a','ậ'=>'a',
    	'À'=>'a','Ả'=>'a','Ã'=>'a','Á'=>'a','Ạ'=>'a','Ă'=>'a','Ằ'=>'a','Ắ'=>'a','Ẵ'=>'a','Ẳ'=>'a','Ặ'=>'a','Â'=>'a','Ầ'=>'a','Ẩ'=>'a','Ẫ'=>'a','Ấ'=>'a','Ậ'=>'a',
    	'đ'=>"d",'Đ'=>"d",
    	'è'=>'e','ẻ'=>'e','ẽ'=>'e','é'=>'e','ẹ'=>'e','ê'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ế'=>'e','ệ'=>'e',
    	'È'=>'e','Ẻ'=>'e','Ẽ'=>'e','É'=>'e','Ẹ'=>'e','Ê'=>'e','Ề'=>'e','Ể'=>'e','Ễ'=>'e','Ế'=>'e','Ệ'=>'e',
    	'ì'=>'i','ỉ'=>'i','ĩ'=>'i','í'=>'i','ị'=>'i',
    	'Ì'=>'i','Ỉ'=>'i','Ĩ'=>'i','Í'=>'i','Ị'=>'i',
    	'ò'=>'o','ỏ'=>'o','õ'=>'o','ó'=>'o','ọ'=>'o','ô'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ố'=>'o','ộ'=>'o','ơ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ớ'=>'o','ợ'=>'o',
    	'Ò'=>'o','Ỏ'=>'o','Õ'=>'o','Ó'=>'o','Ọ'=>'o','Ô'=>'o','Ồ'=>'o','Ổ'=>'o','Ỗ'=>'o','Ố'=>'o','Ộ'=>'o','Ơ'=>'o','Ờ'=>'o','Ở'=>'o','Ỡ'=>'o','Ớ'=>'o','Ợ'=>'o',
    	'ù'=>'u','ủ'=>'u','ũ'=>'u','ú'=>'u','ụ'=>'u','ư'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ứ'=>'u','ự'=>'u',
    	'Ù'=>'u','Ủ'=>'u','Ũ'=>'u','Ú'=>'u','Ụ'=>'u','Ư'=>'u','Ừ'=>'u','Ử'=>'u','Ữ'=>'u','Ứ'=>'u','Ự'=>'u',
    	'ỳ'=>'y','ỷ'=>'y','ỹ'=>'y','ý'=>'y',
    	'Ỳ'=>'y','Ỷ'=>'y','Ỹ'=>'y','Ý'=>'y',
        ' ' => "-",'  ' => "-",'   ' => "-",'     ' => "-",
        '!'=>"-",'@'=>"-",'$'=>"-",'%'=>"-",'^'=>"-",'*'=>"-",'('=>"-",')'=>"-",'+'=>"-",'='=>"-",'<'=>"-",'>'=>"-",'?'=>"-",'/'=>"-",','=>"-",'.'=>"-",
        ':'=>"-",'\''=>"-",'\"'=>"-",'&'=>"-",'#'=>"-",'['=>"-",']'=>"-",'\\'=>"-","~"=>"-","_"=>"-","{"=>"-","}"=>"-","`"=>"-",
        ';' => "-"
    ),

    'mime_magic' => "",

    'maxImageWidth' => 1366,
    'maxImageHeight' => 2000,

    'thumbWidth' => 100,
    'thumbHeight' => 100,

    'thumbsDir' => "_thumbs",

    'jpegQuality' => 90,

    'cookieDomain' => "",
    'cookiePath' => "",
    'cookiePrefix' => 'KCFINDER_',

    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION CONFIGURATION
    '_check4htaccess' => true,
    //'_tinyMCEPath' => "/tiny_mce",

    '_sessionVar' => &$_SESSION['KCFINDER'],
    //'_sessionLifetime' => 30,
    //'_sessionDir' => "/full/directory/path",

    //'_sessionDomain' => ".mysite.com",
    //'_sessionPath' => "/my/path",
);

?>