<?php

namespace CameraCapture\CameraType;

use CameraCapture\AbstractCamera;
use CameraCapture\Exceptions\CameraCaptureException;

/**
 * Class HttpSnapshotCamera
 * Implements camera capture for devices that offer a direct HTTP snapshot URL.
 * NOTE: The snapshot URL is highly brand and model dependent.
 * This is a generic example. Real-world implementation would require
 * a mapping of brands/models to their specific snapshot URLs.
 */
class HttpSnapshotCamera extends AbstractCamera
{
    private string $snapshotUrlPath;

    /**
     * @param string $id Unique identifier for the camera instance.
     * @param string $brand The camera brand (e.g., 'Generic HTTP', 'Axis').
     * @param string $host The IP address or hostname.
     * @param string $snapshotUrlPath The path to the snapshot endpoint (e.g., '/cgi-bin/snapshot.cgi').
     * @param string|null $model The camera model.
     */
    public function __construct(string $id, string $brand, string $host, string $snapshotUrlPath, ?string $model = null)
    {
        parent::__construct($id, $brand, $host, $model);
        $this->snapshotUrlPath = $snapshotUrlPath;
    }

    /**
     * {@inheritdoc}
     */
    public function captureImage(): string
    {
        $url = "http://{$this->host}{$this->snapshotUrlPath}";

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
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE); // NEW: Get Content-Type header
        $error = curl_error($ch);
        curl_close($ch);

        if ($imageData === false || $httpCode !== 200) {
            throw new CameraCaptureException(
                "Failed to capture image from {$this->brand} camera at {$this->host}. " .
                "HTTP Code: {$httpCode}. cURL Error: {$error}"
            );
        }

        // NEW: Validate MIME type using finfo
        $supportedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            // Fallback to Content-Type header if finfo fails
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