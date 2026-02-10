<?php

namespace App\Services\Authentication;

use App\Interfaces\Authentication\RegisterInterface;
use App\Helpers\{
    AuthHelper,
    DatabaseHelper,
    UserActivityHelper
};

class RegisterService
{
    protected $repository;
    // Holds the injected repository implementing RegisterInterface

    public function __construct(RegisterInterface $repository)
    // Constructor with dependency injection of RegisterInterface implementation
    {
        $this->repository = $repository;
        // Assigns the repository to the class property
    }

    public function register(array $validated)
    // Handles user registration logic using validated input data
    {
        // Validate company code against CENTRAL database
        $company = $this->repository->validateCompanyCode($validated['company_code']);

        // If company does not exist or is invalid
        if (!$company) {
            return AuthHelper::onError('company_not_found', false);
            // Returns a standardized authentication error response
        }

        $fcon = $company->fcon;
        // Get the company database connection string (e.g. host=...|db=...)
        
        // Initialize empty array for tenant database credentials
        $credential = [];

        // Split the fcon string into key=value pairs using "|" as delimiter
        foreach (explode('|', $fcon) as $pair) {

            // Ensure the pair contains a "=" sign
            if (strpos($pair, '=') !== false) {

                // Split the pair into key and value (limit to 2 parts)
                [$k, $v] = explode('=', $pair, 2);

                // Store credential key-value into array
                $credential[$k] = $v;
            }
        }

        // Switch the database connection to the TENANT database
        DatabaseHelper::setConnection($credential);

        // Create the user record in the tenant database (NO TOKEN GENERATED)
        $user = $this->repository->registerUser($validated);

        // Record user registration activity for auditing purposes
        UserActivityHelper::record([
            'user'           => $user,  // The newly created user model
            'event'          => 'Register',  // Type of activity performed
            'auditable_type' => 'Authentication',  // Category of the audit event
            'old_value'      => null, // No previous data (new registration)
            'new_value'      => null,  // No change comparison needed
            'remarks'        => 'User registered successfully',  // Human-readable activity description
        ]);

        // Return a simple success response without logging the user in
        return AuthHelper::onSuccess2(
            null,                       // No auth token generated
            null,                       // No company cookie set
            null,                       // No user payload returned
            'register'                  // Action identifier
        );
    }
}
