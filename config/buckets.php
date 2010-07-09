<?php  defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bucket Config
 *
 * Each $bucket item can be ant of the following:
 * - An array of ints
 * - An array of strings
 * - An int
 * - A string
 * - A range in the form of a string (i.e. '1-100')
 *
 * Examples:
 * 
 * $buckets['group1'] = array(23, 5, 12);
 * $buckets['group2'] = array('user1', 'user2', 'user3');
 * $buckets['group3'] = 45;
 * $buckets['group4'] = 'user1';
 * $buckets['group5'] = '1-100';
 */

$buckets['group1'] = array(23, 5, 12, 78, 45,10);

/* End of file buckets.php */
