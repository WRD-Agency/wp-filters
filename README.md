# wrd\wp-filters

Adds complex filtering options to WordPress queries and the REST API by registering them in PHP.

---

## Usage

Filters are registered on a per post type basis with a name and a callback. When a query is made to get posts in the post type, the name of the filter is used to find it and pass it's value to the callback. The callback is also given the current query to modify.

In order for filters to be used, the post type must support `wrd\wp-filters`. You can add this via `add_post_type_support` or during registration for custom post types.

Filters apply to REST API queries and the public main query on archive pages.

Example:

```
add_post_support( 'post', 'wrd\filters' );

add_post_meta_filter( 'post', array(
	'name'     => 'my_meta_filter', // Optional for Post Meta filters, defaults to 'meta_key'.
	'label'    => __('My Meta Filter', 'wrd'), // Optional.
	'values'   => null, // Optional. Array of objects with 'value', 'label' and 'count' properties. Defaults to all unique values for this meta key.

	'meta_key' => 'meta_key', // Required.
));
```

`GET /wp-json/wp/v2/types` -- Now includes a 'filters' property that lists all the filters available and their values.

`GET /wp-json/wp/v2/post` -- Now supports a 'my_meta_filter' field that applies the filter.

---

## Functions

You can set up a custom filter using `add_post_filter`.

Alternatively, filters can be easily auto-generated using the following functions:

- `add_post_meta_filter`
- `add_post_sort_filter`
- `add_post_term_filter`

---

## API Reference

### /wp-json/v2/types/

A new propery, `filters`, is added to all post type objects. It contains an array of Filters.

### /wp-json/v2/post/

Applies to all post type queries. The name property of the register filters can be added.

## Types

**Filter**

| Property | Type          | Default |
| -------- | ------------- | ------- |
| name     | string        | ''      |
| label    | string        | ''      |
| values   | FilterValue[] | []      |
| singular | boolean       | false   |
| data     | any           | []      |

**FilterValue**

| Property | Type   |
| -------- | ------ |
| value    | any    |
| label    | string |
| count    | number |
