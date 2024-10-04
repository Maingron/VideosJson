<?php
$input_creatorId = 2;
DEFINE("VIDPATH", __DIR__."/../vids");
$vidsArray = scandir(VIDPATH);
$vids = [];
$vids[] = json_decode("{}", true);

foreach($vidsArray as $vid) {
	if(is_numeric($vid)) {
		$vidtemp = json_decode(file_get_contents(VIDPATH . "/" . $vid . "/meta.json"), true);
		if(!isset($vidtemp['pub']) || $vidtemp['pub'] != $input_creatorId) {
			continue;
		}
		$vids[] = $vidtemp;
	}
}

// header('Content-Type: application/json');

echo json_encode($vids);
