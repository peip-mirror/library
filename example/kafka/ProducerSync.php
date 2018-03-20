<?php
declare(strict_types=1);

require dirname(__DIR__) . '/../vendor/autoload.php';

use Kafka\Producer;
use Kafka\ProducerConfig;
use Monolog\Handler\StdoutHandler;
use Monolog\Logger;

// Create the logger
$logger = new Logger('my_logger');
// Now add some handlers
$logger->pushHandler(new StdoutHandler());

$config = ProducerConfig::getInstance();
$config->setMetadataRefreshIntervalMs(10000);
$config->setMetadataBrokerList('127.0.0.1:9092');
$config->setBrokerVersion('1.0.0');
$config->setRequiredAck(1);
$config->setIsAsyn(false);
$config->setProduceInterval(500);

// if use ssl connect
//$config->setSslLocalCert('/home/vagrant/code/kafka-php/ca-cert');
//$config->setSslLocalPk('/home/vagrant/code/kafka-php/ca-key');
//$config->setSslEnable(true);
//$config->setSslPassphrase('123456');
//$config->setSslPeerName('nmred');

$producer = new Producer();
$producer->setLogger($logger);

for ($i = 0; $i < 10; $i++) {
    $result = $producer->send([
        [
            'topic' => 'test',
            'value' => 'test1....message. rango',
            'key' => '',
        ],
    ]);
//    var_dump($result);
}
