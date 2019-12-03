<?php
/**
 * Set your app-specific constants here
 */
define( 'IFM_REQUIRED_PHP_VERSION', '5.3' ); // because of get_called_class()
define( 'IFM_REQUIRED_WP_VERSION', '5.0' );
define( 'IFM_REQUIRED_WP_NETWORK', false ); // because plugin is not compatible with WordPress multisite

/**
 * These Are just paths to directories to make the code a bit DRYer
 */
define( 'IFM_BASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'IFM_APP', IFM_BASE_PATH . 'app/' );
define( 'IFM_INC', IFM_BASE_PATH . 'includes/' );

/**
 * Set to determine the base namespace for the router. eventually use a WordPress admin setting
 */
define( 'IFM_API_PREFIX', '' );
define( 'IFM_NAMESPACE', 'ifm' );
