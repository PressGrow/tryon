<h1>Tryon Plus Settings</h1>
<p>Upload license file</p>
<?php
foreach ($errors as $error) {
    echo $error . '<br>';
}
?>
<form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('tryon_content', 'tryon_nonce_field'); ?>
    <p>
        <input type='file' name='license'>
    </p>
    <p>
        <input type='hidden' name='action' value='tryon_content'>
        <input type='submit' value='Submit Content'>
    </p>
</form>