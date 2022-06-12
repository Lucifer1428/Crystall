<?php 
require "db.php";
$all_post_in_news = R::findAll('posts');
$user_posts_show = array();

foreach ($all_post_in_news as $rowpost) {
	$user_posts_show[] = $rowpost;		
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="style/light.css">
	<link rel="icon" href="image/icon/icon_a.png" type="image/x-icon">
	<title>Новости</title>
</head>
<body>
	<?php require "nav.php"; ?>	
	<div class="conteiner_news">
		<div class="block_news">
			<div class="block_show_news">
				<?php if (count($user_posts_show)== 0) : ?>
					<div class="show_message_info_none">
						<div class="content_none">
							<h1>В данный момент нет новостей </h1>
							<div id="icon_friend" class="bx bx-news"></div>
						</div>
					</div>
				<?php endif; ?>	
				<?php for ($i = 0; $i < count($user_posts_show); $i++): ?>
					<?php $user_sender_news = R::findOne('users', 'id = ?', array($user_posts_show[$i]['id_user']));?>
					<div class="public_news"> 
						<div class="avatar_news">
							<img src="image/avatars/<?php echo $user_sender_news->avatar; ?>">	
							<div class="info_block_news">					 	 				
								<div class="name_news_avtor"><?php echo $user_sender_news->firstname.' '.$user_sender_news->lastname; ?></div>	 	 		
								<p class="date_news_public"><?php echo $user_posts_show[$i]->date_publication.' в '.$user_posts_show[$i]->time_publication; ?></p>	
							</div>
						</div>
						<p class="post_text"><?php echo htmlspecialchars($user_posts_show[$i]['post']); ?></p>
						<div class="conteiner_button_news">
							<button><li class="bx bxs-heart"></li>1</button>
							<button class="сomments">Комментарии</button>
						</div>								
					</div><br>
				<?php endfor; ?>
			</div>
		</div>
	</div>
</body>
</html>