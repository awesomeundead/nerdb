<?php

class Session
{
    private static Session $session;

    private function __construct()
    {
        if (headers_sent())
        {
            throw new RuntimeException('Não é possível iniciar a sessão: cabeçalhos já enviados.');
        }

        ini_set('session.use_strict_mode', 1);

        session_set_cookie_params([
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'httponly' => true,
            'lifetime' => 3600,
            'path' => '/',
            'samesite' => 'Lax'
        ]);

        session_start();

        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            throw new RuntimeException('Não é possível iniciar a sessão: ID vazio ou cookie ausente.');
        }

        $now = time();
        $last = $_SESSION['regeneration'] ?? 0;

        if (($now - $last) > 300)
        {
            session_regenerate_id(true);
            $_SESSION['regeneration'] = $now;
        }

        $this->checkIntegrity();
    }

    private function checkIntegrity(): void
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        $address = $_SERVER['HTTP_CLIENT_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['HTTP_X_FORWARDED']
                ?? $_SERVER['HTTP_FORWARDED_FOR']
                ?? $_SERVER['HTTP_FORWARDED']
                ?? $_SERVER['REMOTE_ADDR']
                ?? 'UNKNOWN';

        $_SESSION['user_agent'] ??= $user_agent;
        $_SESSION['address'] ??= $address;

        if ($_SESSION['user_agent'] != $user_agent || $_SESSION['address'] != $address)
        {
            $_SESSION = [];
            session_destroy();
            
            //throw new RuntimeException('Sessão potencialmente sequestrada.');
        }
    }

    public static function all(): array
    {
        self::start();
        
        return $_SESSION;
    }

    public static function destroy(): void
    {        
        self::start();
        
        $_SESSION = [];

        session_destroy();
    }

    public static function get(string $key): mixed
    {        
        self::start();
        
        return $_SESSION[$key] ?? null;
    }

    public static function has(string $index): bool
    {
        self::start();

        return isset($_SESSION[$index]);
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        
        $_SESSION[$key] = $value;
    }

    public static function start(): void
    {
        if (!isset(self::$session))
        {
            self::$session = new Session();
        }
    }
}