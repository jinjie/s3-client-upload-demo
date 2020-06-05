<?php

use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Dotenv\Dotenv;

require "vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Start a session so we can get a unique key for this session.
// We do not want to overwrite existing files when they are the
// same
session_start();

if (empty($_SESSION["prefix"])) {
    $prefix = time() . "_" . session_id(); // Add time() to make it more unique
    $_SESSION["prefix"] = $prefix;
} else {
    $prefix = $_SESSION["prefix"];
}

$s3client = new S3Client([
    'version'           => 'latest',
    'signature_version' => 'v4',
    'region'            => getenv('AWS_REGION'),

    // Optional, as long as both the keys is set in PHP environment variables
    // 'credentials'       => [
    //     'key'    => getenv('AWS_ACCESS_KEY_ID'),
    //     'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
    // ],
]);

$bucket = getenv('AWS_S3_BUCKET');

$formInputs = [
    'acl' => 'private',
    'key' => $prefix . '_${filename}', // Change _ to / if you want to store it in directory instead
];

$postObject = new PostObjectV4(
    $s3client,
    $bucket,
    $formInputs,
    [
        ['acl'    => 'private'],
        ['bucket' => $bucket],
        ['starts-with', '$key', $prefix],
    ],
    '+10 minutes'
);

$formAttributes = $postObject->getFormAttributes();
$formInputs = $postObject->getFormInputs();

?><!DOCTYPE html>
<html>
<head>
    <title>S3 Client Upload Demo</title>
    <style>
        body {
            margin: 1rem;
        }
        textarea {
            width: 100%;
        }
    </style>
    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.5.3/dist/css/uikit.min.css" />
    <!-- UIkit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.5.3/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.5.3/dist/js/uikit-icons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body class="uk-padding">
    <h1>S3 Client Upload Demo</h1>

    <label>Form Attributes</label>
    <textarea name="form_attributes" id="" cols="30" rows="10" class="uk-textarea"><?php print_r($formAttributes); ?></textarea>
    <label>Form Inputs</label>
    <textarea name="form_inputs" id="" cols="30" rows="10" class="uk-textarea"><?php print_r($formInputs); ?></textarea>

    <form 
        <?php
            // Build form attributes
            foreach ($formAttributes as $key => $value) {
                echo "{$key}=\"{$value}\" ";
            }?> class="uk-margin" id="upload-form">
        
        <?php 
            // Build extra form inputs
            foreach ($formInputs as $key => $value) {
                echo "<input type=\"hidden\" name=\"{$key}\" value=\"{$value}\" />";
            }
        ?>

        <div uk-form-custom>
            <input type="file" name="file">
            <button class="uk-button uk-button-default" type="button" tabindex="-1">Select File</button>
        </div>

        <button type="submit" name="submit" class="uk-button uk-button-primary">Start Upload</button>

        <div>
            <label>File Info</label>
            <textarea name="file_info" id="file_info" cols="30" rows="10" class="uk-textarea"></textarea>
        </div>
    </form>
    <script>
        $("input[name=file]").on("change", function() {
            var fileInput = $("input[name=file]")[0];
            var fileInfo = $("#file_info");

            var file = fileInput.files[0];
            fileInfo.text(
                "Filename: " + file.name + "\n" +
                "Size: " + Math.round(file.size / 1000 / 1000) + " MB\n"
            );
        });

        $("#upload-form").on("submit", function(e) {
            var form = $(this);

            if (form.find("input[name=file]")[0].files.length <= 0) {
                alert("Please select file");
                return false;
            }

            var uploadButton = form.find("button[name=submit]");
            var defaultUploadText = uploadButton.text();

            uploadButton.attr("disabled", "disabled");
            uploadButton.text("Uploading...");

            // Uploading using AJAX. You can also use the basic POST if you want to
            $.ajax({
                url: form.attr("action"),
                method: form.attr("method"),
                data: new FormData(form[0]),
                contentType: false,
                processData: false
            }).done(function() {
                alert("File uploaded");
                location.reload();
            }).fail(function() {
                // Handle error
                alert("There was an error uploading.");
            }).always(function() {
                uploadButton.text(defaultUploadText);
                uploadButton.removeAttr("disabled");
            });

            e.preventDefault();
        });
    </script>
</body>
</html>