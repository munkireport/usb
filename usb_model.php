<?php

use CFPropertyList\CFPropertyList;

class Usb_model extends \Model {

    function __construct($serial='')
    {
        parent::__construct('id', 'usb'); // Primary key, tablename
        $this->rs['id'] = '';
        $this->rs['serial_number'] = $serial;
        $this->rs['name'] = '';
        $this->rs['type'] = ''; // Mouse, Trackpad, Hub, etc.
        $this->rs['manufacturer'] = '';
        $this->rs['vendor_id'] = '';
        $this->rs['device_speed'] = ''; // USB Speed
        $this->rs['internal'] = 0; // True or False
        $this->rs['media'] = 0; // True or False
        $this->rs['bus_power'] = 0;
        $this->rs['bus_power_used'] = 0;
        $this->rs['extra_current_used'] = 0;
        $this->rs['usb_serial_number'] = ''; // USB device serial number
        $this->rs['printer_id'] = ''; // 1284 Device ID information, only used by printers
        $this->rs['device_speed_bps'] = null; 
        $this->rs['timestamp'] = null; // Unix time when the report was uploaded
        $this->rs['connected'] = 0; // True or False of if device is currently connected

        // Add local config
        configAppendFile(__DIR__ . '/config.php');

        $this->serial_number = $serial;
    }

    // ------------------------------------------------------------------------
    /**
     * Process data sent by postflight
     *
     * @param string data
     * @author miqviq, revamped by tuxudo
     **/
    function process($plist)
    {
        // Check if we have data
        if ( ! $plist){
            throw new Exception("Error Processing Request: No property list found", 1);
        }

        // If we didn't specify in the config that we like history then
        // we nuke any data we had with this computer's serial number
        if (! conf('usb_historical')) {
            $this->deleteWhere('serial_number=?', $this->serial_number);
        } else {

            // Set all USB devices to "0" for not connected, but only if we're keeping historical devices
            $sql = "UPDATE `usb` 
                    SET `connected` = '0'
                    WHERE `serial_number` = '$this->serial_number' AND `name` <> 'T2Bus' AND `name` <> 'Internal Memory Card Reader'";
                    // Never set T2Bus and Internal Memory Card Reader as not connected
            $this->query($sql);
        }

        // Delete internal devices if we don't want to keep those
        if (! conf('usb_internal')) {
            $this->deleteWhere('serial_number=? AND internal=?', array($this->serial_number, "1"));
        }

        // Timestamp added by the server
        $this->timestamp = time();

        $parser = new CFPropertyList();
        $parser->parse($plist, CFPropertyList::FORMAT_XML);
        $myList = $parser->toArray();

        // Process each device
        foreach ($myList as $device) {
            // Check if we have a name
            if( ! array_key_exists("name", $device)){
                continue;
            }

            // Skip Bus types USB31Bus, USB11Bus, etc.
            if(preg_match('/^USB(\d+)?Bus$/', $device['name']))
            {
                continue;
            }

            // Check for USB bus devices and simulated USB devices to exclude
            $excludeusb = array("OHCI Root Hub Simulation","UHCI Root Hub Simulation","EHCI Root Hub Simulation","RHCI Root Hub Simulation","XHCI Root Hub Simulation","XHCI Root Hub SS Simulation","XHCI Root Hub USB 2.0 Simulation");
            if (in_array($device['name'], $excludeusb)) {
                continue;
            }

            // Skip internal devices if value is FALSE
            if (! conf('usb_internal')) {
                if ($device['internal']){
                    continue;
                }
            }

            // Adjust names
            $device['name'] = str_replace(array('bluetooth_device','hub_device','composite_device'), array('Bluetooth USB Host Controller','USB Hub','Composite Device'), $device['name']);

            // Override Internal T/F based on name
            if (stripos($device['name'], 'Internal') !== false || stripos($device['name'], 'Built-in') !== false || stripos($device['name'], 'T2Bus') !== false || stripos($device['name'], 'IR Receiver') !== false || stripos($device['name'], 'Apple T2 Controller') !== false || stripos($device['name'], 'Ambient Light Sensor') !== false || stripos($device['name'], 'Touch Bar Display') !== false || stripos($device['name'], 'Touch Bar Backlight') !== false || stripos($device['name'], 'BRCM20702 Hub') !== false || stripos($device['name'], 'BRCM2046 Hub') !== false || stripos($device['name'], 'BRCM2046 Hub') !== false || stripos($device['name'], 'Apple T1 Controller') !== false || (stripos($device['name'], 'Bluetooth Controller') !== false && $device['manufacturer'] = "Apple, Inc.") || (stripos($device['name'], 'Bluetooth Controller') !== false && stripos($device['vendor_id'], 'Broadcom') !== false) || (stripos($device['name'], 'Bluetooth USB Host Controller') !== false && stripos($device['vendor_id'], 'Apple') !== false)) {

                $device['internal'] = 1;

                // Skip internal devices if value is FALSE
                if (! conf('usb_internal')) {
                    continue;
                }
            }

            // Adjust USB speeds
            if (array_key_exists("device_speed",$device)) {
                $device['device_speed'] = str_replace(array('low_speed','full_speed','high_speed','super_speed_plus_by_2','super_speed_plus','super_speed'), array('USB 1.0','USB 1.1','USB 2.0','USB 3.x','USB 3.x','USB 3.x'), $device['device_speed']);
            } else {
                $device['device_speed'] = 'USB 1.1';
            }

            // Make sure manufacturer is set
            $device['manufacturer'] = isset($device['manufacturer']) ? $device['manufacturer'] : '';

            // Make sure printer_id is set
            $device['printer_id'] = isset($device['printer_id']) ? $device['printer_id'] : '';

             // Map name to device type
            $device_types = array(
                'AV Adapter' => 'usb-c digital av multiport adapter|usb type-c digital av adapter|video adaptor',
                'Camera' => 'isight|camera|video|facetime|webcam|cybertrack|brio|meeting owl|logitech brio',
                'USB Hub' => 'hub',
                'Keyboard' => 'keyboard|keykoard|usb kb',
                'IR Receiver' => 'ir receiver',
                'Bluetooth Controller' => 'bluetooth|bt-68 dongle|bt-88 dongle',
                'iPhone' => 'iphone',
                'iPad' => 'ipad',
                'iPod' => 'ipod',
                'Mouse' => 'mouse|ps2 orbit|trackpad',
                'Mass Storage' => 'card reader|os x install disk|superdrive|ultra fast media reader|usb to serial-ata bridge|superdrive|mass storage|superdrive',
                'Audio Device' => 'audio|sound|headset|microphone|akm|apc mini|yealink|at2020usb|cmteck',
                'Display' => 'displaylink|display|monitor|touchscreen|billboard',
                'Composite Device' => 'composite device',
                'Network' => 'network|ethernet|modem|bcm|lan',
                'UPS' => 'ups',
                'iBridge' => 'ibridge|apple t2 controller|t2bus|apple t1 controller|touch bar|touchbar',
                'Scanner' => 'scanner',
                'Wacom Tablet' => 'wacom|ptz-|intuos|ctl-',
                'Interactive Board' => 'smartboard|activboard',
                'Wireless Mouse Keyboard' => 'usb receiver|wireless receiver|wireless desktop receiver|dell universal receiver|2.4g receiver|nano transceiver',
                'Ambient Light Sensor' => 'ambient light sensor',
                'AirPod Case' => 'airpod',
                'Apple Watch' => 'apple watch'
            );

            // Set device type to be default of unknown
            $device['type'] = "unknown";

            // Set new device type based on device name
            $device_name = strtolower($device['name']);
            foreach($device_types as $type => $pattern){
                if (preg_match('/'.$pattern.'/', $device_name)){
                    $device['type'] = $type;
                    break;
                }
            }

            // Set that the device is currently connected
            $device['connected'] = 1;

            // Set device types based on other criteria
            if (stripos($device['manufacturer'], 'DisplayLink') !== false) {
                $device['type'] = 'Display'; // Set by manufacturer instead of name
            } else if ($device['printer_id'] !== '') {
                $device['type'] = 'Printer'; // Set type to printer if printer_id field is not blank
            }

            // Check for Mass Storage
            if ($device['media'] == 1 ) {
                $device['type'] = 'Mass Storage';
            }

            // Adjust Apple vendor ID
            if (array_key_exists('vendor_id',$device)) {
                if ($device['vendor_id'] == 'apple_vendor_id') {
                    $device['vendor_id'] = '0x05ac (Apple, Inc.)';
                }

                // Set manufacturer from vendor ID if it's blank
                if ($device['manufacturer'] == '' && $device['vendor_id'] != '') {
                    preg_match('/\((.*?)\)/s', $device['vendor_id'], $manufactureroutput);
                    $device['manufacturer'] = $manufactureroutput[1];
                }
            }

            // T2Bus is 0x05ac (Apple, Inc.)
            if ($device['name'] == "T2Bus"){
                $device['vendor_id'] = '0x05ac (Apple, Inc.)';
                $device['manufacturer'] = 'Apple Inc.';
            }

            // Process each key
            foreach ($this->rs as $key => $value) {
                $this->rs[$key] = $value;
                if(array_key_exists($key, $device))
                {
                    $this->rs[$key] = $device[$key];
                } else if ($key !== "serial_number" && $key !== "id" && $key !== "timestamp"){
                    $this->rs[$key] = null;
                }
            }

            // If we are to not keep historical data, do a selective delete
            if (conf('usb_historical')) {
                // Selectively delete display by matching different aspects of the USB device. Do NOT use USB device serial number
                $this->deleteWhere('serial_number=? AND name=? AND manufacturer=? AND vendor_id=? AND device_speed=? AND media=?', array($this->serial_number, $this->name, $this->manufacturer, $this->vendor_id, $this->device_speed, $this->media));

                // T2Bus is needs extra cleaning
                if ($device['name'] == "T2Bus"){
                    $this->deleteWhere('serial_number=? AND name=?', array($this->serial_number, $this->name));
                }
            }

            // Save device
            $this->id = '';
            $this->save();
        }
    }
}
