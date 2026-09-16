<?php
/**
 * Functions for creating & getting filters.
 *
 * @package wrd\wp-filters
 */

namespace wrd\wp_filters;

/**
 * Creates a value object for a filter.
 *
 * @param string $value The value of the filter.
 *
 * @param string $label Optional. The text label of the value. Defaults to capitalized version of the value.
 *
 * @param int    $count Optional. Number of posts that match the value. Values with 0 count will be removed.
 *
 * @return false|array The filter value. False if $count is 0.
 */
function get_post_filter_value( $value, $label = '', $count = null ) {
	if ( empty( $label ) ) {
		$label = ucwords( $value );
	}

	if ( is_numeric( $count ) && 0 === $count ) {
		return false;
	}

	return array(
		'value' => $value,
		'label' => $label,
		'count' => $count,
	);
}

/**
 * Registers a filter to a post type.
 *
 * Filters with no values will not be added. Falsey values are removed.
 *
 * @param string $post_type The post type to add the filter to.
 *
 * @param array  $args {
 *      Arguments for the filter.
 *
 *      @type string    $name           Required. Name of the filter, used for the input's name attribute & query parameter. Defaults to value of 'meta_key'.
 *      @type string    $label          User facing label for the filter's name. Defaults to capitalized version of 'name'.
 *      @type array     $values         Choices the user can pick. Array of arrays, @see get_post_filter_value for information on arguments. Defaults to all unique values of this post meta key.
 *
 *      @type callable  $get_callback   Callback function for applying the filter. Recieves the current WP_Query arguments, the filters value, and the filter settings. Must return the altered WP_Query arguments.
 *
 *      @type bool      $singular       If only one value is allowed for the filter. Determines if the input should be checkboxes or radio buttons.
 * }
 *
 * @return false|void False on failure.
 */
function add_post_filter( $post_type, $args ) {
	$parsed_args = wp_parse_args(
		$args,
		array(
			'name'         => '',
			'label'        => '',
			'values'       => array(),
			'get_callback' => null,
			'singular'     => false,
			'data'         => array(),
		)
	);

	if ( empty( $parsed_args['name'] ) ) {
		_doing_it_wrong( 'add_post_filter', esc_html__( '`name` argument is required.', 'wrd' ), '1.0.0' );
	}

	if ( empty( $parsed_args['label'] ) ) {
		$parsed_args['label'] = ucwords( $parsed_args['name'] );
	}

	if ( empty( $parsed_args['values'] ) || ! is_array( $parsed_args['values'] ) ) {
		return false;
	}

	// get_post_filter_value may return false. Filter out all falsey values.
	$parsed_args['values'] = array_filter( $parsed_args['values'] );

	if ( 0 === count( $parsed_args['values'] ) ) {
		// Don't add empty filters.
		return false;
	}

	add_filter(
		'wrd/get_post_filters_' . $post_type,
		function ( $filters ) use ( $parsed_args ) {
			$filters[] = $parsed_args;
			return $filters;
		}
	);
}

/**
 * Retrieves the filters for a post type.
 *
 * @param string $post_type The post type to get the filters for.
 *
 * @return array[] Array of filters. @see add_post_filter for description of filter array keys.
 */
function get_post_filters( $post_type ) {
	return apply_filters( "wrd/get_post_filters_{$post_type}", array() ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Namespaced hook name.
}
