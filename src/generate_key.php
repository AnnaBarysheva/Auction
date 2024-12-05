<?php
$key = base64_encode(openssl_random_pseudo_bytes(32)); // 32 байта для AES-256
echo "Generated Key: " . $key . "\n";
