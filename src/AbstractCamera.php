<?php

namespace Mimicak\CameraCapture;

/**
 * Abstract class AbstractCamera
 * Provides common properties and methods for camera implementations.
 */
abstract class AbstractCamera implements CameraInterface
{
    protected string $id;
    protected string $brand;
    protected ?string $model;
    protected string $host;
    protected ?string $username = null;
    protected ?string $password = null;

    /**
     * Constructor for AbstractCamera.
     *
     * @param string $id Unique identifier for the camera instance.
     * @param string $brand The camera brand.
     * @param string $host The IP address or hostname.
     * @param string|null $model The camera model.
     */
    public function __construct(string $id, string $brand, string $host, ?string $model = null)
    {
        $this->id = $id;
        $this->brand = $brand;
        $this->host = $host;
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function setCredentials(string $username, string $password): void
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Returns the unique ID of the camera instance.
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}