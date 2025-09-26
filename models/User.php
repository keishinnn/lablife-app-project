<?php

class UserPreferences {
    public int $minAge;
    public int $maxAge;
    public int $distance;
    public array $genderPreference; // ["male", "female", "other"]

    public function __construct(array $data) {
        $this->minAge = $data['age_range']['min'] ?? 18;
        $this->maxAge = $data['age_range']['max'] ?? 99;
        $this->distance = $data['distance'] ?? 0;
        $this->genderPreference = $data['gender_preference'] ?? [];
    }
}

class UserProfile {
    public string $id;
    public string $fullName;
    public string $username;
    public string $email;
    public string $gender;
    public string $birthdate;
    public string $bio;
    public string $avatarUrl;
    public UserPreferences $preferences;
    public ?float $locationLat;
    public ?float $locationLng;
    public string $lastActive;
    public bool $isVerified;
    public bool $isOnline;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->fullName = $data['full_name'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->gender = $data['gender'];
        $this->birthdate = $data['birthdate'];
        $this->bio = $data['bio'];
        $this->avatarUrl = $data['avatar_url'];
        $this->preferences = new UserPreferences($data['preferences']);
        $this->locationLat = $data['location_lat'] ?? null;
        $this->locationLng = $data['location_lng'] ?? null;
        $this->lastActive = $data['last_active'];
        $this->isVerified = $data['is_verified'];
        $this->isOnline = $data['is_online'];
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'];
    }

    public function calculateAge(): int {
        $birthDate = new DateTime($this->birthdate);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        return $age;
    }
}
