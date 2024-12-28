<?php
/*
Plugin Name: Forminator File Upload
Description: Upload Forminator form files to an S3 bucket and include file links in email notifications.
Version: 1.0
Author: Dillon Lad
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include AWS SDK
require_once '/opt/bitnami/wordpress/aws/vendor/autoload.php'; // Adjust if you have the SDK elsewhere

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Forminator_API;

/**
 * Upload Forminator files to S3 and include links in emails
 */
class Forminator_S3_Upload {
    private $s3_client;
    private $bucket_name = AWS_S3_BUCKET;
    private $region = AWS_S3_REGION;
    private $s3_base_url;
    private $uploaded_files = [];

    public function __construct() {
        $this->s3_base_url = "https://{$this->bucket_name}.s3.{$this->region}.amazonaws.com/";

        $this->initialize_s3_client();
        add_action('forminator_custom_form_submit_before_set_fields', [$this, 'handle_file_upload'], 10, 3);
        add_filter('forminator_custom_form_email_data', [$this, 'modify_email_content']);
    }

    /**
     * Initialize the S3 Client
     */
    private function initialize_s3_client() {
        $this->s3_client = new S3Client([
            'region' => $this->region,
            'version' => 'latest',
            'credentials' => [
                'key' => AWS_ACCESS_KEY,
                'secret' => AWS_SECRET_KEY,
            ],
        ]);
    }

    /**
     * Handle File Upload to S3
     */
    public function handle_file_upload($entry, $form_id, $field_data_array) {
        $this->uploaded_files = [];
        
        $form_name = 'Other';
        $form = Forminator_API::get_form( $form_id );
        if ($form && isset($form->name)) {
            $form_name = $form->name;
        }


        foreach ($field_data_array as $field_data) {
            $keys = array_keys($field_data);
            if (isset($field_data['field_type']) && $field_data['field_type'] === 'upload') {
                $keys2 = array_keys($field_data['value']);
                $keys3 = array_keys($field_data['value']['file']);
                $file_path = $field_data['value']['file']['file_path'];
                $file_name = basename($file_path);

                try {
                    $result = $this->s3_client->putObject([
                        'Bucket' => $this->bucket_name,
                        'Key' => "uploads/{$form_name}/{$file_name}",
                        'SourceFile' => $file_path,
                    ]);

                    $this->uploaded_files[] = $this->s3_base_url . "uploads/{$file_name}";

                    // Delete local file after upload
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                } catch (AwsException $e) {
                    error_log('S3 Upload Error: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Modify Email Content with S3 File Links
     */
    public function modify_email_content($email_data) {
        error_log('RUNNING?? ' . $email_data['message']);
        if (!empty($this->uploaded_files)) {
            $email_data['message'] .= "\nUploaded Files:\n" . implode("\n", $this->uploaded_files);
        }
        return $email_data;
    }
}

// Initialize the Plugin
new Forminator_S3_Upload();
