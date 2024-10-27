<?php
$input_creatorId = null;
$input_videoId = null;

DEFINE("VIDPATH", __DIR__."/../vids");
$vidsArray = scandir(VIDPATH);
$vids = [];
$vids[] = json_decode("{}", true);

function getAllVids($vidsArray) {
	foreach($vidsArray as $vid) {
		if(is_numeric($vid)) {
			if(file_exists(VIDPATH . "/" . $vid . "/meta.json")) {
				$temp1 = json_decode(file_get_contents(VIDPATH . "/" . $vid . "/meta.json"), true);
				if(isset($temp1[0])) { // We got a file with multiple videos in an array
					foreach($temp1 as $subentry) {
						$vids[] = $subentry;
					}
				} else {
					$vids[] = $temp1;
				}
			}
		}
	}
	return $vids;
}

function filterVidsByPub($pub, $vidsjson) {
	$temp1 = [];
	foreach($vidsjson as $vid) {
		if(!isset($vid['pub']) || $vid['pub'] != $pub) {
			continue;
		}

		$temp1[] = $vid;
	}

	return $temp1;
}

function filterVidsById($id, $vidsjson) {
	$temp1 = [];
	foreach($vidsjson as $vid) {
		if(!isset($vid['id']) || $vid['id'] != $id) {
			continue;
		}

		$temp1[] = $vid;
		return $temp1;
	}

	return $temp1;
}

$vids = getAllVids($vidsArray);

if($input_creatorId != null) {
	$vids = filterVidsByPub($input_creatorId, $vids);
}

if($input_videoId != null) {
	$vids = filterVidsById($input_videoId, $vids);
}

function addFallbackToVids($vidsjson) {
	$newvidsjson = [];
	foreach($vidsjson as $vid) {
		if(!isset($vid['thumbnails']) || empty($vid['thumbnails'])) {
			if(isset($vid['links']['youtube'])) {
				$vid['thumbnails'][0] = "https://img.youtube.com/vi/".$vid['links']['youtube']."/maxresdefault.jpg";
			} else {
				$vid['thumbnails'][] = "";
			}
		}

		if(!($vid['description']??!1)) {
			$vid['description'] = null;
		}

		// Convert to timestamps if human dates are used:
		$convertKeyValuesToTimestamp = ['dateRec', 'datePub'];

		foreach($convertKeyValuesToTimestamp as $vidKey) {
			if(isset($vid[$vidKey]) && gettype($vid[$vidKey]) == 'string') {
				$vid[$vidKey] = strtotime($vid[$vidKey]);
			}
		}

		$newvidsjson[] = $vid;
	}

	return $newvidsjson;
}

$vids = addFallbackToVids($vids);

// foreach($vidsArray as $vid) {
// 	if(is_numeric($vid)) {
// 		$vidtemp = json_decode(file_get_contents(VIDPATH . "/" . $vid . "/meta.json"), true);
// 		if($input_creatorId != null) {
// 			if(!isset($vidtemp['pub']) || $vidtemp['pub'] != $input_creatorId) {
// 				continue;
// 			}
// 		}



// 		$vids[] = $vidtemp;
// 	}
// }

// header('Content-Type: application/json');

echo json_encode($vids);
