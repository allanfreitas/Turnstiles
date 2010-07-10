<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Specify whether to use models or not.
 * If set to TRUE, it will use the models only (ignoring configs).
 * If set to FALSE, it will use the config files only.
 */
$turnstiles['use_models'] = FALSE;

/**
 * Set the model names for features and buckets
 */
$turnstiles['feature_model'] = 'redis/feature_model';
$turnstiles['bucket_model'] = 'redis/bucket_model';

/* End of file turnstiles.php */
