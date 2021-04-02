USB module
==============

Provides the status of USB devices.

Data can be viewed under the USB Devices tab on the client details page or using the USB Devices list view 

Thanks to MiqViq for starting work on this module and bochoven for rewriting the USB script in Python

Configuration
-------------

By default the USB module will collect information on all USB devices.
Setting `USB_INTERNAL` to `FALSE` will skip all internal devices.
```
USB_INTERNAL=TRUE
```

Table Schema
-----
* name - varchar(255) - name of the USB device
* type - varchar(255) - type of device, manually set via model
* manufacturer - varchar(255) - reported maker of device
* vendor_id - varchar(255) - device's vendor ID
* device_speed - varchar(255) - USB bus speed
* internal - int - 0/1 for internal USB device
* media - int - 0/1 for removable media device
* bus_power - int - available bus power
* bus_power_used - int - bus power in use
* extra_current_used - int - extra current being provided
* usb_serial_number - varchar(255) - the serial number of the USB device
* printer_id - TEXT - information about the connected USB printer
