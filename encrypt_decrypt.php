
<?php
function encryptData($data, $key) {
    $cipher = "aes-256-cbc";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
    $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
    return base64_encode($encrypted . "::" . $iv);
}

function decryptData($data, $key) {
    $cipher = "aes-256-cbc";
    list($encrypted_data, $iv) = explode("::", base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, $cipher, $key, 0, $iv);
}

// Пример ключа шифрования
define('ENCRYPTION_KEY', 'LZFVlsJUeaDe/Dfsfw2QKiGjbNubEkVxNhLn28ZW65k=');

// Обработка запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $value = $_POST['value'];

    if ($action === 'encrypt') {
        echo encryptData($value, ENCRYPTION_KEY);
    } elseif ($action === 'decrypt') {
        echo decryptData($value, ENCRYPTION_KEY);
    }
}
?>