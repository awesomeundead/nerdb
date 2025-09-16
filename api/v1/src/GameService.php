<?php

class GameService
{
    private $pdo;
    private $loggedIn;
    private $userId;

    public function __construct($pdo, $loggedIn = false, $userId = null)
    {
        $this->pdo = $pdo;
        $this->loggedIn = $loggedIn;
        $this->userId = $userId;
    }

    public function getGameDetails($gameId)
    {
        $game = $this->fetchGame($gameId);

        if (!$game)
        {
            return null;
        }

        $game['friends'] = $this->loggedIn ? $this->fetchFriendsActivity($gameId) : [];
        $game['related_games'] = $this->fetchRelatedGames($gameId);

        return $game;
    }

    private function fetchGame($id)
    {
        $params = ['id' => $id];

        if ($this->loggedIn)
        {
            $params['user_id'] = $this->userId;
            $query = 'SELECT games.*, list.playlist, list.played, list.rating, list.liked
                      FROM games
                      LEFT JOIN user_game_list AS list ON games.id = list.game_id AND list.user_id = :user_id
                      WHERE games.id = :id';
        }
        else
        {
            $query = 'SELECT * FROM games WHERE id = :id';
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchFriendsActivity($gameId)
    {
        $query = 'SELECT IF(user_id1 = :user_id, user_id2, user_id1) AS id
                  FROM friendship WHERE :user_id IN (user_id1, user_id2)';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['user_id' => $this->userId]);
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