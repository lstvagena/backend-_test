<?php

namespace App\Helpers;

use Illuminate\Support\Facades\{
  Config, // Facade used to dynamically set Laravel configuration values
  DB,     // Facade used to manage database connections
};

class DatabaseHelper
{
  // Helper class responsible for dynamically switching database connections
  // (used for multi-tenant / per-company databases)
  public static function setConnection($credential)
  {
    $connectionName = 'dynamic';
    // Name of the dynamic database connection
    // This will be registered at runtime in Laravel's config

    $config = [
      'driver' => 'mysql', // Database driver type (MySQL / MariaDB)
      'host' => $credential['host'], // Database host (e.g. localhost, 127.0.0.1, remote IP)
      'port' => 3306, // MySQL default port (can be changed if needed)

      'database' => $credential['dbname'], // Target database name for the selected company

      'username' => $credential['user'], // Database username

      'password' => $credential['pass'], // Database password

      'charset' => 'utf8mb4', // Character set (supports emojis and full UTF-8)

      'collation' => 'utf8mb4_unicode_ci', // Collation rules for string comparison

      'prefix' => '', // Table prefix (unused in this setup)

      'strict' => true, // Enable strict SQL mode for safer queries

      'engine' => null, // Storage engine (null lets MySQL decide, usually InnoDB)
    ];

    Config::set('database.connections.' . $connectionName, $config); // Dynamically inject the new database connection into Laravel's config
    DB::purge($connectionName); // Clear any existing connection with the same name
    // Prevents Laravel from reusing a stale connection
    DB::setDefaultConnection($connectionName); // Set this dynamic connection as the default for the current request
    DB::reconnect($connectionName); // Force Laravel to establish a fresh connection using the new config
  }
}
