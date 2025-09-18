<?php

class LoginService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getLog($selector)
    {
        $query = 'SELECT * FROM login_log WHERE selector = :selector';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('selector', $selector);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addLog($user_id, $address)
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $token = $selector . ':' . $validator;

        $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
        $expire_date = strtotime('+1 month');

        $params = [
            'user_id' => $user_id,
            'selector' => $selector,
            'hashed_validator' => $hashed_validator,
            'login_date' => date('Y-m-d H:i:s'),
            'expire_date' => date('Y-m-d H:i:s', $expire_date),
            'user_ip' => $address
        ];

        $query = 'INSERT INTO login_log (user_id, selector, hashed_validator, login_date, expire_date, user_ip)
                  VALUES (:user_id, :selector, :hashed_validator, :login_date, :expire_date, :user_ip)';

        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute($params);

        if ($result)
        {
            return [
                'expire_date' => $expire_date,
                'token' => $token
            ];
        }

        return false;
    }
}