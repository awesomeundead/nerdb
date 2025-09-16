<?php

class GamesService
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

    public function getTopRatedGames()
    {
        $query = 'SELECT games.*, SUM(rating) as rating FROM user_game_list
                  INNER JOIN games ON games.id = user_game_list.game_id
                  WHERE rating >= 1 GROUP BY games.id ORDER BY rating DESC LIMIT 60';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}