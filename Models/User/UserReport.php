<?php
// Models/User/UserReport.php
namespace Models\User;

use Core\Database;

class UserReport
{
    public static function fetchOptions()
    {
        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        $cats = $db->query("SELECT id, name FROM report_categories ORDER BY name ASC", [])->fetchAll(\PDO::FETCH_ASSOC);
        $reasons = $db->query("SELECT id, category_id, reason FROM report_reasons ORDER BY id ASC", [])->fetchAll(\PDO::FETCH_ASSOC);

        return ['categories' => $cats, 'reasons' => $reasons];
    }

    public static function createReport($reporter_id, $reported_id, $category_id, $reason_id = null, $description = '')
    {
        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        $sql = "INSERT INTO user_reports (id, reporter_id, reported_id, category_id, reason_id, description, created_at)
                VALUES (gen_random_uuid(), :reporter_id, :reported_id, :category_id, :reason_id, :description, NOW())
                RETURNING id";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':reporter_id' => $reporter_id,
            ':reported_id' => $reported_id,
            ':category_id' => $category_id,
            ':reason_id' => $reason_id ?: null,
            ':description' => $description
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
