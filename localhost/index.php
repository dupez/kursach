<?php
	require_once 'includes/db.php';
	require_once 'includes/sessions.php';
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

						<?php
							if (mySession_start())
								echo '<a href="lk.php">Личный кабинет</a>';
							else
								echo '<a href="login.php">Войти</a>';
						?>
						
					</div>
				</div>
			</div>

			<!-- КОНТЕНТ -->
			<div class="content">
				<div class="wrapper">
					<table class="catalog-list">
						
						<?php 
							$result = $db->query("SELECT * FROM `apteka_dish`");
							$items = '';
							$count = $result->rowCount();
							$price = '';
							$img = '';
							$dish_id = '';
							
							while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
								$items = $items.', '.$row['dish_tittle'];
								$price = $price.' '.$row['dish_price'];
								$img = $img.' '.$row['dish_img'];
								$dish_id = $dish_id.', '.$row['dish_id'];
								
							}

							echo '<script type="text/javascript"> 
								  	dishes_tbl("'.$count.'", "'.$items.'", "'.$price.'", "'.$img.'", "'.$dish_id.'"); 
								  </script>';  

							$result = null;
						?>

					</table>
				</div>
			</div>

			<!-- ПОДВАЛ -->
			<div class="footer">
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