<?php

namespace SeoThemes\ChildThemeUpdater;

add_action( 'upgrader_source_selection', __NAMESPACE__ . '\before_update', 10, 4 );
/**
 * Runs before theme update.
 *
 * @since 1.0.0
 *
 * @param string       $source        File source location.
 * @param string       $remote_source Remote file source location.
 * @param \WP_Upgrader $theme_object  WP_Upgrader instance.
 * @param array        $hook_extra    Extra arguments passed to hooked filters.
 *
 * @return mixed
 */
function before_update( $source, $remote_source, $theme_object, $hook_extra ) {
	if ( is_wp_error( $source ) || ! is_a( $theme_object, 'Theme_Upgrader' ) ) {
		return $source;
	}

	// Setup WP_Filesystem.
	include_once ABSPATH . 'wp-admin /includes/file.php';
	\WP_Filesystem();
	global $wp_filesystem;

	// Duplicate theme to /temp/ directory.
	$src    = \get_stylesheet_directory();
	$target = dirname( $src ) . '/temp';
	$skip   = apply_filters( 'child_theme_updater_skip', [ 'vendor' ] );

	\wp_mkdir_p( $target );
	\copy_dir( $src, $target, $skip );

	return $source;
}

add_action( 'upgrader_post_install', __NAMESPACE__ . '\after_update', 10, 3 );
/**
 * Runs after theme update.
 *
 * @since 1.0.0
 *
 * @param bool  $response
 * @param array $hook_extra
 * @param array $result
 *
 * @return mixed
 */
function after_update( $response, $hook_extra, $result ) {
	if ( ! $response || ! array_key_exists( 'destination', $result ) ) {
		return $response;
	}

	// Setup WP_Filesystem.
	include_once ABSPATH . 'wp-admin/includes/file.php';
	\WP_Filesystem();
	global $wp_filesystem;

	/*
	 * Step 1. Move new vendor directory to temp.
	 */
	$src  = \get_stylesheet_directory() . '/vendor';
	$dest = dirname( dirname( $src ) ) . '/temp/vendor';

	\wp_mkdir_p( $dest );
	\copy_dir( $src, $dest );

	/*
	 * Step 2. Bump temp style sheet version.
	 */
	$new_theme    = \get_stylesheet_directory() . '/style.css';
	$new_data     = \get_file_data( $new_theme, [
		'Version' => 'Version',
	] );
	$new_version  = $new_data['Version'];
	$old_theme    = dirname( dirname( $new_theme ) ) . '/temp/style.css';
	$old_data     = \get_file_data( $old_theme, [
		'Version' => 'Version',
	] );
	$old_version  = $old_data['Version'];
	$old_contents = $wp_filesystem->get_contents( $old_theme );
	$new_contents = str_replace( $old_version, $new_version, $old_contents );

	$wp_filesystem->put_contents( $old_theme, $new_contents, FS_CHMOD_FILE );

	/*
	 * Step 3. Bring everything back except vendor directory.
	 */
	$target = \get_stylesheet_directory();
	$source = dirname( $target ) . '/temp';
	$skip   = apply_filters( 'child_theme_updater_skip', [ 'vendor' ] );

	\copy_dir( $source, $target, $skip );

	$wp_filesystem->delete( $source, true, 'd' );

	return $response;
}
