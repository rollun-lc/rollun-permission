<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace rollun\permission\Authentication\Factory;

use PDO;
use PDOException;
use Psr\Container\ContainerInterface;
use RuntimeException;

class OAuth2ServerPdoFactory
{
    public const SERVICE_NAME = 'oauth2-server.db';

    public function __invoke(ContainerInterface $container): PDO
    {
        $host = getenv('OAUTH2_SERVER_DB_HOST');
        $database = getenv('OAUTH2_SERVER_DB_NAME') ?: 'oauth2_server';
        $username = getenv('OAUTH2_SERVER_DB_USER');
        $password = getenv('OAUTH2_SERVER_DB_PASS');
        $port = getenv('OAUTH2_SERVER_DB_PORT') ?: '3306';

        if (!$host || !$username) {
            throw new RuntimeException('OAuth2 DB connection env vars are not configured.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            return new PDO($dsn, $username, $password ?: '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to connect to oauth2_server database.', 0, $e);
        }
    }
}
