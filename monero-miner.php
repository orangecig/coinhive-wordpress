<?php
/*
   Plugin Name: Coinhive Monero (XMR) Miner
   Plugin URI: https://codecanyon.net/user/AhmetHakan/portfolio
   Version: 1.0
   Author: <a href="https://codecanyon.net/user/AhmetHakan" target="_blank">Ahmet Hakan</a>
   Description: Mine XMR (Monero) with visitors's CPU via Coinhive
   Text Domain: monero-miner
   License: Creative Commons Attribution-NonCommercial 4.0 International Public License
  */

$MoneroMiner_minimalRequiredPhpVersion = '5.5';
function MoneroMiner_noticePhpVersionWrong() {
    global $MoneroMiner_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "Monero Miner" requires a newer version of PHP to be running.',  'monero-miner').
            '<br/>' . __('Minimal version of PHP required: ', 'monero-miner') . '<strong>' . $MoneroMiner_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'monero-miner') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}

function MoneroMiner_PhpVersionCheck() {
    global $MoneroMiner_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $MoneroMiner_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'MoneroMiner_noticePhpVersionWrong');
        return false;
    }
    return true;
}

function MoneroMiner_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('monero-miner', false, $pluginDir . '/languages/');
}

add_action('plugins_loadedi','MoneroMiner_i18n_init');

if (MoneroMiner_PhpVersionCheck()) {
    include_once('monero-miner_init.php');
    MoneroMiner_init(__FILE__);
}
