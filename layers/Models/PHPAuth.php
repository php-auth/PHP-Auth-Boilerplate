<?php
/**
 * PHPAuth
 *
 * @version 1.0.0
 */
namespace Layer\Model;

use \PDO;
use Core\Connection;

class PHPAuth
{
    private $pdo = null;

    /**
     * __construct
     *
     * @access public
     */
    public function __construct()
    {
        $this->pdo = Connection::pdo();
        if (ini_get('display_errors')) {
            try {
                $table = $this->pdo->query('SELECT 1 FROM users LIMIT 1');
                if (!$table) {
                    $this->executeSQL();
                }
            } catch (\PDOException $e) {
                $this->executeSQL();
            }
        }
    }

    /**
     * executeSQL
     *
     * @access private
     */
    private function executeSQL()
    {
        $driver = strtolower($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        $path   = PATH['sql'] . '/php-auth';
        switch ($driver) {
          case 'mysql':
            $this->pdo->exec(file_get_contents("$path/MySQL.sql"));
            break;
          case 'pgsql':
            $this->pdo->exec(file_get_contents("$path/PostgreSQL.sql"));
            break;
          case 'sqlite':
            $this->pdo->exec(file_get_contents("$path/SQLite.sql"));
            break;
        }
    }

    /**
     * connection
     *
     * @return mixed
     * @access public
     */
    public function connection() : mixed
    {
        return $this->pdo;
    }

    /**
     * select
     *
     * @param string $query
     * @return mixed
     * @access public
     */
    public function select($query = '') : mixed
    {
        $query = $query ? "WHERE username LIKE '%{$query}%'" : '';
        $stmt  = $this->pdo->prepare("SELECT id,
                                             username,
                                             email,
                                             verified,
                                             status
                                        FROM users $query");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * update
     *
     * @param int $id
     * @param string $data
     * @param string $field
     * @return bool
     * @access public
     */
    public function update($id, $data, $field) : bool
    {
        $stmt = $this->pdo->prepare("UPDATE users
                                     SET    $field=?
                                     WHERE  id=?");
        if ($stmt->execute([$data, $id])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * selectRoute
     *
     * @param string $query
     * @return mixed
     * @access public
     */
    public function selectRoute() : mixed
    {
        $stmt = $this->pdo->prepare('SELECT base, dest FROM route');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * updateRoute
     *
     * @param int $id
     * @param string $base_route
     * @param string $dest_route
     * @return bool
     * @access public
     */
    public function updateRoute($base_route, $dest_route) : bool
    {
        $stmt = $this->pdo->prepare("UPDATE route SET base=?, dest=?");
        if ($stmt->execute([$base_route, $dest_route])) {
            return true;
        } else {
            return false;
        }
    }
}
