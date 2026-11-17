
<?php
require_once 'config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        if (!isset($_FILES['document'])) {
            throw new Exception('Please select a file.');
        }

        $file = $_FILES['document'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed.');
        }

        // Maximum file size: 10 MB
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new Exception('Maximum file size is 10 MB.');
        }

        // Allowed file types
        $allowedTypes = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png'
        ];

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (
            !isset($allowedTypes[$extension]) ||
            $allowedTypes[$extension] !== $mimeType
        ) {
            throw new Exception('Only PDF, JPG, JPEG and PNG files are allowed.');
        }

        $fileData = file_get_contents($file['tmp_name']);

        if ($fileData === false) {
            throw new Exception('Could not read the uploaded file.');
        }

        // Encrypt the file
        $encryptedData = encryptData($fileData);

        // Generate a random storage ID
        $storageId = bin2hex(random_bytes(16));

        // Store encrypted data and file information
        $payload = [
            'original_name' => basename(str_replace('\\', '/', $file['name'])),
            'mime_type' => $mimeType,
            'iv' => $encryptedData['iv'],
            'tag' => $encryptedData['tag'],
            'data' => $encryptedData['data']
        ];

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        $filePath = UPLOAD_DIR . $storageId . '.enc';

        $saved = file_put_contents(
            $filePath,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            LOCK_EX
        );

        if ($saved === false) {
            throw new Exception('Could not save the encrypted file.');
        }

        $message = 'File uploaded and encrypted successfully!';
        $messageType = 'success';

    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure File Upload</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f0fa;
            color: #302747;
        }

        .container {
            max-width: 650px;
            margin: 70px auto;
            padding: 35px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(50, 30, 80, 0.08);
        }

        h1 {
            color: #7041a5;
            margin-bottom: 10px;
        }

        .description {
            color: #777;
            margin-bottom: 30px;
        }

        .upload-box {
            padding: 35px;
            border: 2px dashed #b99bdc;
            border-radius: 14px;
            text-align: center;
            background: #faf8fd;
        }

        input[type="file"] {
            display: block;
            width: 100%;
            margin: 20px 0;
        }

        button {
            background: #7041a5;
            color: white;
            border: none;
            padding: 13px 25px;
            border-radius: 9px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #583184;
        }

        .message {
            padding: 13px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .success {
            background: #e4f6e9;
            color: #24713c;
        }

        .error {
            background: #fde8e8;
            color: #a52a2a;
        }

        .links {
            margin-top: 25px;
            display: flex;
            gap: 20px;
        }

        a {
            color: #7041a5;
            text-decoration: none;
            font-weight: bold;
        }

        .info {
            margin-top: 25px;
            color: #888;
            font-size: 13px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Secure File Upload</h1>

    <p class="description">
        Upload your documents and protect them with encryption.
    </p>

    <?php if ($message): ?>
        <div class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="upload-box">

        <h3>Select Your Document</h3>

        <form method="POST" enctype="multipart/form-data">

            <input
                type="file"
                name="document"
                accept=".pdf,.jpg,.jpeg,.png"
                required
            >

            <button type="submit">
                Upload & Encrypt
            </button>

        </form>

    </div>

    <div class="links">
        <a href="files.php">View Encrypted Files →</a>
    </div>

    <p class="info">
        Supported formats: PDF, JPG, JPEG, PNG.
        Maximum file size: 10 MB.
    </p>

</div>

</body>
</html>
