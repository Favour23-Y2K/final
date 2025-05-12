<?php

require_once('..\assets\apache\db_con.php'); 
require_once('..\assets\apache\functions.php'); 

// Chat.php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $rabbitConnection;
    protected $channel;

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        // Connect to RabbitMQ
        try {
            $host = getenv('RABBITMQ_HOST') ?: 'localhost';
            $port = getenv('RABBITMQ_PORT') ?: 5672;
            $user = getenv('RABBITMQ_USER') ?: 'chat_user';
            $password = getenv('RABBITMQ_PASSWORD') ?: 'password';
            $vhost = getenv('RABBITMQ_VHOST') ?: 'chat_app';

            $this->rabbitConnection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
            $this->channel = $this->rabbitConnection->channel();
            $this->channel->queue_declare('chat_queue', false, true, false, false);
            $this->channel->basic_consume('chat_queue', '', false, true, false, false, [$this, 'handleRabbitMQMessage']);
            echo "Successfully connected to RabbitMQ\n";
        } catch (\Exception $e) {
            error_log("RabbitMQ Connection Error: " . $e->getMessage());
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Broadcast the message to all clients
        foreach ($this->clients as $client) {
            try {
                $client->send($msg);
            } catch (\Exception $e) {
                error_log("Error sending message to client {$client->resourceId}: " . $e->getMessage());
            }
        }

        // Publish to RabbitMQ for backend handling (optional)
        try {
            $message = new AMQPMessage($msg);
            $this->channel->basic_publish($message, '', 'chat_queue');
            echo "Message published to RabbitMQ: {$msg}\n";
        } catch (\Exception $e) {
            error_log("Error publishing message to RabbitMQ: " . $e->getMessage());
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("Error: {$e->getMessage()}\n");
        $conn->close();
    }

    public function handleRabbitMQMessage($msg) {
        // Broadcast messages received from RabbitMQ
        foreach ($this->clients as $client) {
            try {
                $client->send($msg->body);
            } catch (\Exception $e) {
                error_log("Error sending RabbitMQ message to client {$client->resourceId}: " . $e->getMessage());
            }
        }
    }

    public function __destruct() {
        if ($this->channel) {
            $this->channel->close();
            echo "RabbitMQ channel closed\n";
        }
        if ($this->rabbitConnection) {
            $this->rabbitConnection->close();
            echo "RabbitMQ connection closed\n";
        }
    }
}

