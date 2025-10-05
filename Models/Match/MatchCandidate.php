<?php

namespace Models\Match;

use Models\User\User;
use Models\Match\MatchSession;
use Core\App;
use Models\User\UserHobbies;
use Models\User\UserInterests;
use Models\User\UserPersonality;
use Models\User\UserPreferences;
use PDO;

class MatchCandidate extends User
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function findPotentialMatch(string $currentUserId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        // 1) Fetch current user's preferences
        $userPreferencesData = UserPreferences::getCurrentUserPreferences($currentUserId);
        $userPreferences = $userPreferencesData instanceof UserPreferences
            ? $userPreferencesData
            : new UserPreferences($userPreferencesData);

        $userPersonalityType = UserPersonality::getCurrentUserPersonality($currentUserId);
        $userHobbies = UserHobbies::getCurrentUserHobbies($currentUserId) ?? [];
        $userInterests = UserInterests::getCurrentUserInterests($currentUserId) ?? [];

        $minAge = $userPreferences->minAge ?? 18;
        $maxAge = $userPreferences->maxAge ?? 50;
        $genderPref = $userPreferences->genderPreference
            ? explode(',', $userPreferences->genderPreference)
            : ['other'];

        $hobbies = array_map(fn($h) => $h->id, $userHobbies);
        $interests = array_map(fn($i) => $i->id, $userInterests);
        $personalities = $userPersonalityType ? [$userPersonalityType->id] : [];

        // Convert arrays to PostgreSQL literal strings
        $genderPrefPg = count($genderPref) ? '{' . implode(',', $genderPref) . '}' : '{}';
        $hobbiesPg = count($hobbies) ? '{' . implode(',', $hobbies) . '}' : '{}';
        $interestsPg = count($interests) ? '{' . implode(',', $interests) . '}' : '{}';
        $personalitiesPg = count($personalities) ? '{' . implode(',', $personalities) . '}' : '{}';

        // 2) Main query
        $stmt = $pdo->prepare("
    SELECT *
    FROM (
        SELECT u.id, u.full_name, u.username, u.email, u.gender, u.birthdate,
               u.bio, u.avatar_url, u.location_lat, u.location_lng, u.last_active,
               u.is_verified, u.is_online, u.created_at, u.updated_at,
               COUNT(DISTINCT uh.hobby_id) AS shared_hobbies,
               COUNT(DISTINCT ui.interest_id) AS shared_interests,
               COUNT(DISTINCT up.personality_id) AS shared_personalities
        FROM users u
        LEFT JOIN user_hobbies uh 
            ON uh.user_id = u.id AND uh.hobby_id = ANY(:hobbies::uuid[])
        LEFT JOIN user_interests ui 
            ON ui.user_id = u.id AND ui.interest_id = ANY(:interests::uuid[])
        LEFT JOIN user_personality_type up 
            ON up.user_id = u.id AND up.personality_id = ANY(:personalities::uuid[])
        WHERE u.id != :currentUserId
          AND u.id NOT IN (
                SELECT to_user_id FROM likes WHERE from_user_id = :currentUserId
                UNION
                SELECT to_user_id FROM dislikes WHERE from_user_id = :currentUserId
                UNION
                SELECT CASE WHEN user1_id = :currentUserId THEN user2_id ELSE user1_id END
                FROM matches
                WHERE user1_id = :currentUserId OR user2_id = :currentUserId
          )
          AND EXTRACT(YEAR FROM AGE(u.birthdate)) BETWEEN :minAge AND :maxAge
          AND u.gender = ANY(:genderPref::text[])
        GROUP BY u.id, u.full_name, u.username, u.email, u.gender, u.birthdate,
                 u.bio, u.avatar_url, u.location_lat, u.location_lng, u.last_active,
                 u.is_verified, u.is_online, u.created_at, u.updated_at
    ) AS sub
    ORDER BY sub.shared_hobbies + sub.shared_interests + sub.shared_personalities DESC
    LIMIT 1
");

        $stmt->execute([
            'currentUserId' => $currentUserId,
            'minAge' => $minAge,
            'maxAge' => $maxAge,
            'genderPref' => $genderPrefPg,
            'hobbies' => $hobbiesPg,
            'interests' => $interestsPg,
            'personalities' => $personalitiesPg
        ]);

        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$candidate) return null;

        // 3) Create 1-min match session
        $session = MatchSession::createSession($currentUserId, $candidate['id'], 60);

        return [
            'candidate' => $candidate,
            'session' => $session
        ];
    }
}
