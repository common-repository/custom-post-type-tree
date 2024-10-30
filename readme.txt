=== Custom Post Type Tree - parents children relationship ===
Plugin Name: Custom Post Type Tree - parents-children relationship
Description: Create Parents / Children Relationship between different post types checking one or more parent.
Plugin URI: http://cristianocarletti.com.br/wordpress/plugins/custom-post-type-tree/
Author: Cristiano Carletti <cristianocarletti@gmail.com>
Author URI: http://cristianocarletti.com.br
Contributors: Cristiano Carletti <cristianocarletti@gmail.com>
Tags: Parent, Child, Relationship, between, different, post, types, parent, custom, custom post, custom post type, link, related, relation, parents, child, children, relationship, tree, between
Requires at least: 3.2
Tested up to: 3.3
Stable tag: 1.8
Version: 1.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Create Parents / Children Relationship between different post types checking one or more parent.

== Installation ==
1. Upload the folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Edit a page, post or custom post to choice the parents custom posts.
4. See Screenshots for more details.

== Screenshots ==
1. Before starting, you have to create your Custom Post Type with an plugin or coding wordpress with register_post_type. I created: Selos.
2. I added a post type Selos.
3. I chose two custom post type as parents, jacuzzi_banheira and jacuzzi_spa.
4. Opening the cutom post type Spa.
5. I added a post SPA J180.
6. I can see Selos registered and choose which I want to display in the template.
7. And then they appear in the template, you can printing also using: customPostTypeTree::viewChildren($customPostTypeTree); on single.php code, or use the Array $customPostTypeTree as you wish.

== Changelog ==
= 1.7 =
Now posts and pages may be sons of custom posts, beyond their own custom posts.
Bug fix.
= 1.6 =
Bug fix. Shows only if there are parents.
= 1.5 =
Now you can define the parent post at the edit post page checking the boxes with parent post.
= 1.4 =
Added permalink to children post.
= 1.3 =
Added screenshot-10.png and screenshot-11.png
= 1.2 =
Added static function viewChildren to be used whit single.php
= 1.1 =
Screenshots added.
= 1.0 =
New plugin Custom Post Typ Tree - parent-child relationship
This version works with thumbnail, parent post, post id, post name and this post id of an custom post type tree

== Please Vote and Enjoy ==
Your votes really make a difference! Thanks.

== Atention ==
1. Before starting, you have to create your Custom Post Type with an plugin or coding wordpress with register_post_type.
2. In case of Warning: Cannot modify header information - headers already sent by
You can activate the buffer using: ob_start();