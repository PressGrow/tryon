<?php

define('UPLOAD_DIR', dirname(__FILE__) . '/TryonSavings/');
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
$img = $_POST['img'];

$img = str_replace('data:image/png;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$data = base64_decode($img);
$filename = uniqid() . '.png';
$success = file_put_contents(UPLOAD_DIR . $filename, $data);
print $success ? UPLOAD_URL_DIR . $filename : 'Unable to save the file.';
?>