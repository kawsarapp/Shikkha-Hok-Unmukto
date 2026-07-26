<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Redis;

class LeaderboardService
{
    /**
     * Record score in Redis Sorted Set for O(log N) instant leaderboard queries
     */
    public function recordScore(int $examId, int $userId, float $score): void
    {
        try {
            Redis::zadd("leaderboard:exam:{$examId}", $score, $userId);
        } catch (\Throwable $e) {
            // Redis fallback log
        }
    }

    /**
     * Get Top N ranks for an exam with user info
     */
    public function getTopRanks(int $examId, int $limit = 100): array
    {
        try {
            // Fetch top user IDs with scores from Redis
            $results = Redis::zrevrange("leaderboard:exam:{$examId}", 0, $limit - 1, 'WITHSCORES');
            
            if (empty($results)) {
                return [];
            }

            $userIds = array_keys($results);
            $users = User::whereIn('id', $userIds)->get()->keyBy('id');

            $leaderboard = [];
            $rank = 1;
            foreach ($results as $userId => $score) {
                if (isset($users[$userId])) {
                    $leaderboard[] = [
                        'rank' => $rank++,
                        'user_id' => $userId,
                        'name' => $users[$userId]->name,
                        'study_streak' => $users[$userId]->study_streak,
                        'score' => (float) $score,
                    ];
                }
            }

            return $leaderboard;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getUserRank(int $examId, int $userId): int
    {
        try {
            $rank = Redis::zrevrank("leaderboard:exam:{$examId}", $userId);
            return $rank !== null ? (int)$rank + 1 : 1;
        } catch (\Throwable $e) {
            return 1;
        }
    }
}
