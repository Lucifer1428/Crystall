<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="style/light.css">
	<link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
	<title>Documednt</title>
</head>
<body>	
	<div class="nav"> 
		<div id="tasdd"></div>
		<a class="navbar-logo" href="/user">КРИСТАЛЛ</a>
		<ul class="menu-main"> 
			<div class="poisk">
				<form autocomplete="off" action="/search?id=<?php echo $_GET['id'];?>" method="POST">
					<input name="search" type="text" onkeydown="CheckInput(this)" onkeyup="CheckInput(this)" placeholder="Поиск">
					<button type="submit" name="search_button" class="button_poisk" id="button_poisk" style="display:none">Найти</button>
				</form>
			</div>
			<li class="notification" onclick="myFunction()">
				<a class="dropbtn"><div class="bx bx-bell dropbtn"></div> 
					<?php if (count($id_friend_sender)== 0) : ?><?php endif; ?>	
					<?php if (count($id_friend_sender) != 0) : ?>
						<span><?php echo count($id_friend_sender) ?></span><?php endif; ?>						
					</a>
					<div class="dropcontent" id="myDropdown">
						<div class="top_notify_header"><p>Уведомления</p></div>
						<?php if (count($id_friend_sender)== 0) : ?>
							<div class="notification_none">
								<p>У вас пока нет уведомлений</p>
								<div id="icon_notification" class="bx bx-notification-off"></div>	
							</div>
						<?php endif; ?>	
						<?php if (count($id_friend_sender) != 0) : ?>
							<div class="notification_info">
								<?php for ($i = 0; $i < count($id_friend_sender); $i++) : ?>
									<?php 
									$user_send_friend = R::findOne('users', 'id = ?', array($id_friend_sender[$i]['id_add_user']));
									?>
									<div class="notification_add_friends">
										<div class="info_notification_add_friends">
											<img src="image/avatars/<?php echo $user_send_friend->avatar; ?>" class="avatar_notification_add_friends">
											<div class="block_notification_add_friends">
												<p class="friend_name_add_friends"><?php echo ($user_send_friend->firstname.' '.$user_send_friend->lastname); ?></p>
												<div class="text_print">Вам прислали запрос в друзья</div>
											</div>									
										</div>
										<div class="block_button_add_notification">
											<form action="/user?id=<?php echo $_GET['id'];?>" method="POST">
												<button type="submit" name="accept_friend_notification" value="<?php echo $id_friend_sender[$i]->id ?>">Принять</button>
												<button type="submit" name="delte_friend_notification" value="<?php echo $user_send_friend->id ?>" class="cancellation">Отклонить</button>
											</form>
										</div>						
									</div>	
								<?php endfor; ?>
							</div>
						<?php endif; ?>	
					</div>	  	
				</li>
				<li class="right-item"><a href="/logout"><p class="bx bx-exit"></p>Выйти</a></li>
			</ul>
		</div>		
		<div class="navigation"> 
			<div class="navigation_button">
				<ul> 
					<li><a href="/user"><i class="bx bxs-user-rectangle"></i>Мой профиль</a></li>
					<li><a href="/news"><i class="bx bx-news"></i>Новости</a></li>
					<li><a href="/messages"><i class="bx bx-chat"></i>Сообщения<div class="mess_numbering"></div></a></li>
					<li><a href="/friends"><i class="bx bx-user"></i>Друзья</a></li>
					<li><a href="/gallery"><i class="bx bx-photo-album"></i>Фотографии</a></li>
				</ul>			
			</div>

			<div class="navigation_footer">
				<h1>Сменить тему сайта</h1>
				<button id="toogle-theme-btn"></button>
			</div>
		</div>	
		<script>
			/* Когда пользователь нажимает на уведомление, переключаться раскрывает содержимое */
			function myFunction() {
				document.getElementById("myDropdown").classList.toggle("show");
			}
// Закрыть раскрывающийся список, если пользователь щелкнет за его пределами.
window.onclick = function(e) {
	if (!e.target.matches('.dropbtn')) {
		var myDropdown = document.getElementById("myDropdown");
		if (myDropdown.classList.contains('show')) {
			myDropdown.classList.remove('show');
		}
	}
}
</script>
<script type="text/javascript">
	function CheckInput(e) {
		if (e.value == "") {
			document.getElementById("button_poisk").style.display = "none";
		} else {
			document.getElementById("button_poisk").style.display = "block";
		}
	}
</script>	
</body>
</html>
<script type="text/javascript">
	const toggleThemeBtn = document.getElementById('toogle-theme-btn');
	toggleThemeBtn.innerText = document.body.classList.contains('dark') ? "" : "Поставить темную тему"
	document.getElementById('tasdd').innerHTML='<img src=image/icon/icon_a.png>'

	toggleThemeBtn.addEventListener('click', () =>{
		if(document.body.classList.contains('dark')){
			document.body.classList.remove('dark')
			localStorage.theme = 'light'
			toggleThemeBtn.innerText = document.body.classList.contains('dark') ? "" : "Поставить темную тему"
			document.getElementById('tasdd').innerHTML='<img src=image/icon/icon_a.png>'
		} else {
			document.body.classList.add('dark')
			localStorage.theme = 'dark'
			toggleThemeBtn.innerText = document.body.classList.contains('dark') ? "Поставить светлую тему" : ""
			document.getElementById('tasdd').innerHTML='<img src=image/icon/icon_a_dark.png>'
		}
	});
	if (localStorage.theme == 'dark') {
		document.body.classList.add('dark')
		localStorage.theme = 'dark'	
		toggleThemeBtn.innerText = document.body.classList.contains('dark') ? "Поставить светлую тему" : ""	
		document.getElementById('tasdd').innerHTML='<img src=image/icon/icon_a_dark.png>'
	}
</script>