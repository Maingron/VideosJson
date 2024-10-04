function loadvidinframe(event, link) {
	console.log(event);
	event.target.outerHTML = `<iframe loading="lazy" src="${link}"></iframe>`;
}
