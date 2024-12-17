<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = [];
    $uploadedImages = [];
    $zip = new ZipArchive();

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $zipFileName = $uploadDir . 'images_' . uniqid() . '.zip';

    if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
        die("Unable to create zip file. Please check the directory permissions.");
    }

    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $images = $_FILES['images'];
        $compress = isset($_POST['compress']) ? $_POST['compress'] : false;
        $convert = isset($_POST['convert']) ? $_POST['convert'] : false;
        $compressionRate = isset($_POST['compressionRate']) ? $_POST['compressionRate'] : 75;
        $format = isset($_POST['format']) ? $_POST['format'] : 'jpeg';

        foreach ($images['name'] as $index => $imageName) {
            $imageTmpName = $images['tmp_name'][$index];
            $imageError = $images['error'][$index];

            if ($imageError === 0) {
                $newImageName = uniqid('', true) . '.' . pathinfo($imageName, PATHINFO_EXTENSION);

                $imageInfo = getimagesize($imageTmpName);
                $imageWidth = $imageInfo[0];
                $imageHeight = $imageInfo[1];

                switch ($imageInfo['mime']) {
                    case 'image/jpeg':
                        $image = imagecreatefromjpeg($imageTmpName);
                        break;
                    case 'image/png':
                        $image = imagecreatefrompng($imageTmpName);
                        break;
                    case 'image/gif':
                        $image = imagecreatefromgif($imageTmpName);
                        break;
                    case 'image/webp':
                        $image = imagecreatefromwebp($imageTmpName);
                        break;
                    default:
                        $errors[] = "Unsupported image format: $imageName";
                        continue 2;
                }

                // Compression
                if ($compress) {
                    $compressionQuality = 100 - $compressionRate; // Adjust based on input rate

                    switch ($imageInfo['mime']) {
                        case 'image/jpeg':
                            imagejpeg($image, "uploads/$newImageName", $compressionQuality);
                            break;
                        case 'image/png':
                            imagepng($image, "uploads/$newImageName", (int)(9 - $compressionRate / 11));
                            break;
                        case 'image/gif':
                            imagegif($image, "uploads/$newImageName");
                            break;
                        case 'image/webp':
                            imagewebp($image, "uploads/$newImageName", $compressionQuality);
                            break;
                    }
                } else {
                    move_uploaded_file($imageTmpName, "uploads/$newImageName");
                }

                // Convert if needed
                if ($convert) {
                    $convertedImageName = uniqid('', true) . '.' . $format;

                    switch ($format) {
                        case 'jpeg':
                            imagejpeg($image, "uploads/$convertedImageName", 100); 
                            break;
                        case 'png':
                            imagepng($image, "uploads/$convertedImageName", 0); 
                            break;
                        case 'gif':
                            imagegif($image, "uploads/$convertedImageName"); 
                            break;
                        case 'webp':
                            imagewebp($image, "uploads/$convertedImageName", 100); 
                            break;
                        default:
                            $errors[] = "Invalid format for conversion: $format";
                            continue 2;
                    }

                    // Update the name to the converted image
                    $newImageName = $convertedImageName;
                }

                // Add image to zip
                $zip->addFile("uploads/$newImageName", $newImageName);
                $uploadedImages[] = "uploads/$newImageName";
                imagedestroy($image);
            } else {
                $errors[] = "Error uploading $imageName.";
            }
        }

        $zip->close();
        if (empty($errors)) {
            echo "Images uploaded, compressed, and converted into a ZIP file successfully!<br>";
            echo "Download the zip file: <a href='$zipFileName' download>Click here</a>";
        } else {
            foreach ($errors as $error) {
                echo "<p style='color: red;'>$error</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>No files uploaded. Please try again.</p>";
    }
}
?>
