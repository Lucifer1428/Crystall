<?php 
require "db.php";
$data = $_POST; 
//Поиск	
$search_user = array();
if (isset($data['search_button'])) {
	$search = $data['search'];
	if ($search) {
		$friend_print = R::getAll("SELECT * FROM `users` WHERE `firstname` LIKE '".$search."%' OR `lastname` LIKE '".$search."%' OR concat(users.firstname,' ',users.lastname) LIKE '".$search."%'");
	}
	foreach ($friend_print as $rowpost) {
		$search_user[] = $rowpost;		
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="image/icon/icon_a.png" type="image/x-icon">
	<title>Поиск</title>
</head>
<body>
	<?php require "nav.php"; ?>	
	<div class="conteiner_search">
		<div class="content_search">
			<div class="text_search">Результаты поиска</div>
			<div class="block_search_user">


				<?php if (count($search_user)== 0) : ?>
					<div class="show_search_none" >
						<div>Ничего не найдено</div>
					</div>
				<?php endif; ?>	



				<?php for ($i = 0; $i < count($search_user); $i++) : ?>
					<?php 
					$user_search = R::findOne('users', 'id = ?', array($search_user[$i]['id']));
					?>			
					<div class="show_users">
						<img src="image/avatars/<?php echo $user_search->avatar; ?>" class="search_avatar">
						<div class="search_info_user">
							<a href="/user?id=<?php echo $user_search->id; ?>"><p class="search_name"><?php echo htmlspecialchars($user_search->firstname.' '.$user_search->lastname); ?></p></a>
							<div class="search_info">
								<div class="search_text_info">
									<p>Пол:</p>
									<p>Страна:</p>
									<p>Город:</p>
								</div>
								<div class="search_info_dan">
									<p><?php 
									if (empty($user_search->gender != null)) {
										echo "Нет информации";
									}
									else{
										echo $user_search->gender;
									}
									?>
								</p>
								<p><?php 
								if (empty($user_search->country != null)) {
									echo "Нет информации";
								}
								else{
									echo $user_search->country;
								}
								?>
							</p>
							<p><?php 
							if (empty($user_search->city != null)) {
								echo "Нет информации";
							}
							else{
								echo $user_search->city;
							}
							?>
						</p>
					</div>						
				</div>
			</div>
		</div>
	<?php endfor; ?>





</div>
</div>
</div>
</body>
</html>