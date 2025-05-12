<?php

// server.php
require 'vendor/autoload.php';
require 'bin/chat.php';

use Ratchet\Server\IoServer;

$server = IoServer::factory(
    new Chat(),
    8081
);

$server->run();
