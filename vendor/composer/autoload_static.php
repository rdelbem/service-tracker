<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita1c3f2669fea2498beb51d23e8edaf17
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'STOLMCServiceTracker\\' => 21,
        ),
        'R' => 
        array (
            'Rdelbem\\WPMailerClass\\' => 22,
        ),
        'M' => 
        array (
            'Moment\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'STOLMCServiceTracker\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
        'Rdelbem\\WPMailerClass\\' => 
        array (
            0 => __DIR__ . '/..' . '/rdelbem/wp-mailer-class/src',
        ),
        'Moment\\' => 
        array (
            0 => __DIR__ . '/..' . '/fightbulc/moment/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita1c3f2669fea2498beb51d23e8edaf17::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita1c3f2669fea2498beb51d23e8edaf17::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita1c3f2669fea2498beb51d23e8edaf17::$classMap;

        }, null, ClassLoader::class);
    }
}
