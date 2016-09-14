<?php
include ('config.php');
//include("./config/database.php");
$gantt = new JSONGanttConnector($res, $dbtype);

$gantt->mix("open", 1);
  mysql_query( "SET CHARACTER SET utf8", $res);
$list = new JSONOptionsConnector($res, $dbtype);
$listcus = new JSONOptionsConnector($res, $dbtype);
//$list->render_table("lifetek_employees","person_id", "person_id(value),username(label)");
//$list->render_table("select person_id as value, first_name as label from lifetek_people where certify <> 1", "value", "value, label");
$list->render_table("select lifetek_people.person_id as value, CONCAT(lifetek_people.first_name,' ',lifetek_people.last_name) as label 
from lifetek_people right join lifetek_employees on lifetek_people.person_id = lifetek_employees.person_id where lifetek_employees.deleted = 0", "value", "value, label");
$gantt->set_options("persons", $list);
$listcus->render_table("select lifetek_people.person_id as value, CONCAT(lifetek_people.first_name,' ',lifetek_people.last_name) as label 
from lifetek_people join lifetek_customers where lifetek_people.person_id = lifetek_customers.person_id and deleted = 0", "value", "value, label");
$gantt->set_options("customer", $listcus);
$gantt->render_links("lifetek_gantt_links", "id", "source,target,type");
//$gantt->render_table("lifetek_gantt_tasks","id","start_date,duration,text,progress,parent","");
$gantt->render_table("lifetek_gantt_project","id","start_date,duration,text,progress,parent,person_id(user),customer_id(customer),report","");
$gantt->enable_order("sortorder");

?>