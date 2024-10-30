<?php 
// CUSTOM POST TYPE TREE - the_tree will return $customPostTypeTree to be used in template
global $post;
require_once(WP_PLUGIN_DIR.'/custom-post-type-tree/index.php');
customPostTypeTree::init();
$customPostTypeTree = customPostTypeTree::getTree($post->ID, $post->post_type);
?>