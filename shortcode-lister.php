<?php
/**
 * Plugin Name: Shortcode Lister
 * Plugin URI: http://fahdmurtaza.com/
 * Description: Lists all shortcodes used in pages, grouped by pages.
 * Version: 1.0
 * Author: Fahad Murtaza
 * Author URI: http://fahdmurtaza.com/
 */

function find_shortcodes_in_pages() {
	$pages = get_pages();
	$shortcodes = array();

	foreach ($pages as $page) {
		preg_match_all('/' . get_shortcode_regex() . '/s', $page->post_content, $matches, PREG_SET_ORDER);
		if (!empty($matches)) {
			foreach ($matches as $match) {
				$shortcodes[$page->post_title][] = $match[2]; // Index 2 has the shortcode name
			}
		}
	}

	return $shortcodes;
}

function shortcode_lister_menu() {
	add_menu_page('Shortcode Lister', 'Shortcode Lister', 'manage_options', 'shortcode-lister', 'display_shortcode_list');
}
add_action('admin_menu', 'shortcode_lister_menu');

function display_shortcode_list() {
	$shortcode_data = find_shortcodes_in_pages();
	echo '<div class="wrap"><h1>Shortcode Lister</h1>';
	foreach ($shortcode_data as $page_title => $shortcodes) {
		echo '<h2>' . esc_html($page_title) . '</h2>';
		echo '<ul>';
		foreach (array_unique($shortcodes) as $shortcode) {
			echo '<li>' . esc_html($shortcode) . '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}
