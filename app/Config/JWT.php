<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    public string $secret = 'secure-access-jwt-secret-key-2026';
    public int $expiration = 86400;
    public string $algorithm = 'HS256';
}
