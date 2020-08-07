# Woocommerce Shipping Event

Filter items and shipping methods available depending on the date of shipping ( local pickup or delivery) selected by the client (from a list previously configured).

* Requires at least: 5.4
* Tested up to: 5.4.2
* Requires PHP: 7.3
* Requires WooCommerce: 4.0.0

## Description
This plugin forces the user to select a date before accessing the store. This date will filter products and shipping methods previously configured in the Shipping Event.
It also extends the local pickup adding a configured address to the button in the cart and checkout.
Both address and shipping date will be informed after the order is completed.
Obs: Doesn\'t support held stock feature of woocommerce

## Installation

[Manual Installation]
1. Download the [zip] (https://github.com/filipexavier/woocommerce-shipping-event/archive/master.zip) now or from the o WordPress Module [Directory] (https://br.wordpress.org/plugins/woocommerce-shipping-event/)
2. Unzip the folder and rename it to ”woocommerce-shipping-event”
3. Copy the \"woocommerce-shipping-event\" file into your WordPress directory, inside the \"Plugins\" folder.


[Configuration]
1. Disable held stock feature of woocommerce (not supported)
2. Create a Shipping Event, and select dates, products and shipping methods.
3. Create a Local Pickup Details, to add an address to a specific local pickup shipping method.
