<?php

class GameRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addGame(array $data): mixed
    {
        $params = [
            'title'         => $data['title'],
            'developer'     => $data['developer'],
            'genres'        => $data['genres'],
            'release_year'  => $data['release_year'],
            'steam'         => $data['steam'],
            'title_url'     => $data['title_url'],
            'first_user_id' => $data['user_id'],
            'last_user_id'  => $data['user_id']
        ];

        $query = 'INSERT INTO games (title, developer, genres, release_year, steam, title_url, first_user_id, last_user_id)
                  VALUES (:title, :developer, :genres, :release_year, :steam, :title_url, :first_user_id, :last_user_id)';

        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute($params);

        if (!$result)
        {
            return false;
        }

        $query = 'UPDATE score SET add_game = add_game + 1 WHERE user_id = :user_id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $data['user_id'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findGames(array $filters = [], int $limit, int $offset = 0): array
    {
        $query = 'SELECT * FROM games';
        $params = [];
        $conditions = [];

        if (!empty($filters['developer']))
        {
            $conditions[] = 'developer LIKE :developer';
            $params['developer'] = "%{$filters['developer']}%";
        }

        if (!empty($filters['genre']))
        {
            $conditions[] = 'genres LIKE :genre';
            $params['genre'] = "%{$filters['genre']}%";
        }

        if (!empty($filters['release']) && preg_match('/^\d{4}$/', $filters['release']))
        {
            $conditions[] = 'release_year = :release';
            $params['release'] = $filters['release'];
        }

        if (!empty($filters['search']))
        {
            $conditions[] = 'MATCH(title, developer) AGAINST(:search)';
            $params['search'] = $filters['search'];
        }

        if (!empty($conditions))
        {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= " LIMIT {$offset}, {$limit}";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountGames(): int
    {
        $query = 'SELECT COUNT(id) FROM games';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getGameDetails($gameId, $userId = null): ?array
    {
        $game = $this->fetchGame($gameId, $userId);

        if (!$game)
        {
            return null;
        }

        $game['friends'] = $userId ? $this->fetchFriendsActivity($gameId, $userId) : [];
        $game['related_games'] = $this->fetchRelatedGames($gameId);

        return $game;
    }

    public function getGameId(string $title, string $year): int|false
    {
        $query = 'SELECT id FROM games WHERE title = :title AND release_year = :release_year';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('title', $title);
        $stmt->bindValue('release_year', $year);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getGames(int $limit, int $offset = 0): array
    {
        $query = 'SELECT * FROM games LIMIT :offset, :limit';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopRatedGames(int $limit): array
    {
        $query = 'SELECT games.*, SUM(rating) as rating FROM user_game_list
                  INNER JOIN games ON games.id = user_game_list.game_id
                  WHERE rating >= 1 GROUP BY games.id ORDER BY rating DESC LIMIT :limit';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserGames(int $userId, int $limit, int $offset = 0): array
    {
        $query = 'SELECT id, title, media, title_url FROM games WHERE first_user_id = :user_id LIMIT :offset, :limit';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateGame(array $data): bool
    {
        $params = [
            'id'            => $data['id'],
            'title'         => $data['title'],
            'developer'     => $data['developer'],
            'genres'        => $data['genres'],
            'release_year'  => $data['release_year'],
            'steam'         => $data['steam'],
            'title_url'     => $data['title_url'],
            'last_user_id'  => $data['user_id']
        ];

        $query = 'UPDATE games SET
                  title = :title,
                  developer = :developer,
                  genres = :genres,
                  release_year = :release_year,
                  steam = :steam,
                  title_url = :title_url,
                  last_user_id = :last_user_id
                  WHERE id = :id';

        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute($params);

        if (!$result)
        {
            return false;
        }

        $query = 'UPDATE score SET update_game = update_game + 1 WHERE user_id = :user_id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $data['user_id'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function fetchGame($gameId, $userId = null): ?array
    {
        $params = ['id' => $gameId];
        $query = 'SELECT * FROM games WHERE id = :id';

        if ($userId)
        {
            $params['user_id'] = $userId;
            $query = 'SELECT games.*, list.playlist, list.played, list.rating, list.liked
                      FROM games
                      LEFT JOIN user_game_list AS list ON games.id = list.game_id AND list.user_id = :user_id
                      WHERE games.id = :id';
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchFriendsActivity($gameId, $userId): array
    {
        $query = 'SELECT IF(user_id1 = :user_id, user_id2, user_id1) AS id
                  FROM friendship WHERE :user_id IN (user_id1, user_id2)';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $friendIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($friendIds))
        {
            return [];
        }

        $params = [
            'game_id' => $gameId,
            'playlist' => 0,
            'played' => 0,
            'rating' => 0,
            'liked' => 0
        ];

        $placeholders = [];
        
        foreach ($friendIds as $index => $id) 
        {
            $key = ":friend_$index";
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $inClause = implode(', ', $placeholders);
        $query = "SELECT personaname, avatarhash, l.*
                  FROM user_game_list AS l
                  LEFT JOIN users ON users.id = l.user_id
                  WHERE l.game_id = :game_id
                  AND (l.playlist != :playlist OR l.played != :played OR l.rating != :rating OR l.liked != :liked)
                  AND users.id IN ($inClause)";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchRelatedGames($gameId)
    {
        $query = 'SELECT g.id, g.title, g.release_year, g.media, g.title_url
                  FROM related_games AS r
                  INNER JOIN games AS g ON g.id = IF (r.game_id1 = :game_id, r.game_id2, r.game_id1)
                  WHERE :game_id IN (r.game_id1, r.game_id2)
                  ORDER BY g.release_year
                  LIMIT 24';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['game_id' => $gameId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}