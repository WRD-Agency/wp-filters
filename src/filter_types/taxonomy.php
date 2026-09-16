<?php
/**
 * Functions for creating taxonomy filters.
 *
 * @package wrd\wp-filters
 */

namespace wrd\wp_filters;

/**
 * Gets all the unique post filter values for a taxonomy in a specific post type.
 *
 * Only includes terms with posts assigned to them.
 *
 * @param string $taxonomy The taxonomy to get terms for.
 *
 * @return array[] Array of post filter values.
 */
function get_default_term_filter_values( $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'     => $taxonomy,
			'hide_empty'   => true,
			'hierarchical' => false,
		)
	);

	$res = array();

	foreach ( $terms as $term ) {
		$res[] = get_post_filter_value( $term->term_id, $term->name, $term->count );
	}

	return $res;
}

/**
 * Registers a filter for a taxonomy key.
 *
 * @param string $post_type The post type to add the filter to.
 *
 * @param array  $args {
 *      Arguments for the filter.
 *
 *      @type string    $taxonomy   Required. Name of the taxonomy.
 *
 *      @type string    $label      User facing label for the filter's name. Defaults to the taxonomy's label.
 *      @type array     $values     Choices the user can pick. Array of arrays, @see get_post_filter_value for information on arguments. Defaults to all terms with assigned posts @see get_default_term_filter_values.
 * }
 *
 * @return false|void False on failure.
 */
function add_post_term_filter( $post_type, $args = array() ) {
	$parsed_args = wp_parse_args(
		$args,
		array(
			'label'    => null,
			'values'   => null,
			// No name option. The name must be the taxonomy name.

			'taxonomy' => 'cat',
		)
	);

	if ( ! taxonomy_exists( $parsed_args['taxonomy'] ) ) {
		_doing_it_wrong( 'add_post_term_filter', esc_html__( '`taxonomy` argument is required and must be a valid taxonomy.', 'wrd' ), '1.0.0' );
	}

	$tax = $parsed_args['taxonomy'];

	if ( empty( $parsed_args['label'] ) ) {
		$parsed_args['label'] = ( get_taxonomy_labels( $tax ) )->name;
	}

	if ( empty( $parsed_args['values'] ) ) {
		$parsed_args['values'] = get_default_term_filter_values( $tax );
	}

	return add_post_filter(
		$post_type,
		array(
			'name'         => $tax,
			'label'        => $parsed_args['label'],
			'values'       => $parsed_args['values'],
			'get_callback' => null, // Built-in to WordPress.
		)
	);
}
