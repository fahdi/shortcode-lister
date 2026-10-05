<?php
/**
 * Plugin Name: Shortcode Lister
 * Plugin URI: https://app.codeable.io/tasks/new?preferredContractor=49688
 * Description: Lists all shortcodes used in pages, grouped by pages.
 * Version: 1.0
 * Author: Fahad Murtaza
 * Author URI: https://app.codeable.io/tasks/new?preferredContractor=49688
 */

function find_shortcodes_in_pages() {
	$shortcodes = array();
	$regex      = '/' . get_shortcode_regex() . '/s';
	$paged      = 1;

	// Walk pages in batches so large sites do not load every post_content at once.
	do {
		$query = new WP_Query(array(
			'post_type'              => 'page',
			'post_status'            => 'any',
			'posts_per_page'         => 100,
			'paged'                  => $paged,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		));

		foreach ($query->posts as $page) {
			if (!current_user_can('edit_post', $page->ID)) {
				continue;
			}
			if (!preg_match_all($regex, $page->post_content, $matches, PREG_SET_ORDER)) {
				continue;
			}
			// Key by ID so two pages with the same title are not merged.
			$shortcodes[$page->ID] = array(
				'title'      => $page->post_title,
				'url'        => get_permalink($page->ID),
				'shortcodes' => array_unique(wp_list_pluck($matches, 2)),
			);
		}

		$paged++;
	} while ($paged <= $query->max_num_pages);

	return $shortcodes;
}


function shortcode_lister_menu() {
	add_menu_page('Shortcode Lister', 'Shortcode Lister', 'manage_options', 'shortcode-lister', 'display_shortcode_list');
}
add_action('admin_menu', 'shortcode_lister_menu');

function display_shortcode_list() {
	$shortcode_data = find_shortcodes_in_pages();
	echo '<div class="wrap"><h1>Shortcode Lister</h1>';
	foreach ($shortcode_data as $data) {
		echo '<h2>' . esc_html($data['title']) . '</h2>';
		echo '<p>URL: <a href="' . esc_url($data['url']) . '" target="_blank">' . esc_url($data['url']) . '</a></p>';
		echo '<ul>';
		foreach ($data['shortcodes'] as $shortcode) {
			echo '<li>' . esc_html($shortcode) . '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}

