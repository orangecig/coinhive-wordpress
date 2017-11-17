<?php
class MoneroMiner_OptionsManager {

	public function getOptionNamePrefix() {
		return get_class($this) . '_';
	}

	public function getOptionMetaData() {
		return array();
	}

	public function getOptionNames() {
		return array_keys($this->getOptionMetaData());
	}

	protected function initOptions() {
	}

	protected function deleteSavedOptions() {
		$optionMetaData = $this->getOptionMetaData();
		if (is_array($optionMetaData)) {
			foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                $prefixedOptionName = $this->prefix($aOptionKey); // how it is stored in DB
                delete_option($prefixedOptionName);
            }
        }
    }

    public function getPluginDisplayName() {
    	return get_class($this);
    }

    public function prefix($name) {
    	$optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) { // 0 but not false
            return $name; // already prefixed
        }
        return $optionNamePrefix . $name;
    }

    public function &unPrefix($name) {
    	$optionNamePrefix = $this->getOptionNamePrefix();
    	if (strpos($name, $optionNamePrefix) === 0) {
    		return substr($name, strlen($optionNamePrefix));
    	}
    	return $name;
    }

    public function getOption($optionName, $default = null) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        $retVal = get_option($prefixedOptionName);
        if (!$retVal && $default) {
        	$retVal = $default;
        }
        return $retVal;
    }

    public function deleteOption($optionName) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return delete_option($prefixedOptionName);
    }

    public function addOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return add_option($prefixedOptionName, $value);
    }

    public function updateOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return update_option($prefixedOptionName, $value);
    }

    public function getRoleOption($optionName) {
    	$roleAllowed = $this->getOption($optionName);
    	if (!$roleAllowed || $roleAllowed == '') {
    		$roleAllowed = 'Administrator';
    	}
    	return $roleAllowed;
    }

    protected function roleToCapability($roleName) {
    	switch ($roleName) {
    		case 'Super Admin':
    		return 'manage_options';
    		case 'Administrator':
    		return 'manage_options';
    		case 'Editor':
    		return 'publish_pages';
    		case 'Author':
    		return 'publish_posts';
    		case 'Contributor':
    		return 'edit_posts';
    		case 'Subscriber':
    		return 'read';
    		case 'Anyone':
    		return 'read';
    	}
    	return '';
    }

    public function isUserRoleEqualOrBetterThan($roleName) {
    	if ('Anyone' == $roleName) {
    		return true;
    	}
    	$capability = $this->roleToCapability($roleName);
    	return current_user_can($capability);
    }

    public function canUserDoRoleOption($optionName) {
    	$roleAllowed = $this->getRoleOption($optionName);
    	if ('Anyone' == $roleAllowed) {
    		return true;
    	}
    	return $this->isUserRoleEqualOrBetterThan($roleAllowed);
    }

    /**
     * see: http://codex.wordpress.org/Creating_Options_Pages
     * @return void
     */
    public function createSettingsMenu() {
    	$pluginName = $this->getPluginDisplayName();
        //create new top-level menu
    	add_menu_page($pluginName . ' Plugin Settings',
    		$pluginName,
    		'administrator',
    		get_class($this),
    		array(&$this, 'settingsPage')
        /*,plugins_url('/images/icon.png', __FILE__)*/); // if you call 'plugins_url; be sure to "require_once" it

        //call register settings function
    	add_action('admin_init', array(&$this, 'registerSettings'));
    }

    public function registerSettings() {
    	$settingsGroup = get_class($this) . '-settings-group';
    	$optionMetaData = $this->getOptionMetaData();
    	foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
    		register_setting($settingsGroup, $aOptionMeta);
    	}
    }


    public function settingsPage() {
    	if (!current_user_can('manage_options')) {
    		wp_die(__('You do not have sufficient permissions to access this page.', 'monero-miner'));
    	}

    	$optionMetaData = $this->getOptionMetaData();

        // Save Posted Options
    	if ($optionMetaData != null) {
    		foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
    			if (isset($_POST[$aOptionKey])) {
    				$this->updateOption($aOptionKey, $_POST[$aOptionKey]);
    			}
    		}
    	}

        // HTML for the page
    	$settingsGroup = get_class($this) . '-settings-group';
    	?>
    	<div class="wrap">
    		<h2><?php _e('System Settings', 'monero-miner'); ?></h2>
    		<table cellspacing="1" cellpadding="2"><tbody>
    			<tr><td><?php _e('System', 'monero-miner'); ?></td><td><?php echo php_uname(); ?></td></tr>
    			<tr><td><?php _e('PHP Version', 'monero-miner'); ?></td>
    				<td><?php echo phpversion(); ?>
    					<?php
    					if (version_compare('5.2', phpversion()) > 0) {
    						echo '&nbsp;&nbsp;&nbsp;<span style="background-color: #ffcc00;">';
    						_e('(WARNING: This plugin may not work properly with versions earlier than PHP 5.2)', 'monero-miner');
    						echo '</span>';
    					}
    					?>
    				</td>
    			</tr>
    			<tr><td><?php _e('MySQL Version', 'monero-miner'); ?></td>
    				<td><?php echo $this->getMySqlVersion() ?>
    					<?php
    					echo '&nbsp;&nbsp;&nbsp;<span style="background-color: #ffcc00;">';
    					if (version_compare('5.0', $this->getMySqlVersion()) > 0) {
    						_e('(WARNING: This plugin may not work properly with versions earlier than MySQL 5.0)', 'monero-miner');
    					}
    					echo '</span>';
    					?>
    				</td>
    			</tr>
    		</tbody></table>

    		<h2><?php echo $this->getPluginDisplayName(); echo ' '; _e('Settings', 'monero-miner'); ?></h2>

    		<form method="post" action="">
    			<?php settings_fields($settingsGroup); ?>
    			<style type="text/css">
    				table.plugin-options-table {width: 100%; padding: 0;}
    				table.plugin-options-table tr:nth-child(even) {background: #f9f9f9}
    				table.plugin-options-table tr:nth-child(odd) {background: #FFF}
    				table.plugin-options-table tr:first-child {width: 35%;}
    				table.plugin-options-table td {vertical-align: middle;}
    				table.plugin-options-table td+td {width: auto}
    				table.plugin-options-table td > p {margin-top: 0; margin-bottom: 0;}
    			</style>
    			<table class="plugin-options-table"><tbody>
    				<?php
    				if ($optionMetaData != null) {
    					foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
    						$displayText = is_array($aOptionMeta) ? $aOptionMeta[0] : $aOptionMeta;
    						?>
    						<tr valign="top">
    							<th scope="row"><p><label for="<?php echo $aOptionKey ?>"><?php echo $displayText ?></label></p></th>
    							<td>
    								<?php $this->createFormControl($aOptionKey, $aOptionMeta, $this->getOption($aOptionKey)); ?>
    							</td>
    						</tr>
    						<?php
    					}
    				}
    				?>
    			</tbody></table>
    			<p class="submit">
    				<input type="submit" class="button-primary"
    				value="<?php _e('Save Changes', 'monero-miner') ?>"/>
    			</p>
    		</form>
    	</div>
    	<?php

    }

    protected function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue) {
        if (is_array($aOptionMeta) && count($aOptionMeta) >= 2) { // Drop-down list
        	$choices = array_slice($aOptionMeta, 1);
        	?>
        	<p><select name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>">
        		<?php
        		foreach ($choices as $aChoice) {
        			$selected = ($aChoice == $savedOptionValue) ? 'selected' : '';
        			?>
        			<option value="<?php echo $aChoice ?>" <?php echo $selected ?>><?php echo $this->getOptionValueI18nString($aChoice) ?></option>
        			<?php
        		}
        		?>
        	</select></p>
        	<?php

        }
        else { // Simple input field
        	?>
        	<p><input type="text" name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>"
        		value="<?php echo esc_attr($savedOptionValue) ?>" size="50"/></p>
        		<?php

        	}
        }

        protected function getOptionValueI18nString($optionValue) {
        	switch ($optionValue) {
        		case 'true':
        		return __('true', 'monero-miner');
        		case 'false':
        		return __('false', 'monero-miner');

        		case 'Administrator':
        		return __('Administrator', 'monero-miner');
        		case 'Editor':
        		return __('Editor', 'monero-miner');
        		case 'Author':
        		return __('Author', 'monero-miner');
        		case 'Contributor':
        		return __('Contributor', 'monero-miner');
        		case 'Subscriber':
        		return __('Subscriber', 'monero-miner');
        		case 'Anyone':
        		return __('Anyone', 'monero-miner');
        	}
        	return $optionValue;
        }

    /**
     * Query MySQL DB for its version
     * @return string|false
     */
    protected function getMySqlVersion() {
    	global $wpdb;
    	$rows = $wpdb->get_results('select version() as mysqlversion');
    	if (!empty($rows)) {
    		return $rows[0]->mysqlversion;
    	}
    	return false;
    }


    public function getEmailDomain() {
        // Get the site domain and get rid of www.
    	$sitename = strtolower($_SERVER['SERVER_NAME']);
    	if (substr($sitename, 0, 4) == 'www.') {
    		$sitename = substr($sitename, 4);
    	}
    	return $sitename;
    }
}