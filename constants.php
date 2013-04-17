<?php
define('DHP_BENCHMARK_START', microtime(TRUE));
define('DHP_BENCHMARK_MEMORY', memory_get_peak_usage());
define('DS', DIRECTORY_SEPARATOR);
define('YES', TRUE);
define('NO', FALSE);
define('NIL', NULL);
if (php_sapi_name() == 'cli' or PHP_SAPI == 'cli') {
    define('DHP_CLI', TRUE);
}
else {
    define('DHP_CLI', FALSE);
}
define('DHP_HTTP', !DHP_CLI);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    define('DHP_AJAX_REQUEST', TRUE);
}
else {
    define('DHP_AJAX_REQUEST', FALSE);
}