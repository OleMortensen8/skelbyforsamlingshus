<?php
namespace App;

/**
 * AuditLogger - Comprehensive Audit Logging System
 * 
 * Logs security events, user actions, and changes for compliance and security tracking
 */
class AuditLogger
{
    private $database;
    private $config;
    private const EVENT_TYPES = [
        'security' => 'Security Events',
        'auth' => 'Authentication',
        'user_action' => 'User Actions',
        'data_change' => 'Data Changes',
        'access' => 'Access Control',
        'error' => 'System Errors',
        'admin' => 'Administrative Actions',
    ];

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->config = include __DIR__ . '/../config/security.php';
    }

    /**
     * Log event to audit log
     * 
     * @param string $type Event type
     * @param string $action Action performed
     * @param array $data Additional event data
     * @param int|null $userId User ID performing action
     * @return int|null Inserted record ID
     */
    public function log($type, $action, $data = [], $userId = null)
    {
        try {
            // Validate event type
            if (!isset(self::EVENT_TYPES[$type])) {
                $type = 'user_action';
            }

            $logData = [
                'type' => $type,
                'action' => $action,
                'user_id' => $userId ?? ($_SESSION['user_id'] ?? null),
                'ip_address' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'referer' => $_SERVER['HTTP_REFERER'] ?? '',
                'data' => !empty($data) ? json_encode($data) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            return $this->database->insert('audit_logs', $logData);
        } catch (Exception $e) {
            error_log('AuditLogger error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log authentication event
     * 
     * @param string $action Action (login_success, login_failed, logout, password_change)
     * @param int|null $userId User ID
     * @param array $data Additional data
     * @return int|null Log ID
     */
    public function logAuthEvent($action, $userId = null, $data = [])
    {
        return $this->log('auth', $action, $data, $userId);
    }

    /**
     * Log security event
     * 
     * @param string $action Action (failed_2fa, suspicious_activity, brute_force_attempt)
     * @param array $data Additional data
     * @param int|null $userId User ID
     * @return int|null Log ID
     */
    public function logSecurityEvent($action, $data = [], $userId = null)
    {
        return $this->log('security', $action, $data, $userId);
    }

    /**
     * Log user action
     * 
     * @param string $action Action description
     * @param array $data Action details
     * @param int|null $userId User ID
     * @return int|null Log ID
     */
    public function logUserAction($action, $data = [], $userId = null)
    {
        return $this->log('user_action', $action, $data, $userId);
    }

    /**
     * Log data change
     * 
     * @param string $table Table name
     * @param string $action Action (insert, update, delete)
     * @param int $recordId Record ID
     * @param array $changes Old and new values
     * @param int|null $userId User ID
     * @return int|null Log ID
     */
    public function logDataChange($table, $action, $recordId, $changes = [], $userId = null)
    {
        $data = [
            'table' => $table,
            'record_id' => $recordId,
            'changes' => $changes,
        ];

        return $this->log('data_change', $action, $data, $userId);
    }

    /**
     * Log access attempt
     * 
     * @param string $resource Resource accessed
     * @param string $result Result (allowed, denied)
     * @param int|null $userId User ID
     * @param array $data Additional data
     * @return int|null Log ID
     */
    public function logAccessAttempt($resource, $result, $userId = null, $data = [])
    {
        $action = $resource . '_' . $result;
        $data['resource'] = $resource;
        $data['result'] = $result;

        return $this->log('access', $action, $data, $userId);
    }

    /**
     * Log system error
     * 
     * @param string $message Error message
     * @param array $context Error context
     * @param int|null $userId User ID
     * @return int|null Log ID
     */
    public function logError($message, $context = [], $userId = null)
    {
        $data = [
            'message' => $message,
            'context' => $context,
        ];

        return $this->log('error', 'system_error', $data, $userId);
    }

    /**
     * Log administrative action
     * 
     * @param string $action Action taken
     * @param int|null $targetUserId User affected by action
     * @param array $data Additional data
     * @param int|null $adminId Admin performing action
     * @return int|null Log ID
     */
    public function logAdminAction($action, $targetUserId = null, $data = [], $adminId = null)
    {
        if ($targetUserId) {
            $data['target_user_id'] = $targetUserId;
        }

        return $this->log('admin', $action, $data, $adminId);
    }

    /**
     * Get audit logs
     * 
     * @param array $filters Filter criteria
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @return array Array of audit logs
     */
    public function getLogs($filters = [], $limit = 100, $offset = 0)
    {
        try {
            // Build query
            $query = 'SELECT * FROM audit_logs WHERE 1=1';
            $params = [];

            if (!empty($filters['type'])) {
                $query .= ' AND type = ?';
                $params[] = $filters['type'];
            }

            if (!empty($filters['user_id'])) {
                $query .= ' AND user_id = ?';
                $params[] = $filters['user_id'];
            }

            if (!empty($filters['action'])) {
                $query .= ' AND action LIKE ?';
                $params[] = '%' . $filters['action'] . '%';
            }

            if (!empty($filters['from_date'])) {
                $query .= ' AND created_at >= ?';
                $params[] = $filters['from_date'];
            }

            if (!empty($filters['to_date'])) {
                $query .= ' AND created_at <= ?';
                $params[] = $filters['to_date'] . ' 23:59:59';
            }

            if (!empty($filters['ip_address'])) {
                $query .= ' AND ip_address = ?';
                $params[] = $filters['ip_address'];
            }

            // Add ordering and limit
            $query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

            // Execute query
            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('AuditLogger::getLogs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user activity
     * 
     * @param int $userId User ID
     * @param int $limit Limit results
     * @return array Array of recent activities
     */
    public function getUserActivity($userId, $limit = 50)
    {
        return $this->getLogs(['user_id' => $userId], $limit);
    }

    /**
     * Get failed login attempts
     * 
     * @param string $email Email address
     * @param int $minutes Minutes to look back
     * @return int Number of failed attempts
     */
    public function getFailedLoginAttempts($email, $minutes = 60)
    {
        try {
            $query = 'SELECT COUNT(*) as count FROM audit_logs 
                      WHERE type = ? AND action = ? 
                      AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)';

            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute(['auth', 'login_failed_invalid_password', $minutes]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get security events
     * 
     * @param int $limit Limit results
     * @param int $daysBack Days to look back
     * @return array Array of security events
     */
    public function getSecurityEvents($limit = 100, $daysBack = 7)
    {
        $fromDate = date('Y-m-d H:i:s', strtotime("-{$daysBack} days"));

        return $this->getLogs([
            'type' => 'security',
            'from_date' => $fromDate
        ], $limit);
    }

    /**
     * Get data changes for entity
     * 
     * @param string $table Table name
     * @param int $recordId Record ID
     * @param int $limit Limit results
     * @return array Array of changes
     */
    public function getChangeHistory($table, $recordId, $limit = 50)
    {
        try {
            $query = 'SELECT * FROM audit_logs 
                      WHERE type = ? AND action IN (?, ?, ?)
                      AND data LIKE ? 
                      ORDER BY created_at DESC LIMIT ?';

            $searchData = '%"table":"' . $table . '"%"record_id":' . $recordId . '%';

            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute(['data_change', 'insert', 'update', 'delete', $searchData, $limit]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get audit statistics
     * 
     * @param string $period Period: 'day', 'week', 'month'
     * @return array Statistics array
     */
    public function getStatistics($period = 'day')
    {
        try {
            $dateFormat = match ($period) {
                'week' => '%Y-W',
                'month' => '%Y-%m',
                default => '%Y-%m-%d'
            };

            $query = 'SELECT 
                        DATE_FORMAT(created_at, ?) as period,
                        type,
                        COUNT(*) as count
                      FROM audit_logs
                      GROUP BY period, type
                      ORDER BY period DESC
                      LIMIT 30';

            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute([$dateFormat]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get event types breakdown
     * 
     * @param int $daysBack Days to look back
     * @return array Breakdown by type
     */
    public function getEventTypeBreakdown($daysBack = 30)
    {
        try {
            $fromDate = date('Y-m-d H:i:s', strtotime("-{$daysBack} days"));

            $query = 'SELECT type, COUNT(*) as count
                      FROM audit_logs
                      WHERE created_at >= ?
                      GROUP BY type
                      ORDER BY count DESC';

            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute([$fromDate]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Clean old audit logs
     * 
     * @param int $daysToKeep Days to keep in database
     * @return int Number of records deleted
     */
    public function cleanup($daysToKeep = 90)
    {
        try {
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));

            $query = 'DELETE FROM audit_logs WHERE created_at < ?';
            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute([$cutoffDate]);

            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log('AuditLogger::cleanup error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Export audit logs
     * 
     * @param array $filters Filter criteria
     * @param string $format Format: 'csv', 'json'
     * @return string Exported data
     */
    public function export($filters = [], $format = 'csv')
    {
        $logs = $this->getLogs($filters, 10000);

        if ($format === 'json') {
            return json_encode($logs, JSON_PRETTY_PRINT);
        }

        // CSV format
        if (empty($logs)) {
            return '';
        }

        $handle = fopen('php://memory', 'r+');

        // Write header
        fputcsv($handle, array_keys($logs[0]));

        // Write data
        foreach ($logs as $log) {
            fputcsv($handle, $log);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    /**
     * Get IP address reputation
     * 
     * @param string $ip IP address
     * @param string $action Filter by action
     * @return array IP statistics
     */
    public function getIpReputation($ip, $action = null)
    {
        try {
            $query = 'SELECT type, action, COUNT(*) as count
                      FROM audit_logs
                      WHERE ip_address = ?';
            $params = [$ip];

            if ($action) {
                $query .= ' AND action = ?';
                $params[] = $action;
            }

            $query .= ' GROUP BY type, action';

            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get event type description
     * 
     * @param string $type Event type
     * @return string Description
     */
    public static function getTypeDescription($type)
    {
        return self::EVENT_TYPES[$type] ?? 'Unknown';
    }

    /**
     * Get all event types
     * 
     * @return array Event types
     */
    public static function getEventTypes()
    {
        return self::EVENT_TYPES;
    }

    /**
     * Get client IP address
     * 
     * @return string Client IP
     */
    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Create comprehensive security report
     * 
     * @param int $daysBack Days to include
     * @return array Comprehensive report
     */
    public function getSecurityReport($daysBack = 30)
    {
        $fromDate = date('Y-m-d H:i:s', strtotime("-{$daysBack} days"));

        return [
            'period' => [
                'from' => $fromDate,
                'to' => date('Y-m-d H:i:s'),
                'days' => $daysBack
            ],
            'event_summary' => $this->getEventTypeBreakdown($daysBack),
            'failed_logins' => $this->getFailedLoginAttempts('', $daysBack * 24 * 60),
            'security_events' => $this->getSecurityEvents(50, $daysBack),
            'most_active_users' => $this->getTopUsers($daysBack, 10),
            'top_ips' => $this->getTopIps($daysBack, 10),
        ];
    }

    /**
     * Get top users by activity
     * 
     * @param int $daysBack Days to look back
     * @param int $limit Limit results
     * @return array Top users
     */
    private function getTopUsers($daysBack, $limit)
    {
        try {
            $fromDate = date('Y-m-d H:i:s', strtotime("-{$daysBack} days"));

            $query = 'SELECT user_id, COUNT(*) as count
                      FROM audit_logs
                      WHERE user_id IS NOT NULL AND created_at >= ?
                      GROUP BY user_id
                      ORDER BY count DESC
                      LIMIT ?';

            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute([$fromDate, $limit]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get top IPs
     * 
     * @param int $daysBack Days to look back
     * @param int $limit Limit results
     * @return array Top IPs
     */
    private function getTopIps($daysBack, $limit)
    {
        try {
            $fromDate = date('Y-m-d H:i:s', strtotime("-{$daysBack} days"));

            $query = 'SELECT ip_address, COUNT(*) as count
                      FROM audit_logs
                      WHERE created_at >= ?
                      GROUP BY ip_address
                      ORDER BY count DESC
                      LIMIT ?';

            $stmt = $this->database->getPdo()->prepare($query);
            $stmt->execute([$fromDate, $limit]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
