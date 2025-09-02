<?php

header('Content-Type: application/json; charset=utf-8');



$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$selector = bin2hex(random_bytes(16));
$validator = bin2hex(random_bytes(32));
$token = $selector . ':' . $validator;

$hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
$expire_date = strtotime('+1 month');

require ROOT_DIR . '/pdo.php';

$params = [
    'user_id' => $dados['user_id'],
    'selector' => $selector,
    'hashed_validator' => $hashed_validator,
    'login_date' => date('Y-m-d H:i:s'),
    'expire_date' => date('Y-m-d H:i:s', $expire_date),
    'user_ip' => $dados['address']
];

$query = 'INSERT INTO login_log (user_id, selector, hashed_validator, login_date, expire_date, user_ip)
          VALUES (:user_id, :selector, :hashed_validator, :login_date, :expire_date, :user_ip)';

$stmt = $pdo->prepare($query);
$result = $stmt->execute($params);

if ($result)
{
    $data = [
        'expire_date' => $expire_date,
        'token' => $token
    ];
}

echo json_encode($data ?? false);