<?php

include ('../codebase/connector/db_sqlite3.php');
include ('../codebase/connector/gantt_connector.php');

// SQLite
/* $dbtype = "SQLite3";
$res = new SQLite3(dirname(__FILE__)."/samples.sqlite"); */

// Mysql

$dbtype = "MySQL";
//$res=mysql_connect("localhost", "lifeone18_4bizn", "aGHoUSt7");
//$res=mysql_connect("localhost", "test4biz_admin", "errtawdd3");
<<<<<<< .mine
$res=mysql_connect("localhost", "hunglich66_use", "Hfkw32zF");
=======
$res=mysql_connect("localhost", "test4biz", "errtaWdd#");
>>>>>>> .r1299

/* mysql_query("set character_set_results='utf8'"); 
mysql_query( "SET CHARACTER SET utf8", $res);*/

//mysql_select_db("test4biz_use"); 
<<<<<<< .mine
mysql_select_db("hunglich66_name"); 
=======
mysql_select_db("qlsx68_name"); 
>>>>>>> .r1299
?>