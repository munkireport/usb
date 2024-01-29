<?php

return [
  /*
  |===============================================
  | usb
  |===============================================
  |
  | By default, usb does not keep historical
  | devices that have been connected in the past.
  | To enable, set `usb_historical` to
  | true.
  |
  */

  'usb_historical' => env('USB_HISTORICAL', false),
  'usb_internal' => env('USB_INTERNAL', true),
];
