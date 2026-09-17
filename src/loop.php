<?php
/**
 * Initializes filters in the main query loop.
 *
 * @package wrd\wp-filters
 */

namespace wrd\wp_filters;

/**
 * Alters the query arguments for a post query loops to match filters.
 *
 * @param array $args The current query arguments.
 *
 * @return array $args The altered query arguments.
 */
function loop_add_filters_to_main_query( $args ) {
	if ( ! array_key_exists( 'post_type', $args ) ) {
		return $args;
	}

	if ( ! post_type_supports( $args['post_type'], 'wrd\wp-filters' ) ) {
		return $args;
	}

	$filters = get_post_filters( $args['post_type'] );

	foreach ( $filters as $filter ) {
		if ( empty( $_GET[ $filter['name'] ] ) ) {
			continue;
		}

		$callback = $filter['get_callback'];
		$value    = $_GET[ $filter['name'] ];

		if ( ! $callback || ! is_callable( $callback ) ) {
			continue;
		}

		$args = call_user_func( $callback, $args, $value, $filter );
	}

	return $args;
}

/**
 * Alters the public main archive query to match filters.
 *
 * @param \WP_Query $query The main query.
 *
 * @return void
 */
function loop_filter_main_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_archive() ) {
		return;
	}

	$args = loop_add_filters_to_main_query( $query->query_vars );

	foreach ( $args as $key => $value ) {
		$query->set( $key, $value );
	}
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\loop_filter_main_query' );
