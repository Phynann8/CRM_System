<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define path to public directory
defined('PUBLIC_PATH') || define('PUBLIC_PATH', realpath(dirname(__FILE__)));

// Load local configuration for secrets
$localConfigPath = APPLICATION_PATH . '/configs/local_config.php';
if (file_exists($localConfigPath)) {
    $localConfig = include $localConfigPath;
} else {
    $localConfig = array();
}

// Define secrets from config or environment
defined('SECRET_KEY') || define('SECRET_KEY', isset($localConfig['api']['secret_key']) ? $localConfig['api']['secret_key'] : getenv('SECRET_KEY'));
defined('DEFAULT_PASSWORD') || define('DEFAULT_PASSWORD', isset($localConfig['db']['password']) ? $localConfig['db']['password'] : getenv('DEFAULT_PASSWORD'));
defined('APP_API_KEY') || define('APP_API_KEY', isset($localConfig['api']['key']) ? $localConfig['api']['key'] : getenv('APP_API_KEY'));
defined('AZURE_STORAGE_KEY') || define('AZURE_STORAGE_KEY', isset($localConfig['api']['azure_storage_key']) ? $localConfig['api']['azure_storage_key'] : getenv('AZURE_STORAGE_KEY'));

defined('TITLE_REPORT') || define('TITLE_REPORT', "style='color:#000; font-size:12px;font-family:Times New Roman,Khmer OS Muol Light;white-space:nowrap;'");
defined('OTHER_LANG_REQUIRED') || define('OTHER_LANG_REQUIRED', 'false');
defined('SYSTEM_SES') || define('SYSTEM_SES', "authtest");
defined('TEACHER_AUTH') || define('TEACHER_AUTH', "teacherAuth");
defined('SYSTEM_LOCALE') || define('SYSTEM_LOCALE', 2); // initialize lang ,1=Khmer,2=English

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

defined('HEADER_REPORT_TYPE') || define('HEADER_REPORT_TYPE', 3); // 1,2,3
// SECRET_KEY defined above
defined('SECRET_KEY') || define('SECRET_KEY', 'replaced_by_local_config');
defined('PICKUP_TYPE') || define('PICKUP_TYPE', 1); // type=1 for ELT and psis , type=2 for good will
defined('CARD_TYPE') || define('CARD_TYPE', 1); // type=1 for ELT , type=2 for good will , type=3 for New World
defined('BRANCHES') || define('BRANCHES', '1');
defined('EDUCATION_LEVEL') || define('EDUCATION_LEVEL', 0); // 1=true to show,0=false not show
defined('SHOW_IN_DEGREE') || define('SHOW_IN_DEGREE', 0); // 1=show , 0=hide
defined('SHOW_IN_GRADE') || define('SHOW_IN_GRADE', 0); // 1=show , 0=hide
defined('SUTUDENT_SESSION') || define('SUTUDENT_SESSION', 'student_auth');
defined('AUTO_PUSH_NOTIFICATION') || define('AUTO_PUSH_NOTIFICATION', 0); // 1=push , 0=not push
defined('APP_ID') || define('APP_ID', 'dfc704ab-e023-4b0b-b030-e300f13b74eb');
defined('RECEIPT_TYPE') || define('RECEIPT_TYPE', 2); // 1elt,2nws,3psis
defined('ICODE') || define('ICODE', '100323');
defined('SCORE_RESULT_TEMPLATE') || define('SCORE_RESULT_TEMPLATE', 2); // 1=for general,2=for AHS
defined('STU_ID_TYPE') || define('STU_ID_TYPE', 2); // 1=Auto By Branch,2=Auto By Degree,3 by school option
defined('STU_SERIAL_TYPE') || define('STU_SERIAL_TYPE', 2); // 1=for general,2=for psis
defined('TEST_CONDICTION') || define('TEST_CONDICTION', 1); // 1=show branch in text index,2=psis(not show)
defined('TIEM_IS_MANUAL') || define('TIEM_IS_MANUAL', 1); // 0=static 1=manual customize
defined('FEATURE_SCAN_CALLOUT') || define('FEATURE_SCAN_CALLOUT', 1); // 0=disable, 1=enable
defined('SCORE_RESULT_TEMPLATE') || define('SCORE_RESULT_TEMPLATE', 2); // 1=for general,2=for AHS
defined('URL_STUDENT_PROFILE') || define('URL_STUDENT_PROFILE', 'http://psischvsystem.cam-app.com/psischv16-07-24/');
defined('classHideHeight') || define('classHideHeight', '110px');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Load base config and merge local overrides when present.
// Keep the app compatible with legacy Zend 1 deployments.
$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
$localConfigPath = APPLICATION_PATH . '/configs/application.local.ini';
if (is_readable($localConfigPath)) {
    $localConfig = new Zend_Config_Ini($localConfigPath, APPLICATION_ENV);
    $config->merge($localConfig);
}

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, $config);
$application->bootstrap()->run();
