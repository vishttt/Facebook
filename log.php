<?php
echo 'KingNNT said post block user' . PHP_EOL;
if (isset($_GET)) {
	//print_r($_GET);	
	echo $_GET['name'] . ' | ' . $_GET['uid'] . PHP_EOL;
	file_put_contents('logs.txt', $_GET['name'] . ' | ' . $_GET['uid'] . PHP_EOL, FILE_APPEND | LOCK_EX);
}

?>