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
    private static $_features = array();
    
    /**
     * Holds the buckets from the config
     */
    private static $_buckets = array();
    
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
        
        // Make sure we only run init() once
        if($initialized)
        {
            return;
        }

        // If the features config file exists, load it.
        if(file_exists(APPPATH . 'config/features.php'))
        {
            include APPPATH . 'config/features.php';
            self::$_features = array_merge($features, self::$_features);
            unset($features);
        }

        // If the buckets config file exists, load it.
        if(file_exists(APPPATH . 'config/buckets.php'))
        {
            include APPPATH . 'config/buckets.php';
            self::$_buckets = array_merge($buckets, self::$_buckets);
            unset($buckets);
        }

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

        isset(self::$_features[$feature_name]) OR show_error('Feature "' . $feature_name . '" does not exist.');
        
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

    /**
     * Add Feature
     *
     * Adds a feature into the in-memory config
     *
     * @access  public
     * @param   string  $name
     * @param   array   $feature
     * @return  void
     */
    public static function add_feature($name, $feature)
    {
        isset(self::$_features[$name]) AND show_error('Feature "' . $name . '" already exists.');
        
        if(!isset($feature['enable']) OR !isset($feature['bucket']))
        {
            show_error('Invalid feature format for "' . $name . '" in add_feature()');
        }
        
        self::$_features[$name] = $feature;
    }

    /**
     * Add Bucket
     *
     * Adds a bucket into the in-memory config
     *
     * @access  public
     * @param   string  $name
     * @param   array   $bucket
     * @return  void
     */
    public static function add_bucket($name, $bucket)
    {
        (isset(self::$_buckets[$name]) OR $name == '_all_') AND show_error('Bucket "' . $name . '" already exists.');
        
        self::$_buckets[$name] = $bucket;
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