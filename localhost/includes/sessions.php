<?php
	require_once 'includes/db.php';

	function mySession_start()
	{
		if (isset($_COOKIE['SESSID']))
		{
			global $db;
			$sess_id = $_COOKIE['SESSID'];

			$sql = 'SELECT acc_id FROM apteka_session WHERE session_id = :sess_id';
			$stmt = $db->prepare($sql);
			$stmt->execute([':sess_id' => $sess_id]);
			$acc = $stmt->fetch(PDO::FETCH_OBJ);

			if ($acc)
			{

				$sess_update = 'UPDATE apteka_session SET session_date = :s_date, session_id = :s_id WHERE acc_id = :acc_id';
				$params_update = [ ':s_date' => date("Y-m-d H:i:s"), ':s_id' => $sess_id, ':acc_id' => $acc->acc_id];

				$stmt = $db->prepare($sess_update);
				$stmt->execute($params_update);

				return true;
			}

			return false;

		}
	}

	function mySession_write($acc_id)
	{
		$SESSID = uniqid();
		global $db;
		setcookie('SESSID', $SESSID, time()+60*60*24*30);
		$_COOKIE['SESSID'] = $SESSID;
		$sess_check = 'SELECT acc_id FROM apteka_session WHERE acc_id = :account_id';
		$stmt = $db->prepare($sess_check);
		$stmt->execute([':account_id' => $acc_id]);
		$acc = $stmt->fetch(PDO::FETCH_OBJ);

		if ($acc)
		{
			$sess_update = 'UPDATE apteka_session SET session_date = :s_date, session_id = :s_id';
			$params_update = [ ':s_date' => date("Y-m-d H:i:s"), ':s_id' => $SESSID];
			$stmt = $db->prepare($sess_update);
			$stmt->execute($params_update);
			return;
		}

		$sql = 'INSERT INTO apteka_session(session_id, session_date, acc_id) VALUES (:session_id, :session_date, :acc_id)';
		$params = [ ':session_id' => $SESSID, ':session_date' => date("Y-m-d H:i:s"), ':acc_id' => $acc_id ];
		$stmt = $db->prepare($sql);
 		$stmt->execute($params);

	}

	function mySession_stop()
	{
		global $db;
		$SESSID = $_COOKIE['SESSID'];

		$sql = 'DELETE FROM apteka_session WHERE session_id = :sess_id';
		$stmt = $db->prepare($sql);
		$stmt->execute([':sess_id' => $SESSID]);

		setcookie('ACCID', '', time());
		setcookie('SESSID', '', time());
	}

?>