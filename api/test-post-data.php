<?php
/**
 * Test Endpoint - Echo back POST data for debugging
 */

header('Content-Type: application/json');

$response = [
    'success' => true,
    'message' => 'POST Data received',
    'post_keys' => array_keys($_POST),
    'post_data' => $_POST,
    'files_received' => array_keys($_FILES),
    'timestamp' => date('Y-m-d H:i:s')
];

// Add file info if exists
if (!empty($_FILES)) {
    $response['file_details'] = [];
    foreach ($_FILES as $name => $file) {
        $response['file_details'][$name] = [
            'name' => $file['name'],
            'type' => $file['type'],
            'size' => $file['size'],
            'error' => $file['error']
        ];
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
