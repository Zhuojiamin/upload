<?php
!function_exists('readover') && exit('Forbidden');
function Download_file($filedb,$downsize="20000") {
	//function by noizy
	set_time_limit(0);
	$http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$ext		= substr(strrchr($filedb['attachurl'],'.'),1);
	$fname		= &$filedb['name'];
	$fsize		= sprintf("%u",filesize($filedb['attachurl']));
	$attachment	= 'attachment';
	if (isset($http_user_agent)) {
		if (strpos($http_user_agent, 'msie')!==false) {
			ini_set('zlib.output_compression','Off');
			$ext=='torrent' && $attachment='inline';
			$fname = rawurldecode($filedb['name']);
			$type = 'application/octetstream';
		} elseif (strpos($http_user_agent, 'opera')!==false) {
			$type = 'application/octetstream';
		} else {
			$type = 'application/octet-stream';
		}
	}
	$fp = '';
	file_exists($filedb['attachurl']) && $fp = fopen($filedb['attachurl'], "rb");
	if (empty($fp)) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	if ($_SERVER['HTTP_RANGE']) {
		if (!preg_match("/^bytes=(\\d+)-(\\d*)$/", $_SERVER['HTTP_RANGE'], $matches)) {
			header("HTTP/1.1 500 Internal Server Error");
			exit;
		}
		$ffrom	= $matches[1];
		$fto	= $matches[2];
		empty($fto) && $fto = $fsize - 1;
		$content_size = $fto - $ffrom + 1;
		header('HTTP/1.1 206 Partial Content');
		header("Content-Range: $ffrom-$fto/$fsize");
		header('Content-Length: '.$content_size);
		header('Content-Type: '.$type);
		header('Content-Disposition: '.$attachment.'; filename='.$fname);
		header('Content-Transfer-Encoding: binary');
		header('X-Powered-By: LxBlog/Noizy');
		header('Pragma: no-cache');
		header('Content-Encoding: none');
		fseek($fp,$ffrom);
		$cur_pos = ftell($fp);
		while ($cur_pos !== false && ftell($fp) + $downsize < $fto+1) {
			$downcontent = fread($fp, $downsize);
			echo $downcontent;
			$cur_pos = ftell($fp);
			flush();
		}
		$downcontent = fread($fp, $fto+1-$cur_pos);
		echo $downcontent;
	} else {
		while (ob_get_length() !== false) @ob_end_clean();
		header('HTTP/1.1 200 OK');
		header('Content-Length: '.$fsize);
		header('Content-Type: '.$type);
		header('Content-Disposition: '.$attachment.'; filename='.$fname);
		header('Content-Transfer-Encoding: binary');
		header("Content-Encoding: none");
		header('X-Powered-By: LxBlog/Noizy');
		header('Pragma: no-cache');
		header('Content-Encoding: none');
		while ($downcontent = fread($fp, $downsize)) {
			echo $downcontent;
			flush();
		}
	}
	fclose($fp);
}
?>