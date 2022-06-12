<?php 
require "db.php";
$data = $_POST;

if (isset($data['signup'])) {
	$error = array();
	if (trim($data['firstname']) == '') {
		$error[] = 'Введите Имя';
	}
	if (trim($data['lastname']) == '') {
		$error[] = 'Введите фамилию';
	}	
	if (trim($data['email']) == '') {
		$error[] = 'Введите email';
	}
	if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {}
		else{ $error[] = "Email указан не правильно.";}

	if (trim($data['password']) == '') {
		$error[] = 'Введите пароль';
	}	
	if (trim($data['password_2']) == '') {
		$error[] = 'Повторите пароль';
	}
	if (R::count('users', 'email = ?', array($data['email'])) > 0) {
		$error[] = 'Данный пользователь уже зарегистрирован';
	}
	if (trim($data['password']) != trim($data['password_2'])) {
		$error[] = 'Пароли не совпадают';
	}

	if (empty($error)) {
		$user = R::dispense('users');
		$user->firstname = $data['firstname'];
		$user->lastname = $data['lastname'];
		$user->email = $data['email'];
		$user->password = password_hash($data['password'], PASSWORD_DEFAULT);
		$user->ip = $_SERVER['REMOTE_ADDR'];
		$user->date_reg = date("d.m.Y");
		$user->time_reg = date("H:i");
		$user->avatar = 'no_avatar.png';
		$user->gender = '';
		$user->date_birth = '';
		$user->city = '';
		$user->country = '';
		$user->mobile_number = '';
		$user->status = '';
		R::store($user);
		echo "<script> document.location.href='index.php'</script>";

	}else {
		
	}
		//echo "<script> document.location.href='index.php'</script>";
}

if(isset($_POST['button_login']))
{
	echo "<script> document.location.href='index.php'</script>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head> 
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="style/register.css">
	<link rel="icon" href="image/icon/icon_a.png" type="image/x-icon">
	<title>Регистрация</title>
</head>
<body> 
	<div class="conteiner_register"> 
		<div class="contact-form"> 
			<div class="form_header"><h3>РЕГИСТРАЦИЯ</h3></div>
			<form action="/reg.php" method="POST" id="register_form">
				<div class="form"> 
					<input autocomplete="off" type="text" name="firstname" placeholder="Имя">
					<input autocomplete="off" type="text" name="lastname" placeholder="Фамилия">
					<input autocomplete="off" type="text" name="email" placeholder="Email">
					<input autocomplete="off" type="password" name="password" placeholder="Пароль">
					<input autocomplete="off" type="password_2" name="password_2" placeholder="Повторите пароль">
					<?php echo "<p id='error_reg'> $error[0] </p>";?>
				</div>			
			</form>		
			<div class="send-button">
				<button type="submit" form="register_form" name="signup" class="btn_reg">РЕГИСТРАЦИЯ</button>
				<button type="submit" form="register_form" name="button_login" class="btn_avt">Авторизация</button>    
			</div>					    	
		</div>
	</div>
</body>
</html>