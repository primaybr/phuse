# Encryption in Phuse

`Core\Security\Encryption` provides symmetric encryption/decryption of arbitrary data using
`aes-256-cbc` (32-byte key, 16-byte IV). Use it for data you need to **decrypt back** later
(tokens, encrypted columns, cookie payloads) - it is not for passwords. For password storage, use
`Core\Security\Password` (see [security.md](security.md)), which is one-way by design.

> **v1.2.8 fix**: earlier versions used `aes-256-cbc-hmac-sha256` with a SHA-512-derived key.
> Neither actually worked - the cipher fails under `openssl_encrypt()`/`openssl_decrypt()` on
> OpenSSL 3.x, and the 64-byte SHA-512 key didn't match any AES-256 cipher's required 32-byte key
> length either way. `encrypt()`/`decrypt()` are functional as of v1.2.8; anything encrypted with
> a pre-1.2.8 build was never successfully encrypted in the first place, so there is no migration
> concern for existing data.

## Generating a Key and Salt

Each encryption operation needs a key and a salt (used as the IV). Generate both from a secret
string - typically an app-wide secret stored outside version control (an environment variable or a
`Config` value), not hardcoded:

```php
use Core\Security\Encryption;

$encryption = new Encryption();

// $appSecret should come from config/env, not be hardcoded in application code
$combined = $encryption->generateKey($appSecret); // "<hex key>/<hex salt>"
[$key, $salt] = explode('/', $combined);
```

`generateKey()` derives a key via SHA-256 (32 bytes, matching `aes-256-cbc`'s required key length)
and generates a fresh random salt (IV) sized for the cipher every time it's called - so if you need
to decrypt data later, you must persist the salt alongside the ciphertext (the key can be
re-derived from the same secret string, but the salt cannot).

## Encrypting and Decrypting

```php
$plaintext = 'sensitive data';

$ciphertext = $encryption->encrypt($plaintext, $key, $salt);

// ... store $ciphertext and $salt together (e.g. "$salt:$ciphertext" or separate columns) ...

$decrypted = $encryption->decrypt($ciphertext, $key, $salt);
// $decrypted === $plaintext
```

Both `$key` and `$salt` must be the same hex strings used for encryption - `encrypt()`/`decrypt()`
internally `hex2bin()` them before passing to OpenSSL.

`decrypt()` returns `string|false` - it returns `false` (not garbage plaintext) when given the
wrong key/salt or corrupted ciphertext, since CBC's padding validation rejects it outright. Always
check the return value before using it:

```php
$decrypted = $encryption->decrypt($ciphertext, $key, $salt);
if ($decrypted === false) {
    // wrong key/salt, or the ciphertext was tampered with/corrupted
}
```

## Key & Salt Storage Guidance

- **The app secret** (input to `generateKey()`) belongs in configuration/environment, never
  committed to source control. Rotating it invalidates every previously-encrypted value unless you
  keep the old secret around to decrypt legacy data during a migration.
- **The salt is per-encrypted-value**, not per-app - store it next to the ciphertext it belongs to
  (same row, or concatenated with a separator that can't appear in the hex-encoded salt, e.g.
  `$salt . ':' . $ciphertext`).
- Do not reuse a single salt across many encrypted values with the same key - `generateSalt()`
  exists precisely to avoid that; call it (via `generateKey()`) per value.

## When to Use Encryption vs. Password vs. CSRF

| Need | Use |
| --- | --- |
| Store a user's password | `Core\Security\Password` - one-way hash, never decrypted |
| Store data you must read back later (API tokens, PII at rest) | `Core\Security\Encryption` - two-way |
| Protect a form from cross-site submission | `Core\Security\CSRF` - see [csrf-protection.md](csrf-protection.md) |
