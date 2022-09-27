<?php
/*
  Plugin Name: Is there a problem
  Description: tell you if there are integration's problem with your website
  Version: 1.1.14
  author URI: https://ingenius.agency/
  Text Domain: is-there-a-problem
  Author: MartinDev
  License: GPL v2 or later

*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('ITAP_VERSION', '1.1.14');

function activate_itap() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-itap-activator.php';
    Itap_Activator::activate();
}

function deactivate_itap() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-itap-deactivator.php';
    Itap_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_itap');
register_deactivation_hook(__FILE__, 'deactivate_itap');

require plugin_dir_path(__FILE__) . 'includes/class-itap.php';

function run_itap() {
    $plugin = new Itap();
    $plugin->run();
}
run_itap();

require 'plugin-update-checker-master/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/martinfeuillet/Is-there-a-problem',
    __FILE__, //Full path to the main plugin file or functions.php.
    'is_there_a_problem'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');
