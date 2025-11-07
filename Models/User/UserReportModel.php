<?php
namespace Models\User;

use Core\Database;
use PDO;
use Exception;

class UserReportModel
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getCategories()
    {
        $sql = "SELECT id, name FROM report_categories ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReasons()
    {
        $sql = "SELECT id, category_id, reason FROM report_reasons ORDER BY id ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertReport($reporterId, $reportedUserId, $categoryId, $reasonId = null)
    {
        try {
            $sql = "
                INSERT INTO user_reports (id, reporter_id, reported_user_id, category_id, reason_id, created_at)
                VALUES (gen_random_uuid(), :reporter, :reported_user, :category, :reason, NOW())
                RETURNING id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':reporter'      => $reporterId,
                ':reported_user' => $reportedUserId,
                ':category'      => $categoryId,
                ':reason'        => $reasonId ?: null
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }
}
