<?php
!function_exists('usermsg') && exit('Forbidden');

if ($job!='update') {
	$rt = $db->get_one("SELECT subject,content FROM pw_lcustom WHERE authorid='$admin_uid' AND sign='notice'");
	$title = $rt['subject'];
	$content = $rt['content'];
	require_once PrintEot('usernotice');footer();
} else {
	$subject = Char_cv($title);
	$content = Char_cv($content);
	$rt = $db->get_one("SELECT id FROM pw_lcustom WHERE authorid='$admin_uid' AND sign='notice'");
	$rt['id'] ? $db->update("UPDATE pw_lcustom SET subject='$subject',content='$content' WHERE authorid='$admin_uid' AND sign='notice'") : $db->update("INSERT INTO pw_lcustom(authorid,sign,setdate,subject,content) VALUES ('$admin_uid','notice','$timestamp','$subject','$content')");
	usermsg('operate_success',$basename);
}
?>