<?php

$minVersion = '5.6.0';
$maxVersion = '7.1.99';
$requiredExtensions = array('pdo', 'pdo_mysql', 'mysqli', 'mbstring');

$phpVersion = PHP_VERSION;
$versionOk = version_compare($phpVersion, $minVersion, '>=')
    && version_compare($phpVersion, $maxVersion, '<=');

echo "CRM_System Environment Check\n";
echo "============================\n";
echo "PHP Version: " . $phpVersion . "\n";
echo "Supported Range: " . $minVersion . " - " . $maxVersion . "\n";
echo "Version Status: " . ($versionOk ? "OK" : "NOT SUPPORTED") . "\n\n";

echo "Extensions\n";
echo "----------\n";
foreach ($requiredExtensions as $extension) {
    $loaded = extension_loaded($extension);
    echo str_pad($extension, 12, ' ', STR_PAD_RIGHT) . ': ' . ($loaded ? "OK" : "MISSING") . "\n";
}

echo "\nDatabase Config File\n";
echo "--------------------\n";
$baseConfig = realpath(__DIR__ . '/../application/configs/application.ini');
$localConfig = realpath(__DIR__ . '/../application/configs/application.local.ini');

echo "Base config : " . ($baseConfig ? $baseConfig : "NOT FOUND") . "\n";
echo "Local config: " . ($localConfig ? $localConfig : "NOT FOUND (optional)") . "\n";
