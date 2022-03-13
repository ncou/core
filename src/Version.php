<?php

declare(strict_types=1);

namespace Chiron\Core;

//https://github.com/phalcon/cphalcon/blob/v3.4.0/phalcon/version.zep

//https://github.com/phalcon/cphalcon/blob/f1315049b46c0e3d348c6446e592c071b901f011/tests/unit/Version/ConstantsCest.php
//https://github.com/phalcon/cphalcon/blob/f1315049b46c0e3d348c6446e592c071b901f011/tests/unit/Version/GetPartCest.php
//https://github.com/phalcon/cphalcon/blob/f1315049b46c0e3d348c6446e592c071b901f011/tests/unit/Version/GetCest.php
//https://github.com/phalcon/cphalcon/blob/f1315049b46c0e3d348c6446e592c071b901f011/tests/unit/Version/GetIdCest.php

// TODO : déplacer cette classe dans le package chiron/chiron !!!!

/**
 * This class allows to get the installed version of the framework
 */
class Version
{
    /**
     * The constant referencing the major version. Returns 0
     *
     * ```php
     * echo Chiron\Core\Version::getPart(
     *     Chiron\Core\Version::VERSION_MAJOR
     * );
     * ```
     */
    public const VERSION_MAJOR = 0;

    /**
     * The constant referencing the major version. Returns 1
     *
     * ```php
     * echo Chiron\Core\Version::getPart(
     *     Chiron\Core\Version::VERSION_MEDIUM
     * );
     * ```
     */
    public const VERSION_MEDIUM = 1;

    /**
     * The constant referencing the major version. Returns 2
     *
     * ```php
     * echo Chiron\Core\Version::getPart(
     *     Chiron\Core\Version::VERSION_MINOR
     * );
     * ```
     */
    public const VERSION_MINOR = 2;

    /**
     * The constant referencing the major version. Returns 3
     *
     * ```php
     * echo Chiron\Core\Version::getPart(
     *     Chiron\Core\Version::VERSION_SPECIAL
     * );
     * ```
     */
    public const VERSION_SPECIAL = 3;

    /**
     * The constant referencing the major version. Returns 4
     *
     * ```php
     * echo Chiron\Core\Version::getPart(
     *     Chiron\Core\Version::VERSION_SPECIAL_NUMBER
     * );
     * ```
     */
    public const VERSION_SPECIAL_NUMBER = 4;

    /**
     * Area where the version number is set. The format is as follows:
     * ABBCCDE
     *
     * A - Major version
     * B - Med version (two digits)
     * C - Min version (two digits)
     * D - Special release: 1 = Alpha, 2 = Beta, 3 = RC, 4 = Stable
     * E - Special release version i.e. RC1, Beta2 etc.
     */
    protected static function getVersion(): array
    {
        return [1, 1, 0, 3, 1];
    }

    /**
     * Translates a number to a special release
     *
     * If Special release = 1 this function will return ALPHA
     */
    protected final static function getSpecial(int $special): string
    {
        $suffix = '';

        switch ($special) {
            case 1:
                $suffix = "alpha";
                break;
            case 2:
                $suffix = "beta";
                break;
            case 3:
                $suffix = "RC";
                break;
        }

        return $suffix;
    }

    /**
     * Returns the active version (string)
     *
     * ```php
     * echo Chiron\Core\Version::get();
     * ```
     */
    public static function get(): string
    {
        $version       = static::getVersion();

        $major         = $version[self::VERSION_MAJOR];
        $medium        = $version[self::VERSION_MEDIUM];
        $minor         = $version[self::VERSION_MINOR];
        $special       = $version[self::VERSION_SPECIAL];
        $specialNumber = $version[self::VERSION_SPECIAL_NUMBER];

        $result  = $major . "." . $medium . "." . $minor;
        $suffix  = static::getSpecial($special);

        if ($suffix !== "") {
            /**
             * A pre-release version should be denoted by appending a hyphen and
             * a series of dot separated identifiers immediately following the
             * patch version.
             */
            $result .= "-". $suffix;

            if ($specialNumber !== 0) {
                $result .= "." . $specialNumber;
            }
        }

        return $result;
    }

    /**
     * Returns the numeric active version
     *
     * ```php
     * echo Chiron\Core\Version::getId();
     * ```
     */
    public static function getId(): string
    {
        $version       = static::getVersion();

        $major         = $version[self::VERSION_MAJOR];
        $medium        = $version[self::VERSION_MEDIUM];
        $minor         = $version[self::VERSION_MINOR];
        $special       = $version[self::VERSION_SPECIAL];
        $specialNumber = $version[self::VERSION_SPECIAL_NUMBER];

        return $major . sprintf("%02s", $medium) . sprintf("%02s", $minor) . $special . $specialNumber;
    }

    /**
     * Returns a specific part of the version. If the wrong parameter is passed
     * it will return the full version
     *
     * ```php
     * echo Chiron\Core\Version::getPart(
     *     Chiron\Core\Version::VERSION_MAJOR
     * );
     * ```
     */
    public static function getPart(int $part): string
    {
        $version = static::getVersion();

        switch ($part) {
            case self::VERSION_MAJOR:
            case self::VERSION_MEDIUM:
            case self::VERSION_MINOR:
            case self::VERSION_SPECIAL_NUMBER:
                return $version[$part];

            case self::VERSION_SPECIAL:
                return static::getSpecial($version[self::VERSION_SPECIAL]);
        }

        return static::get();
    }

}
