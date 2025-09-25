<?php

class UserMovielist
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

    public function getMovies(array $filters = [], int $limit, int $offset = 0): array
    {
        [$conditions, $operator, $params] = $this->buildConditions($filters); 

        $query = 'SELECT movies.*, watchlist, watched, rating, liked
                  FROM user_movie_list AS list
                  INNER JOIN movies ON movies.id = list.movie_id
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

    public function getMoviesFriends($friendId, array $filters = [], int $limit, int $offset = 0): array
    {
        [$conditions, $operator, $params] = $this->buildConditions($filters); 

        $query = 'SELECT  m.id, m.title_br, m.media, m.title_url, list.watchlist, list.watched, list.rating, list.liked,
                ml.watchlist AS ml_watchlist,
                ml.watched AS ml_watched,
                ml.rating AS ml_rating,
                ml.liked AS ml_liked
                FROM user_movie_list AS list
                INNER JOIN movies AS m ON m.id = list.movie_id
                LEFT JOIN user_movie_list AS ml ON ml.movie_id = list.movie_id AND ml.user_id = :user_id
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

    public function getMoviesRatedByFriends(int $userId): array
    {
        $query =   'SELECT m.id, m.title_br, m.release_year, m.media, m.title_url, AVG(uml.rating) AS avg_rating, COUNT(*) AS rating_count
                    FROM user_movie_list AS uml
                    INNER JOIN movies AS m ON m.id = uml.movie_id
                    WHERE 
                    uml.rating > 0
                    AND uml.user_id IN (
                        SELECT 
                            CASE 
                                WHEN f.user_id1 = :user_id THEN f.user_id2
                                ELSE f.user_id1
                            END
                        FROM friendship AS f
                        WHERE f.user_id1 = :user_id OR f.user_id2 = :user_id
                    )
                    AND uml.movie_id NOT IN (
                        SELECT movie_id
                        FROM user_movie_list
                        WHERE user_id = :user_id AND watched = 1
                    )
                    GROUP BY m.id, m.title_br
                    HAVING COUNT(*) >= 2
                    ORDER BY avg_rating DESC, rating_count DESC
                    LIMIT 8';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMoviesRatedByUsers(int $userId): array
    {
        $query =   'SELECT m.id, m.title_br, m.release_year, m.media, m.title_url, AVG(uml.rating) AS avg_rating, COUNT(*) AS rating_count
                    FROM user_movie_list AS uml
                    INNER JOIN movies AS m ON m.id = uml.movie_id
                    WHERE 
                    uml.rating > 0
                    AND uml.movie_id NOT IN (
                        SELECT movie_id
                        FROM user_movie_list
                        WHERE user_id = :user_id AND watched = 1
                    )
                    GROUP BY m.id, m.title_br
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
        $listId = $this->getByMovieId($data['movie_id']);

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

        if (!empty($filters['watchlist']))
        {
            $conditions[] = 'list.watchlist = :watchlist';
            $params['watchlist'] = $filters['watchlist'];
        }

        if (!empty($filters['watched']))
        {
            $conditions[] = 'list.watched = :watched';
            $params['watched'] = $filters['watched'];
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
            $params['watchlist'] = 0;
            $params['watched'] = 0;
            $params['rating'] = 0;
            $params['liked'] = 0;
            $conditions[] = 'list.watchlist != :watchlist';
            $conditions[] = 'list.watched != :watched';
            $conditions[] = 'list.rating != :rating';
            $conditions[] = 'list.liked != :liked';
            $operator = ' OR ';
        }

        return [$conditions, $operator, $params];
    }

    private function getByMovieId($movieId)
    {
        $query = 'SELECT id FROM user_movie_list WHERE user_id = :user_id AND movie_id = :movie_id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue('user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindValue('movie_id', $movieId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function insertNewRecord(array $data)
    {
        $params = [
            'user_id'   => $this->userId,
            'movie_id'  => $data['movie_id'],
            'watchlist' => $data['watchlist'] ?? 0,
            'watched'   => $data['watched'] ?? 0,
            'rating'    => $data['rating'] ?? 0,
            'liked'     => $data['liked'] ?? 0
        ];

        $query = 'INSERT INTO user_movie_list (user_id, movie_id, watchlist, watched, rating, liked)
                  VALUES (:user_id, :movie_id, :watchlist, :watched, :rating, :liked)';
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    private function updateExistingRecord(array $data)
    {
        $conditions = [];
        $params['id'] = $data['id'];

        if (isset($data['watchlist']))
        {
            $conditions[] = 'watchlist = :watchlist';
            $params['watchlist'] = $data['watchlist'];
        }

        if (isset($data['watched']))
        {
            $conditions[] = 'watched = :watched';
            $params['watched'] = $data['watched'];
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
        $query = "UPDATE user_movie_list SET {$subquery} WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }
}