<?php 
require "db.php";
$data = $_POST;

if (isset($data['signin'])) {
	if (trim($data['email']) == '') {
		$error[] = 'Введите email';
	}	
	if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {}
		else{
			$error[] = "Email введен не правильно.";
		}
		if (trim($data['password']) == '') {
			$error[] = 'Введите пароль';
		}	
		$user = R::findOne('users', 'email = ?', array($data['email']));
		if ($user == 0) {
			$error[] = 'Пользователь с таким email не найден';
		}
		if ($user) {
			if (password_verify($data['password'], $user->password)) {
				$_SESSION['logged_user'] = $user;
			}
			else{
				$error[] = 'Пароль введен не верно';
			}
		}
	}
	else if(isset($_POST['button_reg']))
	{
		echo "<script> document.location.href='reg.php'</script>";
	}
	?>




	<?php if(isset($_SESSION['logged_user'])) : ?>
		<meta http-equiv="refresh" content="0; URL='/user'" />

		<?php else :  ?>

			<!DOCTYPE html>
			<html lang="en">
			<head> 
				<meta charset="UTF-8">
				<link rel="stylesheet" type="text/css" href="style/main.css">
				<link rel="icon" href="image/icon/icon_a.png" type="image/x-icon">
				<script src="js/login.js"></script>
				<title>Кристалл</title>
			</head>
			<body> 
				<div class="info_login">
					<h1>Добро пожаловать!</h1>
					<p>Кристалл - социальная сеть</p>
				</div>
				<div class="conteiner_login"> 
					<div class="contact-form"> 
						<div class="form_header"><h3>АВТОРИЗАЦИЯ</h3></div>  
						<form action="/" method="POST" id="aut">
							<div class="form"> 
								<input type="text" name="email" placeholder="Email">
								<input autocomplete="off" type="password" name="password" placeholder="Password">
								<?php echo "<p id='error_aut'> $error[0] </p>";?>	
							</div>			
						</form>	
						<div class="send-button">
							<button type="submit" form="aut" name="signin" class="btn_avt">АВТОРИЗАЦИЯ</button>
							<button type="submit" form="aut" name="button_reg" class="btn_reg">Регистрация</button>
						</div>		 	
					</div>
				</div>
			</body>
			</html>

		<?php endif; ?>
