<?php

namespace CameraCapture;

use CameraCapture\Exceptions\CameraCaptureException;

/**
 * Class CameraCapture
 * The main library class for managing and interacting with IP cameras.
 */
class CameraCapture
{
    /**
     * @var CameraInterface[] An associative array of camera instances, keyed by their ID.
     */
    private array $cameras = [];

    /**
     * @var CameraFactory Factory instance used to create camera objects.
     */
    private CameraFactory $factory;

    /**
     * Constructor with dependency injection of the camera factory.
     *
     * @param CameraFactory|null $factory Optional custom factory (uses default if null).
     */
    public function __construct(?CameraFactory $factory = null)
    {
        $this->factory = $factory ?? new CameraFactory();
    }

    /**
     * Adds a new camera to the library based on its configuration.
     *
     * @param array $config Camera configuration array.
     * @return CameraInterface
     * @throws CameraCaptureException
     */
    public function addCamera(array $config): CameraInterface
    {
        $id = $config['id'] ?? null;
        if (!$id || !is_string($id)) {
            throw new CameraCaptureException("Camera configuration must include a unique non-empty string 'id'.");
        }

        if (isset($this->cameras[$id])) {
            throw new CameraCaptureException("Camera with ID '{$id}' already exists.");
        }

        $camera = $this->factory->createCamera($config);
        $this->cameras[$id] = $camera;

        return $camera;
    }

    /**
     * Adds multiple cameras to the library from an array of configurations.
     *
     * @param array $configs
     */
    public function addCameras(array $configs): void
    {
        foreach ($configs as $config) {
            try {
                $this->addCamera($config);
            } catch (CameraCaptureException $e) {
                $this->logError("Failed to add camera: " . $e->getMessage());
            }
        }
    }

    /**
     * Retrieves a camera instance by its unique ID.
     *
     * @param string $id
     * @return CameraInterface
     * @throws CameraCaptureException
     */
    public function getCamera(string $id): CameraInterface
    {
        if (!isset($this->cameras[$id])) {
            throw new CameraCaptureException("Camera with ID '{$id}' not found.");
        }
        return $this->cameras[$id];
    }

    /**
     * Removes a camera from the library.
     *
     * @param string $id
     * @return bool
     */
    public function removeCamera(string $id): bool
    {
        if (isset($this->cameras[$id])) {
            unset($this->cameras[$id]);
            return true;
        }
        return false;
    }

    /**
     * Returns all registered camera instances.
     *
     * @return CameraInterface[]
     */
    public function getAllCameras(): array
    {
        return $this->cameras;
    }

    /**
     * Returns a list of all unique camera brands currently managed by the library.
     *
     * @return string[]
     */
    public function getAvailableBrands(): array
    {
        $brands = array_map(fn($camera) => $camera->getBrand(), $this->cameras);
        return array_values(array_unique($brands));
    }

    /**
     * Captures an image from a specific camera by its ID.
     *
     * @param string $cameraId
     * @return string Binary image data
     * @throws CameraCaptureException
     */
    public function captureImage(string $cameraId): string
    {
        $camera = $this->getCamera($cameraId);
        return $camera->captureImage();
    }

    /**
     * Logs an error message. This method can be overridden or extended for external logging tools.
     *
     * @param string $message
     * @return void
     */
    protected function logError(string $message): void
    {
        error_log("[CameraCapture] $message");
    }
}
