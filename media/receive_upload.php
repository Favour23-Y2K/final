<?php
// Get the filename and directory from the headers
$filename = $_SERVER['HTTP_X_FILENAME'] ?? '';
$directory = $_SERVER['HTTP_X_DIRECTORY'] ?? '';

// Ensure the upload directory exists
$uploadDir = __DIR__ . $directory;
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Full path where the file will be saved
$targetPath = $uploadDir . $filename;

// Read the raw POST data
$inputData = file_get_contents('php://input');

if ($inputData && $filename && $directory) {
    // Save the file
    if (file_put_contents($targetPath, $inputData)) {
        echo json_encode(['success' => true, 'path' => $directory . $filename]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request or missing data.']);
}