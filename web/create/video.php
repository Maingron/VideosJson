<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Create Video</title>
		<link rel="stylesheet" href="/web/css/style.min.css">
		<script src="/web/index.js"></script>
	</head>
	<body>
		<div hidden>
			<?php
				require_once(__DIR__."/../json_from_id.php");
				$vid = $vids[0];
			?>
		</div>
		<form action="getjson.php">
			<label for="id">ID</label>
			<input type="number" name="id" value="<?= $vid['id'] ?>">
			<br>
			<label for="title">Title</label>
			<input type="text" name="title" value="<?= $vid['title'] ?>">
			<br>
			LINKS
			<label for="lang">Lang</label>
			<input type="text" name="lang" value="<?= $vid['lang'] ?>">
			<br>
			<label for="desc">Description</label>
			<textarea name="desc"><?= $vid['description'] ?></textarea>
			<br>
			<label for="tags">Tags</label>
			<input type="text" name="tags" value="<?= $vid['tags'] ?>">
			<br>
			<label for="daterec">Date Recorded</label>
			<input type="date" name="daterec" value="<?= date('Y-m-d', $vid['dateRec']) ?>">
			<br>
			<label for="datepub">Date First Published</label>
			<input type="date" name="datepub" value="<?= date('Y-m-d', $vid['datePub']) ?>">
			<br>
			<label for="pub">Publisher</label>
			<input type="number" name="pub" value="<?= $vid['pub'] ?>">
			<br>
			<label for="category">Category</label>
			<input type="text" name="category" value="<?= $vid['category'] ?>">
			<br>
			<label for="category-2">Category-2 (Game)</label>
			<input type="text" name="category-2" value="<?= $vid['category-2'] ?>">
			<br>
			<?php
				$platforms = ['youtube', 'twitch', 'rumble', 'vimeo', 'custom-1', 'custom-2', 'custom-3'];
				foreach($platforms as $platform) {
					if(!isset($vid['links'][$platform])) {
						$vid['links'][$platform] = null;
					}
				}
			?>
			<?php foreach($vid['links'] as $linkK => $linkV): ?>
				<label for="links[<?=$linkK?>]">links[<?=$linkK?>]</label>
				<input type="text" name="links[<?=$linkK?>]" value="<?=$linkV?>">
				<br>
			<?php endforeach; ?>
				
			THUMBNAILS
			<button type="submit">Submit</button>
		</form>

		<textarea readonly>{}</textarea>
	</body>
</html>
