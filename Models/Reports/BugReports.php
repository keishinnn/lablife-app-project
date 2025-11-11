<?php

namespace Models\Reports;

use Core\App;
use PDOException;

class BugReports
{

    public string $id;
    public string $userId;
    public string $title;
    public string $description;
    public string $createdAt;
    public string $statusId;
    public string $statusName;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->userId = $data['user_id'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->createdAt = $data['created_at'];
        $this->statusId = $data['status_id'];
        $this->statusName = $data['status_name'] ?? '';
    }

    public static function getAllAwaitingBugReports(): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT b.*, rs.name AS status_name
            FROM bug_reports b
            LEFT JOIN report_statuses rs ON b.status_id = rs.id
            WHERE b.status_id = 'b46a33a6-f4b5-49d0-8307-bf83a1d6e0de'
            ORDER BY b.created_at DESC
        ");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $reports = array_map(fn($row) => new BugReports($row), $rows);

            return $reports;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAllInProgressBugReports(): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT b.*, rs.name AS status_name
            FROM bug_reports b
            LEFT JOIN report_statuses rs ON b.status_id = rs.id
            WHERE b.status_id = '9a5a2d9a-8dc0-4a4b-aa62-80a961149357'
            ORDER BY b.created_at DESC
        ");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $reports = array_map(fn($row) => new BugReports($row), $rows);

            return $reports;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAllResolvedBugReports(): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT b.*, rs.name AS status_name
            FROM bug_reports b
            LEFT JOIN report_statuses rs ON b.status_id = rs.id
            WHERE b.status_id = 'a51a611a-975e-45e6-b085-8a537d80513e'
            ORDER BY b.created_at DESC
        ");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $reports = array_map(fn($row) => new BugReports($row), $rows);

            return $reports;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAllBugReports(): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT b.*, rs.name AS status_name
            FROM bug_reports b
            LEFT JOIN report_statuses rs ON b.status_id = rs.id
            ORDER BY b.created_at DESC
        ");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $reports = array_map(fn($row) => new BugReports($row), $rows);

            return $reports;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAllBugReportsCount(): int
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT COUNT(*) AS report_count FROM bug_reports");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['report_count'] ?? 0);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getRecentBugReports(int $limit = 5): array
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("
            SELECT *
            FROM bug_reports
            ORDER BY created_at DESC
            LIMIT {$limit}
        ");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Map each row to a BugReports object
            $reports = array_map(fn($row) => new BugReports($row), $rows);

            return $reports;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function deleteAwaitingBugReport(string $id)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            DELETE FROM bug_reports
            WHERE id = :id
              AND status_id = 'b46a33a6-f4b5-49d0-8307-bf83a1d6e0de' -- Awaiting status
        ");

            $stmt->execute(['id' => $id]);

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function setResolvedBugReport(string $id)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $resolvedStatusId = 'a51a611a-975e-45e6-b085-8a537d80513e';

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE bug_reports
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
