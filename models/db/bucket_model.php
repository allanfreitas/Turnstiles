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
 * Bucket DB Model
 *
 * @subpackage	Models
 */
class Bucket_model extends Model
{
    /**
     * The name of the bucket table
     */
    private $_table = 'buckets';
    
    /**
     * Add
     *
     * Adds a bucket to the DB
     *
     * @access  public
     * @param   string  $name
     * @param   array   $data
     * @return  void
     */
	public function add($name, $data = array())
	{
	    $bucket = array('name' => $name, 'members' => json_encode($data));

		return $this->db->insert($this->_table, $bucket);
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
	    $bucket = $this->db->get_where($this->_table, array('name' => $name));
	    
	    if($bucket->num_rows() > 0)
        {
            $bucket = $bucket->row_array();
            return json_decode($bucket['members'], TRUE);
        }
        else
        {
            return FALSE;
        }
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
	public function delete($name)
	{
		return $this->db->delete($this->_table, array('name' => $name));
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
	    $members = $this->get($name);
	    if(is_array($members))
	    {
		    return in_arrayi($member, $members);
	    }
	    else
	    {
	        return in_str_range($member, $members);
	    }
	}
}

/* End of file: bucket_model.php */