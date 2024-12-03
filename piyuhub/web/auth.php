<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

class AuthWeb {
    // register
    public function register() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *'); // If necessary   

        global $conn;
    
        $response = array();
    
        $id_pic = $_FILES['id_pic'] ?? '';
        $fname = $_POST['fname'] ?? '';
        $lname = $_POST['lname'] ?? '';
        $college = $_POST['college'] ?? '';
        $id_no = $_POST['id_no'] ?? '';
        $email = $_POST['email'] ?? '';
        $status = $_POST['status'] ?? 'pending'; // Default value
        $position = $_POST['position'] ?? 'Student'; // Default value
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
    
        // Validate inputs
        if (empty($_FILES['id_pic']['name'])) {
            $response['status'] = 'error';
            $response['message'] = 'Please provide your ID picture.';
            echo json_encode($response);
            return;
        }        
    
        $allowed_types = ['image/jpeg', 'image/png', 'image/heic'];
        if (!in_array($id_pic['type'], $allowed_types)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid file type. Only JPG, PNG, and HEIC files are allowed.';
            echo json_encode($response);
            return;
        }
    
        if (empty($fname)) {
            $response['status'] = 'error';
            $response['message'] = 'First Name is required.';
            echo json_encode($response);
            return;
        }
    
        if (empty($lname)) {
            $response['status'] = 'error';
            $response['message'] = 'Last Name is required.';
            echo json_encode($response);
            return;
        }
    
        $valid_colleges = ['CCS', 'CTE', 'CFND', 'CAS', 'COF', 'CCJE', 'CHMT', 'CBAA', 'OTHERS'];
        if (!in_array($college, $valid_colleges)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid college value. Valid values are: ' . implode(', ', $valid_colleges);
            echo json_encode($response);
            return;
        }
    
        if (!preg_match('/^\d{4}-\d{4}$/', $id_no)) {
            $response['status'] = 'error';
            $response['message'] = 'ID Number must be in the format 0000-0000.';
            echo json_encode($response);
            return;
        }
    
        if (empty($email)) {
            $response['status'] = 'error';
            $response['message'] = 'Email is required.';
            echo json_encode($response);
            return;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid email format.';
            echo json_encode($response);
            return;
        }
    
        if (empty($password)) {
            $response['status'] = 'error';
            $response['message'] = 'Password is required.';
            echo json_encode($response);
            return;
        } elseif (strlen($password) < 6) {
            $response['status'] = 'error';
            $response['message'] = 'Password must be at least 6 characters long.';
            echo json_encode($response);
            return;
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $response['status'] = 'error';
            $response['message'] = 'Password must contain both letters and numbers.';
            echo json_encode($response);
            return;
        }
    
        if ($password !== $confirm_password) {
            $response['status'] = 'error';
            $response['message'] = 'Passwords do not match.';
            echo json_encode($response);
            return;
        }
    
        // Generate student_id based on college and existing records
        $student_id = $this->generateStudentId($college);
    
        // Handle file upload
        if ($id_pic && $id_pic['error'] == UPLOAD_ERR_OK) {
            $id_pic_dir = 'Upload/id/'; // Save in admin/Upload/id/
            $id_pic_filename = $student_id . '_' . basename($id_pic['name']);
            $id_pic_path = $id_pic_dir . $id_pic_filename;
            
            // Save file in the directory
            if (!move_uploaded_file($id_pic['tmp_name'], $id_pic_path)) {
                $response['status'] = 'error';
                $response['message'] = 'Error uploading profile picture.';
                echo json_encode($response);
                return;
            }
            
            // Save relative path in database
            $id_pic_db_path = 'Upload/id/' . $id_pic_filename;
        } else {
            $id_pic_db_path = ''; // Or handle as needed
        }
    
        // Check if user already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE id_no = ? OR email = ?");
        $stmt->bind_param("ss", $id_no, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    
        if ($result->num_rows > 0) {
            $response['status'] = 'error';
            $response['message'] = 'Account already exists or pending.';
            echo json_encode($response);
            return;
        }
    
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO users (student_id, id_pic, fname, lname, id_no, college, email, status, position, password, reset_token_hash, reset_token_expires_at, login_attempts, last_attempt_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, 0, CURRENT_TIMESTAMP)");
        $stmt->bind_param("ssssssssss", $student_id, $id_pic_db_path, $fname, $lname, $id_no, $college, $email, $status, $position, $hashed_password);
        $stmt->execute();
        $stmt->close();

        $fb_link = "https://www.facebook.com/profile.php?id=61566608349070";
        $subject = "Your PiyuHub Account Is Pending";
        $message = "
            <html>
            <head>
                <style>
                    .bold { font-weight: bold; }
                </style>
            </head>
            <body>
                <p class='bold'>Dear $fname $lname,</p>
                <p>Your account is currently under review. Please wait while our team verifies your details.</p>
                <p><a href='$fb_link'>Reset Password</a></p>
                <p>Regards,<br/>
                PiyuHub Team
                </p>
            </body>
            </html>
        ";

        // Prepare email headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= 'From: info.piyuhub@gmail.com' . "\r\n"; // Replace with your sender email
        $headers .= 'Reply-To: info.piyuhub@gmail.com' . "\r\n";

        // Send the email to the user
        if (mail($email, $subject, $message, $headers)) {
            $response['status'] = 'success';
            $response['message'] = 'Registration successful. An email notification has been sent to the user.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Registration successful, but email failed to send.';
        }

        echo json_encode($response);
    }
    
    // Helper function to generate student_id based on college
    private function generateStudentId($college) {
        header('Cache-Control: no-cache');
        global $conn;
    
        // Set the prefix based on the college
        $prefix = $college;
    
        // Get the last student_id for the given college
        $query = "SELECT student_id FROM users WHERE college = ? ORDER BY student_id DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $college);
        $stmt->execute();
        $result = $stmt->get_result();
        $last_id = $result->fetch_assoc();
    
        $number = 1; // Default starting number
    
        if ($last_id && isset($last_id['student_id'])) {
            // Extract the numeric part and increment
            $number = intval(substr($last_id['student_id'], strlen($prefix) + 1)) + 1;
        }
    
        // Generate the new student_id
        return $prefix . '-' . str_pad($number, 2, '0', STR_PAD_LEFT);
    }
    
    public function login() {
        global $conn; // Access the database connection

        $response = array();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($email)) {
            $response['status'] = 'error';
            $response['message'] = 'Email is required.';
            echo json_encode($response);
            return;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid email format.';
            echo json_encode($response);
            return;
        }

        if (empty($password)) {
            $response['status'] = 'error';
            $response['message'] = 'Password is required.';
            echo json_encode($response);
            return;
        }

        // Check if user exists and is approved
        $stmt = $conn->prepare("SELECT id, password, status, student_id, id_pic, profile_picture, fname, lname, id_no, college, email, position FROM users WHERE email = ? AND status = 'approved'");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid email or account not approved.';
            echo json_encode($response);
            return;
        }

        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];

        // Verify password
        if (!password_verify($password, $hashed_password)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid password.';
            echo json_encode($response);
            return;
        }

        // Set session variables
        if ($user['status'] === 'success') {
            $_SESSION['user_id'] = $user['userData']['id']; // Set user ID in session
            $_SESSION['student_id'] = $user['userData']['student_id']; // Set student ID in session
            // Continue with your logic...
        }

        // Remove sensitive information before sending
        unset($user['password']);
        unset($user['status']);

        // Successful login
        $response['status'] = 'success';
        $response['message'] = 'Login successful.';
        $response['userData'] = $user;
        echo json_encode($response);
    }


    public function check(){
        // check_exists.php

        header('Content-Type: application/json');

        global $conn;

        $response = array();

        $id_no = $_POST['id_no'] ?? '';
        $email = $_POST['email'] ?? '';

        // Validate inputs
        if (empty($id_no) || empty($email)) {
            $response['status'] = 'error';
            $response['message'] = 'ID Number and Email are required.';
            echo json_encode($response);
            exit;
        }

        // Check if user already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE id_no = ? OR email = ?");
        $stmt->bind_param("ss", $id_no, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $response['status'] = 'error';
            $response['message'] = 'ID Number or Email already exists.';
        } else {
            $response['status'] = 'success';
        }

        echo json_encode($response);
    }
    public function forgot() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
    
        global $conn;
    
        $response = array();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the email from the POST request
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Prepare the SQL query using mysqli
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                
                // Bind the parameter to the query
                $stmt->bind_param("s", $email); // "s" stands for string
                
                // Execute the query
                $stmt->execute();
                
                // Fetch the result
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
    
                if ($user) {
                    // Generate a reset token
                    $reset_token = bin2hex(random_bytes(16)); // Secure random token
                    $reset_token_hash = password_hash($reset_token, PASSWORD_DEFAULT); // Hash the token for security
                    $reset_token_expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Set expiration time (1 hour)
    
                    // Prepare the SQL query for updating the token and expiration
                    $stmt = $conn->prepare("
                        UPDATE users 
                        SET reset_token_hash = ?, reset_token_expires_at = ? 
                        WHERE email = ?
                    ");
                    
                    // Bind the parameters
                    $stmt->bind_param("sss", $reset_token_hash, $reset_token_expires_at, $email);
                    
                    // Execute the query
                    $stmt->execute();
    
                    // Prepare the password reset link
                    $reset_link = "https://piyuhub.netlify.app/reset.html?token=$reset_token&email=$email";
                    
                    // Prepare HTML email headers
                    $subject = "PiyuHub Password Reset";
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                    $headers .= 'From: no-reply@piyuhub.com' . "\r\n";
    
                    // Prepare the HTML email content
                    $email_message = "
                        <html>
                        <head>
                            <style>
                                .bold { font-weight: bold; }
                            </style>
                        </head>
                        <body>
                            <p class='bold'>Ka-Piyu,</p>
                            <p>You requested to reset your password. Please click the link below to reset your password:</p>
                            <p><a href='$reset_link'>Reset Password</a></p>
                            <p>This link is valid for 1 hour.</p>
                            <p>Regards,<br/>PiyuHub Team</p>
                        </body>
                        </html>
                    ";
    
                    // Send the email
                    if (mail($email, $subject, $email_message, $headers)) {
                        // Return success response
                        echo json_encode(['status' => 'success', 'message' => 'A password reset link has been sent to your email.']);
                    } else {
                        // Error sending email
                        echo json_encode(['status' => 'error', 'message' => 'Failed to send email. Please try again later.']);
                    }
                } else {
                    // Email not found in the users table
                    echo json_encode(['status' => 'error', 'message' => 'No account found with that email.']);
                }
            } else {
                // Invalid email format
                echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
            }
        } else {
            // Not a POST request
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
        }
    }
    public function reset_password() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
    
        global $conn;
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the token, email, and new password from the POST request
            $token = $_POST['token'];
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $newPassword = $_POST['newPassword'];
    
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Prepare SQL to check if the token and email match
                $stmt = $conn->prepare("SELECT id, password, reset_token_hash, reset_token_expires_at FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
    
                if ($user) {
                    // Check if the token is valid and has not expired
                    if ($user['reset_token_hash'] && password_verify($token, $user['reset_token_hash']) && strtotime($user['reset_token_expires_at']) > time()) {
                        
                        // Check if the new password matches the old password
                        if (password_verify($newPassword, $user['password'])) {
                            echo json_encode(['status' => 'error', 'message' => 'New password cannot be the same as the old password.']);
                            return;
                        }
    
                        // Validate that the new password contains letters and numbers
                        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]/', $newPassword)) {
                            echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one letter and one number.']);
                            return;
                        }
    
                        // Validate that the password is longer than 6 characters
                        if (strlen($newPassword) <= 6) {
                            echo json_encode(['status' => 'error', 'message' => 'Password must be longer than 6 characters.']);
                            return;
                        }
    
                        // Hash the new password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
                        // Update the user's password and reset token fields
                        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE email = ?");
                        $stmt->bind_param("ss", $hashedPassword, $email);
                        $stmt->execute();
    
                        // Return success response
                        echo json_encode(['status' => 'success', 'message' => 'Password has been reset successfully.']);
                    } else {
                        // Invalid token or token has expired
                        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token.']);
                    }
                } else {
                    // Email not found
                    echo json_encode(['status' => 'error', 'message' => 'No account found with that email.']);
                }
            } else {
                // Invalid email format
                echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
            }
        } else {
            // Not a POST request
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
        }
    }
    public function change_email_auth() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
    
        global $conn;
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Retrieve id_no and password from the POST request
            $id_no = trim($_POST['id_no']); // Use trim() for basic sanitization
            $password = $_POST['password'];
    
            // Prepare SQL to check if the user exists with the given id_no
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE id_no = ?");
            $stmt->bind_param("s", $id_no);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
    
            if ($user) {
                // Verify the password entered matches the hashed password in the database
                if (password_verify($password, $user['password'])) {
                    echo json_encode(['status' => 'success', 'message' => 'Password verified. Proceed to change email.']);
                } else {
                    // If the password is incorrect
                    echo json_encode(['status' => 'error', 'message' => 'Incorrect password. Please try again.']);
                }
            } else {
                // If no user is found with the given id_no
                echo json_encode(['status' => 'error', 'message' => 'No account found with that ID number.']);
            }
        } else {
            // If the request is not a POST request
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
        }
    }
    public function change_email() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
    
        global $conn;
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get user ID and new email from the request
            $userId = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
            $newEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
            if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                // Check if the email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->bind_param("si", $newEmail, $userId);
                $stmt->execute();
                $result = $stmt->get_result();
    
                if ($result->num_rows > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
                    return;
                }
    
                // Prepare SQL to update the email
                $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->bind_param("si", $newEmail, $userId);
    
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Email updated successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update email.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
        }
    }
    
    
    
      
}
?>
