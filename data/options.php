<?php

// Register options page
paf_options( array( 'external_slugified_thumbnails_pattern' => array(
    'page' => 'external_slugified_thumbnails',
    'title' => __( 'Pattern' ),
    'default' => 'http://goo.gl/pchhEo',
    'description' => 'You can use the tokens <code>{{POST_SLUG}}</code> and <code>{{CATEGORY_SLUG}}</code>.'
) ) );
