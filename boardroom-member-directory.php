<?php
/**
 * Plugin Name:       Boardroom Member Directory
 * Plugin URI:        https://yoursite.com
 * Description:       A lightweight member directory for WooCommerce Memberships. Displays members of a specific membership plan with custom profile fields. Embed with [member_directory] shortcode.
 * Version:           1.0.0
 * Author:            Your Company
 * License:           GPL-2.0+
 * Text Domain:       boardroom-member-directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BMD_VERSION', '1.0.0' );
define( 'BMD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BMD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load core files
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-fields.php';
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-query.php';
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-shortcode.php';
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-admin.php';
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-profile.php';

/**
 * Bootstrap the plugin.
 */
function bmd_init() {
    BMD_Fields::init();
    BMD_Shortcode::init();
    BMD_Admin::init();
    BMD_Profile::init();

    // Debug shortcode — admins only, remove after troubleshooting
    // Usage: [bmd_debug plan_id="1102"]
    add_shortcode( 'bmd_debug', function( $atts ) {
        if ( ! current_user_can( 'manage_options' ) ) return '';
        $atts = shortcode_atts( [ 'plan_id' => '' ], $atts );

        if ( ! function_exists( 'wc_memberships_get_membership_plan' ) ) {
            return '<p style="color:red">WooCommerce Memberships is not active.</p>';
        }

        $plan_id = absint( $atts['plan_id'] );
        $plan    = wc_memberships_get_membership_plan( $plan_id );

        if ( ! $plan ) {
            return '<p style="color:red">No plan found for ID: ' . esc_html( $plan_id ) . '</p>';
        }

        $out  = '<p style="color:green">Plan found: <strong>' . esc_html( $plan->get_name() ) . '</strong> (ID: ' . $plan_id . ', Slug: ' . esc_html( $plan->get_slug() ) . ')</p>';

        // Try each status individually
        foreach ( [ 'active', 'complimentary', 'pending', 'paused', 'expired', 'cancelled' ] as $status ) {
            $members = wc_memberships_get_user_memberships( null, [
                'plan'   => $plan->get_id(),
                'status' => [ $status ],
                'limit'  => -1,
            ] );
            $out .= '<p>Status <strong>' . esc_html( $status ) . '</strong>: ' . count( $members ) . ' membership(s)</p>';
        }

        // Also check without any status filter
        $all = wc_memberships_get_user_memberships( null, [
            'plan'  => $plan->get_id(),
            'limit' => -1,
        ] );
        $out .= '<p>No status filter: <strong>' . count( $all ) . ' total</strong></p>';

        if ( ! empty( $all ) ) {
            $out .= '<ul>';
            foreach ( array_slice( $all, 0, 5 ) as $m ) {
                $user = get_user_by( 'id', $m->get_user_id() );
                $out .= '<li>' . esc_html( $user ? $user->display_name : 'Unknown' ) . ' — status: ' . esc_html( $m->get_status() ) . '</li>';
            }
            if ( count( $all ) > 5 ) $out .= '<li>...and ' . ( count( $all ) - 5 ) . ' more</li>';
            $out .= '</ul>';
        }

        return $out;
    } );
}
add_action( 'plugins_loaded', 'bmd_init' );

/**
 * Enqueue front-end assets only when the shortcode is present.
 */
function bmd_enqueue_assets() {
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'member_directory' ) ) {
        wp_enqueue_style(
            'bmd-styles',
            BMD_PLUGIN_URL . 'assets/css/directory.css',
            [],
            BMD_VERSION
        );
        wp_enqueue_script(
            'bmd-scripts',
            BMD_PLUGIN_URL . 'assets/js/directory.js',
            [],
            BMD_VERSION,
            true
        );
        wp_localize_script( 'bmd-scripts', 'BMD', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'bmd_nonce' ),
        ] );
    }
}
add_action( 'wp_enqueue_scripts', 'bmd_enqueue_assets' );

/**
 * Activation hook — nothing to install, but good placeholder.
 */
function bmd_activate() {
    // Future: create custom DB table for extended fields if needed.
}
register_activation_hook( __FILE__, 'bmd_activate' );
