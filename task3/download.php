
<?php

require_once 'config.php';

// Check the file ID
$id = $_GET['id'] ?? '';

if (!preg_match('/^[a-f0-9]{32}$/', $id)) {
    http_response_code(400);
    exit('Invalid file ID.');
}

$filePath = UPLOAD_DIR . $id . '.enc';

if (!is_file($filePath)) {
    http_response_code(404);
    exit('File not found.');
}

// Read encrypted file
$content = file_get_contents($filePath);

if ($content === false) {
    http_response_code(500);
    exit('Could not read the file.');
}

$payload = json_decode($content, true);

if (
    !is_array($payload) ||
    !isset(
        $payload['original_name'],
        $payload['mime_type'],
        $payload['iv'],
        $payload['tag'],
        $payload['data']
    )
) {
    http_response_code(400);
    exit('Invalid encrypted file.');
}

// Check allowed MIME types
$allowedTypes = [
    'application/pdf',
    'image/jpeg',
    'image/png'
];

if (!in_array($payload['mime_type'], $allowedTypes, true)) {
    http_response_code(400);
    exit('Unsupported file type.');
}

try {

    // Decrypt the file
    $decryptedData = decryptData($payload);

    // Prepare a safe filename
    $filename = basename(
        str_replace('\\', '/', $payload['original_name'])
    );

    $filename = preg_replace(
        '/[\x00-\x1F\x7F]/',
        '',
        $filename
    );

    if ($filename === '') {
        $filename = 'downloaded_file';
    }

    $fallbackName = preg_replace(
        '/[^A-Za-z0-9._-]/',
        '_',
        $filename
    );

    // Send the decrypted file to the user
    header('Content-Type: ' . $payload['mime_type']);

    header(
        'Content-Disposition: attachment; filename="' .
        addcslashes($fallbackName, '"\\') .
        '"; filename*=UTF-8\'\'' . rawurlencode($filename)
    );

    header('Content-Length: ' . strlen($decryptedData));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');

    echo $decryptedData;
    exit;

} catch (Throwable $e) {

    http_response_code(500);
    exit('File decryption failed.');

}

?>
