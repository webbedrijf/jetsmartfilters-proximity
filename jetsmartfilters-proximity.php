<?php
/**
 * Plugin Name: Jet SmartFilters Proximity by Webbedrijf.nl
 * Plugin URI: https://webbedrijf.nl
 * Description: Jet SmartFilters Proximity search
 * Version: 1.0.2
 * Author: Bart Fijneman
 * Author URI: https://webbedrijf.nl
 * License: GPL2
 */

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

new JetSmartFiltersProximity\Plugin();
