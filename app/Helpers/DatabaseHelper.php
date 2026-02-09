<?php

namespace App\Helpers;

use Illuminate\Support\Facades\{
  Config,
  DB,
};

class DatabaseHelper
{
  public static function setConnection($credential)
  {
    $connectionName = 'dynamic';
    $config = [
      'driver' => 'mysql',
      'host' => $credential['host'],
      'port' => 3306,
      'database' => $credential['dbname'],
      'username' => $credential['user'],
      'password' => $credential['pass'],
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'strict' => true,
      'engine' => null,
    ];
    Config::set('database.connections.' . $connectionName, $config);
    DB::purge($connectionName);
    DB::setDefaultConnection($connectionName);
    DB::reconnect($connectionName);
  }
}
