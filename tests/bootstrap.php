<?php
declare(strict_types=1);

namespace Tests;

use Nette\Configurator;
use Nette\DI\Container;
use Tester\Environment;

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    fwrite(STDERR, "Install composer dependencies before running the test suite.\n");
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';

if (!class_exists(Environment::class)) {
    fwrite(STDERR, "Install nette/tester (composer require --dev nette/tester) before running the test suite.\n");
    exit(1);
}

Environment::setup();
// Silence deprecations for older Nette version
error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_DEPRECATED);

date_default_timezone_set('Europe/Prague');
$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? '';
putenv('NETTE_DEBUG=1');

final class Bootstrap
{
    private const DEFAULT_DB_NAME = 'fitchart_test';
    private const TEMP_DIR = __DIR__ . '/temp';
    private const SQL_DUMP = __DIR__ . '/../sql/development.sql';

    public static function createContainer(array $cookies = []): Container
    {
        if (!is_dir(self::TEMP_DIR)) {
            mkdir(self::TEMP_DIR, 0755, true);
        } else {
            chmod(self::TEMP_DIR, 0755);
        }

        $sessionsDir = self::TEMP_DIR . '/sessions';
        if (!is_dir($sessionsDir)) {
            mkdir($sessionsDir, 0755, true);
        } else {
            chmod($sessionsDir, 0755);
        }

        if (!empty($cookies)) {
            $_COOKIE = array_merge($_COOKIE, $cookies);
        }

        $configurator = new Configurator();
        $configurator->setDebugMode(true);
        $configurator->enableDebugger(__DIR__ . '/../log');
        error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_DEPRECATED);
        $configurator->setTempDirectory(self::TEMP_DIR);

        // Ensure cache directory exists for RobotLoader
        $cacheDir = self::TEMP_DIR . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }
        $robotLoaderCacheDir = $cacheDir . '/nette.robotLoader';
        if (!is_dir($robotLoaderCacheDir)) {
            mkdir($robotLoaderCacheDir, 0775, true);
        }

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__ . '/../app')
            ->addDirectory(__DIR__ . '/../libs')
            ->register();

        $configurator->addConfig(__DIR__ . '/../app/config/config.neon');
        if (file_exists(__DIR__ . '/../app/config/config.local.neon')) {
            $configurator->addConfig(__DIR__ . '/../app/config/config.local.neon');
        }

        $db = self::getDbConfig();
        $configurator->addConfig([
            'database' => [
                'dsn' => $db['dsn'],
                'user' => $db['user'],
                'password' => $db['password'],
                'options' => ['lazy' => false],
            ],
            'session' => [
                'autoStart' => false, // IMPORTANT: Overrides setting from config.neon
                'savePath' => $sessionsDir,
                // PHP session directives in Nette are written directly here (camelCase)
                'useCookies' => false,
                'useOnlyCookies' => true,
            ],
            'services' => [
                // SimpleRouter for tests - enables link generation and redirects
                'router' => 'Nette\Application\Routers\SimpleRouter',
            ],
        ]);

        // Temporarily set error handler to suppress deprecation warnings
        $oldErrorHandler = null;
        $oldErrorHandler = set_error_handler(function ($severity, $message, $file, $line) use (&$oldErrorHandler) {
            if ($severity === E_USER_DEPRECATED || $severity === E_DEPRECATED) {
                return true; // Suppress deprecation warnings
            }
            // Call Tracy's error handler for other errors
            if ($oldErrorHandler) {
                return call_user_func($oldErrorHandler, $severity, $message, $file, $line);
            }
            return false;
        });

        try {
            return $configurator->createContainer();
        } finally {
            restore_error_handler();
        }
    }

    public static function resetDatabase(): void
    {
        $db = self::getDbConfig();
        $dbName = self::extractDbName($db['dsn']);
        $serverDsn = self::stripDbName($db['dsn']);

        // Validate database name to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
            throw new \RuntimeException('Invalid database name: ' . $dbName);
        }

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ];

        // Connect to server as root to manage databases
        $pdo = new \PDO($serverDsn, $db['user'], $db['password'], $options);

        // Use file lock to prevent parallel collisions
        $lockFile = self::TEMP_DIR . '/db_reset_' . md5($dbName) . '.lock';
        if (!is_dir(self::TEMP_DIR)) {
            mkdir(self::TEMP_DIR, 0775, true);
        }
        
        $fp = fopen($lockFile, 'w');
        if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
            // Wait a bit if lock is held
            usleep(100000); // 100ms
            if (!$fp || !flock($fp, LOCK_EX)) {
                throw new \RuntimeException('Could not acquire database reset lock');
            }
        }

        try {
            // Reset database - always drop first to ensure clean state
            try {
                $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
            } catch (\PDOException $e) {
                // Ignore errors during drop
            }
            
            $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo->exec("USE `$dbName`");

            $sql = file_get_contents(realpath(self::SQL_DUMP));
            if ($sql === false) {
                throw new \RuntimeException('Development SQL dump not found at ' . self::SQL_DUMP);
            }

            // Clean SQL dump for execution: remove USE and SET statements that might interfere
            $sql = preg_replace('/USE\s+`?[^`;]+`?\s*;/i', '', $sql);
            $sql = preg_replace('/SET\s+(NAMES|time_zone)\s+[^;]+;/i', '', $sql);
            
            $pdo->exec($sql);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
            // Lock file is intentionally preserved to avoid race condition where another
            // process could acquire a lock on a deleted filename between unlock and unlink
        }
    }

    public static function getDbConfig(): array
    {
        $host = getenv('TEST_DB_HOST') ?: (getenv('GITHUB_ACTIONS') ? '127.0.0.1' : 'database');
        $port = getenv('TEST_DB_PORT') ?: '3306';
        $name = getenv('TEST_DB_NAME') ?: self::DEFAULT_DB_NAME;

        // Use TESTER_ID or process ID for parallel isolation
        $testerId = getenv('TESTER_ID') ?: getmypid();
        if ($testerId) {
            $name .= '_' . $testerId;
        }

        $dsn = getenv('TEST_DB_DSN');
        if (!$dsn) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $name);
        } else {
            // Always replace dbname in DSN to ensure correct database name
            $dsn = preg_replace('/dbname=([^;]+)/', 'dbname=' . $name, $dsn);
        }

        return [
            'dsn' => $dsn,
            'user' => getenv('TEST_DB_ROOT_USER') ?: 'root',
            'password' => getenv('TEST_DB_ROOT_PASSWORD') ?: 'root',
        ];
    }

    private static function extractDbName(string $dsn): string
    {
        if (preg_match('/dbname=([^;]+)/', $dsn, $m)) {
            return $m[1];
        }
        return self::DEFAULT_DB_NAME;
    }

    private static function stripDbName(string $dsn): string
    {
        return (string) preg_replace('/;?dbname=[^;]+/', '', $dsn);
    }
}
