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
 * Bucket Redis Model
 *
 * @subpackage	Models
 */
class Bucket_model extends Model
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
     * Loads in the Redis library
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
     * Adds a bucket set into the Redis DB
     *
     * @access  public
     * @param   string  $name
     * @param   array   $data
     * @return  void
     */
	public function add($name, $data = array())
	{
	    foreach($data as $member)
	    {
	        $this->_ci->redis->sadd('buckets:' . $name, $member);
	    }
	}

    /**
     * Get
     *
     * Returns all members of the bucket
     *
     * @access  public
     * @param   string  $name
     * @return  array
     */
	public function get($name)
	{
		return $this->_ci->redis->smembers('buckets:' . $name);
	}

    /**
     * Delete
     *
     * Deletes either the entire bucket or a member from the bucket.
     *
     * @access  public
     * @param   string  $name
     * @param   mixed   $member
     * @return  int
     */
	public function delete($name, $member = NULL)
	{
	    if($member !== NULL)
	    {
	        return $this->_ci->redis->srem('buckets:' . $name, $member);
	    }
		return $this->_ci->redis->delete('buckets:' . $name);
	}

    /**
     * Contains
     *
     * Checks to see if a given member is in the bucket.
     *
     * @access  public
     * @param   string  $name
     * @param   mixed   $member
     * @return  bool
     */
	public function contains($name, $member)
	{
	    $count = $this->_ci->redis->scard('buckets:' . $name);
	    if($count > 1)
	    {
		    return $this->_ci->redis->sismember('buckets:' . $name, $member);
	    }
	    else
	    {
	        $members = $this->get('buckets:' . $name);
	        $bucket = $members[0];
	        return in_str_range($member, $bucket);
	    }
	}
}

/* End of file: bucket_model.php */