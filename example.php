<?php

// Manual file inclusion for this example (non-Composer setup)
// In a Composer-based project, use: require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/CameraInterface.php';
require_once __DIR__ . '/src/AbstractCamera.php';
require_once __DIR__ . '/src/Exceptions/CameraCaptureException.php'; // Correct namespace
require_once __DIR__ . '/src/CameraType/HttpSnapshotCamera.php';
require_once __DIR__ . '/src/CameraType/HikvisionCamera.php'; // Added missing file
require_once __DIR__ . '/src/CameraFactory.php';
require_once __DIR__ . '/src/CameraTypes.php';
require_once __DIR__ . '/src/CameraCapture.php';

use CameraCapture\CameraCapture;
use CameraCapture\CameraTypes;
use CameraCapture\Exceptions\CameraCaptureException;

// --- Define Camera Configurations ---
// Configurations use the 'options' array for type-specific settings.
// Replace host, username, and password with your camera's actual values.
$cameraConfigs = [
    [
        'id' => 'front_door_cam',
        'type' => CameraTypes::TYPE_HIKVISION,
        'brand' => 'Hikvision',
        'model' => 'TESTModel',
        'host' => '192.168.1.3', // Replace with your camera's IP
        'username' => 'admin',
        'password' => 'Onemodo1974',
        'options' => ['snapshotChannel' => '101'], // Hikvision uses snapshotChannel
    ],
    [
        'id' => 'backyard_cam',
        'type' => CameraTypes::TYPE_HTTP_SNAPSHOT,
        'brand' => 'Dahua',
        'model' => 'DH-IPC-HFW1230S',
        'host' => '192.168.1.101', // Replace with your camera's IP
        'username' => 'user',
        'password' => 'securepass',
        'options' => ['snapshotUrlPath' => '/cgi-bin/currentpic.cgi'], // HTTP Snapshot uses snapshotUrlPath
    ],
];

// --- Initialize the Library ---
// CameraFactory is initialized internally by CameraCapture
$cameraLibrary = new CameraCapture();

echo "--- Adding Cameras ---\n";
try {
    $cameraLibrary->addCameras($cameraConfigs);
    echo "Successfully added " . count($cameraLibrary->getAllCameras()) . " cameras.\n";
} catch (CameraCaptureException $e) {
    echo "Error adding cameras: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n--- Camera Information ---\n";
try {
    foreach ($cameraLibrary->getAllCameras() as $id => $camera) {
        echo "ID: {$id}, Brand: {$camera->getBrand()}, Model: " .
            ($camera->getModel() ?? 'N/A') . ", Host: {$camera->getHost()}\n";
    }
} catch (Exception $e) {
    echo "Error listing cameras: " . $e->getMessage() . "\n";
}

echo "\n--- Available Brands ---\n";
try {
    $brands = $cameraLibrary->getAvailableBrands();
    echo "Brands: " . implode(', ', $brands) . "\n";
} catch (Exception $e) {
    echo "Error retrieving brands: " . $e->getMessage() . "\n";
}

echo "\n--- Capturing Images ---\n";
foreach (['front_door_cam', 'backyard_cam'] as $cameraId) {
    try {
        $imageData = $cameraLibrary->captureImage($cameraId);
        $dTime = date('Ymd_His');
        $filename = "snapshots/{$cameraId}_{$dTime}_snapshot.jpg"; // Save to a snapshots directory
        if (!is_dir('snapshots')) {
            mkdir('snapshots', 0755, true);
        }
        file_put_contents($filename, $imageData);
        echo "Captured image from $cameraId. Saved as $filename (" . strlen($imageData) . " bytes).\n";

        // Example for web output (uncomment to test in a browser):
        /*
        header('Content-Type: image/jpeg');
        echo $imageData;
        exit;
        */
    } catch (CameraCaptureException $e) {
        echo "Error capturing image from $cameraId: " . $e->getMessage() . "\n";
    }
}

echo "\n--- Error Handling Example (non-existent camera) ---\n";
try {
    $cameraLibrary->captureImage('non_existent_cam');
} catch (CameraCaptureException $e) {
    echo "Caught expected error: " . $e->getMessage() . "\n";
}

echo "\n--- Removing Camera ---\n";
try {
    if ($cameraLibrary->removeCamera('front_door_cam')) {
        echo "Front door camera removed successfully.\n";
    } else {
        echo "Front door camera not found for removal.\n";
    }
} catch (Exception $e) {
    echo "Error removing camera: " . $e->getMessage() . "\n";
}

echo "\n--- Cameras Remaining ---\n";
try {
    $remainingCameras = array_keys($cameraLibrary->getAllCameras());
    echo "Remaining cameras: " . (empty($remainingCameras) ? "None" : implode(', ', $remainingCameras)) . "\n";
} catch (Exception $e) {
    echo "Error listing remaining cameras: " . $e->getMessage() . "\n";
}