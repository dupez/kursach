<?php
	require_once 'includes/db.php';
	require_once 'includes/sessions.php';
	
	if (!mySession_start())
	{
		exit("<br><a href='login.php'>Авторизация</a><br> Необходимо авторизоватсья!");
	}

	if (isset($_GET['action']) && $_GET['action']=="add")
	{
		if (isset($_COOKIE['SESSID'])) 
		{	
			
			$sql = 'SELECT dish_id, dish_tittle, dish_price FROM apteka_dish WHERE dish_id = :dish_id';
			$params = [ ':dish_id' => strval(trim($_GET['id'])) ];
			$stmt = $db->prepare($sql);
			$stmt->execute($params);
			$dish = $stmt->fetch(PDO::FETCH_OBJ);

			if ($dish)
			{
				// информация об аккаунте
				$sql_acc = 'SELECT acc_id FROM apteka_session WHERE session_id = :sess_id';
				$stmt_acc = $db->prepare($sql_acc);
				$stmt_acc->execute([':sess_id' => $_COOKIE['SESSID']]);
				$acc = $stmt_acc->fetch(PDO::FETCH_OBJ);

			

				$get_order = $db->prepare('SELECT * FROM apteka_cart INNER JOIN apteka_dish using (dish_id) WHERE session_id = :sess_id AND acc_id = :acc_id AND dish_tittle = :dish_tittle AND dish_id = :dish_id' );
			
				$get_order->execute([ ':sess_id' => $_COOKIE['SESSID'], ':acc_id' => $acc->acc_id, ':dish_tittle' => $dish->dish_tittle,  ':dish_id' => $dish->dish_id ]);
				$order = $get_order->fetch(PDO::FETCH_OBJ);

				if ($order)
				{
					// обновляем текущую позицию



					$add_cart_sql = 'UPDATE apteka_cart SET count = :new_count
									 WHERE acc_id = :acc_id AND dish_id = :dish_id';

					$add_cart_params = [ 
										 ':new_count' => $order->count + 1, 
										 ':acc_id' => $acc->acc_id, 
										 ':dish_id' => $dish->dish_id
									   ];
				}
				else
				{
					$add_cart_sql = 'INSERT INTO apteka_cart (session_id, price, count, dish_id, acc_id) 
								 	 VALUES (:sess_id, :price, :count, :dish_id, :acc_id)';
					$add_cart_params = [ ':sess_id' => $_COOKIE['SESSID'], 
										 ':price' => $dish->dish_price, 
										 ':count' => 1, 
										 ':dish_id' => $dish->dish_id, 
										 ':acc_id' => $acc->acc_id
									   ];
				}

				$stmt = $db->prepare($add_cart_sql);
				$stmt->execute($add_cart_params);
			}
		}
	}

	if (isset($_GET['action']) && $_GET['action']=="drop")
	{
		$sql = 'DELETE FROM apteka_cart WHERE session_id = :sess_id AND dish_id = :dish_id';
		$params = [  'sess_id' => $_COOKIE['SESSID'],
				     ':dish_id' => strval(trim($_GET['id'])) 
				  ];
		$stmt = $db->prepare($sql);
		$stmt->execute($params);
	}	


    if (isset($_POST['buy']))
    { 
    	$get_items = 'SELECT * FROM apteka_cart WHERE session_id = :sess_id';
    	$stmt = $db->prepare($get_items);
		
    	$stmt->execute([ ':sess_id' => $_COOKIE['SESSID']]);

    	$order = $stmt->fetch(PDO::FETCH_OBJ);
		if ($order)
		{	
			$acc_id = $order->acc_id;
			
			$cur_money_sql = 'SELECT * FROM apteka_cart INNER JOIN apteka_availability USING (acc_id) WHERE acc_id = :acc_id';
			$stmt = $db->prepare($cur_money_sql);
			$stmt->execute([':acc_id' => $acc_id]);
			$cur_money = $stmt->fetch(PDO::FETCH_OBJ);
			$amount = $cur_money->amount;
			$admin_id = '0000a0c5-2aca-4b6e-8413-e02439ceb1ec';
			
			
			$sql = 'SELECT * FROM apteka_cart INNER JOIN apteka_dish using (dish_id) WHERE session_id = :sess_id';
        	$stmt = $db->prepare($sql);
        	$stmt->execute([ ':sess_id' => $_COOKIE['SESSID'] ]);
			
			$totalprice = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
			{
				$totalprice += $row['price'] * $row['count'];
			}
			
			if ($amount >= $totalprice)
			{
				$succes = 1;
				try{
					
					$db->beginTransaction();
					$stmt = $db->prepare('UPDATE apteka_availability SET amount = amount - :totalprice WHERE acc_id = :acc_id');
					$stmt->execute([':totalprice' => $totalprice, ':acc_id' => $acc_id]);
					
					$stmt = $db->prepare('UPDATE apteka_availability SET amount = amount + :totalprice WHERE acc_id = :acc_id');
					$stmt->execute([':totalprice' => $totalprice, ':acc_id' => $admin_id]);
					
					
					$order_id = uniqid();

					$update_cart = 'UPDATE apteka_cart SET session_id = :new_sess_id, order_id = :order_id WHERE session_id = :sess_id';
					$stmt = $db->prepare($update_cart);
					$stmt->execute([':new_sess_id' => NULL, ':sess_id' => $_COOKIE['SESSID'], ':order_id' => $order_id]);

					$add_order = 'INSERT INTO apteka_orders (order_id, order_status, user_id, full_price) 
								  SELECT
										:order_id AS order_id,
										"Обработка заказа" AS order_status,
										apteka_accounts.acc_id AS acc_id,
										:full_price AS full_price
								  FROM apteka_cart
								  INNER JOIN apteka_accounts
								  ON apteka_accounts.acc_id = apteka_cart.acc_id 
								  LIMIT 1
								 ';
					$stmt = $db->prepare($add_order);
					$stmt->execute([':order_id' => $order_id, ':full_price'=>$totalprice]);
					$db->commit();
					echo "Заказ оформлен успешно";
					
					
					

				} 
				catch (Exception $e) {
					$db->rollBack();
					echo "Ошибка: недостаточно средств" . $e->getMessage();
					$succes = 0;
				}
			
			}
			else{
				echo "Ошибка: Недостаточно средств";
			}
		}

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

					<h1>Корзина</h1> 
					<form method="post" action="cart.php?page=cart"> 
						<table> 
          					<tr> 
            					<th>Название</th> 
            					<th>Количество</th> 
            					<th>Цена</th> 
            					<th>Сумма</th> 
        					</tr> 

        					<?php 

								$sql = 'SELECT * FROM apteka_cart INNER JOIN apteka_dish using (dish_id) WHERE session_id = :sess_id';
        						$stmt = $db->prepare($sql);
        						$stmt->execute([ ':sess_id' => $_COOKIE['SESSID'] ]);

        						$products = '';
        						$counts = 0;
        						$totalprice = 0; 

       
								while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
								{
									$totalprice += $row['price'] * $row['count'];
									
							?>
								<tr> 
        							<td><?php echo $row['dish_tittle'] ?></td> 
        						    <td><?php echo $row['count'] ?></td>
        							<td><?php echo $row['price'] ?> ₽</td> 
        							<td><?php echo $row['price'] * $row['count'] ?> ₽</td> 
        							<td> <a class="button" 
        								    href="cart.php?page=cart&action=drop&id=<?php echo $row['dish_id'] ?>">
        									Убрать
        								 </a>  
        							</td>
        						</tr>

        						<?php } ?>

        					<tr> 
                       	 		<td colspan="4">К оплате: <?php echo $totalprice  ?> ₽</td> 
                    		</tr> 

                    		<button type="submit" name="buy">Оформить заказ</button> 
   
        				</table>
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

					<div class="bottom">
						<ul>
							<li><a href="index.php">На главную</a><span></span></li>
						
						</ul>
					</div>

				</div>
			</div>

		</div>

	</body>
</html>