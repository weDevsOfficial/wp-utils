<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package WeDevs\WpUtils
 */

// Define WordPress constants that may be referenced at runtime.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/tmp/wordpress/' );
}

// Suppress deprecation warnings from Mockery on PHP 8.4+.
error_reporting( E_ALL & ~E_DEPRECATED );

// Load Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';
