
<?php

// Encryption configuration
define('ENCRYPTION_KEY', hash(
    'sha256',
    'Task3-My-Secure-File-Encryption-Key-2026',
    true
));

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/uploads_encrypted/');

// Encrypt file data
function encryptData($data)
{
    $iv = random_bytes(12);
    $tag = '';

    $encrypted = openssl_encrypt(
        $data,
        'aes-256-gcm',
        ENCRYPTION_KEY,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($encrypted === false) {
        throw new Exception('File encryption failed.');
    }

    return [
        'iv' => base64_encode($iv),
        'tag' => base64_encode($tag),
        'data' => base64_encode($encrypted)
    ];
}

// Decrypt file data
function decryptData($payload)
{
    $iv = base64_decode($payload['iv'], true);
    $tag = base64_decode($payload['tag'], true);
    $encrypted = base64_decode($payload['data'], true);

    if ($iv === false || $tag === false || $encrypted === false) {
        throw new Exception('Invalid encrypted file.');
    }

    $decrypted = openssl_decrypt(
        $encrypted,
        'aes-256-gcm',
        ENCRYPTION_KEY,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($decrypted === false) {
        throw new Exception('File decryption failed.');
    }

    return $decrypted;
}

?>
