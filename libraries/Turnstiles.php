<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Turnstiles
 *
 * Allows you deploy features based on user buckets.
 *
 * @package     Turnstiles
 * @author      Dan Horrigan <http://dhorrigan.com>
 * @license     Apache License v2.0
 */

/**
 * Turnstiles
 *
 * @subpackage  Turnstiles
 */
class Turnstiles
{
    /**
     * Holds the features from the config
     */
    private static $_features;
    
    /**
     * Holds the buckets from the config
     */
    private static $_buckets;
    
    /**
     * Init
     *
     * Checks to make sure the config files exist and loads them in.
     *
     * @access  public
     * @return  void
     */
    public static function init()
    {
        static $initialized = FALSE;
        
        if($initialized)
        {
            return;
        }
        // Check if the 2 config files exist
        file_exists(APPPATH . 'config/features.php') OR show_error('Features config file is missing.');
        file_exists(APPPATH . 'config/buckets.php') OR show_error('Buckets config file is missing.');
        
        // Include the config files
        include APPPATH . 'config/features.php';
        include APPPATH . 'config/buckets.php';
        
        self::$_features = $features;
        self::$_buckets = $buckets;
        
        // Unset the config arrays to free up the memory
        unset($features, $buckets);

        $initialized = TRUE;
    }

    /**
     * Restrict
     *
     * Checks to see if the given $id is in the feature's bucket(s).
     *
     * @access  public
     * @param   string  $feature_name
     * @param   mixed   $id
     * @return  bool
     */
    public static function restrict($feature_name, $id)
    {
        // Initialize the library
        self::init();
        
        isset(self::$_features[$feature_name]) OR show_error(sprintf('Feature "%s" does not exist in the config.', $feature_name));
        
        $feature = self::$_features[$feature_name];
        if($feature['enable'] === FALSE)
        {
            return FALSE;
        }
        
        // It is enabled for ALL users
        if($feature['bucket'] == '_all_')
        {
            return TRUE;
        }
        
        $feature_bucket = $feature['bucket'];
        $found = FALSE;
        
        // Doing a foreach allows for multiple buckets per feature
        foreach((array) $feature_bucket as $bucket)
        {
            if(isset(self::$_buckets[$bucket]))
            {
                if(is_array(self::$_buckets[$bucket]))
                {
                    if(in_arrayi($id, self::$_buckets[$bucket]))
                    {
                        $found = TRUE;
                        break;
                    }
                }
                else
                {
                    $dash_pos = strpos(self::$_buckets[$bucket], '-');

                    // The bucket is not a range
                    if($dash_pos === FALSE)
                    {
                        if(self::$_buckets[$bucket] == $id)
                        {
                            $found = TRUE;
                            break;
                        }
                    }
                    // The bucket is range
                    else
                    {
                        $start = substr(self::$_buckets[$bucket], 0, $dash_pos);
                        $end = substr(self::$_buckets[$bucket], $dash_pos + 1);
                        if($id >= $start AND $id <= $end)
                        {
                            $found = TRUE;
                            break;
                        }
                    }
                }
            }
            else
            {
                show_error(sprintf('Bucket "%s" does not exist in the config.', $bucket));
            }
        }
        return $found;
    }
    
}

/**
 * in_arrayi
 *
 * A case-insensitive version of in_array.
 *
 * @param   mixed   $needle
 * @param   array   $haystack
 * @return  bool
 */
function in_arrayi($needle, $haystack)
{
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

/* End of file: Feature.php */