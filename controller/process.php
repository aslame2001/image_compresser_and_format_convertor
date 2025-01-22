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
        echo json_encode(['success' => false, 'message' => 'Unable to create zip file.']);
        exit;
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


                if ($compress) {
                    $compressionQuality = 100 - $compressionRate;

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
                            imagewebp($image, "uploads/$convertedImageName"); 
                            break;
                    }

                    unlink("uploads/$newImageName");
                    $newImageName = $convertedImageName;
                }

                $zip->addFile("uploads/$newImageName", $newImageName);
                $uploadedImages[] = $newImageName;

                imagedestroy($image);
            } else {
                $errors[] = "Error uploading image: $imageName";
            }
        }

        $zip->close();

        if (empty($errors)) {
            echo json_encode([
                'success' => true,
                'fileLink' => $zipFileName
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No images uploaded.']);
    }
}
?>
