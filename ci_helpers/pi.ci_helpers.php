<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CI Helpers
 *
 * @package    CIHelpers
 * @author     Focus Lab, LLC <dev@focuslabllc.com>
 * @copyright  Copyright (c) 2011 Focus Lab, LLC
 * @link       https://github.com/focuslabllc/deployment_hooks.ee2_addon
 * @license    MIT  http://opensource.org/licenses/mit-license.php
 */

$plugin_info       = array(
	'pi_name'        => 'CI Helpers',
	'pi_version'     => '0.1',
	'pi_author'      => 'Erik Reagan',
	'pi_author_url'  => 'http://focuslabllc.com',
	'pi_description' => 'Gain access to CI\'s helpers and functions within your EE templates',
	'pi_usage'       => 'Later.'
);

class Ci_helpers {
	
	/**
	 * @var  string
	 */
	public $return_data  = "";
	
	
	/**
	 * @var  object  EE "superobject"
	 */
	private $_EE;
	
	
	/**
	 * @var  array  Arguments parsed for CI Helper use
	 */
	private $_arguments;
	
	
	/**
	 * @var  mixed  Should strings be treated as arrays?
	 */
	private $_do_array_prep;
	
	
	/**
	 * @var  string  array value delimiter for explode() method
	 */
	private $_delimiter;
	
	
	
	
	/**
	 * PHP4-style constructor
	 *
	 * This is due to an EE bug
	 * @link       http://expressionengine.com/bug_tracker/bug/15115/
	 * @access     public
	 * @author     Erik Reagan <erik@focuslabllc.com>
	 */
	public function Ci_helpers()
	{
		$this->__construct();
	}
	
	
	
	
	/**
	 * Plugin constructor
	 * 
	 * The meat of the add-on.
	 * Find the helper + function, prep data, execute function
	 * 
	 * @access     public
	 * @author     Erik Reagan <erik@focuslabllc.com>
	 */
	public function __construct()
	{
		// Get EE instance & setup our _arguments array empty
		$this->_EE =& get_instance();
		$this->_arguments = array();
		
		// Get our plugin parameters
		$helper = $this->_EE->TMPL->fetch_param('helper');
		$function = $this->_EE->TMPL->fetch_param('function');
		$this->_do_array_prep = $this->_EE->TMPL->fetch_param('array');
		$this->_delimiter = $this->_EE->TMPL->fetch_param('delimiter') ? $this->_EE->TMPL->fetch_param('delimiter') : '|' ;
		
		
		// If we have arcuments, we need to "prep" them
		if (isset($this->_EE->TMPL->tagparams['argument[0]']))
		{
			// We know we have at least 1 argument.
			$this->_arguments = $this->_prep_arguments($this->_EE->TMPL->tagparams);
		}
		
		// TODO create check to see if helper exists
		// TODO create check to see if function exists
		
		// Load the requested helper file
		$this->_EE->load->helper($helper);
		
		// Run the function and pass the argument(s) (if any were included)
		// Some CI Helper functions don't need any arguments
		if (count($this->_arguments) > 0)
		{
			$arg = $this->_arguments;
			$this->return_data =  $function($arg[0],$arg[1],$arg[2],$arg[3],$arg[4],$arg[5],$arg[6],$arg[7],$arg[8],$arg[9]);
		} else {
			$this->return_data =  $function();
		}
		
	}
	// End function __construct()
	
	
	
	
	/**
	 * Write items to template debugging log
	 *
	 * @param      string
	 * @access     private
	 * @author     Erik Reagan <erik@focuslabllc.com>
	 * @return     void
	 */
	private function _log_item($string = FALSE)
	{
		if ($string)
		{
			$this->_EE->TMPL->log_item('-> CI Helpers -> ' . $string);
		}
	}
	// End function _log_item()
	
	
	
	
	/**
	 * Prep arguments for passing
	 *
	 * @param      array
	 * @access     private
	 * @author     Erik Reagan <erik@focuslabllc.com>
	 * @return     array
	 */
	private function _prep_arguments($params)
	{
		// We limit the arguments to 10
		$limit = 10;
		$count = 0;
		foreach ($params as $key => $value)
		{
			if (strpos($key,'argument[') !== FALSE)
			{
				// Limit to x number of arguments
				if ($count < $limit)
				{
					$params[$count] = ($this->_do_array_prep) ? $this->_prep_array($value) : $value ;
				}
				$count++;
			}
			unset($params[$key]);
		}
		
		// How many arguments do we actually have?
		$items = count($params);
		
		// If there are less than $limit arguments assign array keys to a NULL value
		if ($items < $limit)
		{
			for ($i = $items; $i < 10; $i++) { 
				$params[$i] = NULL;
			}
		}
		
		return $params;
	}
	// End function _prep_arguments()
	
	
	
	
	/**
	 * Prep array value
	 * 
	 * This is used to convert strings into arrays
	 * It may or may not include key/value pairs
	 * 
	 * @param      string
	 * @access     private
	 * @author     Erik Reagan <erik@focuslabllc.com>
	 * @return     mixed
	 */
	private function _prep_array($value)
	{
		
		// If the delimiter isn't present, don't continue
		if (strpos($value,$this->_delimiter) === FALSE)
		{
			return $value;
		}
		
		// Break the string up into an array with a pipe delimiter
		$argument_array = explode($this->_delimiter, $value);
		foreach ($argument_array as $key => $value) {
			// If the value has the "=>" string present then it's assigning the key and value
			if (strpos($value,'=>') !== FALSE) {
				$key_value = explode('=>',$value);
				$argument_array[$key_value[0]] = $key_value[1];
				// Unset the original key
				unset($argument_array[$key]);
			}
		}
		return $argument_array;
	}
	// End function _prep_array()
		
}
// End class Ci_helpers

/* End of file pi.ci_helpers.php */
/* Location: ./system/expressionengine/third_party/ci_helpers/pi.ci_helpers.php */