# Breaking Changes

## v2.0.0

### What changed

- Laravel 11 support has been removed. The package now requires Laravel 12 or 13.
- Composer constraints were aligned to `laravel/framework:^12.0 || ^13.0` and `illuminate/support:^12.0 || ^13.0`.

### Why

- The package support baseline is now Laravel 12/13, matching the current CI matrix and maintainer guidance.
- Dropping Laravel 11 keeps the release line aligned with supported framework versions.

### Migration steps

1. If you are currently on Laravel 11, remain on the latest `1.x` release of this package.
2. Upgrade your application to Laravel 12 or 13.
3. Update your Composer constraint to `equidna/laravel-toolkit:^2.0`.
4. If you published `config/equidna.php`, review your route matcher overrides and keep them aligned with your application prefixes.

### Before / After

```json
{
  "require": {
    "equidna/laravel-toolkit": "^1.0"
  }
}
```

```json
{
  "require": {
    "equidna/laravel-toolkit": "^2.0"
  }
}
```

### Upgrade note

- Laravel 11 installations should not attempt this upgrade.
- No public API signatures changed for Laravel 12/13 users.
