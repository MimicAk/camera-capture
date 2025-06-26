<?php

namespace Mimicak\CameraCapture\CameraType;

use Mimicak\CameraCapture\AbstractCamera;
use Mimicak\CameraCapture\Exceptions\CameraCaptureException;

/**
 * Class HikvisionCamera
 * Implements camera capture for Hikvision IP cameras using ISAPI.
 */
class HikvisionCamera extends AbstractCamera
{
    private string $snapshotChannel;

    /**
     * @param string $id Unique identifier for the camera instance.
     * @param string $brand The camera brand (e.g., 'Hikvision').
     * @param string $host The IP address or hostname.
     * @param string $snapshotChannel The channel for snapshot (e.g., '101' for main stream).
     * @param string|null $model The camera model.
     */
    public function __construct(string $id, string $brand, string $host, string $snapshotChannel, ?string $model = null)
    {
        parent::__construct($id, $brand, $host, $model);
        $this->snapshotChannel = $snapshotChannel;
    }

    /**
     * {@inheritdoc}
     */
    public function captureImage(): string
    {
        $url = "http://{$this->host}/ISAPI/Streaming/channels/{$this->snapshotChannel}/picture";


        var_dump($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Timeout for connection
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);    // Timeout for entire operation

        if ($this->username && $this->password) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST | CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        }

        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($imageData === false || $httpCode !== 200) {
            throw new CameraCaptureException(
                "Failed to capture image from {$this->brand} camera at {$this->host}. " .
                "HTTP Code: {$httpCode}. cURL Error: {$error}"
            );
        }

        // Validate MIME type
        $supportedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            // Fallback to Content-Type header
            if (!preg_match('/^image\/(jpeg|png|gif|bmp)/', $contentType)) {
                throw new CameraCaptureException(
                    "Invalid response from {$this->brand} camera at {$this->host}: " .
                    "Expected image, got Content-Type '$contentType'."
                );
            }
        } else {
            $mimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);
            if (!in_array($mimeType, $supportedMimeTypes)) {
                throw new CameraCaptureException(
                    "Invalid image data from {$this->brand} camera at {$this->host}: " .
                    "MIME type '$mimeType' not supported."
                );
            }
        }

        return $imageData;
    }
}