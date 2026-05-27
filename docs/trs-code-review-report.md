# TRS REST Services – Structural Code Review Report
**Branch:** `test-micro`

---

## Summary

The micro MVC framework has a sound foundation but contains several issues across Dependency Injection configuration, routing, controller conventions, and service design. Below are the findings and fixes applied.

---

## 1. Dependency Injection – `AppContainer.php`

### 1a. ContainerBuilder incorrectly assigned to class property (Bug)
**File:** `src/Core/AppContainer.php` (lines 28–38)

The `ContainerBuilder` was assigned to `$this->container` during the cache-building phase, only to be immediately overwritten by `new \AppCachedContainer()`. This is misleading and assigns a `ContainerBuilder` to a property typed as `Container`.

**Fix:** Changed to a local `$containerBuilder` variable, keeping `$this->container` only for the final cached instance.

### 1b. Incorrect PHPDoc return type
**File:** `src/Core/AppContainer.php` (line 48)

The `@return ContainerBuilder` docblock contradicted the actual return type hint of `Container`. The returned value is always `\AppCachedContainer` (which extends `Container`, not `ContainerBuilder`).

**Fix:** Removed the misleading docblock since the method signature already declares the correct return type.

---

## 2. Router – `Router.php`

### 2a. Silent failure on missing class or method (Critical Bug)
**File:** `src/Core/Router.php` (lines 43–56)

The original code checked `class_exists()` and `method_exists()` with `if` statements but had **no `else` branches**. If a controller class or method did not exist, the request would silently produce an empty response with no error.

**Fix:** Replaced with explicit exceptions that throw `ResourceNotFoundException` for missing classes/methods and `RuntimeException` for malformed controller strings.

### 2b. Redundant exception catch hierarchy
**File:** `src/Core/Router.php` (line 58)

```php
catch (MethodNotAllowedException|ResourceNotFoundException|Exception $e)
```

`Exception` already catches all exceptions, making the preceding types redundant. Additionally, all exceptions were handled identically (log + rethrow), with no differentiation between a 404 and a 405.

**Fix:** Separated into distinct `catch` blocks returning proper HTTP status codes (404 for `ResourceNotFoundException`, 405 for `MethodNotAllowedException`).

### 2c. Fragile parameter extraction
**File:** `src/Core/Router.php` (line 47)

```php
$params = array_merge((array) array_slice($matcher, 2));
```

- `array_merge()` with a single argument is a no-op
- `array_slice()` on an associative array by position is fragile — it assumes `_controller` and `_route` are always the first two keys
- The `(array)` cast is redundant as `array_slice()` already returns an array

**Fix:** Replaced with `array_filter()` that explicitly removes keys prefixed with `_`, which is the Symfony routing convention for internal parameters.

### 2d. Misleading property name `$containerBuilder`
**File:** `src/Core/Router.php` (line 21)

The property was named `$containerBuilder` but typed as `Container` — the actual value is a cached container, not a builder.

**Fix:** Renamed to `$container`.

### 2e. No controller format validation
The `explode('::', ...)` call had no validation that the result contained exactly two elements. A misconfigured route (e.g., missing `::`) would cause an undefined index error.

**Fix:** Added a count check with a descriptive `RuntimeException`.

---

## 3. Controllers

### 3a. `self::` used to call non-static method (Bug)
**Files:** `src/Controller/ProductController.php` (line 18), `src/Controller/PdoEntityGeneratorController.php` (line 10)

Both controllers called `self::render(...)` but `render()` is an instance method on `AbstractController`, not a static method. While PHP permits this, it is semantically incorrect and would break if `render()` relied on late static binding or polymorphism.

**Fix:** Changed to `$this->render(...)`.

### 3b. Missing return type declarations
**Files:** `ProductController::showAction()`, `PdoEntityGeneratorController::indexAction()`

Both methods lacked a `: void` return type, inconsistent with `HomeController::indexAction()` and `ErrorPageController::errorAction()`.

**Fix:** Added `: void` return types.

---

## 4. AppLogger – `Service/AppLogger.php`

### 4a. Missing `declare(strict_types=1)`
Every other PHP file in `src/` declared strict types except `AppLogger.php`.

**Fix:** Added the declaration.

### 4b. Untyped `$errorLevel` parameter
```php
public function __construct(string $loggerName = 'app', $errorLevel = Logger::WARNING)
```

The `$errorLevel` parameter had no type hint, inconsistent with strict typing elsewhere.

**Fix:** Added `int` type hint.

### 4c. Ineffective `setLogFile()` method
The `setLogFile()` method updated the `$logFile` property but did **not** reconfigure the `StreamHandler` that was already created in the constructor. Calling `setLogFile()` would change the property value but logs would continue writing to the original file — a silent logic bug.

**Fix:** Removed `setLogFile()` and `getLogFile()` as they were misleading. The log file path is now a local variable in the constructor.

### 4d. Property default using runtime constant
```php
private string $logFile = LOG_PATH.'/app.log';
```

`LOG_PATH` is defined at runtime via `define()`. Using it as a property default value is fragile — if the class were ever autoloaded before `Application::loadPathConstants()` runs, it would cause a fatal error.

**Fix:** Moved to a local variable initialised inside the constructor.

---

## 5. Other Observations (Not Fixed – For Consideration)

### 5a. `AbstractController::render()` uses a static local variable
The Twig environment is cached in a `static $twig` variable, creating a hidden singleton. This couples template rendering to a static lifecycle rather than DI. Consider injecting the Twig `Environment` via the container.

### 5b. `AbstractController::render()` duplicates router creation
The render method creates its own `Router` and `UrlGenerator` for Twig path/url functions. This duplicates the routing setup already done in `Router::resolve()`. The `UrlGenerator` should be registered as a service and injected.

### 5c. `ErrorPageController` instantiated outside DI
In `public/index.php`, `ErrorPageController` is created directly with `new`. This is understandable (the DI container may not be available during a bootstrap error), but worth noting.

### 5d. `Application::getBaseUrl()` trusts `HTTP_X_FORWARDED_HOST`
This header can be spoofed by clients. In production, this should be validated against a whitelist of trusted proxies.

### 5e. `public/index.php` had trailing blank lines
Removed 7 unnecessary trailing blank lines.

---

## Files Changed

| File | Changes |
|------|---------|
| `src/Core/AppContainer.php` | Local variable for ContainerBuilder, removed incorrect docblock |
| `src/Core/Router.php` | Error handling, parameter extraction, naming, validation |
| `src/Controller/ProductController.php` | `self::` → `$this->`, added `: void` |
| `src/Controller/PdoEntityGeneratorController.php` | `self::` → `$this->`, added `: void` |
| `src/Service/AppLogger.php` | strict_types, typed param, removed dead methods |
| `src/Application.php` | Simplified boolean returns |
| `public/index.php` | Removed trailing blank lines |
