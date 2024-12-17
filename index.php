<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['images'])) {
        $errors = [];
        // Loop through each uploaded file
        foreach ($_FILES['images']['error'] as $index => $error) {
            // Check if the file was uploaded successfully
            if ($error !== UPLOAD_ERR_OK) {
                // If there's an error, add it to the errors array
                $errors[] = "Error with file upload: " . $error;
            }
        }
        
        if (empty($errors)) {
            // No errors, proceed to process the uploaded files
            // You can move the uploaded files to a specific directory, for example:
            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                // Define where you want to store the uploaded file
                $uploadDir = 'uploads/';
                $fileName = basename($_FILES['images']['name'][$index]);
                $uploadFilePath = $uploadDir . $fileName;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($tmpName, $uploadFilePath)) {
                    echo "File uploaded successfully: $fileName<br>";
                } else {
                    echo "Failed to upload file: $fileName<br>";
                }
            }
        } else {
            // Display all errors
            foreach ($errors as $error) {
                echo "<p style='color: red;'>$error</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>No files uploaded. Please try again.</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload with Compression/Conversion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
        }
        .upload-container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border: 2px dashed #ccc;
            border-radius: 10px;
            text-align: center;
        }
        .upload-container input[type="file"] {
            display: none;
        }
        .upload-container label {
            display: block;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .image-preview {
            position: relative;
            width: 120px;
            height: 120px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .image-preview button {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 14px;
            line-height: 25px;
            text-align: center;
        }
        .options-container {
            text-align: left;
            margin-top: 20px;
        }
        .options-container select {
            padding: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Upload Images with Options</h1>
    <form action="controller/process.php" method="POST" enctype="multipart/form-data">
        <div class="upload-container">
            <label for="imageInput">Choose Images</label>
            <input type="file" id="imageInput" name="images[]" multiple accept="image/*">
            <div class="preview-container" id="previewContainer"></div>
        </div>
        <br><br>
        <div class="upload-container">
            <label>
                <input type="checkbox" id="compressCheckbox" name="compress" value="true">
                Compress Images
            </label>
            <div id="compressionOptions" style="display: none;">
                <label for="compressionRate">Select Compression Rate:</label>
                <select id="compressionRate" name="compressionRate">
                    <option value="25">25%</option>
                    <option value="50">50%</option>
                    <option value="75">75%</option>
                </select>
            </div>
        </div>

        <div class="upload-container">
            <label>
                <input type="checkbox" id="convertCheckbox" name="convert" value="true">
                Convert Images
            </label>
            <div id="formatOptions" style="display: none;">
                <label for="formatDropdown">Select Format:</label>
                <select id="formatDropdown" name="format">
                    <option value="jpeg">JPEG</option>
                    <option value="png">PNG</option>
                    <option value="gif">GIF</option>
                    <option value="webp">WebP</option>
                </select>
            </div>
            <br><br>
            <button type="submit">Convert</button>
        </div>
    </form>

    <script>
        const imageInput = document.getElementById("imageInput");
        const previewContainer = document.getElementById("previewContainer");
        const convertCheckbox = document.getElementById("convertCheckbox");
        const formatOptions = document.getElementById("formatOptions");
        const compressCheckbox = document.getElementById("compressCheckbox");
        const compressionOptions = document.getElementById("compressionOptions");

        let uploadedImages = [];

        // Handle file input change
        imageInput.addEventListener("change", (event) => {
            const files = Array.from(event.target.files);

            files.forEach((file) => {
                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const imageSrc = e.target.result;

                        // Add image to the array
                        uploadedImages.push({ name: file.name, src: imageSrc });

                        // Render the preview
                        renderPreviews();
                    };
                    reader.readAsDataURL(file);
                }
            });

            //event.target.value = ""; // Clear file input
        });

        // Render image previews
        function renderPreviews() {
            previewContainer.innerHTML = ""; // Clear previews

            uploadedImages.forEach((image, index) => {
                const previewDiv = document.createElement("div");
                previewDiv.classList.add("image-preview");

                // Image element
                const img = document.createElement("img");
                img.src = image.src;

                // Remove button
                const removeButton = document.createElement("button");
                removeButton.innerHTML = "&times;";
                removeButton.addEventListener("click", () => removeImage(index));

                previewDiv.appendChild(img);
                previewDiv.appendChild(removeButton);

                previewContainer.appendChild(previewDiv);
            });
        }

        // Remove image
        function removeImage(index) {
            uploadedImages.splice(index, 1); // Remove the image from the array
            renderPreviews(); // Re-render the previews
        }

        // Toggle format dropdown when "Convert" checkbox is clicked
        convertCheckbox.addEventListener("change", (event) => {
            formatOptions.style.display = event.target.checked ? "block" : "none";
        });

        // Toggle compression rate dropdown when "Compress" checkbox is clicked
        compressCheckbox.addEventListener("change", (event) => {
            compressionOptions.style.display = event.target.checked ? "block" : "none";
        });
    </script>
</body>
</html>
