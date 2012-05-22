<?php

	/**
	 * Punch
	 * by alsjeblaft.nl
	 */

	error_reporting(0);

	$db_host	= 'localhost';
	$db_user	= '';
	$db_pass	= '';
	$db_name	= '';
	$table		= 'punch';

	$connection = mysql_connect($db_host, $db_user , $db_pass);

	if(!$connection) die("Can't connect to database. Check username and password.");

	$selection = mysql_select_db($db_name, $connection);
	if(!$selection) die("Can't select database '".$db_name."'. Typo?");


	$current = $_GET['c'];

	$sql = "SELECT *, UNIX_TIMESTAMP(punch_in) as punch_in_ts FROM `".$table."` WHERE `desc` IS NOT NULL ORDER BY `desc` ASC";
	$query = mysql_query($sql);

	while ($row = mysql_fetch_assoc($query))
	{
		$clocks[$row['id']] = $row;
	}

	if(!is_numeric($current))
	{
		$first = key($clocks);
		header("Location: index.php?c=".$first);
	}

	$c = $clocks[$current];

	function time_to_sec($time) { 
		$hours = substr($time, 0, -6); 
		$minutes = substr($time, -5, 2); 
		$seconds = substr($time, -2); 

		return $hours * 3600 + $minutes * 60 + $seconds; 
	} 

	function sec_to_time($seconds) { 
		$hours = floor($seconds / 3600); 
		$minutes = floor($seconds % 3600 / 60); 
		$seconds = $seconds % 60; 

		return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds); 
	} 

	function printable_decimal_time($elapsed)
	{
		return number_format(round(((float) $elapsed / 3600.0),3), 2, ',', '');
	}

	if(isset($_GET['action']))
	switch($_GET['action'])
	{
		case "update":
		mysql_query("UPDATE `".$table."` SET `desc` = '".$_GET['desc']."' WHERE id = ".$current.";") or die(mysql_error());
		header("Location: index.php?c=".$current);
		break;

		case "punch":
		mysql_query("UPDATE `".$table."` SET `desc` = '".$_GET['desc']."', `start` = NOW(), `punch_in` = NOW(), `end` = '0000-00-00 00:00:00',  `elapsed` = 0, status = 1 WHERE id = ".$current.";") or die(mysql_error());
		header("Location: index.php?c=".$current);
		break;

		case "pause":
		$diff = (abs(strtotime(date('Y-m-d H:i:s', time())) - strtotime(date('Y-m-d H:i:s', $c['punch_in_ts']))));

		mysql_query("UPDATE `".$table."` SET `desc` = '".$_GET['desc']."', `punch_out` = NOW(), `end` = NOW(), `elapsed` = (elapsed+".$diff."), `status` = 0 WHERE id = ".$current." LIMIT 1;") or die(mysql_error().'<br />');
		header("Location: index.php?c=".$current);
		break;

		case "continue":
		mysql_query("UPDATE `".$table."` SET `desc` = '".$_GET['desc']."', `punch_in` = NOW(), `punch_out` = NOW(), `status` = 1 WHERE id = ".$current." LIMIT 1;") or die(mysql_error().'<br />');
		header("Location: index.php?c=".$current);
		break;

		case "reset":
		mysql_query("UPDATE `".$table."` SET `desc` = '', `start` = '0000-00-00 00:00:00', `punch_in` = '0000-00-00 00:00:00', `punch_out` = '0000-00-00 00:00:00', `end` = '0000-00-00 00:00:00',`elapsed` = 0, `status` = 0 WHERE id = ".$current." LIMIT 1;") or die(mysql_error().'<br />');
		header("Location: index.php?c=".$current);
		break;
	}


?><html>
<head>
<link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon" /> 
<link rel="icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
<title>punch. <?php echo (($c['status'] == 1) ? '(running)' : ''); ?></title>
<link rel="stylesheet" href="style.css" />
<script src="jquery-1.6.4.min.js"></script>
<script src="jquery.example.min.js"></script>
<script>
$(document).ready(function()
{

	$('input.help').example(function() {
		  return $(this).attr('title');	
	});

	// http://stackoverflow.com/questions/1989572/are-you-sure-you-want-to-delete/1989627#1989627
	$(':submit[name="action"][value="reset"]').click(function() {
		return window.confirm(this.title || 'Reset this timer? All values will be lost.');
	});

});
</script>
<link href='http://fonts.googleapis.com/css?family=Rochester:400,700' rel='stylesheet' type='text/css'>
</head>
<body>


<?php

	echo ($c['status'] == 1) ? '<div class="gps_ring"></div>' : false;
	$sw = ($c['status'] != 1) ? 'punch' : 'pause';

	echo '<div class="header">';
	echo '<ul class="clock-selector">';
	foreach($clocks as $i => $clock)
	{
		echo '<li><a class="clock'.
		($i == $_GET['c'] ? ' current' : null).
		($clock['status'] == 1 ? ' hot' : null).
		($clock['start'] == '0000-00-00 00:00:00' ? ' inactive' : null).
		'" href="index.php?c='.$i.'">'.($clock['desc'] ? $clock['desc'] : '&lt;tab&gt;').'</a></li>';
	}
	echo '<li><a class="clock inactive" href="index.php?c='.$i.'">+</a></li>';
	echo '</ul>';
	echo '</div>';

	echo '<div class="container">';
	echo '<div class="stats">';
	echo '<div class="b">';
	echo '<label>start <span>'.($c['start'] == '0000-00-00 00:00:00' ? null : strftime("%a %d %b", strtotime($c['start']))).'</span></label>';
	echo '<input type="text" readonly="readonly" style="margin-right:10px;" class="block start help" title="..." value="'.($c['start'] == '0000-00-00 00:00:00' ? null : strftime("%H:%M:%S", strtotime($c['start']))).'" />';
	echo '</div>';
	
	echo '<div class="b">';
	echo '<label>end <span>'.($c['end'] == '0000-00-00 00:00:00' ? null : strftime("%a %d %b", strtotime($c['end']))).'</span></label>';
	echo '<input type="text" readonly="readonly" class="block end help" title="..." value="'.($c['end'] == '0000-00-00 00:00:00' ? null : strftime("%H:%M:%S", strtotime($c['end']))).'" />';
	echo '</div>';
	
	echo '<div class="b">';
	echo '<label>duration</label>';
	echo '<input type="text" readonly="readonly" style="margin-right:10px;" class="block diff'.(($c['status'] != 1) ? ' ' : ' active').'" value="'.sec_to_time($c['elapsed']).'" />';
	echo '</div>';

	echo '<div class="b">';
	echo '<label>decimal</label>';
	echo '<input type="text" readonly="readonly" class="block dec'.(($c['status'] != 1 || empty($printable_decimal_time)) ? ' ' : ' active').'" value="'.printable_decimal_time($c['elapsed']).'" />';
	echo '</div>';
	echo '</div>';

	echo '<form method="get" id="change">';
	echo '<input type="hidden" name="c" value="'.$current.'" />';
	echo '<input type="text" name="desc" class="desc help" id="desc" value="'.$c['desc'].'" title="a descriptive name." maxlength="12" /><br />';
	echo '<input type="submit" class="btn" name="action" value="update"'.(($c['status'] == 1) ? ' disabled="disabled"' : null).' />';

	echo '<input type="submit" class="btn" name="action" value="reset"'.(($c['status'] == 1) ? ' disabled="disabled"' : null).' /> ';

	if($c['start'] != '0000-00-00 00:00:00' && $c['end'] != '0000-00-00 00:00:00' && $c['status'] == 0)
	{
		echo '<input class="btn big" type="submit" name="action" value="continue" />';
	}
	else
	{
		echo '<input class="btn big'.($sw == 'pause' ? ' hot' : null).'" type="submit" name="action" value="'.$sw.'" />';
	}
	echo '</form>';

?>
</div>
</body>
</html>