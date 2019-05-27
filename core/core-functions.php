<?php
/**
 * Core Functions.
 *
 * WARNING: This file is part of the Genesis Child Theme core. DO NOT EDIT
 * this file under any circumstances. Please make all modifications in
 * the top level directory of this child theme, e.g functions.php.
 */

namespace SeoThemes\GenesisChildTheme;

// Load Composer.
require_once get_stylesheet_directory() . '/vendor/autoload.php';

add_filter( 'child_theme_updater_skip', __NAMESPACE__ . '\updatable_directories' );
/**
 * Add `core` to the list of updatable directories.
 *
 * @since 1.0.0
 *
 * @param array $defaults List of directories that are changed during an update.
 *
 * @return array
 */
function updatable_directories( $defaults ) {
	return array_merge( [ 'core' ], $defaults );
}

add_filter( 'theme_scandir_exclusions', __NAMESPACE__ . '\theme_scandir_exclusions' );
/**
 * Hide files from admin theme editor.
 *
 * @since 1.0.0
 *
 * @param array $exclusions Array of excluded directories and files.
 *
 * @return array
 */
function theme_scandir_exclusions( $exclusions ) {
	if ( 'theme-editor' !== get_current_screen()->id ) {
		return $exclusions;
	}

	return array_merge( [
		'core',
		'composer.json',
	], $exclusions );
}

add_action( 'init', __NAMESPACE__ . '\load_plugin_update_checker' );
/**
 * Maybe load plugin update checker.
 *
 * @since 1.0.0
 *
 * @return void
 */
function load_plugin_update_checker() {
	if ( ! class_exists( 'Puc_v4p6_Factory' ) ) {
		return;
	}

	$defaults = \apply_filters( 'child_theme_updater', [
		'repo'   => \wp_get_theme()->get( 'ThemeURI' ),
		'file'   => \get_stylesheet_directory(),
		'theme'  => \get_stylesheet(),
		'token'  => '',
		'branch' => 'master',
	] );

	$plugin_update_checker = \Puc_v4_Factory::buildUpdateChecker(
		$defaults['repo'],
		$defaults['file'],
		$defaults['theme']
	);
	$plugin_update_checker->setBranch( $defaults['branch'] );

	if ( '' !== $defaults['token'] ) {
		$plugin_update_checker->setAuthentication( $defaults['token'] );
	}
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts_styles' );
/**
 * Load core scripts and styles.
 *
 * @since 1.0.0
 *
 * @return void
 */
function enqueue_scripts_styles() {
	$handle = 'core';
	$uri    = get_stylesheet_directory_uri() . '/core/core-';

	// Enqueue script.
	wp_register_script( $handle, $uri . 'scripts.js' );
	wp_enqueue_script( $handle );

	// Enqueue style.
	wp_register_style( $handle, $uri . 'styles.css' );
	wp_enqueue_style( $handle );
}
