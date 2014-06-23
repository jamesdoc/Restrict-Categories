<?php

function rc_add_dashboard_widgets() {
	
	global $current_user;
    
	$settings = get_option( 'restrict_categories_user_access' );
	$setting_name = $current_user->user_nicename . '_user_cats';
	
	if($settings && array_key_exists($setting_name, $settings)){
		wp_add_dashboard_widget(
			'section_stats',
			'Section Stats',
			'rc_section_stats'
		);
	}
	
}

add_action( 'wp_dashboard_setup', 'rc_add_dashboard_widgets' );

function rc_get_section_stats($section_slug){
	
	global $wpdb;
	
	$qry = "
		SELECT 'total-posts' as 'id', " . $wpdb->prefix . "terms.name as 'title', COUNT(ID) as count
		FROM " . $wpdb->prefix . "posts
			JOIN " . $wpdb->prefix . "term_relationships ON " . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "term_relationships.object_id
			JOIN " . $wpdb->prefix . "term_taxonomy ON " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id
			JOIN " . $wpdb->prefix . "terms ON " . $wpdb->prefix . "term_taxonomy.term_id = " . $wpdb->prefix . "terms.term_id
		WHERE post_type='post'
			AND post_status = 'publish'
			AND " . $wpdb->prefix . "terms.slug = '".$section_slug."'
		
		UNION ALL
		
		SELECT 'last-x-days' as 'id', 'Last 90 days' as 'title', counter as 'count'
		FROM (
			SELECT COUNT(ID) AS 'counter', name
			FROM " . $wpdb->prefix . "posts
			JOIN " . $wpdb->prefix . "term_relationships ON " . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "term_relationships.object_id
			JOIN " . $wpdb->prefix . "term_taxonomy ON " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id
			JOIN " . $wpdb->prefix . "terms ON " . $wpdb->prefix . "term_taxonomy.term_id = " . $wpdb->prefix . "terms.term_id
			WHERE post_type='post'
				AND post_status = 'publish'
				AND " . $wpdb->prefix . "terms.slug = '".$section_slug."'
			  	AND post_date BETWEEN NOW() - INTERVAL 90 DAY AND NOW()
		) AS xdays
	";
	
	return $wpdb->get_results($qry);
	
}

function rc_get_average_section_stats(){
	global $wpdb;
	
	$qry = "
		SELECT 'average_post_count' as 'id', 'Posts per section in last 90 days' as 'title', floor(avg(counter)) as 'count'
		FROM(
			SELECT COUNT(ID) AS 'counter', name
			FROM " . $wpdb->prefix . "posts
			JOIN " . $wpdb->prefix . "term_relationships ON " . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "term_relationships.object_id
			JOIN " . $wpdb->prefix . "term_taxonomy ON " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id
			JOIN " . $wpdb->prefix . "terms ON " . $wpdb->prefix . "term_taxonomy.`term_id` = " . $wpdb->prefix . "terms.term_id
			WHERE post_type='post'
				AND post_status = 'publish'
			  	AND post_date BETWEEN NOW() - INTERVAL 90 DAY AND NOW()
			  	AND taxonomy = 'category'
			GROUP BY " . $wpdb->prefix . "terms.term_id
		) as siteaverage
	";
	
	return $wpdb->get_results($qry);
}

function rc_section_stats(){
	
	$site_avg = rc_get_average_section_stats();
	
	?>
	<table width="100%" style="background-color: #33A7D5; color: white; padding: 2%">
	
		<tr style="padding: 2%">
			<td colspan="2" width="100%">
				<h4 style="font-size: 2em; color: white;">Section average</h4>
			</td>
		</tr>
		
		<?php foreach ($site_avg as $stat): ?>
	
		<tr style="padding: 2%">
			<td width="50%">
				<?php echo $stat->title; ?>
			</td>
			
			<td width="50%">
				<?php echo $stat->count; $avg=$stat->count ?> Posts
			</td>
		</tr>
			
		
		<?php endforeach; ?>
	</table>
	<?php
	
	global $current_user;
    
	$settings = get_option( 'restrict_categories_user_access' );
	$setting_name = $current_user->user_nicename . '_user_cats';
		
	$settings = $settings[$setting_name];
	array_pop($settings); // Remove dross at the end of the settings array
	
	$i = 0;
	foreach ($settings as $section){
	
		$stats = rc_get_section_stats($section);
		echo '<hr />';
		
		?>
		
		<table width="100%" style="background-color: #0074a2; color: white; padding: 2%">
			<?php $ii = 0; foreach ($stats as $stat): ?>
			
			<?php if($ii == 0): ?>
			<tr style="padding: 2%">
				<td colspan="2" width="100%">
					<h4 style="font-size: 2em; color: white;"><?php echo $stat->title; ?></h4>
				</td>
			</tr>
			
			<tr style="padding: 2%">
				<td width="50%">
					Total posts
				</td>
				
				<td width="50%">
					<?php echo $stat->count; ?> Posts
				</td>
			</tr>
			<?php else: ?>
			<tr style="padding: 2%">
				<td width="50%">
					<?php echo $stat->title; ?>
				</td>
				
				<td width="50%">
					<?php echo $stat->count; ?> Posts
				</td>
			</tr>
			<?php endif; ?>
				
			
			<?php $ii++; endforeach; ?>
			
		</table>
		<?php
	
		$i++;
	}
	
}

?>