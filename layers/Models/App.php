<?php
/**
 * App
 *
 * @version 1.0.0
 */
namespace Layer\Model;

use Core\Connection;

class App
{
    //private $pdo = null;
    //private $orm = null;

    public function __construct()
    {
        //$this->pdo = Connection::pdo();
        //$this->orm = Connection::orm();
    }

    public function insert($data) : bool
    {
        $stmt = $this->pdo->prepare("INSERT
                                     INTO demo (name, email)
                                     VALUES(:name, :email)");

        $stmt->bindParam(':name',   $data['name']);
        $stmt->bindParam(':email', $data['email']);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
