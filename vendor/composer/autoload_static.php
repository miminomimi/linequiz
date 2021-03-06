<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5144fa95f37f6a88d9480406074d0681
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LINE\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LINE\\' => 
        array (
            0 => __DIR__ . '/..' . '/linecorp/line-bot-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5144fa95f37f6a88d9480406074d0681::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5144fa95f37f6a88d9480406074d0681::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
