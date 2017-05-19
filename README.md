PayFast EventEspresso Payment Module v1.2.0 for EventEspresso v3.1.29 and higher
Latest Test with EE v3.1.36.6.P
--------------------------------------------------------------
Update all plugins with copyright notice:
Copyright (c) 2008 PayFast (Pty) Ltd
You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.

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

    Please see the URL below for all information concerning this module:

        https://www.payfast.co.za/shopping-carts/event-espresso/

******************************************************************************
