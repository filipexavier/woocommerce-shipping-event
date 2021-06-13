<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit85e8cde181b9ff7ae33d1a22bbc37486
{
    public static $prefixLengthsPsr4 = array (
        'W' =>
        array (
            'WCShippingEvent\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WCShippingEvent\\' =>
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'WCShippingEvent\\Admin\\Controller\\OrderManagementController' => __DIR__ . '/../..' . '/includes/admin/controller/OrderManagementController.php',
        'WCShippingEvent\\Base\\Activate' => __DIR__ . '/../..' . '/includes/base/Activate.php',
        'WCShippingEvent\\Base\\BaseController' => __DIR__ . '/../..' . '/includes/base/BaseController.php',
        'WCShippingEvent\\Base\\DateController' => __DIR__ . '/../..' . '/includes/base/DateController.php',
        'WCShippingEvent\\Base\\SettingsController' => __DIR__ . '/../..' . '/includes/base/SettingsController.php',
        'WCShippingEvent\\Base\\ShippingEventController' => __DIR__ . '/../..' . '/includes/base/ShippingEventController.php',
        'WCShippingEvent\\Cpt\\CustomPostTypeController' => __DIR__ . '/../..' . '/includes/cpt/CustomPostTypeController.php',
        'WCShippingEvent\\Cpt\\LocalPickupDetails' => __DIR__ . '/../..' . '/includes/cpt/LocalPickupDetails.php',
        'WCShippingEvent\\Cpt\\ShippingEvent' => __DIR__ . '/../..' . '/includes/cpt/ShippingEvent.php',
        'WCShippingEvent\\Cpt\\ShippingEventType' => __DIR__ . '/../..' . '/includes/cpt/ShippingEventType.php',
        'WCShippingEvent\\Frontend\\Controller\\CheckoutController' => __DIR__ . '/../..' . '/includes/frontend/controller/CheckoutController.php',
        'WCShippingEvent\\Frontend\\Controller\\ShopController' => __DIR__ . '/../..' . '/includes/frontend/controller/ShopController.php',
        'WCShippingEvent\\Frontend\\Controller\\ShortcodeController' => __DIR__ . '/../..' . '/includes/frontend/controller/ShortcodeController.php',
        'WCShippingEvent\\Init' => __DIR__ . '/../..' . '/includes/Init.php',
        'WCShippingEvent\\Meta\\LocalPickupDetailsMetabox' => __DIR__ . '/../..' . '/includes/meta/LocalPickupDetailsMetabox.php',
        'WCShippingEvent\\Meta\\MetaBoxesController' => __DIR__ . '/../..' . '/includes/meta/MetaBoxesController.php',
        'WCShippingEvent\\Meta\\ShippingEventMetabox' => __DIR__ . '/../..' . '/includes/meta/ShippingEventMetabox.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit85e8cde181b9ff7ae33d1a22bbc37486::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit85e8cde181b9ff7ae33d1a22bbc37486::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit85e8cde181b9ff7ae33d1a22bbc37486::$classMap;

        }, null, ClassLoader::class);
    }
}
