<?php

class MovieService
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

    public function getMovieDetails($movieId)
    {
        $movie = $this->fetchMovie($movieId);

        if (!$movie)
        {
            return null;
        }

        $movie['cast'] = $this->fetchCast($movieId);
        $movie['platforms'] = $this->fetchPlatforms($movieId);
        $movie['friends'] = $this->loggedIn ? $this->fetchFriendsActivity($movieId) : [];
        $movie['related_movies'] = $this->fetchRelatedMovies($movieId);

        return $movie;
    }

    private function fetchMovie($id)
    {
        $params = ['id' => $id];

        if ($this->loggedIn)
        {
            $params['user_id'] = $this->userId;
            $query = 'SELECT movies.*, list.watchlist, list.watched, list.rating, list.liked
                      FROM movies
                      LEFT JOIN user_movie_list AS list ON movies.id = list.movie_id AND list.user_id = :user_id
                      WHERE movies.id = :id';
        }
        else
        {
            $query = 'SELECT * FROM movies WHERE id = :id';
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchCast($id)
    {
        $query = 'SELECT movies_cast.id, name, movie_character, media
                  FROM movies_cast
                  INNER JOIN people ON people.id = movies_cast.person_id
                  WHERE movie_id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchPlatforms($id)
    {
        $query = 'SELECT name, REPLACE(url, "{id}", external_id) AS url, icon FROM movie_platforms
                  INNER JOIN streaming_platforms ON streaming_platforms.id = movie_platforms.platform_id
                  WHERE movie_id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchRelatedMovies($movieId)
    {
        $query = 'SELECT m.id, m.title_br, m.release_year, m.media, m.title_url
                  FROM related_movies AS r
                  INNER JOIN movies AS m ON m.id = IF (r.movie_id1 = :movie_id, r.movie_id2, r.movie_id1)
                  WHERE :movie_id IN (r.movie_id1, r.movie_id2)
                  ORDER BY m.release_year
                  LIMIT 24';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['movie_id' => $movieId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    private function fetchSimilarMovies($movieId, $genres)
    {
        $params['movie_id'] = $movieId;

        foreach ($genres as $index => $genre)
        {
            $key = ":genre_{$index}";
            $placeholders[] = "INSTR(`genres`, {$key}) > 0";
            $params[$key] = $genre;
        }

        $conditions = implode(' AND ', $placeholders);

        $query = "SELECT id, title_br, release_year, media FROM movies WHERE id != :movie_id AND {$conditions} LIMIT 12";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    */

    private function fetchFriendsActivity($movieId)
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
            'movie_id' => $movieId,
            'watchlist' => 0,
            'watched' => 0,
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
                  FROM user_movie_list AS l
                  LEFT JOIN users ON users.id = l.user_id
                  WHERE l.movie_id = :movie_id
                  AND (l.watchlist != :watchlist OR l.watched != :watched OR l.rating != :rating OR l.liked != :liked)
                  AND users.id IN ($inClause)";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}