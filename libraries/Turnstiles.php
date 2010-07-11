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
 * This is where the config files are stored
 */
define('TS_CONFIG_PATH', APPPATH . 'config/');

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
     * Holds the turnstiles config
     */
    private static $_config = array();

    /**
     * The CI super object
     */
    private static $_ci;
    
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
        
        if(file_exists(TS_CONFIG_PATH . 'turnstiles.php'))
        {
            include TS_CONFIG_PATH . 'turnstiles.php';
            self::$_config = $turnstiles;
            unset($turnstiles);
        }
        else
        {
            show_error('Main turnstiles config not found at ' . TS_CONFIG_PATH);
        }

        if(self::$_config['use_models'])
        {
            self::$_ci =& get_instance();
            self::$_ci->load->model(self::$_config['feature_model'], 'feature_model');
            self::$_ci->load->model(self::$_config['bucket_model'], 'bucket_model');
        }

        // Make sure we only run init() once
        if($initialized)
        {
            return;
        }

        // If the features config file exists, load it.
        if(!self::$_config['use_models'] AND file_exists(TS_CONFIG_PATH . 'features.php'))
        {
            include TS_CONFIG_PATH . 'features.php';
            self::$_features = array_merge($features, self::$_features);
            unset($features);
        }

        // If the buckets config file exists, load it.
        if(!self::$_config['use_models'] AND file_exists(TS_CONFIG_PATH . 'buckets.php'))
        {
            include TS_CONFIG_PATH . 'buckets.php';
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

        if(!self::$_config['use_models'] AND isset(self::$_features[$feature_name]))
        {
            $feature = self::$_features[$feature_name];
        }
        elseif(self::$_config['use_models'])
        {
            $feature = self::$_ci->feature_model->get($feature_name);
            self::$_features[$feature_name] = $feature;
        }

        if(!$feature)
        {
            show_error('Feature "' . $feature_name . '" does not exist.');
        }
        
        if(!$feature['enable'])
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
        foreach((array) $feature_bucket as $bucket_name)
        {
            if(!self::$_config['use_models'] AND isset(self::$_buckets[$bucket_name]))
            {
                $bucket = self::$_buckets[$bucket_name];
            }
            elseif(self::$_config['use_models'])
            {
                if(self::$_ci->bucket_model->contains($bucket_name, $id))
                {
                    return TRUE;
                }
                else
                {
                    return FALSE;
                }
            }

            if(!$bucket)
            {
                show_error('Bucket "' . $bucket_name . '" does not exist.');
            }
            
            if(is_array($bucket))
            {
                if(in_arrayi($id, $bucket))
                {
                    $found = TRUE;
                    break;
                }
            }
            else
            {
                if(in_str_range($bucket, $id))
                {
                    $found = TRUE;
                    break;
                }
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
        self::init();

        if(self::$_config['use_models'])
        {
            show_error('You cannot use the add_feature() function while using Models');
        }
        else
        {
            self::$_features[$name] = $feature;
        }
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
        self::init();

        if(self::$_config['use_models'])
        {
            show_error('You cannot use the add_bucket() function while using Models');
        }
        else
        {
            self::$_buckets[$name] = $bucket;
        }
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

/**
 * in_str_range
 *
 * Checks if the needle is in a given range in string format (i.e. '1-100')
 *
 * @param   mixed   $needle
 * @param   array   $haystack
 * @return  bool
 */
function in_str_range($needle, $haystack)
{
    $dash_pos = strpos($haystack, '-');

    if($dash_pos === FALSE)
    {
        if($haystack == $needle)
        {
            return TRUE;
        }
    }
    else
    {
        $start = substr($haystack, 0, $dash_pos);
        $end = substr($haystack, $dash_pos + 1);
        if($needle >= $start AND $needle <= $end)
        {
            return TRUE;
        }
    }
    
    return FALSE;
}

/* End of file: Turnstiles.php */