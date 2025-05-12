<?php

if (isset($_POST['qr_code'])) {
    $qrDataURL = $_POST['qr_code'];

    // Remove the "data:image/png;base64," part and decode the base64 string
    $qrData = str_replace('data:image/png;base64,', '', $qrDataURL);
    $qrData = str_replace(' ', '+', $qrData);
    $qrImage = base64_decode($qrData);

    // Create an image from the base64 QR code
    $qr = imagecreatefromstring($qrImage);

    if ($qr === false) {
        die("Invalid QR code image data.");
    }

    // Background image (customized design)
    $background_image = "img/qr-bg.png";
    $ext = strtolower(pathinfo($background_image, PATHINFO_EXTENSION));

    // Load the background image based on its extension
    if ($ext == 'jpeg' || $ext == 'jpg') {
        $background = imagecreatefromjpeg($background_image);
    } elseif ($ext == 'png') {
        $background = imagecreatefrompng($background_image);
    } else {
        die("Unsupported background image format: " . $ext);
    }

    // Resize the QR code to 450x450 pixels
    $qr_resized = imagecreatetruecolor(450, 450);
    imagecopyresampled($qr_resized, $qr, 0, 0, 0, 0, 450, 450, imagesx($qr), imagesy($qr));

    // Get background dimensions
    $bg_width = imagesx($background);
    $bg_height = imagesy($background);

    // Calculate the coordinates to center the QR code on the background
    $x = ($bg_width / 2) - (450 / 2);
    $y = ($bg_height / 2) - (450 / 2);

    // Merge the resized QR code onto the background
    imagecopy($background, $qr_resized, $x, $y, 0, 0, 450, 450);

    // Output the final merged image (QR + background) as base64
    ob_start(); // Start output buffering
    imagepng($background); // Output image as PNG
    $final_image_data = ob_get_clean(); // Get the image data

    // Also return the resized QR code separately as base64
    ob_start();
    imagepng($qr_resized);
    $qr_image_data = ob_get_clean();

    // Send both images as base64-encoded data
    echo json_encode([
        'qr_code' => 'data:image/png;base64,' . base64_encode($qr_image_data),
        'final_image' => 'data:image/png;base64,' . base64_encode($final_image_data),
    ]);

    // Free up memory
    imagedestroy($background);
    imagedestroy($qr);
    imagedestroy($qr_resized);
} else {
    die("No QR code data received.");
}

?>
