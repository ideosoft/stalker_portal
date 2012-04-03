<?php
session_start();

ob_start();

include "./common.php";

$error = '';

$db = new Database();

moderator_access();

$search = @$_GET['search'];
$letter = @$_GET['letter'];
$date = @$_GET['date'];
$id = @$_GET['id'];

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">

body {
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
}
td {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #000000;
}
.list{
    border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
}
a{
	color:#0000FF;
	font-weight: bold;
	text-decoration:none;
}
a:link,a:visited {
	color:#5588FF;
	font-weight: bold;
}
a:hover{
	color:#0000FF;
	font-weight: bold;
	text-decoration:underline;
}
</style>
<title>
Все Пользовательские логи
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Все Пользовательские логи&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="users.php"><< Назад</a>
    </td>
</tr>
<tr>
    <td align="center">
    <font color="Red">
    <strong>
    <? echo $error?>
    </strong>
    </font>
    <br>
    <br>
    </td>
</tr>
</table>

<?
function get_mac_by_id(){
    global $db;
    $id = $_GET['id'];
    
    $query = "select * from users where id=$id";
    $rs = $db->executeQuery($query);
    $mac = $rs->getValueByName(0, 'mac');
    return $mac;
}

function isset_date(){
    if (@$_GET['yy'] && @$_GET['mm'] && @$_GET['dd']){
        return 1;
    }else{
        return 0;
    }
}

function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';
    
    if (!$page){
        $start_page = 1;
    }else{
        $start_page = $page;
    }
    if ($page<$total_pages){
        $end_page = $page +2;
    }else{
        $end_page = $total_pages;
    }

    for($i = $start_page; $i <= $end_page; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&id='.@$_GET['id'].'&yy='.@$_GET['yy'].'&mm='.@$_GET['mm'].'&dd='.@$_GET['dd'].'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

$where = '';

if (!isset_date()){
    $date = date("Y-m-d");
}else{
    $date = $_GET['yy']."-".$_GET['mm']."-".$_GET['dd'];
}

$time_from = $date." 00:00:00";
$time_to = $date." 24:00:00";

$where .= ' order by time desc';
$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$query = "select * from user_log $where";
$rs = $db->executeQuery($query);
$total_items = $rs->getRowCount();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select * from user_log $where LIMIT $page_offset, $MAX_PAGE_ITEMS";

$rs = $db->executeQuery($query);

function construct_YY(){
    if (!$_GET['yy']){
        $year = date("Y");
    }else{
        $year = $_GET['yy'];
    }
    $yy  = "<option value='".$year."'>".$year."</option>";
    $yy .= "<option value='".($year+1)."'>".($year+1)."</option>";
    return $yy;
}

function construct_MM(){
    $month = array(
         1 => 'Январь',
         2 => 'Февраль',
         3 => 'Март',
         4 => 'Апрель',
         5 => 'Май',
         6 => 'Июнь',
         7 => 'Июль',
         8 => 'Август',
         9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь',
    );
    $mm = '';
    if (!$_GET['mm']){
        $mon = date("n");
    }else{
        $mon = $_GET['mm'];
    }
    for ($i=1;$i<=12;$i++){
        if ($i == $mon) {
            $mm .= "<option value='".$i."' selected>".$month[$i]."</option>";	
        }else{
            $mm .= "<option value='".$i."'>".$month[$i]."</option>";
        }
    }
    return $mm;
}

function construct_DD(){
    if (!$_GET['dd']){
        $day = date("j");
    }else{
        $day = $_GET['dd'];
    }
    
    for ($i=1;$i<=31;$i++){
        if ($i == $day) {
            $dd .= "<option value='".$i."' selected>".$i."</option>";	
        }else{
            $dd .= "<option value='".$i."'>".$i."</option>";
        }
    }
    return $dd;
}

function parse_param($action, $param){
    global $db;
    $name = '';
    if($action == 'play()' || $action == 'play_not_to()'){
        $sub_param = substr($param, 0, 3);
        
        if ($sub_param == 'rtp'){
            
            $query = "select * from itv where cmd='$param'";
            $rs = $db->executeQuery($query);
            $name = '[Канал] '.@$rs->getValueByName(0, 'name');
            
        }else if ($sub_param == 'aut'){
            preg_match("/auto \/media\/(\d+)\.[a-z]*$/", $param, $tmp_arr);
            $media_id = $tmp_arr[1];
            
            $query = "select * from video where id='$media_id'";
            $rs = $db->executeQuery($query);
            $name = '[Видео] '.@$rs->getValueByName(0, 'name');
            
        }else{
            $name = '';
        }
    }else{
        if($param == '""'){
            $name = '';
        }else{
            $name = $param;
        }
    }
    return $name;
}

?>
<script>
function load_log(){
    yy = document.getElementById('yy').options[document.getElementById('yy').selectedIndex].value
    mm = document.getElementById('mm').options[document.getElementById('mm').selectedIndex].value
    dd = document.getElementById('dd').options[document.getElementById('dd').selectedIndex].value
    if (dd < 10){
        dd = '0'+dd
    }
    if (mm < 10){
        mm = '0'+mm
    }
    action = 'userlog.php?yy='+yy+'&mm='+mm+'&dd='+dd+'&id='<?if(@$id){echo $id;}?>;
    document.location=action
}
</script>
<table border="0" align="center" width="620">
 <!--<tr>
        <td>
            <form action="" method="GET">
            <input type="text" name="search" value="<? //echo $search ?>"><input type="submit" value="Поиск">&nbsp;<font color="Gray">поиск по MAC</font>
            </form>
        <td>
    </tr>-->
    <tr>
        <td align="center">
        <!--<b><?php //echo @$mac?></b>&nbsp;&nbsp;&nbsp;&nbsp;Дата
        <select name="yy" id="yy">
            <? //echo construct_YY()?>
        </select>
        <select name="mm" id="mm">
            <? //echo construct_MM()?>
        </select>
        <select name="dd" id="dd">
            <? //echo construct_DD()?>
        </select>
        &nbsp;<input type="button" value="Перейти" onclick="load_log()">-->
        <td>
    </tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0' width='620'>\n";
echo "<tr>";
echo "<td class='list'><b>Время</b></td>\n";
echo "<td class='list'><b>MAC</b></td>\n";
echo "<td class='list'><b>Действие</b></td>\n";
echo "<td class='list'><b>Параметр</b></td>\n";
echo "</tr>\n";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list' nowrap>".$arr['time']."</td>\n";
    echo "<td class='list' nowrap>".$arr['mac']."</td>\n";
    echo "<td class='list'>".$arr['action']."</td>\n";
    echo "<td class='list'>".parse_param($arr['action'], $arr['param'])."</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";
echo "<table width='600' align='center' border=0>\n";
echo "<tr>\n";
echo "<td width='100%' align='center'>\n";
echo page_bar();
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</center>\n";
?>