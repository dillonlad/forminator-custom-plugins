<?php
/*
Plugin Name: Forminator Spam Prevention
Description: Prevent spam in contact form
Version: 1.0
Author: Dillon Lad
License: GPL2
*/

add_filter(
	'forminator_custom_form_submit_errors',
	function( $submit_errors, $form_id, $field_data_array ) {
		$field_name = 'textarea-1';
		$submitted_fields = wp_list_pluck( $field_data_array, 'value', 'name' );

		if ($form_id != 9789) {
			error_log("Form is exempt from spam prevention: " . $form_id);
			return $submit_errors;
		}

        if ($submitted_fields[ $field_name ] === null) {
            error_log("No textarea field");
            return $submit_errors;
        }

		if( preg_match( '%((https?://)|(www.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i
', $submitted_fields[ $field_name ] ) ) {
			$submit_errors[][ $field_name ] = __( 'There is something wrong ' );
		}

        $text = $submitted_fields[ $field_name ] ;
        $apiKey = CLOUD_TRANSLATE_API_KEY;
        $urlEncoded = urlencode($text);

        $response = file_get_contents("https://translation.googleapis.com/language/translate/v2/detect?key=$apiKey&q=$urlEncoded");
        $result = json_decode($response, true);

        $language = $result['data']['detections'][0][0]['language'];

        error_log("SPAM DETECT: "  . $language);

        if ($language !== 'en') {
            $submit_errors[][ $field_name ] = __( 'Potential spam detected ' );
        };

		return $submit_errors;
	},
	10, 
	3
);

add_filter(
	'forminator_custom_form_submit_errors',
	function( $submit_errors, $form_id, $field_data_array ) {
		$field_name = 'email-1';
		$submitted_fields = wp_list_pluck( $field_data_array, 'value', 'name' );
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(co\.uk|org\.uk|gov\.uk|ac\.uk|sch\.uk|ltd\.uk|plc\.uk|me\.uk|net\.uk|nhs\.uk|mod\.uk|police\.uk|judiciary\.uk|com)$/i';

		if( preg_match($pattern, $submitted_fields[ $field_name ] ) !== 1 ) {
			$submit_errors[][ $field_name ] = __( 'There is something wrong with your email address' );
		}

		return $submit_errors;
	},
	10, 
	3
);