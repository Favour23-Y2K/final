<?php

// Consumer.php
use Ratchet\ConnectionInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;

require '../vendor/autoload.php';

$host = getenv('RABBITMQ_HOST') ?: 'localhost';
$port = getenv('RABBITMQ_PORT') ?: 5672;
$user = getenv('RABBITMQ_USER') ?: 'chat_user';
$password = getenv('RABBITMQ_PASSWORD') ?: 'password';
$vhost = getenv('RABBITMQ_VHOST') ?: 'chat_app';

$this->rabbitConnection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
$channel = $rabbitConnection->channel();
$channel->queue_declare('chat_queue', false, true, false, false);

$channel->basic_consume('chat_queue', '', false, true, false, false, function($msg) {
    foreach ($this->clients as $client) {
        $client->send($msg->body);
    }
});

while ($channel->is_consuming()) {
    $channel->wait();
}
