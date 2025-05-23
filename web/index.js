function loadvidinframe(event, link) {
	console.log(event);
	event.target.outerHTML = `<iframe loading="lazy" src="${link}"></iframe>`;
}


var search = (function() {
	let config = {
		itemSelector: "details.video",
		unisearchElementSelector: "#unisearch",
		keyTimeout: 50,
		ignoreWhitespace: false
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
		toggleIgnoreWhitespace: function(enabled) {
			config.ignoreWhitespace = enabled;
		},

		containsAny: function(query) {
			const querySan = config.ignoreWhitespace ? query.toLowerCase().replace(/\s|-/g, "") : query.toLowerCase();
			for (let entry of document.querySelectorAll(config.itemSelector + ":not([hidden])")) {
				let entryText = entry.querySelector(".info>details pre").innerHTML;
				entryText = entryText.replaceAll("] => ", ":").replaceAll("] =&gt; ", ":");
				if (config.ignoreWhitespace) {
					entryText = entryText.replace(/\s|-/g, "");
				}
				if (!entryText.toLowerCase().includes(querySan)) {
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
					if(!tagFound) {
						entry.setAttribute("hidden", "hidden");
					}
				}
			}, 0);
		},
		containsNot: function(query) {
			const querySan = query.toLowerCase();
			for (let entry of document.querySelectorAll(config.itemSelector + ":not([hidden])")) {
				let entryText = entry.querySelector(".info>details pre").innerHTML;
				entryText = entryText.replaceAll("] => ", ":");
				entryText = entryText.replaceAll("] =&gt; ", ":");
				if (entryText.toLowerCase().includes(querySan)) {
					entry.setAttribute("hidden", "hidden");
				}
			}
		},
		containsNotTags: function(tag) {
			const tagSan = tag?.toLowerCase();
			for (let entry of document.querySelectorAll(config.itemSelector + ":not([hidden])")) {
				let tagFound = false;
				for (let tagElement of entry.querySelectorAll(".tag")) {
					if (tagElement.innerText.toLowerCase().includes(tagSan)) {
						tagFound = true;
						break;
					}
				}
				if (tagFound) {
					entry.setAttribute("hidden", "hidden");
				}
			}
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
					if (part.startsWith("not:tags:")) {
						part = part.split("not:tags:")[1];
						part = part.trim();
						part = part.replace(/['"]/g, "");
						filter.containsNotTags(part);
						continue;
					} else if (part.startsWith("not:")) {
						part = part.split("not:")[1];
						part = part.trim();
						part = part.replace(/['"]/g, "");
						filter.containsNot(part);
						continue;
					} else if(part.includes("tags:")) {
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
	};

	let updateUrlFlags = function() {
		const newUrl = new URL(window.location.href);
		newUrl.searchParams.set("ignore-whitespace", config.ignoreWhitespace);
		window.history.replaceState({}, "", newUrl);
		prevSearch = "";
		filter.handleUnisearch(document.querySelector(config.unisearchElementSelector).value);
	};

	return {
		hideAll: hideAll,
		showAll: showAll,
		filter: filter,
		updateUrlFlags: updateUrlFlags
	};
}());

document.addEventListener("DOMContentLoaded", function() {
	let unisearchElement = document.getElementById("unisearch");
	let ignoreWhitespaceElement = document.getElementById("ignore-whitespace");

	// Use search parameters from URL
	const urlParams = new URLSearchParams(window.location.search);
	const initialSearch = urlParams.get("search");
	const initialIgnoreWhitespace = urlParams.get("ignore-whitespace") === "true";

	if (unisearchElement && search) {
		if (initialSearch) {
			unisearchElement.value = initialSearch;
		}
		search.hideAll();
		search.filter.handleUnisearch(unisearchElement.value);
	}

	if (ignoreWhitespaceElement) {
		ignoreWhitespaceElement.checked = initialIgnoreWhitespace;
		search.filter.toggleIgnoreWhitespace(initialIgnoreWhitespace);
	}

	// Update URL when search bar or flags change
	if (unisearchElement) {
		unisearchElement.addEventListener("input", function() {
			const newSearch = unisearchElement.value;
			const newUrl = new URL(window.location.href);
			if (newSearch) {
				newUrl.searchParams.set("search", newSearch);
			} else {
				newUrl.searchParams.delete("search");
			}
			window.history.replaceState({}, "", newUrl);
			search.filter.handleUnisearch(newSearch);
		});
	}

	if (ignoreWhitespaceElement) {
		ignoreWhitespaceElement.addEventListener("change", function() {
			search.filter.toggleIgnoreWhitespace(ignoreWhitespaceElement.checked);
			search.updateUrlFlags();
		});
	}

	document.body.classList.remove("waitforinit");
});
