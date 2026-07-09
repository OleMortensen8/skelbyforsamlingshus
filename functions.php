<?php
function imagepicker($group) {
    // Directory where images are stored
    $imageDir = 'assets/img/skelby/' . $group . '/thumbnails/';

    // Get all image files from the directory
    $images = glob($imageDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    // Check if there are any images
    if ($images) {
        // Pick a random image
        $randomImage = $images[array_rand($images)];

        // Extract the filename for use in the alt attribute, for accessibility
        $filename = basename($randomImage);

        // Display the image
        echo "<img class='img-front' src='$randomImage' alt='Random image from gallery: $filename' />";
    } else {
        echo "No images found in the directory.";
    }
}