<?php
namespace Models;

use Core\Database;
use Exception;
use PDO;

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
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReasons()
    {
        $sql = "SELECT id, category_id, reason FROM report_reasons ORDER BY id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertReport($reporterId, $reportedId, $categoryId, $reasonId = null)
    {
        $sql = "INSERT INTO user_reports (id, reporter_id, reported_id, category_id, reason_id, created_at)
                VALUES (gen_random_uuid(), :reporter, :reported, :category, :reason, NOW())
                RETURNING id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':reporter' => $reporterId,
            ':reported' => $reportedId,
            ':category' => $categoryId,
            ':reason'   => $reasonId ?: null
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
