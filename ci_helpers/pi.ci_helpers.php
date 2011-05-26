<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CI Helpers
 *
 * @package    CIHelpers
 * @author     Focus Lab, LLC <dev@focuslabllc.com>
 * @copyright  Copyright (c) 2011 Focus Lab, LLC
 * @link       https://github.com/erikreagan/ci_helpers.ee_addon
 * @license    MIT  http://opensource.org/licenses/mit-license.php
 */

$plugin_info       = array(
	'pi_name'        => 'CI Helpers',
	'pi_version'     => '0.2',
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
		
		$this->_log_item('Looking for the function "' . $function . '()" in the "' . ucwords($helper) . '" helper');
		
		// TODO create check to see if helper exists
		
		// Load the requested helper file
		// This is sneaky, but if a helper isn't passed, the user can still use certain PHP call back functions...
		// NOTE: change the above fact?
		if($helper)
		{
			$this->_EE->load->helper($helper);
		}
		
		// If the function doesn't exist at this point we should just stop processing things
		if ( ! function_exists($function))
		{
			$this->_log_item('Callback function ' . $function . '() not found.');
			return;
		}
		
		// We made it this far so the function exists. Let's process the rest of the plugin
		$array_string = 'Array mode is ';
		$array_string .= ($this->_do_array_prep) ? 'enabled' : 'disabled' ;
		$this->_log_item($array_string);
		
		// If we have arguments, we need to "prep" them
		if (isset($this->_EE->TMPL->tagparams['argument[0]']))
		{
			// We know we have at least 1 argument.
			$this->_log_item('Arguments have been detected');
			$this->_arguments = $this->_prep_arguments($this->_EE->TMPL->tagparams);
		}
		
		// Run the function and pass the argument array
		$this->return_data = call_user_func_array($function,$this->_arguments);
		
	}
	// End function __construct()
	
	
	
	
	/**
	 * Write items to template debugging log
	 *
	 * @param      string
	 * @param      int
	 * @access     private
	 * @author     Erik Reagan <erik@focuslabllc.com>
	 * @return     void
	 */
	private function _log_item($string = FALSE, $indent = 1)
	{
		// Load the html helper to easily indent/tab our lines
		// This allows for "pretty" formatting in the template debugger
		$this->_EE->load->helper('html');
		$tab = nbs(7 * $indent);
		
		if ($string)
		{
			$this->_EE->TMPL->log_item($tab . ' - ' . $string);
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
		$new_params = array();
		foreach ($params as $key => $value)
		{
			if (strpos($key,'argument[') !== FALSE)
			{
				$this->_log_item($key . " -> " . $value, 2);
				$new_params[] = ($this->_do_array_prep) ? $this->_prep_array($value) : $value ;
			}
		}
		
		return $new_params;
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
				$this->_log_item($key_value[0] . ' -> ' . $key_value[1],3);
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