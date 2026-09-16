<?php
/**
 * Initializes filters in the rest API.
 *
 * @package wrd\wp-filters
 */

namespace wrd\wp_filters;

/**
 * Adds the details of filters to the post type endpoint.
 *
 * @param \WP_REST_Response $response   The current response.
 *
 * @param \WP_Post_Type     $post_type  The post type object.
 *
 * @return \WP_REST_Response The altered response.
 */
function rest_add_filters_to_post_type( $response, $post_type ) {
	if ( ! post_type_supports( $post_type->name, 'wrd\filters' ) ) {
		return $response;
	}

	$response->data['filters'] = get_post_filters( $post_type->name );

	return $response;
}
add_filter( 'rest_prepare_post_type', __NAMESPACE__ . '\\rest_add_filters_to_post_type', 10, 2 );

/**
 * Alters the query arguments for a GET posts request to match filters.
 *
 * @param array            $args       The current query arguments.
 *
 * @param \WP_Rest_Request $request    The current request.
 *
 * @return array $args The altered query arguments.
 */
function rest_add_filters_to_post_query( $args, $request ) {
	$filters = get_post_filters( $args['post_type'] );

	foreach ( $filters as $filter ) {
		if ( ! $request->has_param( $filter['name'] ) ) {
			continue;
		}

		$callback = $filter['get_callback'];

		if ( ! $callback || ! is_callable( $callback ) ) {
			continue;
		}

		$args = call_user_func( $callback, $args, $request->get_param( $filter['name'] ), $filter );
	}

	return $args;
}

/**
 * Adds the hooks for altering the post query arguments for all post types which support filtering.
 *
 * @return void
 */
function rest_add_post_type_query_filters() {
	$post_types = get_post_types();

	foreach ( $post_types as $post_type ) {
		if ( post_type_supports( $post_type, 'wrd\wp-filters' ) ) {
			add_filter( "rest_{$post_type}_query", __NAMESPACE__ . '\\rest_add_filters_to_post_query', 10, 2 );
		}
	}
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\rest_add_post_type_query_filters' );
