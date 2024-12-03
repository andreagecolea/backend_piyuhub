<?php
class AdminAuth {
    // Define constants for maximum login attempts and lockout duration
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 300; // 5 minutes in seconds

    // login
    public function login() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");

        global $conn;

        header('Content-Type: application/json');
        $response = array();

        $id_no = $_POST['id_no'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($id_no)) {
            $response['status'] = 'error';
            $response['message'] = 'ID Number is required.';
            echo json_encode($response);
            exit();
        } elseif (!preg_match('/^\d{4}-\d{4}$/', $id_no)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid ID Number format.';
            echo json_encode($response);
            exit();
        }

        if (empty($password)) {
            $response['status'] = 'error';
            $response['message'] = 'Password is required.';
            echo json_encode($response);
            exit();
        }

        // Check if user exists, is a Developer, and is approved
        $stmt = $conn->prepare("SELECT id, password, position, login_attempts, last_attempt_time FROM users WHERE id_no = ? AND LOWER(position) = 'developer' AND LOWER(status) = 'approved'");
        $stmt->bind_param('s', $id_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid ID Number or account not approved.';
            echo json_encode($response);
            exit();
        }

        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];
        $user_id = $user['id'];
        $login_attempts = $user['login_attempts'];
        $last_attempt_time = strtotime($user['last_attempt_time']);

        // Check if the account is locked
        $current_time = time();
        if ($login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $time_since_last_attempt = $current_time - $last_attempt_time;
            if ($time_since_last_attempt < self::LOCKOUT_DURATION) {
                $remaining_lockout = self::LOCKOUT_DURATION - $time_since_last_attempt;
                $minutes = floor($remaining_lockout / 60);
                $seconds = $remaining_lockout % 60;
        
                // Ensure the remaining time cannot exceed 5 minutes
                if ($remaining_lockout > self::LOCKOUT_DURATION) {
                    $remaining_lockout = self::LOCKOUT_DURATION;
                    $minutes = 5;
                    $seconds = 0;
                }
        
                $response['status'] = 'error';
                $response['message'] = "Too many login attempts. Please try again in {$minutes} minute(s) and {$seconds} second(s).";
                echo json_encode($response);
                exit();
            } else {
                // Lockout duration has passed, reset login_attempts
                $stmt_reset = $conn->prepare("UPDATE users SET login_attempts = 0, last_attempt_time = NULL WHERE id = ?");
                $stmt_reset->bind_param('i', $user_id);
                $stmt_reset->execute();
                $login_attempts = 0; // Reset locally as well
            }
        }
        

        // Verify password
        if (!password_verify($password, $hashed_password)) {
            // Increment login_attempts
            $login_attempts++;
            $stmt_update = $conn->prepare("UPDATE users SET login_attempts = ?, last_attempt_time = NOW() WHERE id = ?");
            $stmt_update->bind_param('ii', $login_attempts, $user_id);
            $stmt_update->execute();

            if ($login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
                $response['status'] = 'error';
                $response['message'] = "Too many login attempts. Please try again after " . (self::LOCKOUT_DURATION / 60) . " minutes.";
            } else {
                $remaining_attempts = self::MAX_LOGIN_ATTEMPTS - $login_attempts;
                $response['status'] = 'error';
                $response['message'] = "Invalid password. You have {$remaining_attempts} attempt(s) remaining.";
            }
            echo json_encode($response);
            exit();
        }

        // Successful login: Reset login_attempts
        $stmt_reset = $conn->prepare("UPDATE users SET login_attempts = 0 WHERE id = ?");
        $stmt_reset->bind_param('i', $user_id);
        $stmt_reset->execute();

        // Start the session and store admin ID and role
        session_start();
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $user_id;
        $_SESSION['user_role'] = $user['position'];

        // Successful admin login
        $response['status'] = 'success';
        $response['message'] = 'Admin login successful.';
        echo json_encode($response);
        exit();
    }

    public function logout() {
        session_start();

        session_unset();
        session_destroy();
        
        header("Location: piyuhub_adminlogin.html");
        exit();
    }
}
