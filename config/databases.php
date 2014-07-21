<?php
if(strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
	define('db_host', 'localhost');
	define('db_name', 'entre2courses');

	define('db_user', 'root');
	define('db_pass', 'root');
}
else {
	define('db_host', '127.0.0.1');
	define('db_name', 'entre2courses');

	define('db_user', 'root');
	define('db_pass', 'cFngCUMBCQBi');
}