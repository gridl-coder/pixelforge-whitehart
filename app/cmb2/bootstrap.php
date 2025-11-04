<?php

namespace PixelForge\CMB2;

$cmb2Bootstrap = dirname(__DIR__, 2) . '/vendor/cmb2/cmb2/init.php';

if (file_exists($cmb2Bootstrap)) {
    require_once $cmb2Bootstrap;
}
