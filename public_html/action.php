<?php
session_start();
require_once('../assets/apache/db_con.php');
require_once('../assets/apache/functions.php');
require_once('../assets/apache/process.php');
require 'vendor/autoload.php'; // Load WebSocket client library

use WebSocket\Client;

if(isset($_SESSION['user'])) {
    $USER = decrypt($_SESSION['user'], $_SESSION['key']);

    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create_post':
                $result = handlePost($_POST, $_FILES, $USER, $con);
                break;
            case 'send_message':
                $result = handleMessage($_POST, $_FILES, $USER, $con);
                
                // Ensure $row is defined before sending to WebSocket
                if (isset($result['data'])) {
                    $row = $result['data']; // Assuming this is where $row is defined
                    try {
                        $websocketClient = new Client("ws://localhost:8081"); // Replace with your WebSocket server URL
                        $messageData = json_encode($row); // Convert $row to JSON
                        $websocketClient->send($messageData);
                        $websocketClient->close();
                        echo json_encode(['success' => true, 'message' => 'Message sent to WebSocket server']);
                    } catch (Exception $e) {
                        error_log("WebSocket Error: " . $e->getMessage());
                        echo json_encode(['success' => false, 'error' => 'WebSocket connection failed']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'No data to send to WebSocket']);
                }
                
                break;
            default:
                $result = ['success' => false, 'error' => 'Invalid action'];
        }
    } else {
        $result = ['success' => false, 'error' => 'No action specified'];
    }

    echo json_encode($result);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}