<?php

namespace App\Interfaces\Authentication;

interface RegisterInterface
{
  public function registerUser(array $data);
    public function validateCompanyCode(string $companyCode);
}
