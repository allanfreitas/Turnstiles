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
 * Feature DB Model
 *
 * @subpackage	Models
 */
class Feature_model extends Model
{
    /**
     * The name of the bucket table
     */
    private $_table = 'features';
    
    /**
     * Add
     *
     * Adds a feature to the DB
     *
     * @access  public
     * @param   string  $name
     * @param   array   $data
     * @return  int
     */
	public function add($name, $data = array())
	{
	    $feature = array_merge(array('name' => $name), $data);
	    $feature['bucket'] = json_encode($feature['bucket']);
	    
		return $this->db->insert($this->_table, $feature);
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
	    $feature = $this->db->get_where($this->_table, array('name' => $name));
	    
	    if($feature->num_rows() > 0)
        {
            $feature = $feature->row_array();
            $feature['bucket'] = json_decode($feature['bucket'], TRUE);
            return $feature;
        }
        else
        {
            return FALSE;
        }
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
		return $this->db->delete($this->_delete, array('name' => $name));
	}

}

/* End of file: feature_model.php */