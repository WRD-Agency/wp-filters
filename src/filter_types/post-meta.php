<?php
/**
 * Functions for creating filters using post meta.
 *
 * @package wrd\wp-filters;
 */

namespace wrd\wp_filters;

/**
 * Gets all the unique post filter values for a meta key in a specific post type.
 *
 * Posts must also be published.
 *
 * @param string $post_type The post type to get the meta values for.
 *
 * @param string $meta_key The meta key to find unique values for.
 *
 * @return array[] Array of post filter values.
 */
function get_default_meta_filter_values( $post_type, $meta_key ) {
	global $wpdb;

	$cache_key = "wrd_filtering_get_default_meta_filter_values_{$post_type}_{$meta_key}";
	$values    = wp_cache_get( $cache_key );

	if ( ! $values ) {
		$values = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value AS value, COUNT(*) AS count FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s 
				AND p.post_status = 'publish'
				AND p.post_type = %s
				GROUP BY pm.meta_value
				ORDER BY count",
				$meta_key,
				$post_type
			)
		);

		wp_cache_set( $cache_key, $values, '', DAY_IN_SECONDS );
	}

	$res = array();

	foreach ( $values as $i => $row ) {
		$res[] = get_post_filter_value( $row->value, ucwords( $row->value ), $row->count );
	}

	return $res;
}

/**
 * Registers a filter for a post meta key.
 *
 * @param string $post_type The post type to add the filter to.
 *
 * @param array  $args {
 *      Arguments for the filter.
 *
 *      @type string    $meta_key   Required. Post meta key to filter by.
 *
 *      @type string    $name       Name of the filter, used for the input's name attribute & query parameter. Defaults to value of 'meta_key'.
 *      @type string    $label      User facing label for the filter's name. Defaults to capitalized version of 'name'.
 *      @type array     $values     Choices the user can pick. Array of arrays, @see get_post_filter_value for information on arguments. Defaults to all unique values of this post meta key.
 * }
 *
 * @return false|void False on failure.
 */
function add_post_meta_filter( $post_type, $args = array() ) {
	$parsed_args = wp_parse_args(
		$args,
		array(
			'name'     => null,
			'label'    => null,
			'values'   => null,

			'meta_key' => null,
		)
	);

	if ( empty( $parsed_args['meta_key'] ) ) {
		_doing_it_wrong( 'add_post_meta_filter', esc_html__( '`meta_key` argument is required.', 'wrd' ), '1.0.0' );
	}

	if ( empty( $parsed_args['name'] ) ) {
		$parsed_args['name'] = $parsed_args['meta_key'];
	}

	if ( empty( $parsed_args['label'] ) ) {
		$parsed_args['label'] = ucwords( $parsed_args['meta_key'] );
	}

	if ( empty( $parsed_args['values'] ) ) {
		$parsed_args['values'] = get_default_meta_filter_values( $post_type, $parsed_args['meta_key'] );
	}

	return add_post_filter(
		$post_type,
		array(
			'name'         => $parsed_args['name'],
			'label'        => $parsed_args['label'],
			'values'       => $parsed_args['values'],
			'data'         => array(
				'meta_key' => $parsed_args['meta_key'],
			),
			'get_callback' => function ( $args, $value, $filter ) {
				$operator = is_array( $value ) ? 'IN' : '=';

				if ( 'LIKE' === $operator ) {
					$sub_meta_queries = array(
						'relation' => 'OR',
					);

					foreach ( $value as $v ) {
						$sub_meta_queries[] = array(
							'key'     => $filter['data']['meta_key'],
							'value'   => $v,
							'compare' => $operator,
						);
					}

					$args = add_meta_query_arg(
						$args,
						$sub_meta_queries
					);
				} else {
					$args = add_meta_query_arg(
						$args,
						array(
							'key'     => $filter['data']['meta_key'],
							'value'   => $value,
							'compare' => $operator,
						)
					);
				}

				return $args;
			},
		)
	);
}

/**
 * Adds an additional meta query to the arguments for WP_Query.
 *
 * @param array  $args          Current args for the query.
 *
 * @param array  $meta_query    Meta query to add.
 *
 * @param string $relation      Optional. Relationship to current meta queries. Defaults to 'AND'.
 *
 * @see WP_Query
 *
 * @return array The new args.
 */
function add_meta_query_arg( $args, $meta_query, $relation = 'AND' ) {
	if ( ! array_key_exists( 'meta_query', $args ) ) {
		$args['meta_query'] = array();
	}

	$args['meta_query'] = array(
		'relation' => $relation,
		$meta_query,
		$args['meta_query'],
	);

	return $args;
}
