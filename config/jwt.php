<?php

return [
    'secret' => env('JWT_SECRET', 'CpSK6aDesaHe425YzlNRsaPwGvT4EEhRTt4sWRbQ51XPxoQVlsueur27pyKesBMw'),
    'ttl' => 60,
    'refresh_ttl' => 20160,
    'algo' => 'HS256',
    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),
    'decrypt_cookies' => false,
];
