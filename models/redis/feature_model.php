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
 * This is an ugly hack for compatibility with both CI 1.7.2 and CI 2.0
 */
if(!class_exists('Model'))
{
    class Model extends CI_Model { }
}

/**
 * Feature Redis Model
 *
 * @subpackage	Models
 */
class Feature_model extends Model
{
    /**
     * The Redis DB index to use
     */
    private $_redis_db = 0;
    
    /**
     * The CI super object
     */
    private $_ci;
    
    /**
     * Construct
     *
     * This loads in the Redis library
     *
     * @access  public
     * @return  void
     */
	public function __construct()
	{
		parent::Model();
		
		$this->_ci =& get_instance();
		
		if(!class_exists('Redis'))
		{
		    $this->_ci->load->library('redis');
	    }
	    $this->_ci->redis->select_db($this->_redis_db);
	}

    /**
     * Add
     *
     * Adds a feature key into the Redis DB
     *
     * @access  public
     * @param   string  $name
     * @param   array   $data
     * @return  int
     */
	public function add($name, $data = array())
	{
		return $this->_ci->redis->set('features:' . $name, json_encode($data));
	}

    /**
     * Get
     *
     * Gets a feature from the Redis DB, decodes the json and returns it.
     *
     * @access  public
     * @param   string  $name
     * @return  array
     */
	public function get($name)
	{
		return json_decode($this->_ci->redis->get('features:' . $name), TRUE);
	}

    /**
     * Delete
     *
     * Deletes a feature from the Redis DB
     *
     * @access  public
     * @param   string  $name
     * @return  int
     */
	public function delete($name)
	{
		return $this->_ci->redis->delete('features:' . $name);
	}

}

/* End of file: feature_model.php */