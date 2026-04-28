<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * AI Service
 *
 * @class 		PH_AI_Service
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_AI_Service {

	public function make_request( $action, $payload = array() ) {

		if ( empty( $action ) ) {
			return new WP_Error(
				'propertyhive_ai_no_action',
				__( 'No AI action was specified.', 'propertyhive' )
			);
		}

		$body = array(
			'action'       => sanitize_key( $action ),
			'payload'      => $payload,
		);

		$body = apply_filters( 'propertyhive_ai_request_body', $body, $action, $payload );

		$response = wp_remote_post(
            apply_filters( 'propertyhive_ai_service_endpoint', 'https://wp-property-hive.com/ai.php', $action ),
            array(
                'timeout' => 60,
                'sslverify' => true,
                'headers' => array(
                    'Content-Type'        => 'application/json',
                    'Accept'       		  => 'application/json',
                    'X-PH-License-Key'    => get_option( 'propertyhive_pro_license_key', '' ),
                    'X-PH-License-Type'   => PH()->license->get_license_type(),
                    'X-PH-Instance-Id'    => get_option( 'propertyhive_pro_instance_id', '' ),
                    'X-PH-Plugin-Version' => PH_VERSION,
                ),
                'body' => wp_json_encode($body),
            )
        );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$raw_body    = wp_remote_retrieve_body( $response );

		if ( $status_code < 200 || $status_code >= 300 ) {
			return new WP_Error(
				'propertyhive_ai_http_error',
				__( 'The AI service returned an unexpected response.', 'propertyhive' ),
				array(
					'status_code' => $status_code,
					'body'        => $raw_body,
				)
			);
		}

		$decoded = json_decode( $raw_body, true );

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'propertyhive_ai_invalid_response',
				__( 'The AI service returned an invalid response.', 'propertyhive' ),
				array(
					'body' => $raw_body,
				)
			);
		}

		if ( isset( $decoded['success'] ) && !$decoded['success'] ) {
			return new WP_Error(
				! empty( $decoded['code'] ) ? sanitize_key( $decoded['code'] ) : 'propertyhive_ai_request_failed',
				! empty( $decoded['message'] ) ? $decoded['message'] : __( 'The AI service could not complete the request.', 'propertyhive' ),
				$decoded
			);
		}

		return $decoded;
	}
}