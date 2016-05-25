PayFast EventEspresso Payment Module v1.2.0 for EventEspresso v3.1.29 and higher
Latest Test with EE v3.1.36.6.P
--------------------------------------------------------------
Copyright � 2012-2016 PayFast (Pty) Ltd

LICENSE:

This payment module is free software; you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published
by the Free Software Foundation; either version 3 of the License, or (at
your option) any later version.

This payment module is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
License for more details.

Please see http://www.opensource.org/licenses/ for a copy of the GNU Lesser
General Public License.

INTEGRATION:
1. Make sure you have a working WordPress and EventEspresso install
2. Download the Payfast Module from our Shopping Carts directory
3. Unzip the file
4. Using FTP, copy the unzipped files into ‘[your root]/wp-content/uploads/espresso/gateways/payfast’. The payfast directory should be spelled with a lower case ‘p’, and not capitalized. Make sure that this is the base folder for the payfast modules, you should not have a subdirectory inside this directory.
5. EventEspresso automatically reads the gateway directory, so it should pick up the new payfast directory
6. Go into your WordPress admin directory, select EventEspresso in the left hand menu and then select Payment Settings
7. Here you will see a list of all the installed payment gateways. Select “Payfast settings”
8. Select “activate Payfast IPN”
9. For testing purposes set the “Payfast Merchant ID” to 10000100, “Payfast Merchant Key” to 46f0cd694581a, and select the “Use the Debugging Feature and the Payfast Sandbox”
10. The module is now ready to be tested with the Sandbox. To test with the sandbox, use the following login credentials when redirected to the PayFast site:
- Username: sbtu01@payfast.co.za
- Password: clientpass

******************************************************************************
*                                                                            *
*    Please see the URL below for all information concerning this module:    *
*                                                                            *
*        https://www.payfast.co.za/shopping-carts/event-espresso/            *
*                                                                            *
******************************************************************************
