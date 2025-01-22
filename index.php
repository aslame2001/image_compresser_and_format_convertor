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
        .loading {
    display: none;
    text-align: center;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); 
}
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Upload Images with Options</h1>
    <form id="uploadForm" enctype="multipart/form-data">
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

    <div class="loading" id="loading">
        <img src="https://i.gifer.com/YCZH.gif" alt="Loading..."> 
    </div>

    <div class="popup" id="popup">
        <div id="popupMessage"></div>
        <button id="popupCloseBtn">Close</button>
        <a href="" id="downloadBtn" style="display: none;">Download File</a>
    </div>

    <script>
        const imageInput = document.getElementById("imageInput");
        const previewContainer = document.getElementById("previewContainer");
        const convertCheckbox = document.getElementById("convertCheckbox");
        const formatOptions = document.getElementById("formatOptions");
        const compressCheckbox = document.getElementById("compressCheckbox");
        const compressionOptions = document.getElementById("compressionOptions");
        const uploadForm = document.getElementById("uploadForm");
        const loading = document.getElementById("loading");
        const popup = document.getElementById("popup");
        const popupMessage = document.getElementById("popupMessage");
        const popupCloseBtn = document.getElementById("popupCloseBtn");
        const downloadBtn = document.getElementById("downloadBtn");

        let uploadedImages = [];

        imageInput.addEventListener("change", (event) => {
            const files = Array.from(event.target.files);

            files.forEach((file) => {
                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const imageSrc = e.target.result;

                        uploadedImages.push({ name: file.name, src: imageSrc });

                        renderPreviews();
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        function renderPreviews() {
            previewContainer.innerHTML = "";

            uploadedImages.forEach((image, index) => {
                const previewDiv = document.createElement("div");
                previewDiv.classList.add("image-preview");

                const img = document.createElement("img");
                img.src = image.src;

                const removeButton = document.createElement("button");
                removeButton.innerHTML = "&times;";
                removeButton.addEventListener("click", () => removeImage(index));

                previewDiv.appendChild(img);
                previewDiv.appendChild(removeButton);

                previewContainer.appendChild(previewDiv);
            });
        }

        function removeImage(index) {
            uploadedImages.splice(index, 1);
            renderPreviews();
        }

        convertCheckbox.addEventListener("change", (event) => {
            formatOptions.style.display = event.target.checked ? "block" : "none";
        });

        compressCheckbox.addEventListener("change", (event) => {
            compressionOptions.style.display = event.target.checked ? "block" : "none";
        });

        uploadForm.addEventListener("submit", (event) => {
        event.preventDefault();

        loading.style.display = "block";

        const formData = new FormData(uploadForm);

        fetch("controller/process.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loading.style.display = "none";

            if (data.success) {
                popupMessage.textContent = "Upload successful!";
                downloadBtn.style.display = "block";
                downloadBtn.href = 'controller/' + data.fileLink; 
            } else {
                popupMessage.textContent = "Upload failed: " + data.message;
                downloadBtn.style.display = "none";
            }

            popup.style.display = "block";
        })
        .catch(error => {
            loading.style.display = "none";
            popupMessage.textContent = "An error occurred: " + error.message;
            popup.style.display = "block";
        });
    });

        popupCloseBtn.addEventListener("click", () => {
            popup.style.display = "none";
        });
    </script>
</body>
</html>
