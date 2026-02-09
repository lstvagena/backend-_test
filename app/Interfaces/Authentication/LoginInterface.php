<?php

namespace App\Interfaces\Authentication;

interface LoginInterface
{
  public function validateCompanyCode($companyCode);
  public function authenticateUser($username);
  public function incrementLoginAttempts($username);
  public function getSecurityParameters();
  public function lockAccount($username);
  public function updateLoginInformation($username);
}
