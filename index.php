<?php
/*
Plugin Name: Custom Post Type Tree - parents children relationship
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

*/
ob_start();
/**
 * Custom Post Type Tree - parents-children relationship
 * Create Parents / Children Relationship between different post types checking one or more parent.
 * @author Cristiano Carletti <cristianocarletti@gmail.com>
 * @package Custom Post Type Tree - parents children relationship
 * 
 */
class customPostTypeTree 
{
	private static $wpdb;
	private static $info;
	private static $plugins_url;
	private static $get_bloginfo;
	private static $admin_url;
	private static $native_posts;
	
	public static function init()
	{
		global $wpdb, $TREE, $customPostTypeTree;
		
		if ( is_admin() )
		{
			//add_action('admin_menu', array('customPostTypeTree','addMenu'));
			add_action("the_content", array("customPostTypeTree","createTree"));
			add_action('add_meta_boxes', array('customPostTypeTree','setMetaBoxes'));
			add_action('admin_init', array('customPostTypeTree','enqueue_admin_scripts'));
		}

		customPostTypeTree::$wpdb = $wpdb;
		customPostTypeTree::$info['plugin_fpath'] = dirname(__FILE__);
		customPostTypeTree::$plugins_url = plugins_url('',__FILE__);
		customPostTypeTree::$get_bloginfo = get_bloginfo('url');
		customPostTypeTree::$admin_url = admin_url(); 
		customPostTypeTree::$native_posts = array('post','page','attachment','revision','nav_menu_item','object','feedback');		
	}
	
	public static function enqueue_admin_scripts()
	{
		wp_enqueue_script("script", plugins_url(basename(dirname(__FILE__)) . '/script.js'), array('jquery'));
	}   

	public static function install()
	{
		global $TREE;

		if ( is_null(customPostTypeTree::$wpdb) ) customPostTypeTree::init();
		
		$sql = "CREATE TABLE IF NOT EXISTS ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree ( parent_post varchar(255) NOT NULL, post varchar(255) NOT NULL, the_post_id bigint(20), this_post_id bigint(20), CONSTRAINT treeID UNIQUE (parent_post,post,the_post_id) );";
		
		customPostTypeTree::$wpdb->query($sql);
		
	}

	public static function desinstall()
	{

		$sql = "DROP TABLE ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree";
		
		customPostTypeTree::$wpdb->query($sql);
		
	}
	
	public static function addMenu()
	{
		add_options_page('Custom Post Type Tree','Custom Post Tree',10,__FILE__,array("customPostTypeTree","createTree"));
	}
	
	private static function _cbParentPost()
	{
		$post_types = get_post_types();
		
		$PARENT_POSTS = '<select name="parent_post" id="parent_post" style=\"cursor:pointer;\"><option value="0">Select...</option>';
		foreach ($post_types as $k => $v)
		{
			if( !in_array($k, customPostTypeTree::$native_posts) )
				$PARENT_POSTS .= "<option value=\"".$k."\">".$v."</option>";
		}
		$PARENT_POSTS .= "</selec>";

		return $PARENT_POSTS;
	}
	
	private static function _cbPost()
	{
		$post_types = get_post_types();
		
		$POSTS = '<select name="post" id="post" style=\"cursor:pointer;\"><option value="0">Select...</option>';
		foreach ($post_types as $k => $v)
		{
			if( !in_array($k, customPostTypeTree::$native_posts) )
				$POSTS .= "<option value=\"".$k."\">".$v."</option>";
		}
		$POSTS .= "</selec>";

		return $POSTS;
	}
	
	public static function createTree()
	{
		global $TREE;
		
		$templateVars['{UPDATED}'] = '<div id="message" class="updated fade"><p><strong>';
		$templateVars['{ERROS}'] = "";
		$templateVars['{TREE}'] = "";
		
		$continue = true;

		// Execute actions
		if( !empty($_POST['action']) )
		{
			$del = customPostTypeTree::del( $_POST['parent'], $_POST['children'] );
			 if( $del )
				$templateVars['{UPDATED}'] .= 'Relation deleted!';
			else 
				$templateVars['{UPDATED}'] .= 'The relation can not be deleted!';
			 
		}
		else // Validate
		{
			// Please select the posts
			if( empty($_POST))
			{
				$templateVars['{UPDATED}'] .= "Please select the posts!";
				$continue = false;
			}
			
			// Parent Post and Post can not be the same
			if( !empty($_POST) && $_POST['parent_post'] == $_POST['post'] )
			{
				$templateVars['{UPDATED}'] .= "Parent Post and Post can not be the same!";
				$continue = false;
			}
			
			// if relation exists
			if( !empty($_POST['parent_post']) && !empty($_POST['post']) && $_POST['parent_post'] != $_POST['post'] )
			{
				$resultados = customPostTypeTree::$wpdb->get_results( "SELECT count(*) as count FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree WHERE (parent_post='".$_POST['parent_post']."' AND post='".$_POST['post']."') OR (parent_post='".$_POST['post']."' AND post='".$_POST['parent_post']."')" );

				if( $resultados->num_rows > 0 )
				{
					$templateVars['{UPDATED}'] .= "This relationship is already there!";
					$continue = false;
				}
			}
			
			if ( count($_POST) > 0 && $continue )
			{
				$sql = "INSERT INTO ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree (parent_post,post) VALUES ('".$_POST['parent_post']."','".$_POST['post']."')";
				//die($sql);
				$ins = customPostTypeTree::$wpdb->query( $sql );
				if ($ins)
				{
					$templateVars['{UPDATED}'] .= "Data added!";
				}else{
					$templateVars['{UPDATED}'] .= "Error adding data!";
				}	
			}
		}

		$templateVars['{UPDATED}'] .= "</strong></p></div>";

		require_once( dirname(__FILE__) . "/streams.php" );
		
		$admTplObj = new FileReader(customPostTypeTree::$info['plugin_fpath']."/admin_tpl.htm");
		$admTpl = $admTplObj->read($admTplObj->_length);

		$templateVars['{PARENT_POSTS}'] = customPostTypeTree::_cbParentPost();
		$templateVars['{POSTS}'] = customPostTypeTree::_cbPost();
		
		$resultados = customPostTypeTree::$wpdb->get_results( "SELECT DISTINCT(parent_post) as parent_post FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree WHERE the_post_id is NULL" );

		if( !empty($resultados[0]->parent_post) ) 
		{
			$TREE .= "<input type=\"hidden\" id=\"parent\" name=\"parent\">";
			$TREE .= "<input type=\"hidden\" id=\"children\" name=\"children\">";
			$TREE .= "<input type=\"hidden\" id=\"action\" name=\"action\">";
			$i = 0;
			foreach ($resultados as $res) 
			{
				$TREE .= "<br><br>&nbsp;&nbsp;&nbsp;&nbsp;";
				$TREE .= "<b>".$res->parent_post."</b>";
				customPostTypeTree::tree($res->parent_post, 0, $i);
				
				$i++;
			}
			$templateVars['{TREE}'] = $TREE;
		}
		
		$admTpl = strtr($admTpl,$templateVars);
		
		echo $admTpl;
		
	}
	
	private static function tree( $parent_post = '', $children = 0, $i = 0 )
	{
		global $TREE;

		if( !empty($parent_post) )
		{
			$WHERE = " WHERE parent_post='$parent_post'";
		}
		
		$resultados = customPostTypeTree::$wpdb->get_results( "SELECT post FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree $WHERE" );

		$children++;
		if( !empty($resultados) )
		{
			$j = 0;
			foreach ($resultados as $res)
			{
				$TREE .= "<br>";
				$k = 0;
				for($c=1; $c<=$children;$c++)
				{
					$TREE .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					if($c == $children)
					{
						// if has children
						$result = customPostTypeTree::$wpdb->get_results( "SELECT the_post_id FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree WHERE post='".$res->post."'" );
						//print '<pre>';print_r($result);
						if( empty($result[0]->the_post_id) )
						{
							$TREE .= "&nbsp;&nbsp;&nbsp;&nbsp;<input title=\"Delete ".$res->post."?\"; type=\"button\" value=\"&nbsp;-&nbsp;\" style=\"cursor:pointer;\" onclick=\"document.getElementById('ask_del_$i$j$k$c').style.display='block';\">&nbsp;&nbsp;";			
							$TREE .= $res->post;
							$TREE .= "&nbsp;<span id=\"ask_del_$i$j$k$c\" style=\"display:none;\">Are you sure you want to delete the relation ".$parent_post."-".$res->post."?&nbsp;<input type=\"button\" value=\"Yes\" style=\"cursor:pointer;\" onclick=\"del('".$parent_post."','".$res->post."');\">&nbsp;<input type=\"button\" value=\"No\" style=\"cursor:pointer;\" onclick=\"document.getElementById('ask_del_$i$j$k$c').style.display='none';\"></span>";				
						}
					}
					$k++;
				}
				
		       	customPostTypeTree::tree($res->post, $children);
		       	$j++;
		    }
		}
	}
	
	public static function del( $parent_post, $post )
	{
		$WHERE = " WHERE ";
		
		if( !empty($parent_post) )
		{
			$WHERE .= "parent_post='$parent_post' ";
		}
		
		if( !empty($post) )
		{
			$WHERE .= "AND post='$post' ";
		}

		$del = customPostTypeTree::$wpdb->query("DELETE FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree $WHERE" );
		if( $del )
			return true;
			
		return false;
	}

	
	// This function adds a meta box with a callback function of my_metabox_callback()
	public static function setMetaBoxes( $post ) 
	{
		$sql = "SELECT post FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree WHERE parent_post='".$post."'";

		$resultados = customPostTypeTree::$wpdb->get_results( $sql );

		if( !empty($resultados) )
		{
			foreach ($resultados as $res)
			{
				$args = array(
						'post_type'	 =>	$res->post,
						'post_status' => 'publish'
					);
				$loop = new WP_Query($args);
				$array = array();

				foreach( $loop->posts as $tree_custom_posts)				
				{
					$tree_custom_post_id = $tree_custom_posts->ID;

					$array[] = array("the_post_tree_parent"=>$res->post,"the_post_tree_title"=>get_the_title($tree_custom_post_id),"the_post_tree_thumbnail"=>get_the_post_thumbnail($tree_custom_post_id, 'cemXcem'), "the_post_tree_id"=>$tree_custom_post_id, "the_post_tree_name"=>'tree_metabox_'.$res->post);
							
					add_meta_box( 
				           'tree_metabox_'.$res->post,
				           $res->post,
				           'customPostTypeTree::metaboxesCallback',
				           $post,
				           'normal',
		           		   'low'
				           ,$array
				      );
				}
			}
		}
	}
	
	// $post is an object containing $_POST data
	// $metabox is an array with metabox id, title, callback, and args elements. 
	// The args element is an array containing your passed $callback_args variables.
	public static function metaboxesCallback ( $post, $metabox ) 
	{
		global $customPostTypeTree;
		
		$t = 0;
		$d = 0;
		foreach ($metabox['args'] as $args)
		{
			$img = customPostTypeTree::$plugins_url."/ajax_load.gif";
			$url = customPostTypeTree::$plugins_url."/ajax.php";
			$checked = "";
			
			$sql = "SELECT parent_post,post,this_post_id FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree WHERE parent_post='".$metabox['title']."' AND post='".$args['the_post_tree_title']."' AND the_post_id='".$post->ID."'";
			
			$resultados = customPostTypeTree::$wpdb->get_results( $sql );
			if( !empty($resultados) )
			{
				foreach ($resultados as $res)
				{
					if( !empty($res->post) )
					{	
						$checked = "checked=\"checked\"";
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['thumbnail'] = $args['the_post_tree_thumbnail'];
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['this_post_id'] = $args['the_post_tree_id'];
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['post']['id'] = $post->ID;
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['parent_post'] = $args['the_post_tree_parent'];
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['post']['name'] = $args['the_post_tree_title'];
						$t++;
					}
					else
					{
						$checked = "checked=\"\"";
					}
				}
			}
			echo "<div id='load-".$post->ID."-".$args['the_post_tree_id']."-".$d."' style='display:none;'><img src='".WP_PLUGIN_URL."/custom-post-type-tree/ajax_load.gif'></div>";
			echo "<div id='".$post->ID."-".$args['the_post_tree_id']."-".$d."'>";
			echo "<input type='checkbox' $checked onclick=\"ajaxTree('".$url."?the_parent_post_tree_id=".$post->ID."&the_post_tree_id=".$args['the_post_tree_id']."&act=cptt&parent=".$metabox['title']."&children=".$args['the_post_tree_title']."','".$post->ID."-".$args['the_post_tree_id']."-".$d."','load-".$post->ID."-".$args['the_post_tree_id']."-".$d."');\"></input>&nbsp;";
			echo $args['the_post_tree_title']."<br>".$args['the_post_tree_thumbnail'];
			echo "<br><br style='clear:both'/></div>";
			$d++;
		}

	}

	// called by the post
	public static function getTree( $postID, $parent_post )
	{
		global $customPostTypeTree;
		
		$sql = "SELECT post FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree WHERE parent_post='".$parent_post."'";

		$resultados = customPostTypeTree::$wpdb->get_results( $sql );

		if( !empty($resultados) )
		{
			foreach ($resultados as $res)
			{
				$args = array(
						'post_type'	 =>	$res->post,
						'post_status' => 'publish'
					);
				$loop = new WP_Query($args);
				$array = array();

				foreach( $loop->posts as $tree_custom_posts)				
				{
					$tree_custom_post_id = $tree_custom_posts->ID;

					$array[] = array("the_post_tree_parent"=>$res->post,"the_post_tree_title"=>get_the_title($tree_custom_post_id),"the_post_tree_thumbnail"=>get_the_post_thumbnail($tree_custom_post_id, 'cemXcem'), "the_post_tree_id"=>$tree_custom_post_id, "the_post_tree_name"=>'tree_metabox_'.$res->post);
							
					customPostTypeTree::getChildren( $postID, $array );
				}
			}
		}
		return $customPostTypeTree;
	}
	
	public static function getChildren( $postID, $array )
	{
		global $customPostTypeTree;
		
		$t = 0;
		foreach ($array as $args)
		{	
			$sql = "SELECT parent_post,post,this_post_id FROM ".customPostTypeTree::$wpdb->prefix."custom_post_type_tree WHERE parent_post='".$args['the_post_tree_parent']."' AND post='".$args['the_post_tree_title']."' AND the_post_id='".$postID."'";
			
			$resultados = customPostTypeTree::$wpdb->get_results( $sql );
			if( !empty($resultados) )
			{
				foreach ($resultados as $res)
				{
					if( !empty($res->post) )
					{	
						$checked = "checked=\"checked\"";
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['thumbnail'] = $args['the_post_tree_thumbnail'];
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['this_post_id'] = $args['the_post_tree_id'];
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['post']['id'] = $postID;
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['parent_post'] = $args['the_post_tree_parent'];
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['post']['name'] = $args['the_post_tree_title'];
						$customPostTypeTree[$args['the_post_tree_parent']][$t]['permalink'] = '/?post_type='.$args['the_post_tree_parent'].'&p='.$args['the_post_tree_id'];
						$t++;
					}
				}
			}
		}
	}
	
	public static function viewChildren( $array = array() )
	{
		if( !empty( $array ) )
		{
		
			if( !empty($array) )
			{
				foreach ($array as $k => $v)
				{
					for($i=0; $i<count($v); $i++)
					{
						echo '<br>';
						print '<a href="'.$v[$i]['permalink'].'">';
						print $v[$i]['post']['name'];
						print '</a>';
						echo '<br>';
						print '<a href="'.$v[$i]['permalink'].'">';
						print $v[$i]['thumbnail'];
						print '</a>';	
					}				
				}
			}	
		}
	}

}

/**
 *  Add HOOKs
 */
$mppPluginFile = substr(strrchr(dirname(__FILE__),DIRECTORY_SEPARATOR),1).DIRECTORY_SEPARATOR.basename(__FILE__);

register_activation_hook($mppPluginFile,array('customPostTypeTree','install'));

add_filter('init', array('customPostTypeTree','init'));





add_action( 'add_meta_boxes', 'myplugin_add_custom_box' );

wp_enqueue_script("script", plugins_url(basename(dirname(__FILE__)) . '/script.js'), array('jquery'));

function myplugin_add_custom_box()
{
	$post_types = get_post_types();
	$array = array('attachment','revision','nav_menu_item','object','feedback');
	
	foreach ($post_types as $k => $v)
	{
		if( !in_array($k, $array) )
		{
			add_meta_box(
		        'myplugin_sectionid',
		        __( 'Custom Post Parent', 'myplugin_textdomain' ), 
		        'myplugin_inner_custom_box',
		        $v
		    );
		}
	}
}

function myplugin_inner_custom_box( $post )
{
	global $wpdb;

	$img = plugins_url('',__FILE__) . "/ajax_load.gif";
	$url = plugins_url('',__FILE__) . "/ajax.php";
	$checked = "";
			
    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  	$post_types = get_post_types();
	$array = array('attachment','revision','nav_menu_item','object','feedback');
		
	$sql = "SELECT parent_post FROM ".$wpdb->prefix."custom_post_type_tree WHERE post='".get_post_type()."'";
	//print $sql;
	$resultados = $wpdb->get_results( $sql );
        $parents = array();
	foreach ($resultados as $res) 
	{
		$parents[] = $res->parent_post;
	}	
        if( !empty($post_types) )
	foreach ($post_types as $k => $v)
	{
		if( !in_array($k, $array) && $v != get_post_type() )
		{
			$checked = in_array($v, $parents)? "checked=\"checked\"": "";

			echo "<div id='div_load_".$v."'></div>";
			echo "<div id='div_post_".$v."'>";
			echo "<input type='checkbox' $checked name='".$v."' id='".$v."' onclick=\"ajaxTree('".$url."?the_parent_post_tree_id=".get_the_ID($v)."&act=cptt&parent=".$v."&children=".get_post_type()."','div_post_".$v."','div_load_".$v."');\"></input>&nbsp;".$v."<br>";
			echo "</div>";
		}
	}
}	
      
?>