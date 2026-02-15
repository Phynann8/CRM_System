<?php
// application/configs/local_config.example.php
// Copy this file to local_config.php and fill in your actual values.
// local_config.php should be ignored by git.

return array(
    'db' => array(
        'password' => 'YOUR_DB_PASSWORD_HERE',
    ),
    'api' => array(
        'key' => 'YOUR_API_KEY_HERE',
        'secret_key' => 'YOUR_SECRET_KEY_HERE',
        'azure_storage_key' => 'YOUR_AZURE_STORAGE_KEY_HERE',
    ),
);
