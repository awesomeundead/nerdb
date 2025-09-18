<?php

class MovieRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addMovie(array $data): int|false
    {
        $params = [
            'title_br'      => $data['title_br'],
            'title_us'      => $data['title_us'],
            'director'      => $data['director'],
            'genres'        => $data['genres'],
            'release_year'  => $data['release_year'],
            'imdb'          => $data['imdb'],
            'title_url'     => $data['title_url'],
            'first_user_id' => $data['user_id'],
            'last_user_id'  => $data['user_id']
        ];

        $query = 'INSERT INTO movies (title_br, title_us, director, genres, release_year, imdb, title_url, first_user_id, last_user_id)
                  VALUES (:title_br, :title_us, :director, :genres, :release_year, :imdb, :title_url, :first_user_id, :last_user_id)';

        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute($params);

        if (!$result)
        {
            return false;
        }

        $query = 'UPDATE score SET add_movie = add_movie + 1 WHERE user_id = :user_id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $data['user_id'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findMovies(array $filters = []): array
    {
        $query = 'SELECT * FROM movies';
        $params = [];
        $conditions = [];

        if (!empty($filters['actor']))
        {
            $query = 'SELECT movies.* FROM movies
                      INNER JOIN movies_cast ON movies_cast.movie_id = movies.id
                      INNER JOIN people ON people.id = movies_cast.person_id';
            $conditions[] = 'people.name = :actor';
            $params['actor'] = $filters['actor'];
        }

        if (!empty($filters['director']))
        {
            $conditions[] = 'director LIKE :director';
            $params['director'] = "%{$filters['director']}%";
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
            $conditions[] = 'MATCH(title_br, title_us, director) AGAINST(:search)';
            $params['search'] = $filters['search'];
        }
        if (!empty($conditions))
        {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountMovies(): int
    {
        $query = 'SELECT COUNT(id) FROM movies';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getMovieDetails($movieId, $userId = null)
    {
        $movie = $this->fetchMovie($movieId, $userId);

        if (!$movie)
        {
            return null;
        }

        $movie['cast'] = $this->fetchCast($movieId);
        $movie['platforms'] = $this->fetchPlatforms($movieId);
        $movie['friends'] = $userId ? $this->fetchFriendsActivity($movieId, $userId) : [];
        $movie['related_movies'] = $this->fetchRelatedMovies($movieId);

        return $movie;
    }

    public function getMovieId(string $title, string $year): bool
    {
        $query = 'SELECT id FROM movies WHERE title_br = :title_br AND release_year = :release_year';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('title_br', $title);
        $stmt->bindValue('release_year', $year);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getMovies(int $limit, int $offset = 0): array
    {
        $query = 'SELECT * FROM movies LIMIT :offset, :limit';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRandomMovies(int $limit): array
    {
        $query = 'SELECT * FROM movies WHERE media != "" ORDER BY rand() LIMIT :limit';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopRatedMovies(int $limit): array
    {
        $query = 'SELECT movies.*, SUM(rating) as rating FROM user_movie_list
                  INNER JOIN movies ON movies.id = user_movie_list.movie_id
                  WHERE rating >= 1 GROUP BY movies.id ORDER BY rating DESC LIMIT :limit';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserMovies(int $userId, int $limit, int $offset = 0): array
    {
        $query = 'SELECT id, title_br, title_url FROM movies WHERE first_user_id = :user_id LIMIT :offset, :limit';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateMovie(array $data): bool
    {
        $params = [
            'id'            => $data['id'],
            'title_br'      => $data['title_br'],
            'title_us'      => $data['title_us'],
            'director'      => $data['director'],
            'genres'        => $data['genres'],
            'release_year'  => $data['release_year'],
            'imdb'          => $data['imdb'],
            'title_url'     => $data['title_url'],
            'last_user_id'  => $data['user_id']
        ];

        $query = 'UPDATE movies SET
                  title_br = :title_br,
                  title_us = :title_us,
                  director = :director,
                  genres = :genres,
                  release_year = :release_year,
                  imdb = :imdb,
                  title_url = :title_url,
                  last_user_id = :last_user_id
                  WHERE id = :id';

        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute($params);

        if (!$result)
        {
            return false;
        }

        $query = 'UPDATE score SET update_movie = update_movie + 1 WHERE user_id = :user_id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $data['user_id'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function fetchMovie($movieId, $userId)
    {
        $params = ['id' => $movieId];
        $query = 'SELECT * FROM movies WHERE id = :id';

        if ($userId)
        {
            $params['user_id'] = $userId;
            $query = 'SELECT movies.*, list.watchlist, list.watched, list.rating, list.liked
                      FROM movies
                      LEFT JOIN user_movie_list AS list ON movies.id = list.movie_id AND list.user_id = :user_id
                      WHERE movies.id = :id';
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
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
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
        $stmt->bindValue('movie_id', $movieId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchFriendsActivity($movieId, $userId)
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