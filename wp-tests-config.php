<?php
/**
 * WordPress Tests Configuration
 */

define( 'ABSPATH', dirname( __FILE__ ) . '/vendor/wp-phpunit/wp-phpunit/src/' );

define( 'MS_FILES_REWRITING', false );

define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Site' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG_LOG', true );

define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASS', 'root' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';

define( 'WP_TESTS_MULTISITE', false );
define( 'WP_CORE_DIR', ABSPATH );

if ( ! defined( 'WP_TESTS_FORCE_KNOWN_INVALID_PHPUNIT' ) ) {
	define( 'WP_TESTS_FORCE_KNOWN_INVALID_PHPUNIT', true );
}
