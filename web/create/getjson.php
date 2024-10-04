<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>1111 Create Video</title>
		<link rel="stylesheet" href="/web/css/style.min.css">
		<script src="/web/index.js"></script>
	</head>
	<body>
		<script>
			var resultObj = Object.fromEntries(new URLSearchParams(window.location.search));
			resultObj.datepub = Date.parse(resultObj.datepub) / 1000;
			resultObj.daterec = Date.parse(resultObj.daterec) / 1000;
			resultObj.id = 0+ +resultObj.id;
			resultObj.pub = 0+ +resultObj.pub;

			var links = Object.keys(resultObj).filter(function(k, v) {return k.indexOf("links[") > -1});
			// document.body.innerHTML = JSON.stringify(resultObj);
		</script>
	</body>
</html>
