function loadvidinframe(event, link) {
	console.log(event);
	event.target.outerHTML = `<iframe loading="lazy" src="${link}"></iframe>`;
}


var search = (function() {
	const config = {
		itemSelector: "details.video",
		keyTimeout: 200
	}

	let prevSearch;
	let currentSearchTimeout;

	
	let hideAll = function() {
		const allHtmlEntries = document.querySelectorAll(config.itemSelector);
		for(let entry of allHtmlEntries) {
			entry.setAttribute("hidden", "hidden");
		}
	}

	let showAll = function() {
		const allHtmlEntries = document.querySelectorAll(config.itemSelector);
		for(let entry of allHtmlEntries) {
			entry.removeAttribute("hidden");
		}
	}

	let filter = {
		containsAny: function(query) {
			const querySan = query.toLowerCase();
			for(let entry of document.querySelectorAll(config.itemSelector + ":not([hidden])")) {
				entryText = entry.querySelector(".info>details pre").innerHTML;
				entryText = entryText.replaceAll("] => ", ":");
				entryText = entryText.replaceAll("] =&gt; ", ":");
				if(entryText.includes(querySan)) {
				} else {
					entry.setAttribute("hidden", "hidden");
				}
			}
		},
		containsTags: function(tag) {
			window.setTimeout(function() {
				const tagSan = tag?.toLowerCase();
				for(let entry of document.querySelectorAll(config.itemSelector + ":not([hidden])")) {
					let tagFound = false;
					for(let tagElement of entry.querySelectorAll(".tag")) {
						if(tagElement.innerText.toLowerCase().includes(tagSan)) {
							tagFound = true;
							break;
						}
					}
					if(tagFound) {
						// show the entry
					} else {
						entry.setAttribute("hidden", "hidden");
					}
				}
			}, 0);
		},

		handleUnisearch: function(query) {
			const querySan = query?.toLowerCase()?.trim();

			if(prevSearch == querySan) {
				return;
			}

			prevSearch = querySan;

			if(currentSearchTimeout) {
				clearTimeout(currentSearchTimeout);
			}

			currentSearchTimeout = setTimeout(function() {
				// one full query part is space to space, or quote content

				let queryParts = querySan.match(/(?:[^\s"']+|["'][^"']*["'])+/g) || [];

				showAll();

				for(let part of queryParts) {
					if(part.includes("tags:")) {
						part = part.split("tags:")[1];
						part = part.trim();
						part = part.replace(/['"]/g, "");
						filter.containsTags(part);
						continue;
					} else {
						part = part.replace(/['"]/g, "");
						filter.containsAny(part);
					}
				}
				clearTimeout(currentSearchTimeout);
			}, config.keyTimeout);
		}
	}


	return {
		hideAll: hideAll,
		showAll: showAll,
		filter: filter
	}
}());

document.addEventListener("DOMContentLoaded", function() {
	let unisearchElement = document.getElementById("unisearch");
	if(unisearchElement && search) {
		search.hideAll();
		search.filter.handleUnisearch(unisearchElement.value);
	}

	document.body.classList.remove("waitforinit");
});
