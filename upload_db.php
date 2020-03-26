<?php
# Taken mostly from https://www.php.net/manual/en/features.file-upload.php

if(isset($_FILES['upfile'])) {
    try {

        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (
            !isset($_FILES['upfile']['error']) ||
            is_array($_FILES['upfile']['error'])
        ) {
            throw new RuntimeException('Invalid parameters.');
        }

        // Check $_FILES['upfile']['error'] value.
        switch ($_FILES['upfile']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit (10 MB).');
            default:
                throw new RuntimeException('Unknown errors.');
        }

        // You should also check filesize here.
        if ($_FILES['upfile']['size'] > 10000000) { # 10 MB is ok
            throw new RuntimeException('Exceeded filesize limit (10 MB).');
        }

        // You should name it uniquely.
        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
        // On this example, obtain safe unique name from its binary data.
        $hash = sha1_file($_FILES['upfile']['tmp_name']);
        if (!move_uploaded_file(
            $_FILES['upfile']['tmp_name'],
            './dbs/' . $hash
        )) {
            throw new RuntimeException('Failed to move uploaded file.');
        }
        setcookie("beatoraja_db", $hash, 0, "/", $_SERVER['SERVER_NAME'], true, true);
        echo 'File is uploaded successfully. Your hash is: ' . $hash . ".<br><a href='/'>Return</a>";

    } catch (RuntimeException $e) {

        echo $e->getMessage();

    }
} else {
    ?>
<html>
<body>
<p>You can upload your beatoraja score.db here. It is located in the player folder. The maximum size is currently 10 MB.</p>
<p><b>Databases are only guaranteed to be kept one week.</b></p>
<form enctype="multipart/form-data" action="/upload_db.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
    score.db: <input name="upfile" type="file" />
    <input type="submit" value="Upload" />
</form>
</body>
</html>
    <?php
}
?>
