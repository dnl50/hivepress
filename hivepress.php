<?php
/**
 * Plugin Name: HivePress
 * Description: Multipurpose listing & directory plugin.
 * Version: 1.1.0
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Include the core HivePress class.
require_once __DIR__ . '/includes/class-core.php';

/**
 * Returns the core HivePress instance.
 *
 * @return HivePress\Core
 */
function hivepress() {
	return HivePress\Core::instance();
}

// Initialize HivePress.
hivepress();
