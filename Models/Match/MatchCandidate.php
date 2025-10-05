<?php

// file path = root/Models/Match/MatchCandidate.php

namespace Models\Match;

use Models\User\User;
use Models\Match\MatchSession;
use Core\App;
use PDO;

class MatchCandidate extends User
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Find a potential match for the current user and create a 1-min session.
     */
    public static function findPotentialMatch(string $currentUserId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        // 1) Get current user's preferences
        $prefsStmt = $pdo->prepare("
            SELECT 
                uap.min_age, uap.max_age,
                ARRAY_AGG(ugp.gender) AS gender_pref,
                ARRAY_AGG(uh.hobby_id) AS hobbies,
                ARRAY_AGG(ui.interest_id) AS interests,
                ARRAY_AGG(up.personality_id) AS personalities
            FROM users u
            LEFT JOIN user_age_preferences uap ON u.id = uap.user_id
            LEFT JOIN user_gender_preferences ugp ON u.id = ugp.user_id
            LEFT JOIN user_hobbies uh ON u.id = uh.user_id
            LEFT JOIN user_interests ui ON u.id = ui.user_id
            LEFT JOIN user_personality up ON u.id = up.user_id
            WHERE u.id = :currentUserId
            GROUP BY u.id
        ");
        $prefsStmt->execute(['currentUserId' => $currentUserId]);
        $prefs = $prefsStmt->fetch(PDO::FETCH_ASSOC);

        if (!$prefs) return null;

        $minAge = $prefs['min_age'] ?? 18;
        $maxAge = $prefs['max_age'] ?? 50;
        $genderPref = $prefs['gender_pref'] ? '{' . implode(',', $prefs['gender_pref']) . '}' : '{}';
        $hobbies = $prefs['hobbies'] ?? [];
        $interests = $prefs['interests'] ?? [];
        $personalities = $prefs['personalities'] ?? [];

        // 2) Find top candidate based on shared attributes
        $stmt = $pdo->prepare("
            SELECT u.*, 
                COUNT(DISTINCT uh.hobby_id) AS shared_hobbies,
                COUNT(DISTINCT ui.interest_id) AS shared_interests,
                COUNT(DISTINCT up.personality_id) AS shared_personalities
            FROM users u
            LEFT JOIN user_hobbies uh ON uh.user_id = u.id AND uh.hobby_id = ANY(:hobbies)
            LEFT JOIN user_interests ui ON ui.user_id = u.id AND ui.interest_id = ANY(:interests)
            LEFT JOIN user_personality up ON up.user_id = u.id AND up.personality_id = ANY(:personalities)
            WHERE u.id != :currentUserId
              AND u.id NOT IN (
                    SELECT target_id FROM user_likes WHERE user_id = :currentUserId
                    UNION
                    SELECT target_id FROM user_dislikes WHERE user_id = :currentUserId
                    UNION
                    SELECT CASE WHEN user1_id = :currentUserId THEN user2_id ELSE user1_id END
                    FROM matches
                    WHERE user1_id = :currentUserId OR user2_id = :currentUserId
              )
              AND EXTRACT(YEAR FROM AGE(u.birthdate)) BETWEEN :minAge AND :maxAge
              AND u.gender = ANY(:genderPref::text[])
            GROUP BY u.id
            ORDER BY shared_hobbies + shared_interests + shared_personalities DESC
            LIMIT 1
        ");

        $stmt->execute([
            'currentUserId' => $currentUserId,
            'minAge' => $minAge,
            'maxAge' => $maxAge,
            'genderPref' => $genderPref,
            'hobbies' => $hobbies,
            'interests' => $interests,
            'personalities' => $personalities
        ]);

        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$candidate) return null;

        // 3) Create a 1-minute match session
        $session = MatchSession::createSession($currentUserId, $candidate['id'], 60);

        // 4) Return both candidate info and session
        return [
            'candidate' => $candidate,
            'session' => $session
        ];
    }
}
