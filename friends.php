<?php 
require "db.php";
$data = $_POST;

$all_friends = R::findAll('friends');
$my_friends = array();

foreach ($all_friends as $row) {
	if ($row['id_add_user'] == $_SESSION['logged_user']->id) {
		if ($row['status'] == 1) {
			$my_friends[] = $row;
		}
	}
	if ($row['id_friend'] == $_SESSION['logged_user']->id) {
		if ($row['status'] == 1) {
			$my_friends[] = $row;
		}
	}
}
if (isset($data['delete_friend'])) {
	$delfrendID = $data['delete_friend'];
	if ($delfrendID) {
		R::hunt('friends', 'id_add_user = :id_add_user AND id_friend = :id_friend', [':id_add_user' => $_SESSION['logged_user']->id, ':id_friend' => $delfrendID]);	
		R::hunt('friends', 'id_friend = :id_friend AND id_add_user = :id_add_user', [':id_friend' => $_SESSION['logged_user']->id, ':id_add_user' => $delfrendID]);		
		header("Refresh:0");	
	}

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="icon" href="image/icon/icon_a.png" type="image/x-icon">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Друзья</title>
</head>
<body>
	<?php require "nav.php"; ?>
	
	<div class="page_friends" align="center">
		<div class="conteiner"> 
			<div class="text_block">Друзья</div>
			<div class="poisk_my_friend">
				<form autocomplete="off">
					<input type="text" placeholder="Поиск">
				</form>
			</div>

			<div class="array_friends">
				<!--Если нет друзей выводит пустую форму-->
				<?php if (count($my_friends)== 0) : ?>
					<div class="show_friends_form" >
						<div>У вас нет друзей</div>
						<div id="icon_friend" class="bx bx-customize"></div>
					</div>
				<?php endif; ?>	
				<?php for ($i = 0; $i < count($my_friends); $i++) : ?>
					<?php 
					if($my_friends[$i]['id_add_user'] == $_SESSION['logged_user']->id){
						$user = R::findOne('users', 'id = ?', array($my_friends[$i]['id_friend']));
					}else{
						$user = R::findOne('users', 'id = ?', array($my_friends[$i]['id_add_user']));
					}
					?>
					<div class="block_friends" align="left">
						<img src="image/avatars/<?php echo $user->avatar; ?>" class="friend_avatar">
						<div>
							<a href="/user?id=<?php echo $user->id; ?>"><p class="friend_name"><?php echo htmlspecialchars($user->firstname.' '.$user->lastname); ?></p></a>
							<form action="/Friends?id=<?php echo $_GET['id'];?>" method="POST">
								<button type="submit" class="delete_friend" name="delete_friend" value="<?php echo htmlspecialchars($user['id']); ?>">Удалить из друзей</button>
							</form>
						</div>
					</div> 
				<?php endfor; ?>
			</div>		
		</div>
	</div>
</body>
</html>