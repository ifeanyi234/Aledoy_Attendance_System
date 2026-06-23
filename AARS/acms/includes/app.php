<?php

declare(strict_types=1);

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!headers_sent()) {
        ob_start();
    }
}

function clearOutputBuffer(): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

function redirectTo(string $url): void
{
    clearOutputBuffer();
    header('Location: ' . $url);
    exit;
}

function sendJsonResponse(array $payload, int $status = 200): void
{
    clearOutputBuffer();
    header('Content-Type: application/json; charset=utf-8', true, $status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function isJsonRequest(): bool
{
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    return str_contains($acceptHeader, 'application/json') || str_contains($contentType, 'application/json');
}

function getPdoConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = 'localhost';
    $db = 'aledoy_attendance_register_system';
    $user = 'aledoy_attendance_register_system';
    $pass = 'attendance_';
    $charset = 'utf8mb4';

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $db, $charset);
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}

function fetchUserByUsername(PDO $pdo, string $username): ?array
{
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function ensureDefaultAdminUser(PDO $pdo): void
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => 'Administrator']);
    if ($stmt->fetch() === false) {
        $stmt = $pdo->prepare('INSERT INTO users (`username`, `password`) VALUES (:username, :password)');
        $stmt->execute([
            ':username' => 'Administrator',
            ':password' => password_hash('contents', PASSWORD_BCRYPT),
        ]);
    }
}

function authenticateUser(PDO $pdo, string $username, string $password): bool
{
    $user = fetchUserByUsername($pdo, $username);
    if ($user === null) {
        return false;
    }

    $storedPassword = (string)$user['password'];
    if (password_verify($password, $storedPassword)) {
        if (password_needs_rehash($storedPassword, PASSWORD_BCRYPT)) {
            $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
            $stmt->execute([':password' => password_hash($password, PASSWORD_BCRYPT), ':id' => $user['id']]);
        }
        return true;
    }

    if ($storedPassword === $password) {
        $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute([':password' => password_hash($password, PASSWORD_BCRYPT), ':id' => $user['id']]);
        return true;
    }

    return false;
}

function loginUser(string $username): void
{
    $_SESSION['acms_valid_user'] = $username;
}

function isAuthenticated(): bool
{
    return isset($_SESSION['acms_valid_user']) && is_string($_SESSION['acms_valid_user']) && $_SESSION['acms_valid_user'] !== '';
}

function requireAuth(): void
{
    if (!isAuthenticated()) {
        redirectTo('login.php');
    }
}

function getTodayDate(): string
{
    $tz = new DateTimeZone('Africa/Lagos');
    return (new DateTimeImmutable('now', $tz))->format('Y-m-d');
}

function getTodayTime(): string
{
    $tz = new DateTimeZone('Africa/Lagos');
    return (new DateTimeImmutable('now', $tz))->format('H:i:s');
}

function getTotalStaff(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM staff');
    return (int)$stmt->fetchColumn();
}

function getPresentCount(PDO $pdo): int
{
    $stmt = $pdo->prepare('SELECT COUNT(DISTINCT staff_id) FROM attendance WHERE log_date = :log_date AND status = :status');
    $stmt->execute([':log_date' => getTodayDate(), ':status' => 'check_in']);
    return (int)$stmt->fetchColumn();
}

function getMemberPresentCount(PDO $pdo, string $staffType): int
{
    $stmt = $pdo->prepare('SELECT COUNT(DISTINCT a.staff_id) FROM attendance a INNER JOIN staff s ON a.staff_id = s.staff_id WHERE a.log_date = :log_date AND a.status = :status AND s.staff_type = :staff_type');
    $stmt->execute([':log_date' => getTodayDate(), ':status' => 'check_in', ':staff_type' => $staffType]);
    return (int)$stmt->fetchColumn();
}

function getStaffList(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, staff_id, firstname, lastname, email, phone, staff_type, course, date_created FROM staff ORDER BY lastname ASC, firstname ASC');
    return $stmt->fetchAll();
}

function getAttendanceRecords(PDO $pdo, string $searchTerm = ''): array
{
    $sql = 'SELECT a.id, a.staff_id, a.log_date, a.log_time, a.status, s.firstname, s.lastname, s.staff_type FROM attendance a LEFT JOIN staff s ON a.staff_id = s.staff_id';
    $params = [];

    if ($searchTerm !== '') {
        $sql .= ' WHERE a.staff_id LIKE :term OR s.firstname LIKE :term OR s.lastname LIKE :term OR s.email LIKE :term';
        $params[':term'] = '%' . $searchTerm . '%';
    }

    $sql .= ' ORDER BY a.log_date DESC, a.log_time DESC LIMIT 250';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function createStaff(PDO $pdo, array $data): array
{
    $errors = [];
    $staffId = trim((string)($data['staff_id'] ?? ''));
    $firstname = trim((string)($data['firstname'] ?? ''));
    $lastname = trim((string)($data['lastname'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $phone = trim((string)($data['phone'] ?? ''));
    $staffType = trim((string)($data['staff_type'] ?? ''));
    $course = trim((string)($data['course'] ?? ''));

    if ($staffId === '') {
        $errors[] = 'Staff ID is required.';
    }
    if ($firstname === '') {
        $errors[] = 'First name is required.';
    }
    if ($lastname === '') {
        $errors[] = 'Last name is required.';
    }
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email must be a valid email address.';
    }
    if (!in_array($staffType, ['main', 'academy', 'occasional'], true)) {
        $errors[] = 'Staff type must be main, academy, or occasional.';
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO staff (staff_id, firstname, lastname, email, phone, staff_type, course) VALUES (:staff_id, :firstname, :lastname, :email, :phone, :staff_type, :course)');
        $stmt->execute([
            ':staff_id' => $staffId,
            ':firstname' => $firstname,
            ':lastname' => $lastname,
            ':email' => $email,
            ':phone' => $phone,
            ':staff_type' => $staffType,
            ':course' => $course !== '' ? $course : null,
        ]);
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            if (str_contains($exception->getMessage(), 'staff_id')) {
                $errors[] = 'Staff ID already exists.';
            } elseif (str_contains($exception->getMessage(), 'email')) {
                $errors[] = 'Email is already registered.';
            } else {
                $errors[] = 'A staff record with the same data already exists.';
            }
        } else {
            $errors[] = 'Unable to save staff record. Please try again.';
        }
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    return ['success' => true, 'message' => 'Staff member created successfully.'];
}

function lookupStaffByStaffId(PDO $pdo, string $staffId): ?array
{
    $stmt = $pdo->prepare('SELECT staff_id, firstname, lastname FROM staff WHERE staff_id = :staff_id LIMIT 1');
    $stmt->execute([':staff_id' => $staffId]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function getLatestAttendanceRecord(PDO $pdo, string $staffId, string $logDate): ?array
{
    $stmt = $pdo->prepare('SELECT status FROM attendance WHERE staff_id = :staff_id AND log_date = :log_date ORDER BY log_time DESC LIMIT 1');
    $stmt->execute([':staff_id' => $staffId, ':log_date' => $logDate]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function recordAttendance(PDO $pdo, string $staffId): array
{
    $staff = lookupStaffByStaffId($pdo, $staffId);
    if ($staff === null) {
        return ['success' => false, 'message' => 'Staff ID not found.'];
    }

    $today = getTodayDate();
    $previous = getLatestAttendanceRecord($pdo, $staffId, $today);
    $status = $previous !== null && $previous['status'] === 'check_in' ? 'check_out' : 'check_in';

    $stmt = $pdo->prepare('INSERT INTO attendance (staff_id, log_date, log_time, status) VALUES (:staff_id, :log_date, :log_time, :status)');
    $stmt->execute([
        ':staff_id' => $staffId,
        ':log_date' => $today,
        ':log_time' => getTodayTime(),
        ':status' => $status,
    ]);

    return [
        'success' => true,
        'message' => sprintf('%s %s recorded successfully.', ucfirst(str_replace('_', ' ', $status)), $staff['firstname'] . ' ' . $staff['lastname']),
        'status' => $status,
        'staffName' => $staff['firstname'] . ' ' . $staff['lastname'],
    ];
}

function renderStaffTypeBadge(string $staffType): string
{
    return match ($staffType) {
        'main' => '<span class="badge bg-primary">Main</span>',
        'academy' => '<span class="badge bg-success">Academy</span>',
        'occasional' => '<span class="badge bg-warning text-dark">Occasional</span>',
        default => '<span class="badge bg-secondary">' . htmlspecialchars($staffType, ENT_QUOTES, 'UTF-8') . '</span>',
    };
}

function renderAttendanceStatusBadge(string $status): string
{
    return match ($status) {
        'check_in' => '<span class="badge bg-success">Check In</span>',
        'check_out' => '<span class="badge bg-secondary">Check Out</span>',
        default => '<span class="badge bg-light text-dark">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>',
    };
}
