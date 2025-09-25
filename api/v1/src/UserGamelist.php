<?php

class UserGamelist
{
    private PDO $pdo;
    private $loggedIn;
    private $userId;

    public function __construct(PDO $pdo, $loggedIn = false, $userId = null)
    {
        $this->pdo = $pdo;
        $this->loggedIn = $loggedIn;
        $this->userId = $userId;
    }

    public function getGames(array $filters = [], int $limit, int $offset = 0): array
    {
        [$conditions, $operator, $params] = $this->buildConditions($filters); 

        $query = 'SELECT games.*, playlist, played, rating, liked
                  FROM user_game_list AS list
                  INNER JOIN games ON games.id = list.game_id
                  WHERE list.user_id = :user_id';
        $query .= ' AND (' . implode($operator, $conditions) . ')';

        if (!empty($filters['rating']) && $filters['rating'] == 1)
        {
            $query .= ' ORDER BY list.rating DESC';
        }

        $query .= " LIMIT {$offset}, {$limit}";

        $params['user_id'] = $this->userId;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    public function getGamesFriends($friendId, array $filters = [], int $limit, int $offset = 0): array
    {
        [$conditions, $operator, $params] = $this->buildConditions($filters);

        $query = 'SELECT  games.id, games.title, games.media, games.title_url, list.playlist, list.played, list.rating, list.liked,
                    gl.playlist AS gl_playlist,
                    gl.played AS gl_played,
                    gl.rating AS gl_rating,
                    gl.liked AS gl_liked
                    FROM user_game_list AS list
                    INNER JOIN games ON games.id = list.game_id
                    LEFT JOIN user_game_list AS gl ON gl.game_id = list.game_id AND gl.user_id = :user_id
                    WHERE list.user_id = :friend_id';
        $query .= ' AND (' . implode($operator, $conditions) . ')';

        if (!empty($filters['rating']) && $filters['rating'] == 1)
        {
            $query .= ' ORDER BY list.rating DESC';
        }

        $query .= " LIMIT {$offset}, {$limit}";

        $params['user_id'] = $this->userId;
        $params['friend_id'] = $friendId;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGamesRatedByFriends(int $userId): array
    {
        $query =   'SELECT g.id, g.title, g.release_year, g.media, g.title_url, AVG(ugl.rating) AS avg_rating, COUNT(*) AS rating_count
                    FROM user_game_list AS ugl
                    INNER JOIN games AS g ON g.id = ugl.game_id
                    WHERE 
                    ugl.rating > 0
                    AND ugl.user_id IN (
                        SELECT 
                            CASE 
                                WHEN f.user_id1 = :user_id THEN f.user_id2
                                ELSE f.user_id1
                            END
                        FROM friendship AS f
                        WHERE f.user_id1 = :user_id OR f.user_id2 = :user_id
                    )
                    AND ugl.game_id NOT IN (
                        SELECT game_id
                        FROM user_game_list
                        WHERE user_id = :user_id AND played = 1
                    )
                    GROUP BY g.id, g.title
                    HAVING COUNT(*) >= 2
                    ORDER BY avg_rating DESC, rating_count DESC
                    LIMIT 8';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGamesRatedByUsers(int $userId): array
    {
        $query =   'SELECT g.id, g.title, g.release_year, g.media, g.title_url, AVG(ugl.rating) AS avg_rating, COUNT(*) AS rating_count
                    FROM user_game_list AS ugl
                    INNER JOIN games AS g ON g.id = ugl.game_id
                    WHERE 
                    ugl.rating > 0
                    AND ugl.game_id NOT IN (
                        SELECT game_id
                        FROM user_game_list
                        WHERE user_id = :user_id AND played = 1
                    )
                    GROUP BY g.id, g.title
                    HAVING COUNT(*) >= 2
                    ORDER BY avg_rating DESC, rating_count DESC
                    LIMIT 8';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function set(array $data): bool
    {
        $listId = $this->getByGameId($data['game_id']);

        if ($listId === false)
        {
            return $this->insertNewRecord($data);
        }
        else
        {
            $data['id'] = $listId;

            return $this->updateExistingRecord($data);
        }
    }

    private function buildConditions(array $filters): array
    {
        $conditions = [];
        $params = [];
        $operator = ' AND ';

        if (!empty($filters['playlist']))
        {
            $conditions[] = 'list.playlist = :playlist';
            $params['playlist'] = $filters['playlist'];
        }

        if (!empty($filters['played']))
        {
            $conditions[] = 'list.played = :played';
            $params['played'] = $filters['played'];
        }

        if (!empty($filters['rating']))
        {
            $conditions[] = 'list.rating >= :rating';
            $params['rating'] = $filters['rating'];
        }

        if (!empty($filters['liked']))
        {
            $conditions[] = 'list.liked = :liked';
            $params['liked'] = $filters['liked'];
        }

        if (empty($conditions))
        {
            $params['playlist'] = 0;
            $params['played'] = 0;
            $params['rating'] = 0;
            $params['liked'] = 0;
            $conditions[] = 'list.playlist != :playlist';
            $conditions[] = 'list.played != :played';
            $conditions[] = 'list.rating != :rating';
            $conditions[] = 'list.liked != :liked';
            $operator = ' OR ';
        }

        return [$conditions, $operator, $params];
    }

    private function getByGameId($gameId)
    {
        $query = 'SELECT id FROM user_game_list WHERE user_id = :user_id AND game_id = :game_id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindValue('game_id', $gameId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function insertNewRecord(array $data)
    {
        $params = [
            'user_id'  => $this->userId,
            'game_id'  => $data['game_id'],
            'playlist' => $data['playlist'] ?? 0,
            'played'   => $data['played'] ?? 0,
            'rating'   => $data['rating'] ?? 0,
            'liked'    => $data['liked'] ?? 0
        ];

        $query = 'INSERT INTO user_game_list (user_id, game_id, playlist, played, rating, liked)
                  VALUES (:user_id, :game_id, :playlist, :played, :rating, :liked)';
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    private function updateExistingRecord(array $data)
    {
        $conditions = [];
        $params['id'] = $data['id'];

        if (isset($data['playlist']))
        {
            $conditions[] = 'playlist = :playlist';
            $params['playlist'] = $data['playlist'];
        }

        if (isset($data['played']))
        {
            $conditions[] = 'played = :played';
            $params['played'] = $data['played'];
        }

        if (isset($data['rating']) && is_numeric($data['rating']))
        {
            $conditions[] = 'rating = :rating';
            $params['rating'] = $data['rating'];
        }

        if (isset($data['liked']))
        {
            $conditions[] = 'liked = :liked';
            $params['liked'] = $data['liked'];
        }

        if (empty($conditions))
        {
            return false;
        }

        $subquery = implode(', ', $conditions);
        $query = "UPDATE user_game_list SET {$subquery} WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

}