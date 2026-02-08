<?php
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$scheme = $isHttps ? 'https' : 'http';
$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
$requestUri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$normalizedUri = rtrim($requestUri, '/');

if ($normalizedUri === '') {
    $normalizedUri = '/';
}

if (!preg_match('#/public$#', $normalizedUri)) {
    $target = rtrim($scheme . '://' . $host . $normalizedUri, '/') . '/public';
    header('Location: ' . $target, true, 302);
    exit;
}
