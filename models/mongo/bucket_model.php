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
 * Bucket Mongo Model
 *
 * @subpackage	Models
 */
class Bucket_model extends Model
{
    /**
     * The Mongo Collection to use
     */
    private $_collection = 'buckets';

    /**
     * The CI super object
     */
    private $_ci;
    
    /**
     * Construct
     *
     * Loads in the Mongo library
     *
     * @access  public
     * @return  void
     */
	public function __construct()
	{
		parent::Model();
		
		$this->_ci =& get_instance();
		
		if(!class_exists('Mongo_db'))
		{
		    $this->_ci->load->library('mongo_db');
	    }
	}

    /**
     * Add
     *
     * Adds a bucket set into the Mongo Collection
     *
     * @access  public
     * @param   string  $name
     * @param   array   $data
     * @return  void
     */
	public function add($name, $data = array())
	{
	    $bucket = array(
	        'name'      => $name,
	        'members'   => $data,
	        'range'     => (!is_array($data))
	    );
	    if($this->_ci->mongo_db->where(array('name' => $name))->count($this->_collection) > 0)
	    {
	        return FALSE;
	    }
		return $this->_ci->mongo_db->insert($this->_collection, $bucket);
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
	    $feature = $this->_ci->mongo_db->where(array('name' => $name))->get($this->_collection);

	    if(!empty($bucket))
	    {
    		return $bucket[0];
	    }
		return FALSE;
	}

    /**
     * Delete
     *
     * Deletes either the entire bucket or a member from the bucket.
     *
     * @access  public
     * @param   string  $name
     * @return  int
     */
	public function delete($name)
	{
		return $this->_ci->mongo_db->delete_all($this->_collection, array('name' => $name));
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
	    // This checks to see if 'members' is a range or an array
	    if($this->_ci->mongo_db->where(array('name' => $name, 'range' => 0))->count($this->_collection) == 0)
	    {
	        // Its a range so lets see if its in it.
            $range = $this->_ci->mongo_db->where(array('name' => $name))->get($this->_collection);
            
            if(empty($range))
            {
                show_error($name . ' is not a valid bucket');
            }
	        return in_str_range($member, $range[0]['members']);
	    }
	    else
	    {
	        $bucket = $this->_ci->mongo_db->where(array('name' => $name))->where_in('members', array($member))->count($this->_collection);
	        return ($bucket == 0) ? FALSE : TRUE;
	    }
        
	}
}

/* End of file: bucket_model.php */