
<?php
require_once 'config.php';

$files = [];

if (is_dir(UPLOAD_DIR)) {

    $storedFiles = scandir(UPLOAD_DIR);

    foreach ($storedFiles as $storedFile) {

        if (!preg_match('/^[a-f0-9]{32}\.enc$/', $storedFile)) {
            continue;
        }

        $filePath = UPLOAD_DIR . $storedFile;

        $content = file_get_contents($filePath);

        if ($content === false) {
            continue;
        }

        $data = json_decode($content, true);

        if (
            !is_array($data) ||
            !isset($data['original_name'], $data['mime_type'])
        ) {
            continue;
        }

        $files[] = [
            'id' => substr($storedFile, 0, 32),
            'name' => $data['original_name'],
            'type' => $data['mime_type'],
            'size' => filesize($filePath)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Encrypted Documents</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px 20px;
            font-family: Arial, sans-serif;
            background: #f3f0fa;
            color: #302747;
        }

        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(50, 30, 80, 0.08);
        }

        h1 {
            color: #7041a5;
        }

        .description {
            color: #777;
            margin-bottom: 30px;
        }

        .file-card {
            padding: 18px;
            margin-bottom: 15px;
            border: 1px solid #eee7f5;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .file-name {
            font-weight: bold;
            word-break: break-word;
        }

        .file-info {
            color: #888;
            font-size: 13px;
            margin-top: 7px;
        }

        .download {
            background: #7041a5;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            white-space: nowrap;
        }

        .empty {
            padding: 30px;
            text-align: center;
            background: #faf8fd;
            border-radius: 12px;
            color: #888;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            color: #7041a5;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Encrypted Documents</h1>

    <p class="description">
        Manage and download your securely stored documents.
    </p>

    <?php if (empty($files)): ?>

        <div class="empty">
            No encrypted documents found.
            <p>Upload your first document to get started.</p>
        </div>

    <?php else: ?>

        <?php foreach ($files as $file): ?>

            <div class="file-card">

                <div>
                    <div class="file-name">
                        <?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?>
                    </div>

                    <div class="file-info">
                        <?= htmlspecialchars($file['type']) ?>
                        ·
                        <?= number_format($file['size'] / 1024, 2) ?> KB
                        · Encrypted
                    </div>
                </div>

                <a
                    class="download"
                    href="download.php?id=<?= urlencode($file['id']) ?>"
                >
                    Download
                </a>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

    <a class="back" href="upload.php">
        ← Back to Upload
    </a>

</div>

</body>
</html>
