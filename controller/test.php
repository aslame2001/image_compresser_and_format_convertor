<?php
function getImageOptions() {
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $compress = isset($_POST['compress']) ? $_POST['compress'] : false;
        $convert = isset($_POST['convert']) ? $_POST['convert'] : false;
        $compressionRate = isset($_POST['compressionRate']) ? $_POST['compressionRate'] : 75;
        $format = isset($_POST['format']) ? $_POST['format'] : 'jpeg';

        return [$compress, $convert, $compressionRate, $format]; // Return as an array
    }
    return "no images";
}

// Usage example:
$options = getImageOptions();
if ($options !== "no images") {
    list($compress, $convert, $compressionRate, $format) = $options;

    // Print the values
    echo "Compress: " . ($compress ? 'Yes' : 'No') . "<br>";
    echo "Convert: " . ($convert ? 'Yes' : 'No') . "<br>";
    echo "Compression Rate: $compressionRate<br>";
    echo "Format: $format<br>";
} else {
    echo "No images uploaded.";
}
?>
