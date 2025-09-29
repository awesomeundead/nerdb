<?php

class UserService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function countFriends($userId)
    {
        $query = 'SELECT COUNT(*) FROM friendship WHERE user_id1 = :user_id OR user_id2 = :user_id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function createAutoLogin(int $user_id): void
    {
        $address = $_SERVER['HTTP_CLIENT_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['HTTP_X_FORWARDED']
                ?? $_SERVER['HTTP_FORWARDED_FOR']
                ?? $_SERVER['HTTP_FORWARDED']
                ?? $_SERVER['REMOTE_ADDR']
                ?? 'UNKNOWN';

        $service = new LoginService($this->pdo);
        $data = $service->addLog($user_id, $address);

        if ($data)
        {
            setcookie('login', $data['token'], ['expires' => $data['expire_date'], 'path' => '/', 'httponly' => true]);
        }
    }

    public function createSession(array $data): void
    {
        Session::set('logged_in', true);
        Session::set('user_id', $data['id']);
        Session::set('steamid', $data['steamid']);
        Session::set('personaname', $data['personaname']);
        Session::set('avatarhash', $data['avatarhash']);
    }

    public function getUserById($id): ?array
    {
        $query = 'SELECT * FROM users WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserBySteam($steamid): ?array
    {
        $query = 'SELECT * FROM users WHERE steamid = :steamid';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':steamid', $steamid, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getFriendById(int $userId, int $friendId): ?array
    {
        $query =   'SELECT users.* FROM users
                    WHERE users.id = :friend_id AND EXISTS (
                        SELECT 1 FROM friendship
                        WHERE (user_id1 = :user_id AND user_id2 = :friend_id)
                        OR (user_id1 = :friend_id AND user_id2 = :user_id)
                    )';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':friend_id', $friendId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getFriends(int $userId): array
    {
        $query = 'SELECT IF(user_id1 = :user_id, user_id2, user_id1) AS id
                  FROM friendship WHERE :user_id IN (user_id1, user_id2)';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $list = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($list))
        {
            return [];
        }

        foreach ($list as $index => $item)
        {
            $key = ":placeholder_{$index}";
            $keys[] = $key;
            $params[$key] = $item;
        }

        $placeholders = implode(', ', $keys);

        $query = "SELECT * FROM users WHERE id IN ({$placeholders})";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getScore($userId): array
    {
        $query = 'SELECT *,
                  (add_movie * 10) AS add_movie_total, (add_game * 10) AS add_game_total, (update_movie * 2) AS update_movie_total, (update_game * 2) AS update_game_total,
                  (add_movie * 10 + add_game * 10 + update_movie * 2 + update_game * 2 + rating_movie + rating_game) AS total
                  FROM score WHERE user_id = :user_id';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addNewUser(array $player): mixed
    {
        $params = [
            'steamid'     => $player['steamid'],
            'personaname' => $player['personaname'],
            'avatarhash'  => $player['avatarhash'],
            'name'        => $player['realname'] ?? ''
        ];

        $query = 'INSERT INTO users (steamid, personaname, avatarhash, name, created_date) VALUES (:steamid, :personaname, :avatarhash, :name, NOW())';

        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute($params);

        if (!$result)
        {
            return false;
        }

        $id = $this->pdo->lastInsertId();

        $query = 'INSERT INTO score (user_id, add_movie, add_game, update_movie, update_game, rating_movie, rating_game) VALUES (:user_id, 0, 0, 0, 0, 0, 0)';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $id;
    }

    public function updateUser(array $player): bool
    {
        $params = [
            'steamid'     => $player['steamid'],
            'personaname' => $player['personaname'],
            'avatarhash'  => $player['avatarhash'],
            'name'        => $player['realname'] ?? ''
        ];

        $query = 'UPDATE users SET personaname = :personaname, avatarhash = :avatarhash, name = :name WHERE steamid = :steamid';

        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    public function updateUserFriendship(int $userId, int $steamid)
    {
        $steam_api_key = (require ROOT_DIR . '/config.php')['steam_api_key'];
        /*
        * Pode ocorrer um erro se o steamid estiver errado ou se a lista de amigos não estiver pública.
        */
        $context = stream_context_create(['http' => ['ignore_errors' => true]]);
        $response = file_get_contents("https://api.steampowered.com/ISteamUser/GetFriendList/v1/?key={$steam_api_key}&steamid={$steamid}&relationship=friend", false, $context);
        $data = json_decode($response, true);
        $list = $data['friendslist']['friends'] ?? false;

        if (!$list)
        {
            return;
        }

        foreach ($list as $index => $item)
        {
            $key = ":placeholder_{$index}";
            $keys[] = $key;
            $params[$key] = $item['steamid'];
        }

        $placeholders = implode(', ', $keys);

        $query = "SELECT id FROM users WHERE steamid IN ({$placeholders})";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $list = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($list))
        {
            return;
        }

        foreach ($list as $friend)
        {
            $params = [
                'me' => $userId,
                'friend' => $friend
            ];
            $query = "SELECT 1 FROM friendship
                      WHERE (user_id1 = :me AND user_id2 = :friend) OR (user_id1 = :friend AND user_id2 = :me) LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            if ($stmt->fetchColumn() === false)
            {
                $query = 'INSERT INTO friendship (user_id1, user_id2) VALUES (LEAST(:me, :friend), GREATEST(:me, :friend))';
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params);
            }
        }
    }
}