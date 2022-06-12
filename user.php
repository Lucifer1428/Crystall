<?php 
require "db.php";
$data = $_POST;

if ($_GET['id']== '') {
	header('Location: /user?id='.$_SESSION['logged_user']->id);
}

if ($_GET['id'] == $_SESSION['logged_user']->id) {
	$position = 'access';
}else{
	$position = 'view';
}

$user = R::findOne('users', 'id = ?', array($_GET['id']));

if (isset($data['send_post'])) {
	$post = $data['post'];
	if ($post) {
		$db_post = R::dispense('posts');
		$db_post->id_user = $_SESSION['logged_user']->id;
		$db_post->post = $post;
		$db_post->ip = $_SERVER['REMOTE_ADDR'];
		$db_post->date_publication = date("d.m.Y");
		$db_post->time_publication = date("H.i");
		R::store($db_post);
		header("Refresh:0");

	}
}

//Выгрузка всех постов и фильтрация по id
$all_post = R::findAll('posts');
$user_posts = array();

foreach ($all_post as $rowpost) {
	if ($rowpost['id_user'] == $_GET['id']) {
		$user_posts[] = $rowpost;		
	}
}
//Удаление постов
if (isset($data['delete_button_post'])) {
	$delpost = $data['delete_button_post'];
	if ($delpost) {
		$book = R::load('posts', $delpost);
		R::trash($book);
		header("Refresh:0");
	}
}
//отправить запрос в друзья 
if (isset($data['add_friend'])) {
	$id_user = $data['id_user'];
	if ($id_user) {
		$add_f = R::dispense('friends');
		$add_f->id_add_user = $_SESSION['logged_user']->id;
		$add_f->id_friend = $id_user;
		$add_f->status = 0;
		R::store($add_f);
	}
}
//подгружаем таблицу с друзьями
$all_friends = R::findAll('friends');
$friend_status = 2;
foreach ($all_friends as $row) {
	if ($row['id_add_user'] == $_SESSION['logged_user']->id) {
		if ($row['id_friend'] == $_GET['id']) {
			if ($row['status'] == 0) {
				$friend_status = 0;
			}
			if ($row['status'] == 1) {
				$friend_status = 1;
			}
		}
	}
}
//Проверка на отправку заявки в друзья Кнопка принять запрос вместо добавить
$add_request = 2;
$id_request = '';
foreach ($all_friends as $row) {
	if ($row['id_friend'] == $_SESSION['logged_user']->id) {
		if ($row['id_add_user'] == $_GET['id']) {
			if ($row['status'] == 0) {
				$add_request = 0;
				$id_request = $row['id'];
			}
			if ($row['status'] == 1) {
				$friend_status = 1;
			}
		}
	}
}
//Принять в друзья
if (isset($data['accept_request'])) {
	$id_request = $data['id_request'];
	if ($id_request) {
		$request = R::findOne('friends', 'id = ?', array($id_request));
		$request->status = 1;
		R::store($request);
		header("Refresh:0");
	}
}
//Удалить из друзей
if (isset($data['delete_friend'])) {
	$delfrendID = $data['delete_friend'];
	if ($delfrendID) {
		R::hunt('friends', 'id_add_user = :id_add_user AND id_friend = :id_friend', [':id_add_user' => $_SESSION['logged_user']->id, ':id_friend' => $delfrendID]);	
		R::hunt('friends', 'id_friend = :id_friend AND id_add_user = :id_add_user', [':id_friend' => $_SESSION['logged_user']->id, ':id_add_user' => $delfrendID]);	
		header("Refresh:0");	
	}
}
//Отмена заявки другу
if (isset($data['cancellation_friend'])) {
	$delfrendID = $data['cancellation_friend'];
	if ($delfrendID) {
		R::hunt('friends', 'id_add_user = :id_add_user AND id_friend = :id_friend', [':id_add_user' => $_SESSION['logged_user']->id, ':id_friend' => $delfrendID]);	
		R::hunt('friends', 'id_friend = :id_friend AND id_add_user = :id_add_user', [':id_friend' => $_SESSION['logged_user']->id, ':id_add_user' => $delfrendID]);	
		header("Refresh:0");	
	}
}
//Мои друзья
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

//друзья ДРУГА
$all_friends = R::findAll('friends');
$friends_friends = array();

foreach ($all_friends as $row) {
	if ($row['id_friend'] == $user->id) {
		if ($row['status'] == 1) {
			$friends_friends[] = $row;
		}
	}
	if ($row['id_add_user'] == $user->id) {
		if ($row['status'] == 1) {
			$friends_friends[] = $row;
		}
	}
}
//Загрузка аватарки
function loadAvatar($avatar){
	$type = $avatar['type'];
	$name = md5(microtime()).'.'.substr($type, strlen("image/"));
	$dir = 'image/avatars/';
	$uploadfile = $dir.$name;

	if(move_uploaded_file($avatar['tmp_name'], $uploadfile)){
		$user = R::findOne('users', 'id = ?', array($_SESSION['logged_user']->id));
		$user->avatar = $name;
		R::store($user);
	}else{
		return false;
	}
	return true;
}

if (isset($data['set_avatar'])) {
	$avatar = $_FILES['avatar'];
	if(avatarSecurity($avatar)) loadAvatar($avatar);
	header("Refresh:0");
}

//editor save button
if (isset($data['edit_save'])) {
	$editor = R::findOne('users', 'id = ?', array($_SESSION['logged_user']->id));
	$editor->lastname = $data['lastname'];
	$editor->firstname = $data['firstname'];
	$editor->gender = $data['gender'];
	$editor->date_birth = $data['user_day'].'.'.$data['user_month'].'.'.$data['user_year'];
	$editor->city = $data['city'];
	$editor->country = $data['country'];
	$editor->mobile_number = $data['number'];
	R::store($editor);
	header("Refresh:0");
}
//status save button
if (isset($data['status_save'])) {
	$status_user = R::findOne('users', 'id = ?', array($_SESSION['logged_user']->id));
	$status_user->status = $data['status_user'];
	R::store($status_user);
	header("Refresh:0");
}
//Отправка сообщения пользователю и создание БД
if (isset($data['send_mess'])) {
	$id_recipient = $data['send_mess'];
	$mess = $data['message'];
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
		header("Refresh:0");
	}
}
$all_friends_for_notification = R::findAll('friends');
$id_friend_sender = array();

foreach ($all_friends_for_notification as $row) {
	if ($row['id_friend'] == $_SESSION['logged_user']->id) {
		if ($row['status'] == 0) {
			$id_friend_sender[] = $row;
		}
	}
}
//Принять в друзья из уведомлений
if (isset($data['accept_friend_notification'])) {
	$id_friend_row = $data['accept_friend_notification'];
	if ($id_friend_row) {
		$friend_print = R::findOne('friends', 'id = ?', array($id_friend_row));
		$friend_print->status = 1;
		R::store($friend_print);
		header("Refresh:0");
	}
}
//Отмена заявки другу из уведомлени
if (isset($data['delte_friend_notification'])) {
	$delfrendIDnotif = $data['delte_friend_notification'];
	if ($delfrendIDnotif) {
		R::hunt('friends', 'id_add_user = :id_add_user AND id_friend = :id_friend', [':id_add_user' => $_SESSION['logged_user']->id, ':id_friend' => $delfrendIDnotif]);	
		R::hunt('friends', 'id_friend = :id_friend AND id_add_user = :id_add_user', [':id_friend' => $_SESSION['logged_user']->id, ':id_add_user' => $delfrendIDnotif]);	
		header("Refresh:0");	
	}

}
//Загрузка фотографий в галерею
function loadPhoto($photo){
	$type = $photo['type'];
	$name = md5(microtime()).'.'.substr($type, strlen("image/"));
	$dir = 'image/gallery/';
	$uploadfile = $dir.$name;

	if(move_uploaded_file($photo['tmp_name'], $uploadfile)){
		$add_photo = R::dispense('gallery');
		$add_photo->id_user_photo = $_SESSION['logged_user']->id;
		$add_photo->images = $name;
		$add_photo->ip = $_SERVER['REMOTE_ADDR'];
		$add_photo->date_publication_image = date("d.m.Y");
		$add_photo->time_publication_image = date("H.i");
		R::store($add_photo);
	}else{
		return false;
	}
	return true;
}
//Загрузка в галерею
if (isset($data['set_photo_add'])) {
	$photo = $_FILES['photo'];
	if(photoSecurity($photo)) loadPhoto($photo);
	header("Refresh:0");
}
//Выгрузка фотографий по id
$all_image = R::findAll('gallery', 'id_user_photo = ? ORDER BY id DESC LIMIT 4',array($_GET['id']));
$all_image_user = R::findAll('gallery', 'id_user_photo = ?',array($_GET['id']));
$user_photos = array();

foreach ($all_image as $rowpost) {
	if ($rowpost['id_user_photo'] == $_GET['id']) {
		$user_photos[] = $rowpost;		
	}
}

?>
<script type="text/javascript">
//показывает блок загрузки аватарки
function FormAvatar() {
	document.getElementById('conteiner_avatar').style.display = 'block';
}
function CloseFormAvatar() {
	document.getElementById('conteiner_avatar').style.display = 'none';
}
//показывает блок редактирование информации пользователя
function FormRedaktor() {
	document.getElementById('edit_profile').style.display = 'block';
}
function CloseFormRedaktor() {
	document.getElementById('edit_profile').style.display = 'none';
}
//показывает блок редактирование информации пользователя
function StatusOn() {
	document.getElementById('status_block').style.display = 'block';
	document.getElementById('user_status').style.display = 'none';
}
function StatusOff() {
	document.getElementById('user_status').style.display = 'block';
	document.getElementById('status_block').style.display = 'none';
}
function FormWriteMessage() {
	document.getElementById('block_write_a_message').style.display = 'block';
}
function CloseWriteMessage() {
	document.getElementById('block_write_a_message').style.display = 'none';
}
</script>
<!DOCTYPE html>
<html lang="en">
<head> 
	<meta charset="UTF-8">
	<link rel="icon" href="image/icon/icon_a.png" type="image/x-icon">
	<title><?php echo $user->firstname.' '.$user->lastname; ?></title>
</head>
<body> 
	<!--Фон для показа фотографий-->
	<div class="slider">
		<div class="slider_container">
			<button class="slider_btn slider_btn_left">
				<i class="fas fa-chevron-left"></i>
			</button>
			<button class="slider_btn slider_btn_right">
				<i class="fas fa-chevron-right"></i>
			</button>      	
		</div>
		<button class="slider_close">
			<i class="far fa-times-circle"></i>
		</button>     
	</div>	
	<!--Форма добавления фотографий-->
	<div class="conteiner_photo_add">
		<div class="add_photo_block">
			<div class="text_photo_form"><p>Загрузка новой фотографии</p></div>	
			<div class="block_add_photo">
				<div class="info_text_phpto_form">Вы можете загрузить изображение в формате JPG, GIF или PNG.</div>
				<div class="photo_selection">
					<form action="/user?id=<?php echo $_GET['id'];?>" method="POST" enctype="multipart/form-data" id="form_photo_add">
						<input type="file" name="photo">
					</form>					
				</div>
			</div>  
			<div class="save_button_photo">
				<button form="form_photo_add" type="submit" name="set_photo_add">Добавить новую фотографию</button>
			</div>			  
		</div>
		<button class="add_photo_close">
			<i class="far fa-times-circle"></i>
		</button>     
	</div>	
	<!--Установка аватарки пользователя-->
	<div class="conteiner_avatar" id="conteiner_avatar">
		<div class="add_avatar">
			<div class="avatar_text"><p>Загрузка аватара</p>
				<button onclick="CloseFormAvatar()">&#10006;</button>
			</div>
			<div class="OwnerAvatarEditor__content">
				<div class="OwnerAvatarEditor__desc">Друзьям будет проще узнать вас, если вы загрузите свою настоящую фотографию.<br>Вы можете загрузить изображение в формате JPG, GIF или PNG.</div>
				<div class="photo_selection">
					<form action="/user?id=<?php echo $_GET['id'];?>" method="POST" enctype="multipart/form-data" id="form_avatar">
						<input type="file" name="avatar">
					</form>					
				</div>
			</div>
			<div class="save_button_avatar">
				<button form="form_avatar" type="submit" name="set_avatar">Сохранить</button>
			</div>			
		</div>
	</div>
	<!--Форма редактирования личной информации-->
	<div class="edit_profile" id="edit_profile">
		<div class="edit_user">
			<div class="edit_text"><p>Редактирование информации</p>
				<button onclick="CloseFormRedaktor()">&#10006;</button>
			</div>	
			<div class="editor_content">
				<div class="editor_input">
					<form action="/user?id=<?php echo $_GET['id'];?>" method="POST" id="editor_form">
						<div class="name_input">
							<p>Имя:</p>
							<input value="<?php echo $user->lastname; ?>" type="text" name="lastname" placeholder="Имя" autocomplete="off">	
						</div>
						<div class="name_input">
							<p>Фамилия:</p>
							<input value="<?php echo $user->firstname; ?>" type="text" name="firstname" placeholder="Фамилия" autocomplete="off">	
						</div>
						<div class="gender_input">
							<p>Пол:</p>
							<select name="gender" >
								<option selected value="<?php echo $user->gender; ?>" >
									<?php 
									if (empty($user->gender != null)) {
										echo "Пол";
									}
									else{
										echo $user->gender;
									}
									?>	
								</option>
								<option id="man_none" name="Мужской">Мужской</option>
								<option id="woman_none" name="Женский">Женский</option>
							</select>
						</div>	
						<div class="name_input_birth">
							<p>Дата рождения:</p>
							<select name="user_day">
								<option disabled selected>Число</option>
								<option name="01">01</option>
								<option name="02">02</option>
								<option name="03">03</option>
								<option name="04">04</option>
								<option name="05">05</option>
								<option name="06">06</option>
								<option name="07">07</option>
								<option name="08">08</option>
								<option name="09">09</option>
								<option name="10">10</option>
								<option name="11">11</option>
								<option name="12">12</option>
								<option name="13">13</option>
								<option name="14">14</option>
								<option name="15">15</option>
								<option name="16">16</option>
								<option name="17">17</option>
								<option name="18">18</option>
								<option name="19">19</option>
								<option name="20">20</option>
								<option name="21">21</option>
								<option name="22">22</option>
								<option name="23">23</option>
								<option name="24">24</option>
								<option name="25">25</option>
								<option name="26">26</option>
								<option name="27">27</option>
								<option name="28">28</option>
								<option name="29">29</option>
								<option name="30">30</option>
								<option name="31">31</option>
							</select>
							<select name="user_month">
								<option disabled selected>Месяц</option>
								<option value="01">Январь</option>
								<option value="02">Февраль</option>
								<option value="03">Март</option>
								<option value="04">Апрель</option>
								<option value="05">Май</option>
								<option value="06">Июнь</option>
								<option value="07">Июль</option>
								<option value="08">Август</option>
								<option value="09">Сентябрь</option>
								<option value="10">Октябрь</option>
								<option value="11">Ноябрь</option>
								<option value="12">Декабрь</option>
							</select>
							<select class="input_left" name="user_year">
								<option disabled selected>Год</option>
								<option name="1950">1950</option>
								<option name="1951">1951</option>
								<option name="1952">1952</option>
								<option name="1953">1953</option>
								<option name="1954">1954</option>
								<option name="1955">1955</option>
								<option name="1956">1956</option>
								<option name="1957">1957</option>
								<option name="1958">1958</option>
								<option name="1959">1959</option>
								<option name="1960">1960</option>
								<option name="1961">1961</option>
								<option name="1962">1962</option>
								<option name="1961">1961</option>
								<option name="1964">1964</option>
								<option name="1965">1965</option>
								<option name="1966">1966</option>
								<option name="1967">1967</option>
								<option name="1968">1968</option>
								<option name="1969">1969</option>
								<option name="1970">1970</option>
								<option name="1972">1972</option>
								<option name="1972">1972</option>
								<option name="1973">1973</option>
								<option name="1974">1974</option>
								<option name="1975">1975</option>
								<option name="1976">1976</option>
								<option name="1977">1977</option>
								<option name="1978">1978</option>
								<option name="1979">1979</option>
								<option name="1980">1980</option>
								<option name="1981">1981</option>
								<option name="1982">1982</option>
								<option name="1983">1983</option>
								<option name="1984">1984</option>
								<option name="1985">1985</option>
								<option name="1986">1986</option>
								<option name="1987">1987</option>
								<option name="1988">1988</option>
								<option name="1989">1989</option>
								<option name="1990">1990</option>
								<option name="1991">1991</option>
								<option name="1992">1992</option>
								<option name="1993">1993</option>
								<option name="1994">1994</option>
								<option name="1995">1995</option>
								<option name="1996">1996</option>
								<option name="1997">1997</option>
								<option name="1998">1998</option>
								<option name="1999">1999</option>
								<option name="2000">2000</option>
								<option name="2001">2001</option>
								<option name="2002">2002</option>
								<option name="2003">2003</option>
								<option name="2004">2004</option>
								<option name="2005">2005</option>
								<option name="2006">2006</option>
								<option name="2007">2007</option>
								<option name="2008">2008</option>
								<option name="2009">2009</option>
								<option name="2010">2010</option>
								<option name="2011">2011</option>
								<option name="2012">2012</option>
								<option name="2013">2013</option>
								<option name="2014">2014</option>
								<option name="2015">2015</option>
								<option name="2016">2016</option>
								<option name="2017">2017</option>
								<option name="2018">2018</option>
								<option name="2019">2019</option>
								<option name="2020">2020</option>
								<option name="2021">2021</option>
								<option name="2022">2022</option>
							</select>					   											
						</div>	
						<div class="name_input">
							<p>Страна:</p>
							<input value="<?php echo $user->country; ?>" type="text" name="country" placeholder="Страна" autocomplete="off">	
						</div>
						<div class="name_input">
							<p>Город:</p>
							<input value="<?php echo $user->city; ?>" type="text" name="city" placeholder="Город" autocomplete="off">	
						</div>
						<div class="name_input">
							<p>Мобильный номер:</p>
							<input value="<?php echo $user->mobile_number; ?>" type="text" name="number" placeholder="Мобильный номер" autocomplete="off">	
						</div>	
					</form>
				</div>
			</div>
			<div class="save_button_avatar">
				<button form="editor_form" type="submit" name="edit_save">Сохранить</button>
			</div>	
		</div>
	</div>
	<!--Форма отправки сообщения пользователю-->
	<div class="block_write_a_message" id="block_write_a_message">
		<div class="conteiner_write_a_message">
			<div class="message_text_form"><p>Новое сообщение</p>
				<button onclick="CloseWriteMessage()">&#10006;</button>
			</div>	
			<div class="message_content">
				<div class="message_user_info">
					<div class="block_info_mess">
						<img src="image/avatars/<?php echo $user->avatar; ?>" class="friend_avatar">
						<div class="block_flex_mess">
							<p class="friend_name"><?php echo htmlspecialchars($user->firstname.' '.$user->lastname); ?></p>
							<p class="time_block_mess">был в сети 33 минуты назад</p>								
						</div>
					</div>	
					<div class="block_message_input_friend">
						<form action="/user?id=<?php echo $_GET['id'];?>" method="POST" id="send_mess_id">

							<textarea type="text" name="message" autocomplete="off"></textarea>									
						</form>
					</div>
				</div>
			</div>
			<div class="send_mess_button">
				<button form="send_mess_id" type="submit" name="send_mess" value="<?php echo htmlspecialchars($user['id']); ?>">Отправить</button>
			</div>					
		</div>
	</div>
	<?php require "nav.php"; ?>
	<div class="content_block" align="center">
		<div class="block_profile"> 
			<div class="avatar_info">
				<div class="avatar">
					<?php if($position == 'access'): ?>
						<div class="avatar_button">
							<img src="image/avatars/<?php echo $user->avatar; ?>" onclick="FormAvatar()">									
						</div>
					<?php endif; ?>					
					<?php if($position == 'view'): ?>
						<div class="avatar_button">
							<img src="image/avatars/<?php echo $user->avatar; ?>">									
						</div>
					<?php endif; ?>
					<?php if($position == 'view'): ?>
						<div class="user_nav"> 
							<button onclick="FormWriteMessage()">Написать сообщение</button>
							<?php if($friend_status == 2 & $add_request == 2) : ?>
								<form class="left_form" action="/user?id=<?php echo $_GET['id'];?>" method="POST" >
									<input type="hidden" name="id_user" value="<?php echo $_GET['id'];?>">
									<button type="submit" name="add_friend">Добавить в друзья</button>			
								</form>
							<?php endif; ?>

							<?php if($add_request == 0) : ?>
								<form class="left_form" action="/user?id=<?php echo $_GET['id'];?>" method="POST" >
									<input type="hidden" name="id_request" value="<?php echo $id_request; ?>">
									<button type="submit" name="accept_request">Принять запрос в друзья</button>			
								</form>
							<?php endif; ?>				

							<?php if($friend_status == 0) : ?>
								<form class="left_form" action="/user?id=<?php echo $_GET['id'];?>" method="POST">
									<button type="submit" name="cancellation_friend" value="<?php echo htmlspecialchars($user['id']); ?>">Отменить заявку</button>
								</form>
							<?php endif; ?>

							<?php if($friend_status == 1) : ?>
								<form class="left_form" action="/user?id=<?php echo $_GET['id'];?>" method="POST">
									<button type="submit" name="delete_friend" value="<?php echo htmlspecialchars($user['id']); ?>">Удалить из друзей</button>
								</form>
							<?php endif; ?>
						</div>
						<?php else: ?>
							<div class="user_nav"> 
								<button onclick="FormRedaktor()">Редактировать профиль</button>
							</div>					
						<?php endif; ?>								
					</div>
					<div class="page_block">
						<div class="fried_text">
							<div>Друзья</div>					
						</div>
						<div class="my_friends_conteiner">
							<!--Если нет друзей у друга выводит пустую форму-->					
							<?php if($position == 'view'): ?>
								<?php if (count($friends_friends)== 0) : ?>
									<div class="show_friend" >
										<div>У пользователя ещё нет друзей</div>
										<div id="icon_friend" class="bx bx-customize"></div>
									</div>
								<?php endif; ?>
							<?php endif; ?>
							<!--Если нет друзей выводит пустую форму-->
							<?php if($position == 'access'): ?>
								<?php if (count($my_friends)== 0) : ?>
									<div class="show_friend" >
										<div>У вас нет друзей</div>
										<div id="icon_friend" class="bx bx-customize"></div>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<!--Выводит друзей друга-->
							<?php if($position == 'view'): ?>
								<?php for ($i = 0; $i < count($friends_friends); $i++) : ?>
									<?php 
									if($friends_friends[$i]['id_friend'] == $user->id){
										$namefriend = R::findOne('users', 'id = ?', array($friends_friends[$i]['id_add_user']));
									}else{
										$namefriend = R::findOne('users', 'id = ?', array($friends_friends[$i]['id_friend']));
									}							
									?>
									<div class="show_friend" >
										<img src="image/avatars/<?php echo $namefriend->avatar; ?>" class="friends_avatar">
										<a href="/user?id=<?php echo $namefriend->id; ?>"><p class="friend_firstname"><?php echo htmlspecialchars($namefriend->lastname); ?></p></a>	
									</div>
								<?php endfor; ?>
							<?php endif; ?>
							<!--Выводит моих друзей-->
							<?php if($position == 'access'): ?>
								<?php for ($i = 0; $i < count($my_friends); $i++) : ?>
									<?php 
									if($my_friends[$i]['id_add_user'] == $_SESSION['logged_user']->id){
										$namefriend = R::findOne('users', 'id = ?', array($my_friends[$i]['id_friend']));
									}else{
										$namefriend = R::findOne('users', 'id = ?', array($my_friends[$i]['id_add_user']));
									}							
									?>
									<div class="show_friend" >
										<img src="image/avatars/<?php echo $namefriend->avatar; ?>" class="friends_avatar">
										<a href="/user?id=<?php echo $namefriend->id; ?>"><p class="friend_firstname"><?php echo htmlspecialchars($namefriend->lastname);  ?></p></a>
									</div>
								<?php endfor; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>	
				<div class="block_info">
					<div class="user_info" align="left">
						<p class="user_name"><?php echo $user->firstname.' '.$user->lastname; ?></p>
						<?php if($position == 'access'): ?>		
							<a class="user_status" id="user_status" onclick="StatusOn()">
								<?php 
								if (empty($user->status != null)) {
									echo "Установить статус";
								}
								else{
									echo $user->status;
								}
								?>						
							</a>
							<div class="status_block" id="status_block">				
								<form action="/user?id=<?php echo $_GET['id'];?>" method="POST">
									<input type="text" name="status_user">
									<button type="submit" name="status_save" onclick="StatusOff()">Сохранить</button>
								</form>
							</div>
						<?php endif; ?>
						<?php if($position == 'view'): ?>		
							<a class="user_status_friend">
								<?php 
								if (empty($user->status != null)) {
									echo "";
								}
								else{
									echo $user->status;
								}
								?>						
							</a>
						<?php endif; ?>
						<hr>
						<div class="info_block_user">
							<div class="info_city">
								<h3>Родной город:</h3>
								<div class="text_vivod_user_info">
									<?php 
									if (empty($user->city != null)) {
										echo "Нет информации";
									}
									else{
										echo $user->city;
									}
									?>	
								</div>
							</div>	
							<div class="info_dateofbirth">
								<h3>Дата рождения:</h3>
								<div class="text_vivod_user_info">
									<?php 
									if (empty($user->date_birth != null)) {
										echo "Нет информации";
									}
									else{
										echo $user->date_birth;
									}
									?>									
								</div>
							</div>					
						</div>	
						<details class="additional_information"><summary></summary>
							<div class="info_block_user">
								<div class="info_city">
									<h3>Пол:</h3>
									<div class="text_vivod_user_info">
										<?php 
										if (empty($user->gender != null)) {
											echo "Нет информации";
										}
										else{
											echo $user->gender;
										}
										?>							
									</div>
								</div>	
								<div class="info_dateofbirth">
									<h3>Страна:</h3>
									<div class="text_vivod_user_info">
										<?php 
										if (empty($user->country != null)) {
											echo "Нет информации";
										}
										else{
											echo $user->country;
										}
										?>	
									</div>
								</div>
								<div class="info_dateofbirth">
									<h3>Номер телефона:</h3>
									<div class="text_vivod_user_info">
										<?php 
										if (empty($user->mobile_number != null)) {
											echo "Нет информации";
										}
										else{
											echo $user->mobile_number;
										}
										?>	
									</div>
								</div>						
							</div>
						</details>
						<hr>	
						<div class="counts_module">
							<a class="page_counter" href="friends.php">
								<div class="numbering"><?php echo ($i); ?></div>
								<div>Друга</div>
							</a>
							<a class="page_counter" href="">
								<div class="numbering">0</div>
								<div>Аудиозаписи</div>
							</a>
						</div>	
					</div>	
					<div class="gallery">
						<div class="text_block_gallery"><a href="/gallery">Фотографии</a><p><?php echo count($all_image_user)?></p></div>
						<div class="block_gallery">
							<?php if (count($user_photos)== 0) : ?>
								<?php if($position == 'view') : ?>
									<div class="none_phote">У пользователя нет фотографий</div>
									<?php else: ?> 
										<button class="button_add_photo"><i class="bx bx-image-add"></i>Загрузить фотографию</button>
									<?php endif ?>
								<?php endif; ?>					    
								<?php for ($i = 0; $i < count($user_photos); $i++): ?>
									<div class="gallery_card">
										<img src="image/gallery/<?php echo $user_photos[$i]->images; ?>" alt="Машина" class="gallery_card_pic">
									</div>
								<?php endfor; ?>

							</div>

						</div>
						<div class="blog_list">
							<?php if($position == 'view') : ?>
								<?php else: ?> 	
									<div class="blog">
										<div class="post">
											<div class="post_input">
												<form action="/user?id=<?php echo $_GET['id'];?>" method="POST">
													<input type="text" name="post" placeholder="Что у вас нового?" class="post_enter" autocomplete="off">
													<button type="submit" name="send_post" class="btn_area">Опубликовать</button>
												</form>
											</div>			 	
										</div>
									<?php endif ?>
									<div class="array_notes"> 
										<?php if (count($user_posts)== 0) : ?>
											<div class="show_post_none" >
												<p>На стене пока нет ни одной записи</p>
												<div id="icon_post" class=" bx bx-detail"></div>
											</div>
										<?php endif; ?>		
										<?php for ($i = 0; $i < count($user_posts); $i++): ?>
											<div class="public_post"> 
												<div class="avatar_post">
													<img src="image/avatars/<?php echo $user->avatar; ?>">	
													<div class="name_block_post">					 	 				
														<div class="name_post"><?php echo $user->firstname.' '.$user->lastname; ?>
														<?php if($position == 'access') : ?>
															<div class="troetochik">
																<button class="button_drop"></button>
																<div class="drop_troitohie">
																	<button class="edit_post_button" href="">Редактировать запись</button>
																	<form action="/user?id=<?php echo $_GET['id'];?>" method="POST">	 		
																		<button type="submit" name="delete_button_post" class="delete_button_post" 
																		value="<?php echo htmlspecialchars($user_posts[$i]['id']); ?>">Удалить запись</button>
																	</form>
																</div>
															</div>	
														<?php endif ?>
													</div>	 	 		
													<p class="date_post"><?php echo $user_posts[$i]->date_publication.' в '.$user_posts[$i]->time_publication; ?></p>	
												</div>
											</div>
											<p class="post_text"><?php echo htmlspecialchars($user_posts[$i]['post']); ?></p>
											<div class="conteiner_button_post">
												<button class="like_button"><li class="bx bxs-heart"></li>1</button>
											</div>
										</div><br>
									<?php endfor; ?>
								</div>						
							</div>			 
						</div>	
					</div>
				</div>
			</div>
		</body>
		</html>
<script type="text/javascript">
	const cards = Array.from(document.querySelectorAll(".gallery_card"));
	const slider = document.querySelector(".slider");
	const sliderContainer = document.querySelector(".slider_container");
	const picture = Array.from(document.querySelectorAll(".gallery_card_pic"));	
	const sliderBtnLeft = document.querySelector(".slider_btn_left");
	const sliderBtnRight = document.querySelector(".slider_btn_right");
	const sliderClose = document.querySelector(".slider_close");

	for (const card of cards) {
		card.addEventListener("click", (event) => {
			cardIndex = cards.indexOf(card);
			pictureFull = picture[cardIndex].cloneNode();
			pictureFull.style.objectFit = "contain";
			sliderContainer.append(pictureFull);
			slider.classList.add("active");
		});
	}

	sliderBtnLeft.addEventListener("click", (event) => {
		event.preventDefault();
		changePicture("left");
	});

	sliderBtnRight.addEventListener("click", (event) => {
		event.preventDefault();
		changePicture("right");
	});

function changePicture(dir) {
	if (dir === "left") {
		if (cardIndex > 0) {
			cardIndex--;
		} else {
			cardIndex = cards.length - 1;
		}
	} else if (dir === "right") {
		if (cardIndex < cards.length - 1) {
			cardIndex++;
		} else {
			cardIndex = 0;
		}
	}
	let newPictureFull = picture[cardIndex].cloneNode();
	newPictureFull.style.objectFit = "contain";
	pictureFull.replaceWith(newPictureFull);
	pictureFull = newPictureFull;
}

sliderClose.addEventListener("click", (event) => {
	event.preventDefault();
	slider.classList.remove("active");
	pictureFull.remove();
	newPictureFull.remove();
});
</script>
<script type="text/javascript">
const photobutton = Array.from(document.querySelectorAll(".button_add_photo"));
const formPhoto = document.querySelector(".conteiner_photo_add");
const addPhotoClose = document.querySelector(".add_photo_close")
for (const photo of photobutton) {
	photo.addEventListener("click", (event) => {
		formPhoto.classList.add("active_photo_form");
	});
	
addPhotoClose.addEventListener("click", (event) => {
	event.preventDefault();
	formPhoto.classList.remove("active_photo_form");
});	
</script>
<script src="https://kit.fontawesome.com/fce9a50d02.js" crossorigin="anonymous"></script>
