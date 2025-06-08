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
	<search class="search-container">
		<input type="text" id="unisearch" placeholder="Search..." onkeyup="search.filter.handleUnisearch(this.value)" />
		<details class="dropdown">
			<summary>Options</summary>
			<div class="content">
				<label>
					Ignore Whitespace
					<input type="checkbox" id="ignore-whitespace" onchange="search.filter.toggleIgnoreWhitespace(this.checked); search.updateUrlFlags();" />
				</label>
				<label>
					Ignore Case
					<input type="checkbox" id="ignore-case" disabled checked="checked" />
				</label>
			</div>
		</details>
	</search>
</header>

<button id="add-video" onclick="addNewVideo()">Add New Video</button>

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

<script>
	function addNewVideo() {
		// Gather existing options for dropdowns
		const categories = new Set();
		const categories2 = new Set();
		const publishers = new Set();
		const linkTypes = new Set();

		document.querySelectorAll('.video').forEach(video => {
			const category = video.querySelector('.info span:nth-of-type(1)')?.innerText.trim();
			const category2 = video.querySelector('.info span:nth-of-type(2)')?.innerText.trim();
			const publisher = video.querySelector('.summary-closedinfo-pub')?.innerText.trim();
			const links = video.querySelectorAll('.info ul li');

			if (category) categories.add(category);
			if (category2) categories2.add(category2);
			if (publisher) publishers.add(publisher);

			links.forEach(link => {
				const linkType = link.textContent.split(':')[0]?.trim();
				if (linkType) linkTypes.add(linkType);
			});
		});

		const categoryOptions = Array.from(categories).map(cat => `<option value="${cat}">${cat}</option>`).join('');
		const category2Options = Array.from(categories2).sort().map(cat2 => `<option value="${cat2}">${cat2}</option>`).join('');
		const publisherOptions = Array.from(publishers).map(pub => `<option value="${pub}">${pub}</option>`).join('');
		const linkTypeOptions = Array.from(linkTypes).map(type => `<option value="${type.toLowerCase()}">${type}</option>`).join('');

		// Determine the highest existing ID
		const highestId = Math.max(...Array.from(document.querySelectorAll('.video')).map(v => parseInt(v.id.split('-').pop()) || 0));
		const newVideoId = highestId + 1;
		const newVideoAnchor = `v-new-${newVideoId}`;
		
		// Create the new video HTML
		const newVideoHtml = `
			<details class="video" id="${newVideoAnchor}" open>
				<summary>
					<h2><a class="summary-dbid" href="#${newVideoAnchor}">${newVideoId}</a> <span class="dynamic-title">New Video</span></h2>
					<span class="summary-closedinfo">
						<span class="summary-closedinfo-pub"></span>
						<span class="summary-closedinfo-date"></span>
						<span class="summary-closedinfo-cat"></span>
					</span>
				</summary>
				<article>
					<h2 hidden>New Video</h2>
					<div class="videoframe">
						<button onclick="loadvidinframe(event, '')">
							<img inert loading="lazy" src="" alt="Load Video">
						</button>
					</div>
					<div class="info">
						<h3><input type="text" placeholder="Title" class="editable-title" oninput="updateDynamicTitle('${newVideoAnchor}', this.value)" /></h3>
						<p>
							<b>Publisher</b>: 
							<select class="editable-pub">
								<option value="" disabled selected>Select Publisher</option>
								${publisherOptions}
							</select><br>
							<b>Date of Publishing</b>: <input type="datetime-local" class="editable-datePub" /><br>
							<b>Date of Recording</b>: <input type="datetime-local" class="editable-dateRec" /><br>
							<b>Category</b>: 
							<select class="editable-category">
								<option value="" disabled selected>Select Category</option>
								${categoryOptions}
							</select><br>
							<b>Category-2</b>: 
							<select class="editable-category-2">
								<option value="" disabled selected>Select Category-2</option>
								${category2Options}
							</select><br>
							<b>Links</b>:
							<ul class="editable-links-list"></ul>
							<div class="editable-links-container">
								<select class="editable-link-type">
									<option value="" disabled selected>Select Link Type</option>
									${linkTypeOptions}
								</select>
								<input type="text" placeholder="Link URL" class="editable-link-url" />
								<button type="button" onclick="addLink('${newVideoAnchor}')">Add Link</button>
							</div><br>
							<b>Tags</b>: 
							<input type="text" placeholder="Comma-separated tags" class="editable-tags" oninput="updateTags('${newVideoAnchor}', this.value)" /><br>
							<b>Description</b>: <textarea class="editable-description" rows="4" placeholder="Description"></textarea>
						</p>
						<button onclick="copyJson('${newVideoAnchor}')">Copy JSON</button>
					</div>
					<div class="info-below">
						<p class="tags">
							<b>Tags:</b>
							<span class="tag-placeholder">No tags yet</span>
						</p>
					</div>
				</article>
			</details>
		`;

		// Insert the new video at the top of the page
		document.querySelector('header').insertAdjacentHTML('afterend', newVideoHtml);
	}

	function updateDynamicTitle(videoId, title) {
		const videoElement = document.getElementById(videoId);
		const dynamicTitleElement = videoElement.querySelector('.dynamic-title');
		dynamicTitleElement.textContent = title || 'New Video';
	}

	function addLink(videoId) {
		const videoElement = document.getElementById(videoId);
		const linkType = videoElement.querySelector('.editable-link-type').value;
		const linkUrl = videoElement.querySelector('.editable-link-url').value;

		if (!linkUrl) {
			alert('Please enter a valid link URL.');
			return;
		}

		const linksList = videoElement.querySelector('.editable-links-list');
		const newLinkItem = document.createElement('li');
		newLinkItem.textContent = `${linkType}: ${linkUrl}`;
		linksList.appendChild(newLinkItem);

		// Clear the input field
		videoElement.querySelector('.editable-link-url').value = '';
	}

	function updateTags(videoId, tags) {
		const videoElement = document.getElementById(videoId);
		const tagsContainer = videoElement.querySelector('.info-below .tags');
		const tagPlaceholder = tagsContainer.querySelector('.tag-placeholder');

		// Clear existing tags
		tagsContainer.querySelectorAll('.tag').forEach(tag => tag.remove());

		// Add new tags
		const tagList = tags.split(',').map(tag => tag.trim()).filter(tag => tag);
		if (tagList.length > 0) {
			tagPlaceholder.style.display = 'none';
			tagList.forEach(tag => {
				const tagElement = document.createElement('span');
				tagElement.className = 'tag';
				tagElement.textContent = tag;
				tagsContainer.appendChild(tagElement);
			});
		} else {
			tagPlaceholder.style.display = 'inline';
		}
	}

	function copyJson(videoId) {
		const videoElement = document.getElementById(videoId);
		const links = {};
		videoElement.querySelectorAll('.editable-links-list li').forEach(linkItem => {
			const [type, url] = linkItem.textContent.split(': ');
			links[type] = url;
		});

		const tags = videoElement.querySelector('.editable-tags').value.split(',').map(tag => tag.trim()).filter(tag => tag);

		const json = {
			id: videoId.split('-').pop(),
			title: videoElement.querySelector('.editable-title').value,
			pub: videoElement.querySelector('.editable-pub').value,
			datePub: new Date(videoElement.querySelector('.editable-datePub').value).getTime() / 1000 || 0,
			dateRec: new Date(videoElement.querySelector('.editable-dateRec').value).getTime() / 1000 || 0,
			category: videoElement.querySelector('.editable-category').value,
			'category-2': videoElement.querySelector('.editable-category-2').value,
			links: links,
			tags: tags.join(','),
			description: videoElement.querySelector('.editable-description').value,
			thumbnails: []
		};
		navigator.clipboard.writeText(JSON.stringify(json, null, 2));
		alert('JSON copied to clipboard!');
	}
</script>
