
<?php
$to      = 'sxfyxl@126.com';
$subject = 'the subject';
$message = 'hello';
$headers = 'From: webmaster@example.com' . "\r\n" .
    'Reply-To: webmaster@example.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$status = mail($to, $subject, $message, $headers);
if($status){ echo 'ok'; }else{ echo 'bad'; }
?>
