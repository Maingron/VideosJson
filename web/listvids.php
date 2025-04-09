<?php
ob_start(); // begin collecting output

include 'json_from_id.php';

$result = ob_get_clean(); // retrieve output from myfile.php, stop buffering
// echo $result;
$vids = json_decode($result, true);

usort($vids, function($a, $b) {
	return $a['id'] <=> $b['id'];
});

?>

<header>
	<search>
		<input type="text" id="unisearch" placeholder="Search..." onkeyup="search.filter.handleUnisearch(this.value)" />
	</search>
</header>

<?php foreach($vids as $video): ?>
	<?php
		if(!isset($video['id']) || !isset($video['title'])) {
			continue;
		}
	?>

	<?php $videoAnchor = 'v-' . ($video['pub'] ?? 'x' ) . "-" . ($video['id'] ?? 'x'); ?>
	<details class="video" id="<?=$videoAnchor?>" open>
		<summary>
			<h2><a class="summary-dbid" href="#<?=$videoAnchor?>"><?= $video['id']?></a> <?= $video['title'] ?></h2>
			<span class="summary-closedinfo">
				<span class="summary-closedinfo-pub">
					<?= $video['pub'] ?? ''; ?>
				</span>

				<span class="summary-closedinfo-date">
				<?php $mySummaryClosedInfoTime = $video['datePub'] ??!1?: $video['dateRec'] ?? 0 ?>
					<?php if($mySummaryClosedInfoTime): ?>
						<?= date('Y-m-d H:i', $video['datePub'] ??!1?: $video['dateRec'] ?? 0); ?>
					<?php endif; ?>
				</span>
				<span class="summary-closedinfo-cat">
					<?= $video['category-2'] ??!1?: $video['category']; ?>
				</span>
			</span>
		</summary>
		<article>
			<h2 hidden><?= $video['title'] ?> (ID: <?= $video['id']?>)</h2>
			<div class="videoframe">
				<button onclick="loadvidinframe(event, 'https://youtube.com/embed/<?=$video['links']['youtube'] ?? ''?>?autoplay=1')">
					<img inert loading="lazy" src="<?= $video['thumbnails'][0]?>" alt="Load Video">
				</button>
			</div>
			<div class="info">
				<h3><?= $video['title'] ?></h3>
				<p>
					<b>Publisher</b>: <?= $video['pub'] ?><br>
					<?php if($video['datePub'] ?? !1): ?>
						<b>Date of Publishing</b>: <?= date('l, Y-m-d H:i', $video['datePub'] ?? 0); ?> (<?= date('d.m.Y H:i', $video['datePub'] ?? 0); ?>)<br>
					<?php endif; ?>
					<?php if($video['dateRec'] ?? !1): ?>
						<b>Date of Recording</b>: <?= date('l, Y-m-d H:i', $video['dateRec'] ?? 0); ?> (<?= date('d.m.Y H:i', $video['dateRec'] ?? 0); ?>)<br>
					<?php endif; ?>
					<b>Category</b>: <span><?= $video['category']?></span><br>
					<b>Category-2</b>: <span><?= $video['category-2']?></span><br>
					<b>Links</b>:
					<ul>
						<?php foreach($video['links'] ?? [] as $videoK => $videoV) {
							if(!$videoV) {
								continue;
							}

							echo '<li>';
							switch ($videoK) {
								case 'youtube':
									echo "<a href='https://www.youtube.com/watch?v=". $videoV . "'>Youtube</a>";
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

		<?php if(
				!isset($video['links']) || empty($video['links']) &&
				!$video['description'] || strlen($video['description'] < 5) &&
				!isset($video['thumbnails'][0])): ?>
			<script>
				document.currentScript.closest('details').removeAttribute('open');
				document.currentScript.remove();
			</script>
		<?php endif; ?>
	</details>

<?php endforeach; ?>
