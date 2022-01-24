<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf0dd3ec9868b7a4635653d01f1d0fc0b
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Controllers\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Controllers\\Api\\Base' => __DIR__ . '/../..' . '/src/Api/Base.php',
        'Controllers\\Api\\Cart' => __DIR__ . '/../..' . '/src/Api/Cart.php',
        'Controllers\\Api\\Items' => __DIR__ . '/../..' . '/src/Api/Items.php',
        'Controllers\\Base' => __DIR__ . '/../..' . '/src/Base.php',
        'Controllers\\Cart' => __DIR__ . '/../..' . '/src/Cart.php',
        'Controllers\\Checkout' => __DIR__ . '/../..' . '/src/Checkout.php',
        'Controllers\\Checkout\\Confirm' => __DIR__ . '/../..' . '/src/Checkout/Confirm.php',
        'Controllers\\Checkout\\Confirmed' => __DIR__ . '/../..' . '/src/Checkout/Confirmed.php',
        'Controllers\\Items\\Item' => __DIR__ . '/../..' . '/src/Items/Item.php',
        'Controllers\\Items\\ItemGroup' => __DIR__ . '/../..' . '/src/Items/ItemGroup.php',
        'Controllers\\Items\\Item\\Lots' => __DIR__ . '/../..' . '/src/Items/Item/Lots.php',
        'Controllers\\Items\\Search' => __DIR__ . '/../..' . '/src/Items/Search.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf0dd3ec9868b7a4635653d01f1d0fc0b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf0dd3ec9868b7a4635653d01f1d0fc0b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf0dd3ec9868b7a4635653d01f1d0fc0b::$classMap;

        }, null, ClassLoader::class);
    }
}
