<?php
/**
 * Functions for creating filters for sorting.
 *
 * @package wrd\wp-filters;
 */

namespace wrd\wp_filters;

/**
 * Gets the default filter values for a sorting filter.
 *
 * Defaults are:
 *  - Title, A-Z
 *  - Title, Z-A
 *  - Newest to Oldest
 *  - Oldest to Newest
 *
 * @return array[] Array of sort filter values.
 */
function get_default_sort_filter_values() {
	return array(
		get_post_filter_value( 'title/ASC', __( 'Title, A-Z', 'wp-filters' ) ),
		get_post_filter_value( 'title/DESC', __( 'Title, Z-A', 'wp-filters' ) ),
		get_post_filter_value( 'date/DESC', __( 'Newest to Oldest', 'wp-filters' ) ),
		get_post_filter_value( 'date/ASC', __( 'Oldest to Newest', 'wp-filters' ) ),
	);
}

/**
 * Adds a post filter which changes the order of posts.
 *
 * @param string $post_type The post type to add the filter to.
 *
 * @param array  $args {
 *      Arguments for the filter.
 *
 *      @type string    $label      User facing label for the filter's name. Defaults to 'Sort'.
 *      @type array     $values     Choices the user can pick. Array of arrays, @see get_post_filter_value for information on arguments. Defaults to @see get_default_sort_filter_values.
 * }
 *
 * @return false|void False on failure.
 */
function add_post_sort_filter( $post_type, $args = array() ) {
	$parsed_args = wp_parse_args(
		$args,
		array(
			'name'   => 'sort',
			'label'  => __( 'Sort', 'wp-filters' ),
			'values' => null,
		)
	);

	if ( empty( $parsed_args['values'] ) ) {
		$parsed_args['values'] = get_default_sort_filter_values();
	}

	return add_post_filter(
		$post_type,
		array(
			'name'         => $parsed_args['name'],
			'label'        => $parsed_args['label'],
			'values'       => $parsed_args['values'],

			'singular'     => true,

			'get_callback' => function ( $args, $value, $filter ) {
				if ( is_array( $value ) ) {
					$value = $value[0];
				}

				list($orderby, $order) = explode( '/', $value );

				$args['orderby'] = $orderby;
				$args['order'] = $order;

				return $args;
			},
		)
	);
}
