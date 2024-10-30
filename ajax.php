<?php
//if( !empty($_GET['parent']) && !empty($_GET['children']) && !empty($_GET['the_parent_post_tree_id']) && !empty($_GET['the_post_tree_id']) && !empty($_GET['act']) &&  $_GET['act'] == 'cptt' )
//{	
	global $TABLE, $customPostTypeTree;

	require_once('../../../wp-config.php'); 
	
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	
	$result = mysql_list_tables(DB_NAME);
	$num_rows = mysql_num_rows($result);
	
	for ($i = 0; $i < $num_rows; $i++) 
	{
	    $table = mysql_tablename($result, $i);
	    $matche = preg_match('/.*custom_post_type_tree/', $table, $matches, PREG_OFFSET_CAPTURE);
	    if($matche)
	    {
	    	$TABLE = $table;
	    }
	}
	
	if (!$link) 
	{
	    die('Could not connect: ' . mysql_error());
	}
	if (!mysql_select_db(DB_NAME)) 
	{
	    die('Could not select database: ' . mysql_error());
	}
	
	$sql = "INSERT INTO $TABLE (parent_post,post,the_post_id,this_post_id) VALUES ('".$_GET['parent']."','".$_GET['children']."','".$_GET['the_parent_post_tree_id']."','".$_GET['the_post_tree_id']."')";

	$result = mysql_query($sql);
	if ($result) 
	{
	    echo "Data added!";
	}
	else 
	{
		$sql = "DELETE FROM $TABLE WHERE parent_post='".$_GET['parent']."' AND post='".$_GET['children']."' AND the_post_id='".$_GET['the_parent_post_tree_id']."'";
	
		$result = mysql_query($sql);
		if ($result) 
		{
		    echo "Data deleted!";
		}
		else 
		{
			 die('Could not query:' . mysql_error());
		}
	}
	
	function tree( $parent_post = '', $level = 0 )
	{
		global $TABLE, $customPostTypeTree;
		$customPostTypeTree = array();
	
		if( !empty($parent_post) )
		{
			$WHERE = " WHERE parent_post='$parent_post' AND the_post_id='".$_GET['the_parent_post_tree_id']."'";
		}
		
		$sql = "SELECT parent_post,post,the_post_id,this_post_id FROM $TABLE $WHERE";

		$result = mysql_query($sql);
	
		// if has children
		if( $result )
		{
			$t = 0;
			while ($row = mysql_fetch_object($result))
			{
				$children[$row->post] = $row->post;
				$customPostTypeTree["$parent_post"][$t]['thumbnail'] = get_the_post_thumbnail($row->this_post_id, 'cemXcem');
				$customPostTypeTree["$parent_post"][$t]['this_post_id'] = $row->this_post_id;
				$customPostTypeTree["$parent_post"][$t]['post']['id'] = $row->the_post_id;
				$customPostTypeTree["$parent_post"][$t]['parent_post'] = $row->parent_post;
				$customPostTypeTree["$parent_post"][$t]['post']['name'] = $row->post;
				$t++;
		    }
		    $level++;
		}
	}
	
	$sql = "SELECT DISTINCT(parent_post) FROM $TABLE WHERE the_post_id='".$_GET['the_parent_post_tree_id']."'";

	$result = mysql_query($sql);
	if ($result)
	{
		while ($row = mysql_fetch_object($result))
		{
			$customPostTypeTree[$row->parent_post] = $row->parent_post;
			tree( $row->parent_post );
		}
	}
	
	mysql_free_result($result);
	mysql_close($link);
	die;
//}

?>