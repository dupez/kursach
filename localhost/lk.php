<?php
	require_once 'includes/sessions.php';
	require_once 'includes/crypt.php';
	if (!mySession_start())
	{
		header("location: login.php");
	}
	$sql = 'SELECT * FROM apteka_users 
								INNER JOIN apteka_accounts ON apteka_accounts.acc_id = apteka_users.u_id 
								INNER JOIN apteka_session ON apteka_session.acc_id = apteka_accounts.acc_id 
								INNER JOIN apteka_availability ON apteka_availability.acc_id = apteka_accounts.acc_id
								WHERE apteka_session.session_id = :sess_id';
						$stmt = $db->prepare($sql);
 						$stmt->execute([':sess_id' => $_COOKIE['SESSID']]);
 						$user = $stmt->fetch(PDO::FETCH_OBJ);
?>
<?php
	 if (isset($_POST['pay_card']))
	 {
		$sum = $_POST['sum'];
		$sql = 'UPDATE apteka_availability SET amount = amount + :sum WHERE acc_id = :acc_id';
		$params = [':sum' => $sum, ':acc_id' => $user->acc_id];
		$stmt = $db->prepare($sql);		
		$stmt->execute($params);
		header("location: lk.php");
	 }
		 

?>

<!DOCTYPE HTML>

<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Заказ лекарств онлайн</title>
		<link rel="shortcut icon" href="img/favicon.ico">
		<link rel="stylesheet" href="css/style.css" >
		<script src="js/item.js" language="javascript"></script>
	</head>

	<body>
		
		<div class="cn">

			<!-- Навигация -->
			<div class="navigation">
				<div class="wrapper">
					<div class="menu">
						<a href="index.php">Главная</a>
						<a href="cart.php">Корзина</a>
						<a href="logout.php">Выйти</a>
					</div>
				</div>
			</div>

			<!-- КОНТЕНТ -->
			<div class="content">
				<div class="wrapper">

					<b>Добро пожаловать!</b>
					<?php 

						

 						echo '<br>Вы вошли на сайт, как <u>'.$user->u_name.'</u>
 							  <br>На вашем счету '.$user->amount.' ₽
 							 ';

 						if ($user->u_role == 'admin')
 						{

 							echo "<br><br>Меню:
 							  <br><a href='orders.php '> <B><u>Заказы</u></B></a>
 							";
 						
 						}
 		
					?>
					
					<p> Пополнить счет: </p>
					<p> Ваша карта: <?php echo decrypt($user->card_number); ?></p>
					<form method="post" action="lk.php?page=lk">
						<p> Введите сумму <input type="text" name="sum" size="10"></p>
						<p><button type="submit" name="pay_card">Пополнить</button>
					</form>
					
					
					
						
						
				</div>
			</div>

			<!-- ПОДВАЛ -->
			<div class="footer" style="position: absolute; display: table;">
				<div class="wrapper">

					<ul class="head">
						<li><div class="copyright">
					 		<b>Farmani <span>&copy; 2021</span></b>
							<span>Заказ лекарств онлайн</span>
						</div></li>
						<li><div class="phone">
							<b>8 987-725-70-20</b>
							<span>Звонок бесплатный</span>
						</div></li>
					</ul>


				</div>
			</div>
		</div>

	</body>
</html>