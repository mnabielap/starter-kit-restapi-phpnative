<?php

namespace App\Middlewares;

use App\Core\Request;

class RateLimiter
{
    public function handle(Request $request)
    {
        // For this starter kit, we will pass through.
        // Implementing DB-based rate limiting here would add significant complexity to the schema.
        return true;
    }
}