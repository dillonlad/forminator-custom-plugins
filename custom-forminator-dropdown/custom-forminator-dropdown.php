<?php
/*
Plugin Name: Custom Forminator Select Populate
Description: Dynamically populates a Forminator select field with options from a custom database table.
Version: 1.0
Author: Your Name
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register a REST API endpoint
add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/get-select-options', [
        'methods' => 'GET',
        'callback' => 'get_select_options',
        'permission_callback' => '__return_true'
    ]);
});

// Callback function for the REST API endpoint
function get_select_options( $request ) {
    global $wpdb;
    $table_name = 'wp_prescription_only_meds';  // Replace with your actual table name

    // Get the query parameter from the request
    $query = $request->get_param('query');
    $query = '%' . $wpdb->esc_like($query) . '%';  // Sanitize and prepare for LIKE query

    // Query the database with the query parameter to filter results
    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT `name` FROM $table_name WHERE `name` LIKE %s", $query),
        ARRAY_A
    );

    // Prepare the response
    $options = [];
    foreach ($results as $row) {
        $options[] = [
            'label' => $row['name'],
            'value' => $row['name']
        ];
    }

    return rest_ensure_response($options);
}

function enqueue_forminator_custom_script() {
    // Check if this is the page where the specific form is displayed
    if ( is_page() && has_shortcode( get_post()->post_content, 'forminator_form' ) ) {
        $form_id = 9763; // Replace with your actual Forminator form ID

        // Check if the form ID matches
        if ( strpos( get_post()->post_content, 'id="' . $form_id . '"' ) !== false ) {
            wp_enqueue_script(
                'populate-forminator-select',
                plugin_dir_url(__FILE__) . 'populate-forminator-select.js',
                array('jquery'),
                null,
                true
            );

            // Localize the form ID to use in the JavaScript file
            wp_localize_script('populate-forminator-select', 'forminatorFormID', array('id' => $form_id));
        }
    }
}
add_action('wp_enqueue_scripts', 'enqueue_forminator_custom_script');
