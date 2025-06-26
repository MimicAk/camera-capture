<?php

namespace Mimicak\CameraCapture;

/**
 * Interface CameraInterface
 * Defines the contract for all camera implementations.
 */
interface CameraInterface
{
    /**
     * Captures an image from the camera and returns its binary data.
     *
     * @return string The binary image data (e.g., JPEG, PNG).
     * @throws Exceptions\CameraCaptureException If image capture fails.
     */
    public function captureImage(): string;

    /**
     * Returns the brand of the camera.
     *
     * @return string The camera brand (e.g., 'Axis', 'Hikvision', 'Generic HTTP').
     */
    public function getBrand(): string;

    /**
     * Returns the model of the camera, if applicable.
     *
     * @return string|null The camera model.
     */
    public function getModel(): ?string;

    /**
     * Returns the IP address or hostname of the camera.
     *
     * @return string The camera's network address.
     */
    public function getHost(): string;

    /**
     * Sets the credentials for accessing the camera.
     *
     * @param string $username
     * @param string $password
     * @return void
     */
    public function setCredentials(string $username, string $password): void;
}
