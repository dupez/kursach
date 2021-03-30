<?php
	require_once 'includes/db.php';
	require_once 'includes/sessions.php';
	
	mySession_stop();
	
	header('Location: login.php');
?>

