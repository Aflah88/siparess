<?php
/**
 * ========================================
 * SIPARES - Session Management
 * ========================================
 */

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/**
 * Class Session
 * Menangani operasi session
 */
class Session
{

  /**
   * Set session value
   */
  public static function set($key, $value)
  {
    $_SESSION[$key] = $value;
  }

  /**
   * Get session value
   */
  public static function get($key, $default = null)
  {
    return $_SESSION[$key] ?? $default;
  }

  /**
   * Check if session key exists
   */
  public static function has($key)
  {
    return isset($_SESSION[$key]);
  }

  /**
   * Remove session key
   */
  public static function remove($key)
  {
    if (isset($_SESSION[$key])) {
      unset($_SESSION[$key]);
    }
  }

  /**
   * Destroy all session
   */
  public static function destroy()
  {
    session_unset();
    session_destroy();
  }

  /**
   * Regenerate session ID
   */
  public static function regenerate()
  {
    session_regenerate_id(true);
  }

  /**
   * Set flash message
   */
  public static function flash($type, $message)
  {
    $_SESSION['flash'] = [
      'type' => $type,
      'message' => $message
    ];
  }

  /**
   * Get and clear flash message
   */
  public static function getFlash()
  {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
  }

  /**
   * Check if user is logged in
   */
  public static function isLoggedIn()
  {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
  }

  /**
   * Check if admin is logged in
   */
  public static function isAdminLoggedIn()
  {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
  }

  /**
   * Get current user ID
   */
  public static function userId()
  {
    return $_SESSION['user_id'] ?? null;
  }

  /**
   * Get current admin ID
   */
  public static function adminId()
  {
    return $_SESSION['admin_id'] ?? null;
  }

  /**
   * Get current user data
   */
  public static function user()
  {
    return $_SESSION['user_data'] ?? null;
  }

  /**
   * Get current admin data
   */
  public static function admin()
  {
    return $_SESSION['admin_data'] ?? null;
  }
}

/**
 * CSRF Protection
 */
class CSRF
{

  /**
   * Generate CSRF token
   */
  public static function generate()
  {
    if (!Session::has('csrf_token')) {
      Session::set('csrf_token', bin2hex(random_bytes(32)));
    }
    return Session::get('csrf_token');
  }

  /**
   * Get hidden input field with CSRF token
   */
  public static function input()
  {
    return '<input type="hidden" name="csrf_token" value="' . self::generate() . '">';
  }

  /**
   * Verify CSRF token
   */
  public static function verify($token)
  {
    $storedToken = Session::get('csrf_token');
    if (!$storedToken || !hash_equals($storedToken, $token)) {
      return false;
    }
    return true;
  }

  /**
   * Verify and regenerate token
   */
  public static function verifyAndRegenerate($token)
  {
    $valid = self::verify($token);
    Session::remove('csrf_token');
    return $valid;
  }
}
?>

