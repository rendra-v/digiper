<?php
echo 'PHP Version: ' . PHP_VERSION . '<br>';
echo 'OPcache enabled: ' . (function_exists('opcache_reset') ? 'yes' : 'no') . '<br>';
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache CLEARED!<br>';
}
