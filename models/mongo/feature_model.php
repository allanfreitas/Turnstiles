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
 * Feature Mongo Model
 *
 * @subpackage	Models
 */
class Feature_model extends Model
{
    /**
     * The Mongo Collection to use
     */
    private $_collection = 'features';
    
    /**
     * The CI super object
     */
    private $_ci;
    
    /**
     * Construct
     *
     * This loads in the Mongo library
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
     * Adds a feature key into the Mongo Collection
     *
     * @access  public
     * @param   string  $name
     * @param   array   $data
     * @return  int
     */
	public function add($name, $data = array())
	{
	    $feature = array_merge(array('name' => $name), $data);
	    if($this->_ci->mongo_db->where(array('name' => $name))->count($this->_collection) > 0)
	    {
	        return FALSE;
	    }
		return $this->_ci->mongo_db->insert($this->_collection, $feature);
	}

    /**
     * Get
     *
     * Gets a feature from the Mongo Collection, decodes the json and returns it.
     *
     * @access  public
     * @param   string  $name
     * @return  array
     */
	public function get($name)
	{
	    $feature = $this->_ci->mongo_db->where(array('name' => $name))->get($this->_collection);

	    if(!empty($feature))
	    {
    		return $feature[0];
	    }
		return FALSE;
	}
	
    /**
     * Delete
     *
     * Deletes a feature from the Mongo Collection
     *
     * @access  public
     * @param   string  $name
     * @return  int
     */
	public function delete($name)
	{
		return $this->_ci->mongo_db->delete_all($this->_collection, array('name' => $name));
	}
	
}

/* End of file: feature_model.php */