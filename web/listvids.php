<?php
ob_start(); // begin collecting output

include 'json_from_id.php';

$result = ob_get_clean(); // retrieve output from myfile.php, stop buffering
// echo $result;
$vids = json_decode($result, true);
?>

<?php foreach($vids as $video): ?>
	<?php
		if(!isset($video['id']) || !isset($video['title'])) {
			continue;
		}
	?>

	<details class="video" open>
		<summary hidden><?= $video['title'] ?> (ID: <?= $video['id']?>)</summary>
		<article>
			<h2><?= $video['title'] ?> (ID: <?= $video['id']?>)</h2>
			<div class="videoframe">
				<button onclick="loadvidinframe(event, 'https://youtube.com/embed/<?=$video['links']['youtube']?>?autoplay=1')">
					<img inert loading="lazy" src="<?= $video['thumbnails'][0]?>" alt="Load Video">
				</button>
			</div>
			<div class="info">
				<h3><?= $video['title'] ?></h3>
				<p>
					<b>Publisher</b>: <?= $video['pub'] ?><br>
					<b>Date of Publishing</b>: <?= date('Y-m-d H:i', $video['datePub'] ?? 0); ?><br>
					<b>Date of Recording</b>: <?= date('Y-m-d H:i', $video['dateRec'] ?? 0); ?><br>
					<b>Category</b>: <span><?= $video['category']?></span><br>
					<b>Category-2</b>: <span><?= $video['category-2']?></span><br>
					<b>Links</b>:
					<ul>
						<?php foreach($video['links'] as $videoK => $videoV) {
							echo '<li>';
							switch ($videoK) {
								case 'youtube':
									echo "<a href='https://youtu.be/watch/?v=". $videoV . "'>Youtube</a>";
									break;
								case 'rumble':
									echo "<a href='". $videoV . "'>Rumble</a>";
									break;
								default:
									echo "<a href='" . $videoV . "'>" . $videoK . "</a>";
									break;
							}
							echo '</li>';
						}
						?>
					</ul>
					<?php if(!empty($video['description'])): ?>
						<b>Description</b>:
						<textarea class="description" rows="12" readonly><?= $video['description']; ?></textarea>
					<?php endif; ?>
				</p>
				<details>
					<summary>Full Details</summary>
					<pre>
						<?php print_r($video); ?>
					</pre>
				</details>
			</div>
			<div class="info-below">
				<p class="tags">
					<b>Tags:</b>
					<?php
						$tags = $video['tags'] ?? "";
						$tags = explode(",", $tags);

						foreach($tags as $tag) {
							echo "<span class='tag'>" . $tag . "</span>,";
						}
					?>
				</p>
			</div>
		</article>
	</details>
	<br>

<?php endforeach; ?>
