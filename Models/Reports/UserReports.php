<?php

namespace Models\Reports;

use Core\App;
use PDOException;

class UserReports
{

    public string $id;
    public ?string $reporterId;
    public ?string $reportedUserId;
    public string $categoryId;
    public string $reasonId;
    public string $createdAt;
    public string $categoryName;
    public string $reasonText;
    public string $statusId;
    public string $statusName;
    public $context;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->reporterId = $data['reporter_id'] ?? null;
        $this->reportedUserId = $data['reported_user_id'] ?? null;
        $this->categoryId = $data['category_id'];
        $this->reasonId = $data['reason_id'];
        $this->createdAt = $data['created_at'];
        $this->categoryName = $data['category_name'] ?? null;
        $this->reasonText = $data['reason_text'] ?? null;
        $this->statusId = $data['status_id'] ?? '';
        $this->statusName = $data['status_name'] ?? null;

        if (isset($data['context'])) {
            $decoded = json_decode($data['context'], true);
            $this->context = json_last_error() === JSON_ERROR_NONE ? $decoded : $data['context'];
        } else {
            $this->context = null;
        }
    }

    public static function getAllAwaitingUserReports(): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT 
                ur.*, 
                rc.name AS category_name,
                rr.reason AS reason_text,
                rs.name AS status_name
            FROM user_reports ur
            LEFT JOIN report_categories rc ON ur.category_id = rc.id
            LEFT JOIN report_reasons rr ON ur.reason_id = rr.id
            LEFT JOIN report_statuses rs ON ur.status_id = rs.id
            WHERE ur.status_id = 'b46a33a6-f4b5-49d0-8307-bf83a1d6e0de' -- Awaiting status
            ORDER BY ur.created_at DESC
        ");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_map(fn($row) => new UserReports($row), $rows);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAllInProgressUserReports(): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT 
                ur.*, 
                rc.name AS category_name,
                rr.reason AS reason_text,
                rs.name AS status_name
            FROM user_reports ur
            LEFT JOIN report_categories rc ON ur.category_id = rc.id
            LEFT JOIN report_reasons rr ON ur.reason_id = rr.id
            LEFT JOIN report_statuses rs ON ur.status_id = rs.id
            WHERE ur.status_id = '9a5a2d9a-8dc0-4a4b-aa62-80a961149357' -- In Progress status
            ORDER BY ur.created_at DESC
        ");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_map(fn($row) => new UserReports($row), $rows);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAllResolvedUserReports(): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT 
                ur.*, 
                rc.name AS category_name,
                rr.reason AS reason_text,
                rs.name AS status_name
            FROM user_reports ur
            LEFT JOIN report_categories rc ON ur.category_id = rc.id
            LEFT JOIN report_reasons rr ON ur.reason_id = rr.id
            LEFT JOIN report_statuses rs ON ur.status_id = rs.id
            WHERE ur.status_id = 'a51a611a-975e-45e6-b085-8a537d80513e' -- Resolved status
            ORDER BY ur.created_at DESC
        ");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_map(fn($row) => new UserReports($row), $rows);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function getAllUserReportsCount(): int
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT COUNT(*) AS report_count FROM user_reports");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['report_count'] ?? 0);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAllReportsCount(): int
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT 
                (SELECT COUNT(*) FROM user_reports) AS user_reports_count,
                (SELECT COUNT(*) FROM bug_reports) AS bug_reports_count
        ");

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $userReportsCount = (int)($result['user_reports_count'] ?? 0);
            $bugReportsCount  = (int)($result['bug_reports_count'] ?? 0);

            return $userReportsCount + $bugReportsCount;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getRecentUserReports(int $limit = 5): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->prepare("
            SELECT 
                ur.id,
                ur.reporter_id,
                ur.reported_user_id,
                ur.category_id,
                ur.reason_id,
                ur.status_id,
                ur.created_at,
                rc.name AS category_name,
                rr.reason AS reason_text,
                rs.name AS status_name
            FROM user_reports AS ur
            LEFT JOIN report_categories AS rc ON ur.category_id = rc.id
            LEFT JOIN report_reasons AS rr ON ur.reason_id = rr.id
            LEFT JOIN report_statuses AS rs ON ur.status_id = rs.id
            ORDER BY ur.created_at DESC
            LIMIT :limit
        ");

            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_map(fn($row) => new UserReports($row), $rows);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function deleteUser(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->execute(["user_id" => $userId]);

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function setResolvedUserReport(string $id)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $resolvedStatusId = 'a51a611a-975e-45e6-b085-8a537d80513e';

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE user_reports
            SET status_id = :resolvedStatusId
            WHERE id = :id
              AND status_id IN (
                  '9a5a2d9a-8dc0-4a4b-aa62-80a961149357'
              )
        ");

            $stmt->execute([
                'id' => $id,
                'resolvedStatusId' => $resolvedStatusId
            ]);

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
