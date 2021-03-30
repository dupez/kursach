<?php
	require_once 'includes/sessions.php';

	if (!mySession_start())
	{
		header("location: login.php");
	}

	/* ПРОВЕРКА НА АДМИНА [НАЧАЛО] */
	$sql = 'SELECT * FROM apteka_users 
			INNER JOIN apteka_accounts ON apteka_accounts.acc_id = apteka_users.u_id 
			INNER JOIN apteka_session ON apteka_session.acc_id = apteka_accounts.acc_id 
			INNER JOIN apteka_availability ON apteka_availability.acc_id = apteka_accounts.acc_id
			WHERE apteka_session.session_id = :sess_id';

	$stmt = $db->prepare($sql);
 	$stmt->execute([':sess_id' => $_COOKIE['SESSID']]);
 	$user = $stmt->fetch(PDO::FETCH_OBJ);

	if ($user->u_role != 'admin')
 	{
 		header("location: lk.php");
 	}
 	/* ПРОВЕРКА НА АДМИНА [КОНЕЦ] */
	
	

	if (isset($_GET['action']) && $_GET['action']=="drop")
	{ 
		
		
		
		
		try
		{
			
			$db->beginTransaction();
			
			
				$sql = 'SELECT * FROM apteka_orders INNER JOIN apteka_cart USING(order_id) INNER JOIN apteka_availability USING (acc_id) WHERE apteka_orders.order_id = :order_id LIMIT 1';
				$params = [ ':order_id' => strval(trim($_GET['id'])) ];
				$stmt = $db->prepare($sql);
				$stmt->execute($params);
				$order_info = $stmt->fetch(PDO::FETCH_ASSOC);
				$order_status = $order_info['order_status'];

				$acc_id = $order_info['acc_id'];
				
				$full_price = $order_info['full_price'];
				$admin_id = '0000a0c5-2aca-4b6e-8413-e02439ceb1ec';
				
			if ($order_status != "Готово")
			{
				
				$sql = 'UPDATE apteka_availability SET amount = amount + :amount WHERE acc_id = :id';
				$params = [ ':amount' => $full_price, ':id' => $acc_id ];
				$stmt = $db->prepare($sql);
				$stmt->execute($params);
				
				$sql = 'UPDATE apteka_availability SET amount = amount - :amount WHERE acc_id = :acc_id';
				$params = [ ':amount' => $full_price, ':acc_id' => $admin_id ];
				$stmt = $db->prepare($sql);
				$stmt->execute($params);	
			}
			$sql = 'DELETE FROM apteka_cart WHERE order_id = :order_id';
			$params = [ ':order_id' => strval(trim($_GET['id'])) ];
			$stmt = $db->prepare($sql);
			$stmt->execute($params);
			$db->commit();
		}
		
		catch (Exception $e) {
					$db->rollBack();
					echo $e->getMessage();
					
				}
		header("location: orders.php");
	}

	if (isset($_GET['action']) && $_GET['action']=="update")
	{
		$sql = 'UPDATE apteka_orders SET order_status = "Готово" WHERE order_id = :order_id';
		$params = [ ':order_id' => strval(trim($_GET['id'])) ];
		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		
		header("location: orders.php");
		
	}

?>

<!DOCTYPE HTML>

<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Заказ лекарств онлайн.</title>
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
					<h1>Список заказов</h1> 
					<table> 
          					<tr> 
            					<th>Идентификатор</th> 
            					<th>Наименования</th> 
            					<th>Количество</th> 
            					<th>Статус</th> 
            					<th>Сумма</th> 
        					</tr>

							<?php
								$sql = 'SELECT *  FROM apteka_orders
										INNER JOIN apteka_cart ON apteka_cart.order_id = apteka_orders.order_id INNER JOIN apteka_dish ON apteka_cart.dish_id = apteka_dish.dish_id
									   ';

								$stmt = $db->query($sql);

								$totalorder = array('id' => 0, 'price' => 0, 'status' => '');
								$sql = 'SELECT *  FROM apteka_orders
										INNER JOIN apteka_cart ON apteka_cart.order_id = apteka_orders.order_id INNER JOIN apteka_dish ON apteka_cart.dish_id = apteka_dish.dish_id
									   ';

								$stmt = $db->query($sql);

								$totalorder = array('id' => 0, 'price' => 0, 'status' => '');

								while ($order = $stmt->fetch(PDO::FETCH_OBJ)) 
								{
							?>		
									<tr>
										<td> 
											<?php 
												if ($totalorder['id'] != $order->order_id) 
												{	
													echo $order->order_id; 
												}
												else
													echo "";
											?> 
										</td>

										<td>	
											<?php echo $order->dish_tittle ?>
										</td>

										<td>
											<?php echo $order->count ?>
										</td>

										<td>
											<?php 
												if ($totalorder['id'] !=  $order->order_id) 
												{
													$totalorder['status'] = $order->order_status;
													echo $order->order_status;
												}
											?>
										</td>

										<td>
											<?php
												$totalorder['price'] += $order->price * $order->count;
												if ($totalorder['id'] != $order->order_id) 
												{
													echo $order->full_price.' ₽';
												}
											?>
										</td>

										<td>
											<?php 
												if ($totalorder['id'] != $order->order_id) 
												{
													$totalorder['status'] = '';
													$totalorder['id'] = $order->order_id;
											?>
											

													<a 	class="button" 
													   	href="orders.php?page=orders&action=drop&id=<?php echo  $totalorder['id'] ?>" >
        												<?php
														if ($order->order_status == "Готово")
																echo "Убрать из списка";
														else echo "Отменить заказ"?>
        											</a> 

        											<a 	class="button" 
        												href="orders.php?page=orders&action=update&id=<?php echo  $totalorder['id'] ?>" >
        												Изменить статус заказа
        											</a> 
        								
										  <?php } ?>
										
										</td>
									</tr>
						  <?php } ?>
		
        			</table>
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