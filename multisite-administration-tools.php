<?php
/*
Plugin Name: MultiSite Administration Tools
Plugin URI: http://www.wordpress.org/extend/plugins/multisite-administration-tools/
Description: Adds information to the network admin sites, plugins and themes page.  Allows you to easily see what theme and plugins are enabled on a site.
Version: 1.1
Author: Aaron Axelsen
Author URI: http://aaron.axelsen.us
License: GPLv2 or later
Network: true
*/

function msadmintools_sites_themecolumn($columns) {
        $columns['viewthemes'] = 'Current Theme';
        $columns['viewplugins'] = 'Current Plugins';
        return $columns;
}
add_filter ('manage_sites-network_columns', 'msadmintools_sites_themecolumn');

function msadmintools_sites_themevalue($column_name,$blogid) {
	if ($column_name === 'viewthemes') {
		echo 'Name: '.get_blog_option($blogid,'current_theme');
		echo '<br/>Template: '.get_blog_option($blogid,'template');
	} else if ($column_name === 'viewplugins') {
		$plugins = get_blog_option($blogid,'active_plugins');
		foreach ($plugins as $plugin) {
			if (is_wp_error(validate_plugin($plugin))) {
				echo '<span style="color: red">'.$plugin.' (removed)</span><br/>';
			} else {
				$data = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
				echo $data['Name']."<br/>";
			}
		}
	}
}
add_action ('manage_sites_custom_column', 'msadmintools_sites_themevalue', 10, 2);

function msadmintools_themes_sitescolumn($columns) {
        $columns['viewsites'] = 'Sites';
        return $columns;
}
add_filter ('manage_themes-network_columns', 'msadmintools_themes_sitescolumn');

function msadmintools_themes_sitesvalue($column_name,$themekey,$theme) {
	if ($column_name === 'viewsites') {
	        global $wpdb;
        	$sites = $wpdb->get_results("SELECT blog_id from $wpdb->blogs");
        	foreach ($sites as $site) {
			$stylesheet = apply_filters('stylesheet', get_blog_option($site->blog_id,'stylesheet'));
			if ($theme['Stylesheet'] == $stylesheet) {
				$blogname = get_blog_option($site->blog_id,'blogname');
				$siteurl = get_blog_option($site->blog_id,'siteurl');
	                	echo '<a href="'.$siteurl.'" target="_blank">'.(empty($blogname) ? $siteurl : $blogname).'</a><br/>';
			}
        	}
	}
}
add_action ('manage_themes_custom_column', 'msadmintools_themes_sitesvalue', 10, 3);

function msadmintools_plugins_sitescolumn($columns) {
	$columns['viewsites'] = 'Sites';
        return $columns;
}
add_filter ('manage_plugins-network_columns', 'msadmintools_plugins_sitescolumn');

function msadmintools_plugins_sitesvalue($column_name,$pluginid,$plugin) {
	if ($column_name === 'viewsites') {
		global $wpdb;
                $sites = $wpdb->get_results("SELECT blog_id from $wpdb->blogs");
                foreach ($sites as $site) {
			foreach (get_blog_option($site->blog_id,'active_plugins') as $active) {
                        	if ($pluginid == $active) {
					$blogname = get_blog_option($site->blog_id,'blogname');
					$siteurl = get_blog_option($site->blog_id,'siteurl');
		                	echo '<a href="'.$siteurl.'" target="_blank">'.(empty($blogname) ? $siteurl : $blogname).'</a><br/>';
                        	}
			}
                }

	}
}
add_action ('manage_plugins_custom_column', 'msadmintools_plugins_sitesvalue', 10, 3);
