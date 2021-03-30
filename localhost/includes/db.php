<?php

	$host = 'localhost';
	$db_name = 'apteka';
	$db_user = 'admin';
	$db_pass = 'rtfajksd';
	$port = '3307';

	$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

	try {
		$db = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8", $db_user, $db_pass, $options);
	} catch (PDOException $e) {
		die ('Подключение не удалось!');
	}
	
	$cur_money_sql = 'SELECT * FROM apteka_cart INNER JOIN apteka_availability USING (acc_id) WHERE acc_id = :acc_id';
	$stmt = $db->prepare($cur_money_sql);
	$stmt->execute([':acc_id' => $acc_id]);
	

?>