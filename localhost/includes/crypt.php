<?php

  
// Сохраняем строку в переменную, которая
// нужно зашифровать
function encrypt ($text)
{

	// Показать оригинальную строку
	// Сохраняем метод шифрования
	$ciphering = "AES-128-CTR";
	// Используем метод шифрования OpenSSl
	$iv_length = openssl_cipher_iv_length($ciphering);
	$options = 0;
	// Ненулевой вектор инициализации для шифрования
	$encryption_iv = '1234567891011121';
	// Сохраняем ключ шифрования
	$encryption_key = "qwfskfde3sa1sw";
	// Используем функцию openssl_encrypt () для шифрования данных
	$encryption = openssl_encrypt($text, $ciphering,

				$encryption_key, $options, $encryption_iv);
	// Показать зашифрованную строку
	
	return $encryption;

} 
function decrypt ($text)
{
	$options = 0;
	$ciphering = "AES-128-CTR";
	// Ненулевой вектор инициализации для дешифрования
	$decryption_iv = '1234567891011121';
	// Сохраняем ключ дешифрования
	$decryption_key = "qwfskfde3sa1sw";
	// Используем функцию openssl_decrypt () для расшифровки данных
	$decryption=openssl_decrypt ($text, $ciphering, 
			$decryption_key, $options, $decryption_iv);

	// Показать расшифрованную строку
	return $decryption;

}

/*
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
	
	
	$sql = 'SELECT * FROM apteka_availability limit 472';
    $stmt = $db->prepare($sql);
    $stmt->execute();
	$count = 0;
	set_time_limit(6*60*60);
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
	{
		
		
		$shifr = encrypt($row['card_number']);
		
		$sql = 'UPDATE apteka_availability SET card_number = :card_number where acc_id = :acc_id';
		$stm = $db->prepare($sql);
		$stm->execute([':card_number' => $shifr, ':acc_id' => $row['acc_id']]);
		$count = $count + 1;
		
		
		
	}
	echo $count;
	
*/
?>