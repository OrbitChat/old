<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\ConnectionInterface;

class TheSocket implements MessageComponentInterface {
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function db() {
        $conn = new PDO("mysql:host=localhost;dbname=passphrase", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "[PassPhrase] New Client: (".$conn->resourceId.")\n";
        $conn->send(new Frame(gzencode("1.0-DEV"), true, Frame::OP_BINARY));
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "[PassPhrase] Client Disconnect: (".$conn->resourceId.")\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $response = json_decode($msg, true);

        if ($response['action'] == 'a1') {
            if (!isset($response['enc'])) {
                $from->send(new Frame(gzencode(json_encode(['error' => 'Faulty response'])), true, Frame::OP_BINARY));
            } else {
                $id = bin2hex(random_bytes(35));
                $pass = password_hash($response['enc'], PASSWORD_ARGON2ID);
                $stmt = $this->db()->prepare("INSERT INTO `rooms` (`roomId`, `encryptionPassPhrase`) VALUES(:roomId, :encPass)");
                $stmt->bindParam(':roomId', $id);
                $stmt->bindParam(':encPass', $pass);
                $stmt->execute();

                $from->send(new Frame(gzencode(json_encode(['action' => 'r1', 'id' => $id])), true, Frame::OP_BINARY));
            }
        }

        if ($response['action'] == 'a2') {
            if (!isset($response['enc'])) {
                $from->send(new Frame(gzencode(json_encode(['error' => 'Faulty response'])), true, Frame::OP_BINARY));
            } else {
                $stmt = $this->db()->prepare("SELECT * FROM `rooms` WHERE `roomId` = :roomId");
                $stmt->bindParam(':roomId', $response['id']);
                $stmt->execute();
                $row = $stmt->fetch();

                if (!password_verify($response['enc'], $row['encryptionPassphrase'])) {
                    $from->send(new Frame(gzencode(json_encode(['action' => 'r2', 'valid' => false])), true, Frame::OP_BINARY));
                } else {
                    $from->send(new Frame(gzencode(json_encode(['action' => 'r2', 'valid' => true])), true, Frame::OP_BINARY));
                }
            }
        }

        if ($response['action'] == 'a3') {
            if (!isset($response['username']) || !isset($response['message']) || !isset($response['enc']) || !isset($response['room'])) {
                $from->send(new Frame(gzencode(json_encode(['error' => 'Faulty response'])), true, Frame::OP_BINARY));
            } else {
                $stmt = $this->db()->prepare("SELECT * FROM `rooms` WHERE `roomId` = :roomId");
                $stmt->bindParam(':roomId', $response['room']);
                $stmt->execute();
                $row = $stmt->fetch();

                if (!password_verify($response['enc'], $row['encryptionPassphrase'])) {
                    $from->send(new Frame(gzencode(json_encode(['action' => 'r3', 'message' => 'Insufficient permission'])), true, Frame::OP_BINARY));
                } else {
                    foreach ($this->clients as $client) {
                        $client->send(new Frame(gzencode(json_encode(['action' => 'r3', 'room' => $response['room'], 'username' => $response['username'], 'message' => $response['message']])), true, Frame::OP_BINARY));;
                    }
                }
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "[PassPhrase] Conn panic";
        $conn->close();
    }
}

// change first ip if breaks
$app = new Ratchet\App('78.0.46.44', 8080, '0.0.0.0');
$app->route('/socket', new TheSocket, ['*']);
$app->run();
