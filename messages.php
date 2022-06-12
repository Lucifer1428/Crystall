<?php 
require "db.php";
$data = $_POST;

$all_users = R::findAll('dialogi');
$my_users_mess = array();	

$all_messages = R::findAll('message');
$my_messages = array();	

foreach ($all_users as $row) {
	if ($row['id_send'] == $_SESSION['logged_user']->id) {
		$my_users_mess[] = $row;
	}
	if ($row['id_recipient'] == $_SESSION['logged_user']->id) {
		$my_users_mess[] = $row;
	}
}

if (isset($data['test_id'])) {
	$id_dialog_mess = $data['test_id'];

	if ($id_dialog_mess) {
		$all_messages_dialog = R::find('message', 'id_dialog = ?', array($id_dialog_mess));
		foreach ($all_messages_dialog as $row) {
			$message_sender[] = $row;
		}			
	}
	R::exec('UPDATE `message` SET `status_mess` = :statusinmess WHERE id_dialog = :id_dialog', ['id_dialog' => $id_dialog_mess, 'statusinmess' => 1]);
}



//Отправка сообщения пользователю и создание БД
if (isset($data['send_mess'])) {
	$id_recipient = $data['send_mess'];
	$mess = $data['message_box'];
	if ($mess) {
		$dial_dub = R::findOne('dialogi', 'id_send = :id_send AND id_recipient = :id_recipient', [':id_send' => $_SESSION['logged_user']->id, ':id_recipient' => $id_recipient]);
		if (!$dial_dub) {
			$dial_dub = R::findOne('dialogi', 'id_recipient = :id_recipient AND id_send = :id_send', [':id_recipient' => $_SESSION['logged_user']->id, ':id_send' => $id_recipient]);	
		}			
		if (!$dial_dub) {
			$db_dialog = R::dispense('dialogi');
			$db_dialog->id_send = $_SESSION['logged_user']->id;
			$db_dialog->id_recipient = $id_recipient;
			R::store($db_dialog);	
			$dial_dub = $db_dialog;
		}

		$db_message = R::dispense('message');
		$db_message->id_dialog = $dial_dub->id;
		$db_message->id_sender_mess = $_SESSION['logged_user']->id;
		$db_message->id_recipient_mess = $id_recipient;
		$db_message->message = $mess;
		$db_message->status_mess = '0';
		$db_message->ip = $_SERVER['REMOTE_ADDR'];
		$db_message->date_publication = date("d.m.Y");
		$db_message->time_publication = date("H.i");
		R::store($db_message);

		$id_dialog_mess = $dial_dub->id;

		if ($id_dialog_mess) {
			$all_messages_dialog = R::find('message', 'id_dialog = ?', array($id_dialog_mess));
			foreach ($all_messages_dialog as $row) {
				$message_sender[] = $row;
			}			
		}	

	}
}
?>
<?php for ($i = 0; $i < count($message_sender); $i++) : ?>
	<?php 
	if($message_sender[$i]['id_sender_mess'] == $_SESSION['logged_user']->id){
		$user_info = R::findOne('users', 'id = ?', array($message_sender[$i]['id_recipient_mess']));	
	}else{
		$user_info = R::findOne('users', 'id = ?', array($message_sender[$i]['id_sender_mess']));
	}
	?>
<?php endfor; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="image/icon/icon_a.png" type="image/x-icon">
	<title>Сообщения</title>
</head>
<body>
	<?php require "nav.php"; ?>

	<div class="conteiner_messag" align="center">
		<div class="block_mess">
			<div class="message_friend">
				<div class="poisk_friends_mess">
					<form autocomplete="off">
						<input type="text" placeholder="Поиск">
					</form>
				</div>


				<?php for ($i = 0; $i < count($my_users_mess); $i++) : ?>
					<?php 
					if($my_users_mess[$i]['id_send'] == $_SESSION['logged_user']->id){
						$user = R::findOne('users', 'id = ?', array($my_users_mess[$i]['id_recipient']));
						$mess = R::findLast('message', 'id_dialog = ?', array($my_users_mess[$i]['id']));
					}else{
						$user = R::findOne('users', 'id = ?', array($my_users_mess[$i]['id_send']));
						$mess = R::findLast('message', 'id_dialog = ?', array($my_users_mess[$i]['id']));
					}
					?>
					<form method="POST">
						<button class="frinds_mess" type="submit" name="test_id" value="<?php echo $my_users_mess[$i]['id'];?>">
							<div class="block_friend_mess">
								<img src="image/avatars/<?php echo $user->avatar; ?>" class="friend_avatar_mess">
								<div class="mess_vivod">
									<p class="friend_name_mess"><?php echo htmlspecialchars($user->firstname.' '.$user->lastname); ?></p>
									<div><?php echo mb_strimwidth($mess->message, 0, 50, "...");?></div>	
								</div>
							</div>
						</button>		 	
					</form>

				<?php endfor; ?>
			</div>
			<div class="block_message_friend">
				<div class="conteiner_mess">
					<!--Только что открыли сообщение выводит ничего-->
					<?php if ($id_dialog_mess == 0) : ?>
						<div class="show_message_info" >
							<h1>Выберите чат</h1>
							<div id="icon_friend" class="bx bx-message"></div>
						</div>
					<?php endif; ?>
					<!--Показывает блок с сообшениями-->
					<?php if ($id_dialog_mess != 0 ) : ?>
						
						<div class="friends_name_text" >
							<img src="image/avatars/<?php echo $user_info->avatar; ?>" class="friend_avatar_chat">
							<div class="name_flex">
								<a class="friend_name_mess" href="/user?id=<?php echo $user_info->id; ?>"><?php echo htmlspecialchars($user_info->firstname.' '.$user_info->lastname); ?></a>
								<p>был в сети 33 минуты назад</p>						
							</div>
							<div class="right_menu"><a class="post_delete"></a></div>
						</div>
						<div class="show_message" id="box">


							<?php for ($i = 0; $i < count($message_sender); $i++) : ?>
								<?php 
								if($message_sender[$i]['id_sender_mess'] == $_SESSION['logged_user']->id){
									echo "<div class='my_sent_message'>
									<div class='messag_show'>".$message_sender[$i]['message'].
									"<div class='messag_time'>".$message_sender[$i]['time_publication']."</div>
									</div>
									</div>";

								}else{
									echo "<div class='users_message'>
									<div class='mess_friend_show'>".$message_sender[$i]['message'].
									"<div class='messag_time'>".$message_sender[$i]['time_publication']."</div>
									</div>
									</div>";
								}
								?>
							<?php endfor; ?>


						</div>
						<div class="message_add_block">
							<form action="/messages.php" method="POST" enctype="multipart/form-data">
								<input type="text" name="message_box" autocomplete="off" placeholder="Написать сообщение...">
								<button type="submit" name="send_mess" value="<?php echo ($user_info->id); ?>"><li class="bx bx-navigation"></li>Отправить</button>
							</form>
						</div>
					</div>
				</div>
			<?php endif; ?>




		</div>
	</div>
</body>
</html>
<script type="text/javascript">
	var objDiv = document.getElementById("box");
	objDiv.scrollTop = objDiv.scrollHeight;
</script>
