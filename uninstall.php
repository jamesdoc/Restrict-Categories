<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

delete_option( 'RestrictCats_options' );
delete_option( 'RestrictCats_user_options' );
delete_option( 'restrict_categories_user_access' );