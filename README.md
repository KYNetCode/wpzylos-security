# WPZylos Security

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-KYNetCode-181717?logo=github)](https://github.com/KYNetCode/wpzylos-security)

Security primitives for the WPZylos framework — Nonce, Gate, Sanitizer, RateLimiter, UploadSecurity, Middleware, and escaping helpers.

📖 **[Full Documentation](https://wpzylos.com)** | 🐛 **[Report Issues](https://github.com/KYNetCode/wpzylos-security/issues)**

---

## ✨ Features

- **Nonce** — CSRF protection with plugin-scoped nonce generation and verification
- **Gate** — Capability-based authorization (`can`, `cannot`, `authorize`, `isAdmin`, `canAny`, `canAll`, and more)
- **Sanitizer** — Input sanitization (`text`, `email`, `url`, `int`, `float`, `bool`, `html`, `slug`, `key`, `filename`, `sanitizeMany`, etc.)
- **RateLimiter** — Request throttling using WordPress transients (`hit`, `attempt`, `forUser`, `forIp`)
- **UploadSecurity** — Secure file uploads with nonce/capability checks, MIME validation, and size limits
- **Middleware** — `AuthMiddleware` and `NonceMiddleware` for the request pipeline
- **Escaping Helpers** — Global template helpers (`wpzylos_e`, `wpzylos_ea`, `wpzylos_eu`, `wpzylos_ej`, `wpzylos_kses`, `wpzylos_e_json`)

---

## 📋 Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | ^8.0    |
| WordPress   | 6.0+    |

---

## 🚀 Installation

```bash
composer require KYNetCode/wpzylos-security
```

---

## ⚙️ Service Provider Registration

Register `SecurityServiceProvider` in your plugin's service providers:

```php
use WPZylos\Framework\Security\SecurityServiceProvider;

'providers' => [
    SecurityServiceProvider::class,
],
```

This registers **5 services** as singletons, each with a class binding and a string alias:

| Class Binding | String Alias |
| ------------- | ------------ |
| `Nonce::class` | `'nonce'` |
| `Gate::class` | `'gate'` |
| `Sanitizer::class` | `'sanitizer'` |
| `RateLimiter::class` | `'rate-limiter'` |
| `UploadSecurity::class` | `'upload-security'` |

---

## 📖 Quick Start

```php
use WPZylos\Framework\Security\Nonce;
use WPZylos\Framework\Security\Gate;
use WPZylos\Framework\Security\Sanitizer;
use WPZylos\Framework\Security\RateLimiter;
use WPZylos\Framework\Security\UploadSecurity;
```

### Nonce (CSRF Protection)

```php
$nonce = $app->make(Nonce::class);

// Create a nonce token
$token = $nonce->create('save_settings');

// Verify a nonce
if ($nonce->verify($_POST['_wpnonce'], 'save_settings')) {
    // Valid — proceed
}

// Output nonce field in a form
$nonce->field('save_settings');

// Add nonce to a URL
$url = $nonce->url($actionUrl, 'delete_item');
```

### Gate (Authorization)

```php
$gate = $app->make(Gate::class);

// Check capabilities
if ($gate->can('edit_posts')) { /* ... */ }
if ($gate->cannot('manage_options')) { /* ... */ }
if ($gate->isAdmin()) { /* ... */ }

// Abort if unauthorized (calls wp_die with 403)
$gate->authorize('manage_options');

// Check specific user
if ($gate->userCan($userId, 'edit_posts')) { /* ... */ }

// Multiple capabilities
if ($gate->canAny(['edit_posts', 'upload_files'])) { /* ... */ }
if ($gate->canAll(['edit_posts', 'publish_posts'])) { /* ... */ }

// User state
if ($gate->isLoggedIn()) {
    $id = $gate->userId();
}
```

### Sanitizer (Input Sanitization)

```php
$sanitizer = $app->make(Sanitizer::class);

// Individual sanitizers
$title = $sanitizer->text($_POST['title']);
$email = $sanitizer->email($_POST['email']);
$price = $sanitizer->float($_POST['price']);

// Bulk sanitization
$clean = $sanitizer->sanitizeMany($_POST, [
    'title'   => 'text',
    'email'   => 'email',
    'content' => 'html',
    'post_id' => 'absint',
    'active'  => 'bool',
]);
```

### RateLimiter (Throttling)

```php
$limiter = $app->make(RateLimiter::class);

// Key scoped to current user (falls back to IP for guests)
$key = $limiter->forUser('api_call');

if ($limiter->tooManyAttempts($key)) {
    $wait = $limiter->availableIn($key);
    wp_die("Rate limited. Try again in {$wait} seconds.");
}
$limiter->hit($key);

// Or use attempt() with callbacks
$result = $limiter->attempt($key, function () {
    return do_expensive_operation();
}, function (int $waitSeconds) {
    wp_send_json_error(['retry_after' => $waitSeconds], 429);
});

// Clear on success (e.g., after login)
$limiter->clear($key);
```

### UploadSecurity (File Uploads)

```php
$upload = $app->make(UploadSecurity::class);

// Simple upload
$result = $upload->handle($_FILES['file'], 'upload_action');

if (is_wp_error($result)) {
    echo $result->get_error_message();
} else {
    $url = $result['url'];
}

// With custom MIME types and size limit
$result = $upload
    ->allowMimes(['jpg|jpeg' => 'image/jpeg', 'png' => 'image/png'])
    ->maxSize(2 * 1024 * 1024) // 2 MB
    ->handle($_FILES['avatar'], 'upload_avatar');

// Multiple files
$results = $upload->handleMultiple($_FILES['documents'], 'upload_docs');
```

### Escaping Helpers (Templates)

```php
// HTML escape
<h1><?php echo wpzylos_e($title); ?></h1>

// Attribute escape
<input value="<?php echo wpzylos_ea($value); ?>">

// URL escape
<a href="<?php echo wpzylos_eu($link); ?>">Link</a>

// JavaScript escape
<script>var msg = '<?php echo wpzylos_ej($msg); ?>';</script>

// HTML filtering
<?php echo wpzylos_kses($content); ?>           // post-level tags
<?php echo wpzylos_kses($content, 'strip'); ?>  // strip all HTML

// Safe JSON embedding
<script>var config = <?php echo wpzylos_e_json($data); ?>;</script>
```

---

## 📦 Related Packages

| Package                                                                    | Description            |
| -------------------------------------------------------------------------- | ---------------------- |
| [wpzylos-core](https://github.com/KYNetCode/wpzylos-core)             | Application foundation |
| [wpzylos-validation](https://github.com/KYNetCode/wpzylos-validation) | Input validation       |
| [wpzylos-scaffold](https://github.com/KYNetCode/wpzylos-scaffold)     | Plugin template        |

---

## 📖 Documentation

For comprehensive documentation, tutorials, and API reference, visit **[wpzylos.com](https://wpzylos.com)**.

---

## ☕ Support the Project

- [GitHub Sponsors](https://github.com/sponsors/KYNetCode)
- [PayPal Donate](https://www.paypal.com/donate/?hosted_button_id=66U4L3HG4TLCC)

---

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

**Made with ❤️ by [KYNetCode](https://github.com/KYNetCode)**
