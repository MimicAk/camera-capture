<?php

namespace Mimicak\CameraCapture;

use Mimicak\CameraCapture\CameraType\HttpSnapshotCamera;
use Mimicak\CameraCapture\CameraType\HikvisionCamera;
use Mimicak\CameraCapture\Exceptions\CameraCaptureException;

/**
 * Class CameraFactory
 * Responsible for creating camera instances using a registry-based factory pattern.
 * Supports dynamic registration of camera types and flexible configuration.
 */
class CameraFactory
{
    /**
     * @var array<string, callable> Registry of camera creators by type
     */
    protected array $registry = [];

    /**
     * Constructor to initialize the factory.
     * Calls initDefaults to register built-in camera types.
     */
    public function __construct()
    {
        $this->initDefaults();
    }

    /**
     * Registers a new camera type to the factory.
     */
    public function registerCameraType(string $type, callable $creator): void
    {
        $this->registry[strtolower($type)] = $creator;
    }

    /**
     * Creates a camera instance based on the provided configuration.
     */
    public function createCamera(array $config): CameraInterface
    {
        $requiredKeys = ['id', 'type', 'brand', 'host'];
        foreach ($requiredKeys as $key) {
            if (empty($config[$key])) {
                throw new CameraCaptureException("Missing required camera configuration key: '{$key}'.");
            }
        }

        $type = strtolower($config['type']);

        if (!isset($this->registry[$type])) {
            throw new CameraCaptureException(
                "Unsupported or unregistered camera type: '{$type}'. " .
                "Use constants from CameraCapture\\CameraTypes."
            );
        }

        // Ensure options is an array
        $config['options'] = $config['options'] ?? [];
        if (!is_array($config['options'])) {
            throw new CameraCaptureException("Camera configuration 'options' must be an array.");
        }

        $creator = $this->registry[$type];
        $camera = $creator($config);

        if (!$camera instanceof CameraInterface) {
            throw new CameraCaptureException("Camera type '{$type}' must return an instance of CameraInterface.");
        }

        // Set credentials if provided
        if (!empty($config['username']) && !empty($config['password'])) {
            if (!is_string($config['username']) || !is_string($config['password'])) {
                throw new CameraCaptureException("Username and password must be non-empty strings.");
            }
            $camera->setCredentials($config['username'], $config['password']);
        } elseif (!empty($config['username']) xor !empty($config['password'])) {
            throw new CameraCaptureException("Both username and password must be provided together.");
        }

        return $camera;
    }

    /**
     * Registers built-in camera types using CameraTypes constants.
     */
    protected function initDefaults(): void
    {
        $this->registerCameraType(CameraTypes::TYPE_HTTP_SNAPSHOT, function (array $config): HttpSnapshotCamera {
            if (empty($config['options']['snapshotUrlPath'])) {
                throw new CameraCaptureException("Missing 'options.snapshotUrlPath' for HTTP Snapshot camera.");
            }
            return new HttpSnapshotCamera(
                $config['id'],
                $config['brand'],
                $config['host'],
                $config['options']['snapshotUrlPath'],
                $config['model'] ?? null
            );
        });

        $this->registerCameraType(CameraTypes::TYPE_HIKVISION, function (array $config): HikvisionCamera {
            if (empty($config['options']['snapshotChannel'])) {
                throw new CameraCaptureException("Missing 'options.snapshotChannel' for Hikvision camera.");
            }
            return new HikvisionCamera(
                $config['id'],
                $config['brand'],
                $config['host'],
                $config['options']['snapshotChannel'],
                $config['model'] ?? null
            );
        });

        // Future support:
        // $this->registerCameraType(CameraTypes::TYPE_AXIS, fn($config) => new AxisCamera(...));
        // $this->registerCameraType(CameraTypes::TYPE_ONVIF, fn($config) => new OnvifCamera(...));
    }
}
