<?php

class MoviesService
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

    public function getTopRatedMovies()
    {
        $query = 'SELECT movies.*, SUM(rating) as rating FROM user_movie_list
                  INNER JOIN movies ON movies.id = user_movie_list.movie_id
                  WHERE rating >= 1 GROUP BY movies.id ORDER BY rating DESC LIMIT 60';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}