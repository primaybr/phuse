# Security in Phuse

An overview of the framework's security-relevant pieces and how they fit together. For the full
CSRF walkthrough see [csrf-protection.md](csrf-protection.md); for the low-level encryption
primitives see [encryption.md](encryption.md).

## CSRF Protection

`Core\Security\CSRF` (available as `$this->csrf` in every controller) generates and validates
per-session tokens for state-changing requests. See [csrf-protection.md](csrf-protection.md) for
token generation, validation, and form integration.

## Password Hashing (v1.2.7+)

`Core\Security\Password` wraps PHP's native `password_hash()`/`password_verify()` so applications
don't need to pick an algorithm or reach for `Core\Security\Encryption` (which is for symmetric
encryption/decryption of arbitrary data, not for password storage - see
[encryption.md](encryption.md) for when to use which).

```php
use Core\Security\Password;

// When a user registers / changes their password:
$hash = Password::hash($plainPassword); // Argon2id if available, bcrypt fallback

// When a user logs in:
if (Password::verify($plainPassword, $storedHash)) {
    if (Password::needsRehash($storedHash)) {
        // Algorithm/options changed since this hash was created - store a fresh one.
        $newHash = Password::hash($plainPassword);
    }
    // ... proceed with login
}
```

A matching `password` validator rule is available for basic length checks before hashing (it does
not hash - hashing stays the caller's responsibility, on the plaintext, right before storage):

```php
$validator->rule('password', 'password', 12); // require at least 12 characters
```

**Never** store the plaintext password or roll your own hashing - always go through `Password`.

## Trusted Proxies & Client IP (v1.2.5+)

`Core\Http\Client::getIpAddress()` returns the client's real IP for logging, rate limiting, and
session-fixation checks (`Core\Http\Session` uses it to detect IP changes mid-session). By
default it **only** trusts `REMOTE_ADDR`, ignoring `X-Forwarded-For`, `CF-Connecting-IP`, and
similar headers - because any client can forge those headers directly unless a trusted reverse
proxy strips and re-sets them.

If your app sits behind a reverse proxy or CDN (nginx, Cloudflare, a load balancer), register the
proxy's IP(s) once during bootstrap so forwarding headers are honored **only** when the direct
connection actually comes from that proxy:

```php
// e.g. in Config/Boot.php or your app's bootstrap, before routing runs
use Core\Http\Client;

Client::setTrustedProxies([
    '127.0.0.1',      // nginx running on the same host
    '10.0.0.5',       // internal load balancer
    // Cloudflare's published IP ranges, if you terminate TLS behind Cloudflare
]);
```

**Nginx example** - if nginx proxies to PHP-FPM on the same box, `REMOTE_ADDR` as seen by PHP is
`127.0.0.1`; register that so `X-Forwarded-For` (which nginx should set from the real client IP)
is trusted:

```nginx
location / {
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_pass http://127.0.0.1:9000;
}
```

**Cloudflare example** - Cloudflare sets `CF-Connecting-IP` to the real visitor IP. Trust
Cloudflare's edge IPs (published at cloudflare.com/ips) as the proxy list so that header is honored
only when the request truly came through Cloudflare's network, not a forged header hitting your
origin directly.

Without `setTrustedProxies()` configured, `getIpAddress()` always falls back to `REMOTE_ADDR` -
correct and safe, but it will report the proxy's IP instead of the visitor's if you're behind one.
