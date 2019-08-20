<?php

function curl($pageId) {
	$ch = curl_init();
	      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	      curl_setopt($ch, CURLOPT_URL, "https://nhentai.net/g/".$pageId);
	      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36");
	      //curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$h[] = "referer: https://nhentai.net/";
	$h[] = "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3";
	      curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
	$exec = curl_exec($ch);
    $info = curl_getinfo($ch);
 		    curl_close($ch);
 	return (object) [
 		'resp' => $exec,
 		'info' => $info
 	];
}

function downloadImage($pageId, $mediaId, $no){
	$ch = curl_init();
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		  curl_setopt($ch, CURLOPT_URL, "https://i.nhentai.net/galleries/$mediaId/$no.jpg");
	      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36");
	      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$h[] = "referer: https://nhentai.net/g/".$pageId;
	$h[] = "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3";
	      curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
	$ex = curl_exec($ch);
	      curl_close($ch);
	return $ex;
}

if (isset($_GET['code'])) {
	$id = $_GET['code'];

	$getPage = curl($id);
	// var_dump($getPage)->response;
	if ($getPage->info["http_code"] == 200) {

		preg_match_all('/N\.gallery\((.*?)\);/', $getPage->resp, $f);
		$js = json_decode($f[1][0]);
		$mediaId = $js->media_id;
		$pages = $js->num_pages;
		$title = $js->title->pretty;

		echo "Media ID: $mediaId<br>Title: $title<br> Total Page: $pages<br>";
		@mkdir($id);
		@chdir($id);
		for ($no=1;$no<=$pages;$no++) {
			$raw = downloadImage($code,$mediaId,$no);
			$fp = fopen($no.".jpg", "w");
				  fwrite($fp, $raw);
				  fclose($fp);

		}
		@chdir('..');
		(exec("zip -r $id.zip $id > /dev/null")) ? $resp="DOWNLOAD ZIP: <a href=$id.zip>$id.zip</a>" : $resp='DOWNLOAD ZIP: <font color=red>NOT FOUND</font>';
		echo $resp;
	} else if($getPage->info["http_code"] == 400) {
		$output = array('error' => true, array(
				'http_code' => $getPage->info["http_code"],
				'text' => 'code '.$id.' NOT FOUND in NHENTAI',
				'time' => date("F d, Y h:i:s A"),
			));
		header('Content-type: application/json');
		echo json_encode($output);
	} else if ($getPage->info["http_code"] == 0) {
		$output = array('error' => true, array(
				'http_code' => $getPage->info["http_code"],
				'text' => 'failed connection',
				'time' => date("F d, Y h:i:s A"),
			));
		header('Content-type: application/json');
		echo json_encode($output);
	} else {
		$output = array('error' => true, array(
				'http_code' => NULL,
				'text' => 'No action! bad code detection',
				'time' => date("F d, Y h:i:s A"),
			));
		header('Content-type: application/json');
		echo json_encode($output);
	}
} else {
	$output = array('error' => true, array(
				'text' => 'code params not found',
				'usage' => "http://".$_SERVER['HTTP_HOST']."/".$_SERVER['REQUEST_URI']."?code=[NHENTAI CODE]",
				'time' => date("F d, Y h:i:s A"),
			));
	header('Content-type: application/json');
	echo json_encode($output);
}
