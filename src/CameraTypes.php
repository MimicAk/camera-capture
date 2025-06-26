<?php

namespace Mimicak\CameraCapture;

/**
 * Class CameraTypes
 * Defines constants for supported camera types to be used in configuration.
 * Use these constants in the 'type' field of camera configuration arrays
 * to ensure consistency and avoid typos.
 */
final class CameraTypes
{
    /**
     * HTTP Snapshot Camera: Captures images from a generic HTTP snapshot URL.
     */
    public const TYPE_HTTP_SNAPSHOT = 'httpsnapshot';

    /**
     * Hikvision Camera: Captures images using Hikvision ISAPI.
     */
    public const TYPE_HIKVISION = 'hikvision';

    // Placeholder constants for future camera types
    /**
     * Axis Camera: Placeholder for future Axis camera support.
     */
    public const TYPE_AXIS = 'axis';

    /**
     * ONVIF Camera: Placeholder for future ONVIF camera support.
     */
    public const TYPE_ONVIF = 'onvif';


    /**
     * Return all camera types as an associative array
     *
     * @return array
     */
    public static function asArray(): array
    {
        return [
            self::TYPE_HTTP_SNAPSHOT => 'HTTP Snapshot',
            self::TYPE_HIKVISION => 'Hikvision',
            self::TYPE_AXIS => 'Axis (Coming Soon)',
            self::TYPE_ONVIF => 'ONVIF (Coming Soon)',
        ];
    }
}