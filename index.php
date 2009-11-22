<?php

$username = '';
$password = '';
$database = '';
$table = 'prikklok';

$connection = mysql_connect('localhost', $username , $password);
$selection = mysql_select_db($database, $connection);
$c = mysql_fetch_assoc(mysql_query("SELECT *FROM `".$table."`"));

function time_to_sec($time)
{
	$hours = substr($time, 0, -6); 
	$minutes = substr($time, -5, 2); 
	$seconds = substr($time, -2); 

	return $hours * 3600 + $minutes * 60 + $seconds; 
} 

function sec_to_time($seconds)
{
	$hours = floor($seconds / 3600);
	$minutes = floor($seconds % 3600 / 60);
	$seconds = $seconds % 60;

	return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}

$printable_decimal_time  = number_format(round(((float) $c['elapsed'] / 3600.0),3), 2, ',', '');

switch($_GET['action'])
{
	case "start":
	mysql_query("UPDATE `prikklok` SET `start` = NOW(), `punch_in` = NOW(), `end` = '',  `elapsed` = 0, status = 1;") or die(mysql_error());
	header("Location: index.php");
	break;

	case "pause":
	$diff = (time() - strtotime($c['punch_in']));
	mysql_query("UPDATE `prikklok` SET `punch_out` = NOW(), `end` = NOW(), `elapsed` = (elapsed+".$diff."), `status` = 0 LIMIT 1;") or die(mysql_error().'<br />');
	header("Location: index.php");
	break;

	case "continue":
	mysql_query("UPDATE `prikklok` SET `punch_in` = NOW(), `punch_out` = NOW(), `status` = 1 LIMIT 1;") or die(mysql_error().'<br />');
	header("Location: index.php");
	break;

	case "reset":
	mysql_query("UPDATE `prikklok` SET `start` = '', `punch_in` = '', `punch_out` = '', `end` = '',`elapsed` = 0, `status` = 0 LIMIT 1;") or die(mysql_error().'<br />');
	header("Location: index.php");
	break;
}
	
?><html>
<head>
<link rel="shortcut icon" href="<?= (($c['status'] == 1) ? 'Throbber-small.gif' : 'favicon.ico'); ?>" type="image/vnd.microsoft.icon" /> 
<link rel="icon" href="<?= (($c['status'] == 1) ? 'Throbber-small.gif' : 'favicon.ico'); ?>" type="image/vnd.microsoft.icon" /> 
<style>
<!--
body
{
	background-color:#f9f9f9;
<? if($c['status'] == 1):?>
	background-image: url(Throbber-small.gif);
	background-repeat: no-repeat;
	background-position: top right;
<? endif;?>
	margin:0;
	padding:0;
	font: normal 11px tahoma, Arial, Helvetica, sans-serif;
}

div.stats
{
	text-align:center;
}

input.block
{
	text-align:right;
	padding:6px 10px;
	font-size:22px;
	margin-bottom:7px;
	width:109px;
	height:40px;
	background:#fff;
	border:1px solid #ccc;
}

input.start,
input.diff
{
	margin-right:5px;
}

input.active
{
	color:#ccc;
}

form
{
	text-align:center;
}

form input
{
	width:109px;
	padding:6px 10px;
}

-->
</style>
</head>
<body>
<?php

	$sw = ($c['status'] != 1) ? 'start' : 'pause';

	echo '<form method="get">';
	echo '<input type="submit" name="action" value="reset"'.(($c['status'] == 1) ? ' disabled="disabled"' : false).' /> ';

	if($c['start'] != '0000-00-00 00:00:00' && $c['end'] != '0000-00-00 00:00:00' && $c['status'] == 0)
	{
		echo '<input type="submit" name="action" value="continue" />';
	}
	else
	{
		echo '<input type="submit" name="action" value="'.$sw.'" />';
	}
	echo '</form>';

	echo '<div class="stats">';
	echo '<input type="text" readonly="readonly" class="block start" value="'.strftime("%H:%M:%S", strtotime($c['start'])).'" />';
	echo '<input type="text" readonly="readonly" class="block end'.(($c['status'] != 1 || $c['end'] == '0') ? ' ' : ' active').'" value="'.strftime("%H:%M:%S", strtotime($c['end'])).'" />';
	echo '<input type="text" readonly="readonly" class="block diff'.(($c['status'] != 1) ? ' ' : ' active').'" value="'.sec_to_time($c['elapsed']).'" />';
	echo '<input type="text" readonly="readonly" class="block dec'.(($c['status'] != 1 || empty($printable_decimal_time)) ? ' ' : ' active').'" value="'.$printable_decimal_time.'" />';
	echo '</div>';

?>
</body>
</html>