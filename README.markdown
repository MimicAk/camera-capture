# CameraCapture PHP Library

The `CameraCapture` PHP library provides a flexible and extensible way to capture images from IP cameras, such as Hikvision and generic HTTP snapshot cameras. It uses a factory pattern to support multiple camera types and includes robust error handling, authentication, and MIME type validation.

## Features
- **Modular Design**: Supports multiple camera types (e.g., Hikvision, generic HTTP snapshot) via a factory pattern.
- **Extensible**: Easily add new camera types by registering them with the `CameraFactory`.
- **Authentication**: Supports HTTP Basic and Digest authentication.
- **SSL/TLS Support**: Configurable HTTP/HTTPS protocols with SSL verification options.
- **Type Safety**: Uses constants from `CameraTypes` to avoid configuration errors.
- **Error Handling**: Throws detailed exceptions for network issues, invalid responses, or misconfigurations.
- **Flexible Configuration**: Supports type-specific options and customizable timeouts.

## Requirements
- PHP 7.4 or higher
- PHP extensions: `curl`, `fileinfo`
- Composer (recommended for autoloading)

## Installation

### Using Composer (Recommended)
1. Add the library to your project:
   ```bash
   composer require your-vendor/camera-capture
   ```
   *Note*: Replace `your-vendor/camera-capture` with the actual package name if published to Packagist, or use a custom repository.

2. Ensure your `composer.json` includes autoloading:
   ```json
   "autoload": {
       "psr-4": {
           "CameraCapture\\": "src/"
       }
   }
   ```

3. Run `composer dump-autoload` to generate the autoloader.

### Manual Installation
1. Clone or download the library to your project directory.
2. Place the `src/` directory in your project.
3. Manually include the required files:
   ```php
   require_once __DIR__ . '/src/CameraInterface.php';
   require_once __DIR__ . '/src/AbstractCamera.php';
   require_once __DIR__ . '/src/Exception/CameraCaptureException.php';
   require_once __DIR__ . '/src/CameraType/HttpSnapshotCamera.php';
   require_once __DIR__ . '/src/CameraType/HikvisionCamera.php';
   require_once __DIR__ . '/src/CameraFactory.php';
   require_once __DIR__ . '/src/CameraTypes.php';
   require_once __DIR__ . '/src/CameraCapture.php';
   ```

## Usage

### Basic Example
The following example demonstrates how to initialize the library, add cameras, capture images, and manage camera instances.

```php
<?php

require 'vendor/autoload.php'; // Use Composer autoloader

use CameraCapture\CameraCapture;
use CameraCapture\CameraTypes;
use CameraCapture\Exception\CameraCaptureException;

// Define camera configurations
$cameraConfigs = [
    [
        'id' => 'front_door_cam',
        'type' => CameraTypes::TYPE_HIKVISION,
        'brand' => 'Hikvision',
        'model' => 'DS-2CD2345FWD-I',
        'host' => '192.168.1.3', // Replace with your camera's IP
        'username' => 'admin',
        'password' => 'your_password',
        'protocol' => 'https', // Use 'http' if HTTPS is not required
        'options' => [
            'snapshotChannel' => '101',
            'connectTimeout' => 10,
            'timeout' => 15,
            'verifySsl' => false, // Set to true for trusted certificates
        ],
    ],
    [
        'id' => 'backyard_cam',
        'type' => CameraTypes::TYPE_HTTP_SNAPSHOT,
        'brand' => 'Dahua',
        'model' => 'DH-IPC-HFW1230S',
        'host' => '192.168.1.101', // Replace with your camera's IP
        'username' => 'user',
        'password' => 'your_password',
        'protocol' => 'https',
        'options' => [
            'snapshotUrlPath' => '/cgi-bin/currentpic.cgi',
            'connectTimeout' => 10,
            'timeout' => 15,
            'verifySsl' => false,
        ],
    ],
];

// Initialize the library
$cameraLibrary = new CameraCapture();

try {
    // Add cameras
    $cameraLibrary->addCameras($cameraConfigs);
    echo "Successfully added " . count($cameraLibrary->getAllCameras()) . " cameras.\n";

    // List camera information
    echo "\nCamera Information:\n";
    foreach ($cameraLibrary->getAllCameras() as $id => $camera) {
        echo "ID: {$id}, Brand: {$camera->getBrand()}, Model: " . 
             ($camera->getModel() ?? 'N/A') . ", Host: {$camera->getHost()}\n";
    }

    // List available brands
    echo "\nAvailable Brands:\n";
    $brands = $cameraLibrary->getAvailableBrands();
    echo "Brands: " . implode(', ', $brands) . "\n";

    // Capture images
    echo "\nCapturing Images:\n";
    foreach (['front_door_cam', 'backyard_cam'] as $cameraId) {
        try {
            $imageData = $cameraLibrary->captureImage($cameraId);
            $filename = "snapshots/{$cameraId}_snapshot.jpg";
            if (!is_dir('snapshots')) {
                mkdir('snapshots', 0755, true);
            }
            file_put_contents($filename, $imageData);
            echo "Captured image from $cameraId. Saved as $filename (" . strlen($imageData) . " bytes).\n";
        } catch (CameraCaptureException $e) {
            echo "Error capturing image from $cameraId: " . $e->getMessage() . "\n";
        }
    }

    // Test error handling with a non-existent camera
    echo "\nError Handling Example (non-existent camera):\n";
    try {
        $cameraLibrary->captureImage('non_existent_cam');
    } catch (CameraCaptureException $e) {
        echo "Caught expected error: " . $e->getMessage() . "\n";
    }

    // Remove a camera
    echo "\nRemoving Camera:\n";
    if ($cameraLibrary->removeCamera('front_door_cam')) {
        echo "Successfully removed front door camera.\n";
    } else {
        echo "Front door camera not found for removal.\n";
    }

    // List remaining cameras
    echo "\nCameras Remaining:\n";
    $remainingCameras = array_keys($cameraLibrary->getAllCameras());
    echo "Remaining cameras: " . (empty($remainingCameras) ? "None" : implode(', ', $remainingCameras)) . "\n";

} catch (CameraCaptureException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

```

**Expected Output** (assuming cameras are accessible):
```
Successfully added 2 cameras.

Camera Information:
ID: front_door_cam, Brand: Hikvision, Model: DS-2CD2345FWD-I, Host: 192.168.1.3
ID: backyard_cam, Brand: Dahua, Model: DH-IPC-HFW1230S, Host: 192.168.1.101

Available Brands:
Brands: Hikvision, Dahua

Capturing Images:
Captured image from front_door_cam. Saved as snapshots/front_door_cam_snapshot.jpg (123456 bytes).
Captured image from backyard_cam. Saved as snapshots/backyard_cam_snapshot.jpg (98765 bytes).

Error Handling Example (non-existent camera):
Caught expected error: Camera with ID 'non_existent_cam' not found.

Removing Camera:
Successfully removed front door camera.

Cameras Remaining:
Remaining cameras: backyard_cam
```

## Configuration

Each camera configuration is an array with the following keys:

| Key             | Type           | Required | Description                                                                 |
|-----------------|----------------|----------|-----------------------------------------------------------------------------|
| `id`            | string         | Yes      | Unique identifier for the camera.                                           |
| `type`          | string         | Yes      | Camera type (e.g., `CameraTypes::TYPE_HIKVISION`, `CameraTypes::TYPE_HTTP_SNAPSHOT`). |
| `brand`         | string         | Yes      | Camera brand (e.g., 'Hikvision', 'Dahua').                                  |
| `host`          | string         | Yes      | Camera IP address or hostname (e.g., '192.168.1.3').                        |
| `model`         | string|null    | No       | Camera model (e.g., 'DS-2CD2345FWD-I').                                    |
| `username`      | string         | No       | Username for camera authentication.                                         |
| `password`      | string         | No       | Password for camera authentication (required if `username` is provided).    |
| `protocol`      | string         | No       | Connection protocol ('http' or 'https', defaults to 'http').                |
| `options`       | array          | Yes      | Type-specific settings (see below).                                         |

### Type-Specific Options
- **Hikvision (`CameraTypes::TYPE_HIKVISION`)**:
  - `snapshotChannel`: Channel for snapshot (e.g., '101' for main stream).
- **HTTP Snapshot (`CameraTypes::TYPE_HTTP_SNAPSHOT`)**:
  - `snapshotUrlPath`: Path to the snapshot endpoint (e.g., '/cgi-bin/currentpic.cgi').
- **Common Options** (for both types):
  - `connectTimeout`: Connection timeout in seconds (default: 5).
  - `timeout`: Total operation timeout in seconds (default: 10).
  - `verifySsl`: Whether to verify SSL certificates (default: true).

## Troubleshooting

- **Connection Timeouts**:
  - Ensure the camera’s IP and port are correct (e.g., `telnet 192.168.1.3 443` for HTTPS).
  - Try `protocol => 'https'` or `protocol => 'http'` in the config.
  - Increase `connectTimeout` and `timeout` in the `options` array.
  - Set `verifySsl => false` for cameras with self-signed certificates.
- **Authentication Errors**:
  - Verify username and password in the camera’s web interface.
  - Ensure the camera supports Basic or Digest authentication.
- **Invalid MIME Type**:
  - Check the snapshot endpoint in a browser to confirm it returns an image.
  - Verify the endpoint in the camera’s documentation (e.g., `/ISAPI/Streaming/channels/101/picture` for Hikvision).
- **Logs**:
  - Errors are logged via `CameraCapture::logError` (defaults to PHP’s error log).
  - Override `logError` for custom logging (e.g., to a file or external service).

## Extending the Library
To add a new camera type:
1. Create a class implementing `CameraInterface` (e.g., `AxisCamera`).
2. Register it in `CameraFactory`:
   ```php
   $factory = new CameraFactory();
   $factory->registerCameraType(CameraTypes::TYPE_AXIS, function (array $config) {
       return new AxisCamera($config['id'], $config['brand'], $config['host'], ...);
   });
   ```

## Testing
- **Unit Tests**: Use PHPUnit to test `CameraCapture`, `CameraFactory`, and camera classes. Mock `CameraInterface` for isolated tests.
- **Integration Tests**: Test with real cameras to verify snapshot capture.
- **Example Test**:
  ```php
  use PHPUnit\Framework\TestCase;
  use CameraCapture\CameraCapture;

  class CameraCaptureTest extends TestCase
  {
      public function testAddCameraDuplicateId()
      {
          $factory = $this->createMock(\CameraCapture\CameraFactory::class);
          $library = new CameraCapture($factory);
          $config = ['id' => 'test', 'type' => 'test', 'brand' => 'Test', 'host' => 'localhost'];
          $camera = $this->createMock(\CameraCapture\CameraInterface::class);
          $factory->method('createCamera')->willReturn($camera);

          $library->addCamera($config);
          $this->expectException(\CameraCapture\Exception\CameraCaptureException::class);
          $library->addCamera($config);
      }
  }
  ```

## License
MIT License (or specify your preferred license).

## Contributing
Contributions are welcome! Please submit pull requests or open issues on the [GitHub repository](#).

## Contact
For support, contact [muthurose2204@gmail.com] or open an issue on GitHub.