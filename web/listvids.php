<?php
ob_start(); // begin collecting output

include 'json_from_id.php';

$result = ob_get_clean(); // retrieve output from myfile.php, stop buffering
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
					<img inert loading="lazy" src="/vids/<?= $video['id'] . "/" . $video['thumbnails'][0]?>" alt="Load Video">
				</button>
			</div>
			<aside class="info">
				<h3><?= $video['title'] ?></h3>
					<p>
						Publisher: <?= $video['pub'] ?><br>
						Publish Date: <?= date('Y-m-d H:i', $video['datePub']); ?><br>

						Links:
					</p>
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
				<h3></h3>

				<pre>
				<?php print_r($video); ?>
			</pre>
			</aside>
		</article>
		&nbsp;
	</details>
	<hr>

<?php endforeach; ?>
