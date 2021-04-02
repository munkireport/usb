<?php 

/**
 * USB status module class
 *
 * @package munkireport
 * @author miqviq
 **/
class Usb_controller extends Module_controller
{

	/*** Protect methods with auth! ****/
	function __construct()
	{
		// Store module path
		$this->module_path = dirname(__FILE__);
	}

	/**
	 * Default method
	 * @author miqviq
	 *
	 **/
	function index()
	{
		echo "You've loaded the usb module!";
	}

	/**
     * Get USB device names for widget
     *
     * @return void
     * @author tuxudo
     **/
     public function get_usb_devices()
     {
        $sql = "SELECT COUNT(CASE WHEN name <> '' AND name IS NOT NULL THEN 1 END) AS count, name 
                FROM usb
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                GROUP BY name
                ORDER BY count DESC";

        $out = array();
        $queryobj = new Usb_model;
        foreach ($queryobj->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->name = $obj->name ? $obj->name : 'Unknown';
                $out[] = $obj;
            }
        }

        jsonView($out);
     }

     /**
     * Get USB device types for widget
     *
     * @return void
     * @author tuxudo
     **/
     public function get_usb_types()
     {
        $sql = "SELECT COUNT(CASE WHEN type <> '' AND type IS NOT NULL THEN 1 END) AS count, type 
                FROM usb
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                GROUP BY type
                ORDER BY count DESC";

        $out = array();
        $queryobj = new Usb_model;
        foreach ($queryobj->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->type = $obj->type ? $obj->type : 'Unknown';
                $out[] = $obj;
            }
        }

        jsonView($out);
     }
    
	/**
     * Retrieve data in json format
     *
     **/
    public function get_data($serial_number = '')
    {
        // Remove non-serial number characters
        $serial_number = preg_replace("/[^A-Za-z0-9_\-]]/", '', $serial_number);

        $sql = "SELECT name, type, manufacturer, vendor_id, device_speed, internal, media, bus_power, bus_power_used, extra_current_used, usb_serial_number
                        FROM usb 
                        WHERE serial_number = '$serial_number'";
        
        $queryobj = new Usb_model;
        jsonView($queryobj->query($sql));
    }
		
} // END class Usb_controller
