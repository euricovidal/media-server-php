<?php
set_time_limit(0);
$time	= explode(' ', microtime());
$start	= $time[1] + $time[0];
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<title></title>
</head>
<body>

    <h1>teste</h1>

    <img src="/11-80-60" />
    <img src="/11-800-600.html" />
    <img src="/11-45-45.png" />
    <img src="/11-245-245.gif" />
    <img src="/11-640-480.jpg" />

<?php
$time		= explode(' ', microtime());
$finish		= $time[1] + $time[0];
$total_time	= round(($finish - $start), 4);
echo 'Pagina gerada em ' . $total_time . ' segundos.' . "\n";
?>
</body>
</html>
