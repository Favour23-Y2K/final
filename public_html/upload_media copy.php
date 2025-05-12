<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../assets/apache/constants.php');
$type = $_POST['type'];

// Check if this is a chat upload
$isChatUpload = isset($_POST['upload_context']) && $_POST['upload_context'] === 'chat';

// Media server settings
if ($isChatUpload) {
    $mediaServerUploadDir = $type === 'image' ? '/photo/direct/' : 
                            ($type === 'video' ? '/video/direct/' : '/attachment/');
} else {
    // Use existing logic for non-chat uploads
    $mediaServerUploadDir = $type === 'image' ? '/photo/post/' : '/video/post/';
}

$thumbnailServerUploadDir = '/thumbs/';

$response = ['success' => false, 'error' => '', 'path' => '', 'thumbnail_path' => ''];

if (!empty($_FILES['media'])) {
    $file = $_FILES['media'];
    $name = $file['name'];
    $tmp_name = $file['tmp_name'];

    // Generate a unique filename
    $fileExtension = pathinfo($name, PATHINFO_EXTENSION);
    $uniqueName = uniqid() . '.' . $fileExtension;
    $targetPath = $mediaServerUploadDir . $uniqueName;

    // Generate the thumbnail name using MD5 hash of the unique filename
    $thumbnailName = md5($uniqueName) . '.png';

    // Read the file content
    $fileContent = file_get_contents($tmp_name);

    if ($fileContent === false) {
        $response['error'] = 'Failed to read uploaded file.';
    } else {
        // Prepare the POST request for main media
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/octet-stream\r\n" .
                             "X-Filename: " . $uniqueName . "\r\n" .
                             "X-Directory: " . $mediaServerUploadDir,
                'content' => $fileContent
            ]
        ];
        $context = stream_context_create($options);

        // Send the POST request to the media server
        $mediaServerURL = MEDIA_LOCALE . '/receive_upload.php';
        $result = file_get_contents($mediaServerURL, false, $context);

        if ($result === false) {
            $error = error_get_last();
            $response['error'] = 'Failed to upload file to media server. ' . ($error['message'] ?? '');
        } else {
            $mediaResponse = json_decode($result, true);
            if ($mediaResponse && isset($mediaResponse['success']) && $mediaResponse['success']) {
                $response['success'] = true;
                $response['path'] = MEDIA_LOCALE . $targetPath;

                // Handle thumbnail upload
                if (!empty($_FILES['thumbnail'])) {
                    $thumbnail = $_FILES['thumbnail'];
                    $thumbnailContent = file_get_contents($thumbnail['tmp_name']);

                    if ($thumbnailContent !== false) {
                        $thumbnailOptions = [
                            'http' => [
                                'method'  => 'POST',
                                'header'  => "Content-type: application/octet-stream\r\n" .
                                             "X-Filename: " . $thumbnailName . "\r\n" .
                                             "X-Directory: " . $thumbnailServerUploadDir,
                                'content' => $thumbnailContent
                            ]
                        ];
                        $thumbnailContext = stream_context_create($thumbnailOptions);
                        $thumbnailResult = file_get_contents($mediaServerURL, false, $thumbnailContext);

                        if ($thumbnailResult !== false) {
                            $thumbnailResponse = json_decode($thumbnailResult, true);
                            if ($thumbnailResponse && isset($thumbnailResponse['success']) && $thumbnailResponse['success']) {
                                $response['thumbnail_path'] = MEDIA_LOCALE . $thumbnailServerUploadDir . $thumbnailName;
                            } else {
                                $response['error'] .= ' Thumbnail upload failed: ' . ($thumbnailResponse['error'] ?? 'Unknown error');
                            }
                        } else {
                            $thumbnailError = error_get_last();
                            $response['error'] .= ' Failed to upload thumbnail to media server. ' . ($thumbnailError['message'] ?? '');
                        }
                    } else {
                        $response['error'] .= ' Failed to read thumbnail file.';
                    }
                }
            } else {
                $response['error'] = 'Media server reported an error: ' . ($mediaResponse['error'] ?? 'Unknown error');
            }
        }
    }
} else {
    $response['error'] = 'No file uploaded.';
}

echo json_encode($response);
