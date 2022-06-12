<?php 
require "db.php";
$data = $_POST; 


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

if (isset($data['set_photo_add'])) {
	$photo = $_FILES['photo'];
	if(photoSecurity($photo)) loadPhoto($photo);
	header("Refresh:0");
} 
	//Выгрузка фотографий по id
$all_image_gallery = R::findAll('gallery');
$user_photo_gallery = array();

foreach ($all_image_gallery as $row) {
	if ($row['id_user_photo'] == $_SESSION['logged_user']->id) {
		$user_photo_gallery[] = $row;		
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="image/icon/icon_a.png" type="image/x-icon">
	<title>Мои фотографии</title>
</head>
<body>
	<!--Фон для показа фотографий-->
	<div class="slider_gallery">
		<div class="slider_container_picture">
			<button class="slider_btn slider_gallery_btn_left">
				<i class="fas fa-chevron-left"></i>
			</button>
			<button class="slider_btn slider_gallery_btn_right">
				<i class="fas fa-chevron-right"></i>
			</button> 
		</div>
		<button class="slider_gallery_close">
			<i class="far fa-times-circle"></i>
		</button>     
	</div>
	<div class="conteiner_photo_add">
		<div class="add_photo_block">
			<div class="text_photo_form"><p>Загрузка новой фотографии</p></div>	
			<div class="block_add_photo">
				<div class="info_text_phpto_form">Вы можете загрузить изображение в формате JPG, GIF или PNG.</div>
				<div class="photo_selection">
					<form action="/gallery.php" method="POST" enctype="multipart/form-data" id="form_photo_add">
						<input type="file" name="photo">
					</form>					
				</div>
			</div>  
			<div class="save_button_photo">
				<button form="form_photo_add" type="submit" name="set_photo_add">Добавить фотографию</button>
			</div>			  
		</div>
		<button class="add_photo_close">
			<i class="far fa-times-circle"></i>
		</button>     
	</div>

	<?php require "nav.php"; ?>
	<div class="conteiner_gallery">
		<div class="block_gallary">
			<div class="text_gallery">Мои фотографии<button class="button_add_photo">Добавить фотографию</button></div>

			<div class="content_gallery">
				<?php if (count($user_photo_gallery)== 0) : ?>
					<div class="show_gellary_none" >
						<i class="bx bx-image-alt"></i>
					</div>
				<?php endif; ?>	

				<?php for ($i = 0; $i < count($user_photo_gallery); $i++): ?>
					<div class="gallery_cards">
						<img src="image/gallery/<?php echo $user_photo_gallery[$i]->images; ?>" alt="Машина" class="gallery_card_picture">
					</div>	
				<?php endfor; ?>
				
			</div>
		</div>
	</div>
</body>
</html>
<script type="text/javascript">
	const galleryCards = Array.from(document.querySelectorAll(".gallery_cards"));
	const sliderGallery = document.querySelector(".slider_gallery");
	const sliderContainerPicture = document.querySelector(".slider_container_picture");
	const pictures = Array.from(document.querySelectorAll(".gallery_card_picture"));	
	const sliderGalleryBtnLeft = document.querySelector(".slider_gallery_btn_left");
	const sliderGalleryBtnRight = document.querySelector(".slider_gallery_btn_right");
	const sliderGalleryClose = document.querySelector(".slider_gallery_close");

	for (const pict of galleryCards) {
		pict.addEventListener("click", (event) => {
			cardIndex = galleryCards.indexOf(pict);
			pictureFull = pictures[cardIndex].cloneNode();
			pictureFull.style.objectFit = "contain";
			sliderContainerPicture.append(pictureFull);
			sliderGallery.classList.add("activegallery");
		});
	}

	sliderGalleryBtnLeft.addEventListener("click", (event) => {
		event.preventDefault();
		changePicture("left");
	});

	sliderGalleryBtnRight.addEventListener("click", (event) => {
		event.preventDefault();
		changePicture("right");
	});

	function changePicture(dir) {
		if (dir === "left") {
			if (cardIndex > 0) {
				cardIndex--;
			} else {
				cardIndex = galleryCards.length - 1;
			}
		} else if (dir === "right") {
			if (cardIndex < galleryCards.length - 1) {
				cardIndex++;
			} else {
				cardIndex = 0;
			}
		}
		let newPictureFull = pictures[cardIndex].cloneNode();
		newPictureFull.style.objectFit = "contain";
		pictureFull.replaceWith(newPictureFull);
		pictureFull = newPictureFull;
	}

	sliderGalleryClose.addEventListener("click", (event) => {
		event.preventDefault();
		sliderGallery.classList.remove("activegallery");
		pictureFull.remove();
		newPictureFull.remove();
	});
</script>
<script type="text/javascript">
	const photobutton = Array.from(document.querySelectorAll(".button_add_photo"));
	const formPhoto = document.querySelector(".conteiner_photo_add");
	const addPhotoClose = document.querySelector(".add_photo_close");

	for (const photo of photobutton) {
		photo.addEventListener("click", (event) => {
			formPhoto.classList.add("active_photo_form");
		});
	}

	addPhotoClose.addEventListener("click", (event) => {
		event.preventDefault();
		formPhoto.classList.remove("active_photo_form");
	});	
</script>
<script
src="https://kit.fontawesome.com/fce9a50d02.js"
crossorigin="anonymous"
></script>