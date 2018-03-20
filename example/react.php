<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$loop = new Swoole\Driver\React();

$server = stream_socket_server('tcp://127.0.0.1:8080');
stream_set_blocking($server, false);

$loop->addReadStream($server, function ($server) use ($loop) {
    $conn = stream_socket_accept($server);
    if (!$conn) {
        return;
    }

    stream_set_blocking($conn, false);
    $loop->addReadStream($conn, function ($conn) use ($loop) {
        $request = fread($conn, 8192);
        $loop->removeReadStream($conn);

        $loop->addWriteStream($conn, function ($conn) use ($loop) {
            $data = "HTTP/1.1 200 OK\r\nContent-Length: 3\r\n\r\nHi\n";
            $written = fwrite($conn, $data);
            if ($written === strlen($data)) {
                $loop->removeWriteStream($conn);
                fclose($conn);
            } else {
                $data = substr($data, $written);
            }
        });
    });
});

$loop->addPeriodicTimer(5, function () {
    $memory = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3).'K';
    echo "Current memory usage: {$formatted}\n";
});

$loop->addSignal(SIGTERM, function () use ($loop) {
    $loop->stop();
});

$loop->run();