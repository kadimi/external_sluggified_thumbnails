<?php
add_filter( 'post_thumbnail_html', 'est_image_src', 10, 5 );
function est_image_src( $html, $post_id, $post_thumbnail_id, $size, $attr ) {

	$post = get_post( $post_id );

	// Only support posts
	if ( 'post' !== $post->post_type ) {
		return $html;
	}

	// Do nothing if featured image exists
	if ( ! empty( $html ) ) {
		return $html;
	}

	// Do nothing if post doesn't exist
	if ( ! $post ) {
		return $html;
	}
	$category = get_the_category($post_id);
	$category_slug =  $category[0]->slug;

	// Build URL
	$src = paf( 'external_slugified_thumbnails_pattern' );
	$src = str_replace(
		array(
			'{{POST_SLUG}}',
			'{{CATEGORY_SLUG}}',
		),
		array(
			$post->post_name,
			$category_slug,
		),
		$src
	);

	// Do nothing if URL doesn't exist
	$file_exists = url_works( $src );
	if ( ! $file_exists ) {
		return $html;
	}

	// Do nothing if URL doesn't lead to an image
	$size = getimagesize_cached( $src );
	if ( ! $size ) {
		return $html;
	}

	if ( ! $html ) {
		$html = '<img src="{{src}}" tite="{{title}}" alt="{{alt}}" height="{{height}}" width="{{width}}"> ';
		$html = str_replace(
			array(
				'{{src}}',
				'{{title}}',
				'{{alt}}',
				'{{width}}',
				'{{height}}',
			),
			array(
				$src,
				$post->post_title,
				$post->post_title,
				$size[0],
				$size[1],
			),
			$html
		);
	}
	return $html;
}

/**
 * Check if a URL returns a 200 HTTP response
 *
 * The result us cachec for one hour.
 *
 * @param  string $url The URL
 * @return bool   The result   
 */
function url_works( $url ) {

	// Return cached success
	if ( 'url_works' === get_transient( md5( 'url_' . $url ) ) ) {
		return TRUE;
	}

	// Check result
	$response = wp_remote_head( $url );
	if ( is_wp_error($response) || 200 != $response['response']['code'] ) {
		$url_works = FALSE;
	} else {
		$url_works = TRUE;
	}

	// Save success
	if ( $url_works ) {
		set_transient( md5( 'url_' . $url ), 'url_works', 3600 );
	}

	// Return result
	return $url_works;
}

/**
 * Get image size and remember it for one hour
 * @param  string $src Image URL.
 * @return array       Width and ieight.
 */
function getimagesize_cached ( $src ) {
	// Return cached size
	if ( $size = get_transient( md5( 'imagesize_' . $src ) ) ) {
		return unserialize( $size );
	}

	$size = getimagesize( $src );
	if ( $size ) {
		set_transient( md5( 'imagesize_' . $src ), serialize( $size ), 3600 );
	}
	return getimagesize( $src );
}