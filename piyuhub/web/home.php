<?php

class PostWeb {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    public function post_concern() {
        global $conn;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
    
        // Retrieve posted data
        $student_id = $_POST['student_id'] ?? null;
        $student_name = $_POST['student_name'] ?? null;
        $post_text = isset($_POST['concern']) ? trim($_POST['concern']) : null;
    
        if (empty($student_id) || empty($student_name)) {
            echo json_encode(['error' => true, 'message' => 'Student information is missing.']);
            return;
        }
    
        if (empty($post_text)) {
            echo json_encode(['error' => true, 'message' => 'Concern text is missing.']);
            return;
        }
    
        // Handle image upload
        $uploaded_images = [];  // Array to store image paths
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['error'] as $error) {
                if ($error !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'error' => 'Error uploading image.']);
                    return;
                }
            }
    
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                $image_name = basename($_FILES['images']['name'][$i]);
                $target_dir = "Upload/posts/";
                $target_file = $target_dir . uniqid() . "_" . $image_name;
    
                if (!move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    echo json_encode(['success' => false, 'error' => 'Error uploading image.']);
                    return;
                }
    
                // Store each uploaded image path in the array
                $uploaded_images[] = $target_file;
            }
        }
    
        // Insert the post into the 'post' table
        $query = "INSERT INTO post (student_id, student_name, post_text) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $student_id, $student_name, $post_text);
    
        if ($stmt->execute()) {
            $post_id = $stmt->insert_id;  // Get the inserted post's ID
            
            // Insert the images into a separate table (if there are images)
            if (!empty($uploaded_images)) {
                $image_query = "INSERT INTO post_images (post_id, image_path) VALUES (?, ?)";
                $image_stmt = $conn->prepare($image_query);
    
                foreach ($uploaded_images as $image_path) {
                    $image_stmt->bind_param("is", $post_id, $image_path);
                    $image_stmt->execute();
                }
                $image_stmt->close();
            }
    
            echo json_encode(['success' => true, 'message' => 'Your concern has been posted successfully!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error posting concern: ' . $stmt->error]);
        }
    
        $stmt->close();
    }
    public function post_concern_college() {
        global $conn;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
    
        // Retrieve posted data
        $student_id = $_POST['student_id'] ?? null;
        $student_name = $_POST['student_name'] ?? null;
        $post_text = isset($_POST['concern']) ? trim($_POST['concern']) : null;
    
        if (empty($student_id) || empty($student_name)) {
            echo json_encode(['success' => false, 'message' => 'Student information is missing.']);
            return;
        }
    
        if (empty($post_text)) {
            echo json_encode(['success' => false, 'message' => 'Concern text is missing.']);
            return;
        }
    
        // Handle image upload (if any)
        $uploaded_images = [];
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['error'] as $error) {
                if ($error !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'Error uploading image.']);
                    return;
                }
            }
    
            // Upload each image
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                $image_name = basename($_FILES['images']['name'][$i]);
                $target_dir = "Upload/posts/";
                $unique_image_name = uniqid() . "_" . $image_name;
                $target_file = $target_dir . $unique_image_name;
    
                if (!move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    echo json_encode(['success' => false, 'message' => 'Error uploading image.']);
                    return;
                }
    
                $uploaded_images[] = $target_file;
            }
        }
    
        // Insert the post into the 'post_college' table
        $query = "INSERT INTO post_college (student_id, student_name, post_text) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $student_id, $student_name, $post_text);
    
        if ($stmt->execute()) {
            $post_id = $stmt->insert_id; // Get the inserted post's ID
    
            // Insert the images into a separate table (if there are images)
            if (!empty($uploaded_images)) {
                $image_query = "INSERT INTO post_images_college (post_id, image_path) VALUES (?, ?)";
                $image_stmt = $conn->prepare($image_query);
    
                foreach ($uploaded_images as $image_path) {
                    $image_stmt->bind_param("is", $post_id, $image_path);
                    $image_stmt->execute();
                }
                $image_stmt->close();
            }
    
            // Determine the type of user and send notifications
            $position_query = "SELECT position, college, email FROM users WHERE student_id = ?";
            $pos_stmt = $conn->prepare($position_query);
            $pos_stmt->bind_param("s", $student_id);
            $pos_stmt->execute();
            $user_data = $pos_stmt->get_result()->fetch_assoc();
            $user_position = $user_data['position'];
            $user_college = $user_data['college'];
            $user_email = $user_data['email'];
    
            // Logic for sending notifications
            if ($user_position === 'Developer' || $user_position === 'Admin') {
                // Notify all users, regardless of college
                $this->notifyAllUsers($post_id, "New announcement by $student_name", $post_text);
            } elseif ($user_position === 'SSC' || $user_position === 'Representative') {
                // Notify users within the same college
                $this->notifyCollegeUsers($post_id, $user_college, "New announcement by $student_name of $user_college", $post_text);
                // Notify Developers and Admins as well
                $this->notifyAdmins($post_id, "New announcement by $student_name ($user_position) in $user_college", $post_text);
            }
    
            echo json_encode(['success' => true, 'message' => 'Your concern has been posted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error posting concern: ' . $stmt->error]);
        }
    
        $stmt->close();
    }
    public function post_concern_lost() {
        global $conn;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
    
        // Retrieve posted data
        $student_id = $_POST['student_id'] ?? null;
        $student_name = $_POST['student_name'] ?? null;
        $post_text = isset($_POST['concern']) ? trim($_POST['concern']) : null;
        $status = $_POST['status'] ?? 'LOST'; // Default to 'LOST' if status is missing
    
        if (empty($student_id) || empty($student_name)) {
            echo json_encode(['error' => true, 'message' => 'Student information is missing.']);
            return;
        }
    
        if (empty($post_text)) {
            echo json_encode(['error' => true, 'message' => 'Concern text is missing.']);
            return;
        }
    
        // Handle image upload
        $uploaded_images = [];  // Array to store image paths
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['error'] as $error) {
                if ($error !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'error' => 'Error uploading image.']);
                    return;
                }
            }
    
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                $image_name = basename($_FILES['images']['name'][$i]);
                $target_dir = "Upload/posts/";
                $target_file = $target_dir . uniqid() . "_" . $image_name;
    
                if (!move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    echo json_encode(['success' => false, 'error' => 'Error uploading image.']);
                    return;
                }
    
                // Store each uploaded image path in the array
                $uploaded_images[] = $target_file;
            }
        }
    
        // Insert the concern with the status
        $query = "INSERT INTO post_lost (student_id, student_name, post_text, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $student_id, $student_name, $post_text, $status);
    
        if ($stmt->execute()) {
            $post_id = $stmt->insert_id;  // Get the inserted post's ID
    
            // Insert the images into a separate table (if there are images)
            if (!empty($uploaded_images)) {
                $image_query = "INSERT INTO post_images_lost (post_id, image_path) VALUES (?, ?)";
                $image_stmt = $conn->prepare($image_query);
    
                foreach ($uploaded_images as $image_path) {
                    $image_stmt->bind_param("is", $post_id, $image_path);
                    $image_stmt->execute();
                }
                $image_stmt->close();
            }
    
            echo json_encode(['success' => true, 'message' => 'Your concern has been posted successfully!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error posting concern: ' . $stmt->error]);
        }
    
        $stmt->close();
    }
    
    
    // Function to notify all users
    public function notifyAllUsers($post_id, $message, $post_text) {
        global $conn;
    
        // Fetch all users' emails
        $users_query = "SELECT id, email FROM users";
        $users_result = $conn->query($users_query);
    
        while ($row = $users_result->fetch_assoc()) {
            $user_id = $row['id'];
            $user_email = $row['email'];
            
            // Insert notification
            $this->insertNotification($post_id, $user_id, $message, $post_text);
            
            // Send email notification
            $this->sendEmailNotification($user_email, $message, $post_text);
        }
    }
    
    // Function to notify users in the same college
    public function notifyCollegeUsers($post_id, $college, $message, $post_text) {
        global $conn;
    
        // Fetch users' emails from the same college
        $users_query = "SELECT id, email FROM users WHERE college = ?";
        $stmt = $conn->prepare($users_query);
        $stmt->bind_param("s", $college);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];
            $user_email = $row['email'];
            
            // Insert notification
            $this->insertNotification($post_id, $user_id, $message, $post_text);
            
            // Send email notification
            $this->sendEmailNotification($user_email, $message, $post_text);
        }
    
        $stmt->close();
    }
    
    // Function to notify Admins
    public function notifyAdmins($post_id, $message, $post_text) {
        global $conn;
    
        // Fetch Admins' emails
        $users_query = "SELECT id, email FROM users WHERE position IN ('Developer', 'Admin')";
        $users_result = $conn->query($users_query);
    
        // Check if the query was successful
        if ($users_result) {
            while ($row = $users_result->fetch_assoc()) {
                $user_id = $row['id'];
                $user_email = $row['email'];
                
                // Insert notification
                $this->insertNotification($post_id, $user_id, $message, $post_text);
                
                // Send email notification
                $this->sendEmailNotification($user_email, $message, $post_text);
            }
        } else {
            // Handle query failure
            error_log("Error fetching users: " . $conn->error);
        }
    }
    
    // Function to send email notifications
    private function sendEmailNotification($user_email, $message, $post_text) {
        // Prepare email headers
        $subject = "New Announcement from PiyuHub";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= 'From: info.piyuhub@gmail.com' . "\r\n";
    
        // Prepare the email content
        $fb_link = "https://www.facebook.com/profile.php?id=61566942153854";
        $email_message = "
            <html>
            <head>
                <style>
                    .bold { font-weight: bold; }
                </style>
            </head>
            <body>
                <p class='bold'>Dear User,</p>
                <p>$message</p>
                <p><strong>Post Content:</strong> $post_text</p> <!-- Include post text -->
                <p><a href='$fb_link'>Support our facebook page!</a></p>
                <p>Regards,<br/>
                PiyuHub Team
                </p>
            </body>
            </html>
        ";
    
        // Send the email
        mail($user_email, $subject, $email_message, $headers);
    }    
    // Function to insert a notification
    public function insertNotification($post_id, $user_id, $message) {
        global $conn;
        $query = "INSERT INTO notifications (post_id, user_id, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $post_id, $user_id, $message);
        $stmt->execute();
        $stmt->close();
    }
    public function get_notifications() {
        global $conn;
        header('Content-Type: application/json');
    
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
    
            // Fetch unread notifications for the user
            $query = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
    
            echo json_encode($notifications);
            $stmt->close();
        } else {
            echo json_encode(['message' => 'User ID not provided']);
        }
    }
    public function markNotificationAsRead() {
        global $conn;
        header('Content-Type: application/json');
    
        if (isset($_POST['notification_id'])) {
            $notification_id = $_POST['notification_id'];
    
            // Update the notification status to read
            $query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $notification_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating notification status.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Notification ID not provided.']);
        }
    }
    public function get_posts() {
        global $conn;
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET");
    
        $query = "
            SELECT 
                p.id, 
                p.post_text, 
                CONCAT(u.fname, ' ', u.lname) AS student_name, 
                u.college, 
                u.profile_picture, 
                GROUP_CONCAT(pi.image_path) AS post_images,
                p.created_at,
                (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
            FROM 
                post p
            JOIN 
                users u ON p.student_id = u.student_id
            LEFT JOIN 
                post_images pi ON pi.post_id = p.id
            GROUP BY 
                p.id
            ORDER BY 
                p.created_at DESC
        ";
    
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            if (!$row['profile_picture']) {
                $row['profile_picture'] = 'Upload/logo/default_profile.png';
            }
            $posts[] = $row;
        }
    
        echo json_encode($posts);
        $stmt->close();
    }
    public function get_posts_by_position() {
        global $conn;
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET");
    
        // Retrieve the user's college and position from GET parameters (or session if applicable)
        $user_college = isset($_GET['college']) ? $_GET['college'] : '';
        $user_position = isset($_GET['position']) ? $_GET['position'] : '';
    
        if (empty($user_college) || empty($user_position)) {
            echo json_encode(["error" => "User college or position not provided"]);
            return;
        }
    
        // Define positions that can see all posts, regardless of college
        $allowed_positions = ['Developer', 'Admin', 'SSC'];
    
        // SQL query to fetch posts
        // 1. Everyone can see posts by Developers or Admins (no college filter for these posts)
        // 2. If the user is a Developer, Admin, or SSC, they can see all posts.
        // 3. Otherwise, show posts from the same college as the user.
        $query = "
            SELECT 
                p.id, 
                p.post_text, 
                CONCAT(u.fname, ' ', u.lname) AS student_name, 
                u.college, 
                u.profile_picture, 
                GROUP_CONCAT(pi.image_path) AS post_images,
                p.created_at,
                (SELECT COUNT(*) FROM comments_college c WHERE c.post_id = p.id) AS comment_count,
                CASE 
                    WHEN u.position = 'Representative' THEN 'Repre'
                    ELSE u.position
                END AS position
            FROM 
                post_college p
            JOIN 
                users u ON p.student_id = u.student_id
            LEFT JOIN 
                post_images_college pi ON pi.post_id = p.id
            WHERE 
                (
                    u.position IN ('Developer', 'Admin')  -- Posts made by Developers or Admins
                    OR ? IN ('" . implode("', '", $allowed_positions) . "') -- The user is Developer, Admin, or SSC
                    OR u.college = ? -- Otherwise, restrict to the same college
                )
            GROUP BY 
                p.id
            ORDER BY 
                p.created_at DESC
        ";
    
        // Prepare the SQL statement
        $stmt = $conn->prepare($query);
    
        // Bind the user's position and college
        $stmt->bind_param('ss', $user_position, $user_college);
    
        // Execute the statement and get the result
        $stmt->execute();
        $result = $stmt->get_result();
    
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            if (!$row['profile_picture']) {
                $row['profile_picture'] = 'Upload/logo/default_profile.png';
            }
            $posts[] = $row;
        }
    
        // Output the posts as JSON
        echo json_encode($posts);
        
        // Close the statement
        $stmt->close();
    }
    
    public function get_posts_lost() {
        global $conn;
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET");
    
        $query = "
            SELECT 
                p.id, 
                p.post_text,
                p.status, 
                CONCAT(u.fname, ' ', u.lname) AS student_name, 
                u.college, 
                u.profile_picture, 
                GROUP_CONCAT(pi.image_path) AS post_images,
                p.created_at,
                (SELECT COUNT(*) FROM comments_lost c WHERE c.post_id = p.id) AS comment_count
            FROM 
                post_lost p
            JOIN 
                users u ON p.student_id = u.student_id
            LEFT JOIN 
                post_images_lost pi ON pi.post_id = p.id
            GROUP BY 
                p.id
            ORDER BY 
                p.created_at DESC
        ";
    
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            if (!$row['profile_picture']) {
                $row['profile_picture'] = 'Upload/logo/default_profile.png';
            }
            $posts[] = $row;
        }
    
        echo json_encode($posts);
        $stmt->close();
    }
    
    public function load_account() {
        global $conn;
    
        // Check if the student_id parameter is set
        if (isset($_GET['student_id'])) {
            $student_id = $_GET['student_id'];
    
            // Prepare the SQL statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT * FROM users WHERE id_no = ?");
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            
            // Get the result
            $result = $stmt->get_result();
            
            // Check if the user exists
            if ($result->num_rows > 0) {
                // Fetch the user data
                $userData = $result->fetch_assoc();
                $response = [
                    'success' => true,
                    'userData' => $userData
                ];
            } else {
                $response = [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }
    
            // Close the statement
            $stmt->close();
        } else {
            $response = [
                'success' => false,
                'error' => 'Student ID is required'
            ];
        }
    
        // Set the content type to JSON and return the response
        header('Content-Type: application/json');
        echo json_encode($response);
    
    }
    public function upload_profile() {
        // Check if a file is uploaded and if student ID is set
        if (isset($_FILES['profile_picture']) && isset($_POST['student_id'])) {
            $studentId = $_POST['student_id'];
            $profilePic = $_FILES['profile_picture'];
    
            // Define the directory where the image will be saved
            $uploadDir = 'Upload/profile/';
            $fileExtension = pathinfo($profilePic['name'], PATHINFO_EXTENSION);
    
            // Generate a unique filename using student ID and original file name
            $originalFileName = pathinfo($profilePic['name'], PATHINFO_FILENAME); // Get the original file name without extension
            $uniqueFileName = 'CCS-' . $studentId . '-' . uniqid() . '-' . $originalFileName . '.' . $fileExtension; // Create a unique file name
            $uploadFile = $uploadDir . $uniqueFileName;
    
            // Check if the file is an image and its size
            $check = getimagesize($profilePic['tmp_name']);
            if ($check !== false && $profilePic['size'] < 5000000) { // Limit file size to 5MB
    
                // Retrieve the image's width and height
                $width = $check[0];
                $height = $check[1];
    
                // Set the acceptable width-to-height ratio range for proportional images
                $minRatio = 0.9;  // Minimum acceptable width/height ratio
                $maxRatio = 1.1;  // Maximum acceptable width/height ratio
                $imageRatio = $width / $height;
    
                // Check if the image is approximately square (within the ratio range)
                if ($imageRatio >= $minRatio && $imageRatio <= $maxRatio) {
                    // Move the uploaded file to the directory
                    if (move_uploaded_file($profilePic['tmp_name'], $uploadFile)) {
                        // Store the file path in the database
                        $filePathInDb = 'Upload/profile/' . $uniqueFileName;
    
                        // Update the profile picture in the database
                        $sql = "UPDATE users SET profile_picture = ? WHERE id_no = ?";
                        $stmt = $this->db->prepare($sql);
                        if ($stmt->execute([$filePathInDb, $studentId])) {
                            echo json_encode(['success' => 'Profile picture uploaded successfully!', 'profile_picture' => $filePathInDb]);
                        } else {
                            echo json_encode(['error' => 'Failed to update profile picture in database.']);
                        }
                    } else {
                        echo json_encode(['error' => 'Failed to upload profile picture.']);
                    }
                } else {
                    echo json_encode(['error' => 'Invalid image dimensions. Please upload a square or near-square image.']);
                }
            } else {
                echo json_encode(['error' => 'Invalid file type or file size too large.']);
            }
        } else {
            echo json_encode(['error' => 'No file uploaded or missing student ID.']);
        }
    }
    
    public function get_comments() {
        global $conn; // Assuming you are using $conn (similar to get_posts)
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET");
    
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['post_id'])) {
            $post_id = $_GET['post_id'];
            
            // Query to get comments for a specific post, including the profile picture and comment image
            $query = "
                SELECT 
                    c.id,
                    c.comment_text, 
                    c.comment_image,  -- Include the comment_image field
                    c.created_at, 
                    CONCAT(u.fname, ' ', u.lname) AS student_name, 
                    u.college,
                    u.profile_picture -- Add profile_picture to the selected columns
                FROM 
                    comments c 
                JOIN 
                    users u ON c.student_id = u.student_id
                WHERE 
                    c.post_id = ?
                ORDER BY 
                    c.created_at ASC
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $post_id); // Use bind_param for prepared statements
            $stmt->execute();
            $result = $stmt->get_result();
    
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
    
            // Output the comments in JSON format
            echo json_encode($comments);
            $stmt->close();
        } else {
            http_response_code(400); // Bad request
            echo json_encode(["message" => "Invalid request"]);
        }
    }
    public function get_comments_college() {
        global $conn; // Assuming you are using $conn (similar to get_posts)
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET");
    
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['post_id'])) {
            $post_id = $_GET['post_id'];
            
            // Query to get comments for a specific post, including the profile picture and comment image
            $query = "
                SELECT 
                    c.id,
                    c.comment_text, 
                    c.comment_image,  -- Include the comment_image field
                    c.created_at, 
                    CONCAT(u.fname, ' ', u.lname) AS student_name, 
                    u.college,
                    u.position,
                    u.profile_picture -- Add profile_picture to the selected columns
                FROM 
                    comments_college c 
                JOIN 
                    users u ON c.student_id = u.student_id
                WHERE 
                    c.post_id = ?
                ORDER BY 
                    c.created_at ASC
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $post_id); // Use bind_param for prepared statements
            $stmt->execute();
            $result = $stmt->get_result();
    
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
    
            // Output the comments in JSON format
            echo json_encode($comments);
            $stmt->close();
        } else {
            http_response_code(400); // Bad request
            echo json_encode(["message" => "Invalid request"]);
        }
    }
    public function get_comments_lost() {
        global $conn; // Assuming you are using $conn (similar to get_posts)
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET");
    
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['post_id'])) {
            $post_id = $_GET['post_id'];
            
            // Query to get comments for a specific post, including the profile picture and comment image
            $query = "
                SELECT 
                    c.id,
                    c.comment_text, 
                    c.comment_image,  -- Include the comment_image field
                    c.created_at, 
                    CONCAT(u.fname, ' ', u.lname) AS student_name, 
                    u.college,
                    u.profile_picture -- Add profile_picture to the selected columns
                FROM 
                    comments_lost c 
                JOIN 
                    users u ON c.student_id = u.student_id
                WHERE 
                    c.post_id = ?
                ORDER BY 
                    c.created_at ASC
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $post_id); // Use bind_param for prepared statements
            $stmt->execute();
            $result = $stmt->get_result();
    
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
    
            // Output the comments in JSON format
            echo json_encode($comments);
            $stmt->close();
        } else {
            http_response_code(400); // Bad request
            echo json_encode(["message" => "Invalid request"]);
        }
    }
    public function add_comment() {
        global $conn;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        
        // Initialize variables
        $comment_image = null;
        $uploadDir = 'Upload/comments/'; // Directory to store uploaded images
        
        // Check if there's an image upload
        if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
            $comment_image = $uploadDir . basename($_FILES['comment_image']['name']);
            $imageFileType = strtolower(pathinfo($comment_image, PATHINFO_EXTENSION));
            
            // Validate image file type
            $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'jfif', 'heic', 'gif');
            if (!in_array($imageFileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image file type.']);
                return;
            }
            
            // Move uploaded file to the server directory
            if (!move_uploaded_file($_FILES['comment_image']['tmp_name'], $comment_image)) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                return;
            }
        }
        
        // Retrieve posted data from JSON or POST (for text data)
        $post_id = $_POST['post_id'] ?? null;
        $student_id = $_POST['student_id'] ?? null;
        $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : ''; // Set to empty string if not provided
    
        // Validate input
        if (empty($post_id) || empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Post ID or student ID is missing.']);
            return;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Retrieve the student's name
            $stmt = $conn->prepare("SELECT fname, lname FROM users WHERE student_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare student query: " . $conn->error);
            }
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            // Check if student exists
            if ($result->num_rows === 0) {
                throw new Exception("Student not found with ID: $student_id");
            }
            $user = $result->fetch_assoc();
            $stmt->close();
    
            // Insert the comment into the comments table
            $insert_stmt = $conn->prepare("
                INSERT INTO comments (post_id, student_id, comment_text, fname, lname, comment_image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            if (!$insert_stmt) {
                throw new Exception("Failed to prepare insert query: " . $conn->error);
            }
    
            // Allow the comment_text to be NULL if empty
            $insert_stmt->bind_param("isssss", $post_id, $student_id, $comment_text, $user['fname'], $user['lname'], $comment_image);
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to execute insert query: " . $insert_stmt->error);
            }
            $insert_stmt->close();
    
            // Commit transaction
            $conn->commit();
    
            echo json_encode(['success' => true, 'message' => 'Comment added successfully!']);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            error_log("Error in add_comment: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
    public function add_comment_college() {
        global $conn;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        
        // Initialize variables
        $comment_image = null;
        $uploadDir = 'Upload/comments/'; // Directory to store uploaded images
        
        // Check if there's an image upload
        if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
            $comment_image = $uploadDir . basename($_FILES['comment_image']['name']);
            $imageFileType = strtolower(pathinfo($comment_image, PATHINFO_EXTENSION));
            
            // Validate image file type
            $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'jfif', 'heic', 'gif');
            if (!in_array($imageFileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image file type.']);
                return;
            }
            
            // Move uploaded file to the server directory
            if (!move_uploaded_file($_FILES['comment_image']['tmp_name'], $comment_image)) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                return;
            }
        }
        
        // Retrieve posted data from JSON or POST (for text data)
        $post_id = $_POST['post_id'] ?? null;
        $student_id = $_POST['student_id'] ?? null;
        $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : ''; // Set to empty string if not provided
    
        // Validate input
        if (empty($post_id) || empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Post ID or student ID is missing.']);
            return;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Retrieve the student's name
            $stmt = $conn->prepare("SELECT fname, lname FROM users WHERE student_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare student query: " . $conn->error);
            }
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            // Check if student exists
            if ($result->num_rows === 0) {
                throw new Exception("Student not found with ID: $student_id");
            }
            $user = $result->fetch_assoc();
            $stmt->close();
    
            // Insert the comment into the comments table
            $insert_stmt = $conn->prepare("
                INSERT INTO comments_college (post_id, student_id, comment_text, fname, lname, comment_image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            if (!$insert_stmt) {
                throw new Exception("Failed to prepare insert query: " . $conn->error);
            }
    
            // Allow the comment_text to be NULL if empty
            $insert_stmt->bind_param("isssss", $post_id, $student_id, $comment_text, $user['fname'], $user['lname'], $comment_image);
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to execute insert query: " . $insert_stmt->error);
            }
            $insert_stmt->close();
    
            // Commit transaction
            $conn->commit();
    
            echo json_encode(['success' => true, 'message' => 'Comment added successfully!']);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            error_log("Error in add_comment: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
    public function add_comment_lost() {
        global $conn;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        
        // Initialize variables
        $comment_image = null;
        $uploadDir = 'Upload/comments/'; // Directory to store uploaded images
        
        // Check if there's an image upload
        if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
            $comment_image = $uploadDir . basename($_FILES['comment_image']['name']);
            $imageFileType = strtolower(pathinfo($comment_image, PATHINFO_EXTENSION));
            
            // Validate image file type
            $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'jfif', 'heic', 'gif');
            if (!in_array($imageFileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image file type.']);
                return;
            }
            
            // Move uploaded file to the server directory
            if (!move_uploaded_file($_FILES['comment_image']['tmp_name'], $comment_image)) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                return;
            }
        }
        
        // Retrieve posted data from JSON or POST (for text data)
        $post_id = $_POST['post_id'] ?? null;
        $student_id = $_POST['student_id'] ?? null;
        $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : ''; // Set to empty string if not provided
    
        // Validate input
        if (empty($post_id) || empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Post ID or student ID is missing.']);
            return;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Retrieve the student's name
            $stmt = $conn->prepare("SELECT fname, lname FROM users WHERE student_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare student query: " . $conn->error);
            }
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            // Check if student exists
            if ($result->num_rows === 0) {
                throw new Exception("Student not found with ID: $student_id");
            }
            $user = $result->fetch_assoc();
            $stmt->close();
    
            // Insert the comment into the comments table
            $insert_stmt = $conn->prepare("
                INSERT INTO comments_lost (post_id, student_id, comment_text, fname, lname, comment_image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            if (!$insert_stmt) {
                throw new Exception("Failed to prepare insert query: " . $conn->error);
            }
    
            // Allow the comment_text to be NULL if empty
            $insert_stmt->bind_param("isssss", $post_id, $student_id, $comment_text, $user['fname'], $user['lname'], $comment_image);
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to execute insert query: " . $insert_stmt->error);
            }
            $insert_stmt->close();
    
            // Commit transaction
            $conn->commit();
    
            echo json_encode(['success' => true, 'message' => 'Comment added successfully!']);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            error_log("Error in add_comment: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
    public function delete_post() {
        global $conn;
    
        // Check if the request method is DELETE
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // Parse the input JSON body for DELETE requests
            $input = json_decode(file_get_contents('php://input'), true);
    
            // Check if postId is provided
            if (isset($input['postId'])) {
                $postId = intval($input['postId']); // Get the post ID from the request body
    
                // Prepare the SQL statement to delete the post
                $sql = "DELETE FROM post WHERE id = ?";
    
                if ($stmt = $conn->prepare($sql)) {
                    // Bind the parameter
                    $stmt->bind_param('i', $postId);
    
                    // Execute the statement
                    if ($stmt->execute()) {
                        // Check if a row was affected
                        if ($stmt->affected_rows > 0) {
                            http_response_code(200); // OK
                            echo json_encode(['message' => 'Post deleted successfully.']);
                        } else {
                            http_response_code(404); // Not Found
                            echo json_encode(['message' => 'Post not found.']);
                        }
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(['message' => 'Error deleting post: ' . $stmt->error]);
                    }
    
                    // Close the statement
                    $stmt->close();
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(['message' => 'Database error: ' . $conn->error]);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(['message' => 'Invalid request. Please provide a valid post ID.']);
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['message' => 'Invalid request method. Only DELETE allowed.']);
        }
    }
    public function delete_post_college() {
        global $conn;
    
        // Check if the request method is DELETE
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // Parse the input JSON body for DELETE requests
            $input = json_decode(file_get_contents('php://input'), true);
    
            // Check if postId is provided
            if (isset($input['postId'])) {
                $postId = intval($input['postId']); // Get the post ID from the request body
    
                // Prepare the SQL statement to delete the post
                $sql = "DELETE FROM post_college WHERE id = ?";
    
                if ($stmt = $conn->prepare($sql)) {
                    // Bind the parameter
                    $stmt->bind_param('i', $postId);
    
                    // Execute the statement
                    if ($stmt->execute()) {
                        // Check if a row was affected
                        if ($stmt->affected_rows > 0) {
                            http_response_code(200); // OK
                            echo json_encode(['message' => 'Post deleted successfully.']);
                        } else {
                            http_response_code(404); // Not Found
                            echo json_encode(['message' => 'Post not found.']);
                        }
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(['message' => 'Error deleting post: ' . $stmt->error]);
                    }
    
                    // Close the statement
                    $stmt->close();
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(['message' => 'Database error: ' . $conn->error]);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(['message' => 'Invalid request. Please provide a valid post ID.']);
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['message' => 'Invalid request method. Only DELETE allowed.']);
        }
    }
    public function delete_post_lost() {
        global $conn;
    
        // Check if the request method is DELETE
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // Parse the input JSON body for DELETE requests
            $input = json_decode(file_get_contents('php://input'), true);
    
            // Check if postId is provided
            if (isset($input['postId'])) {
                $postId = intval($input['postId']); // Get the post ID from the request body
    
                // Prepare the SQL statement to delete the post
                $sql = "DELETE FROM post_lost WHERE id = ?";
    
                if ($stmt = $conn->prepare($sql)) {
                    // Bind the parameter
                    $stmt->bind_param('i', $postId);
    
                    // Execute the statement
                    if ($stmt->execute()) {
                        // Check if a row was affected
                        if ($stmt->affected_rows > 0) {
                            http_response_code(200); // OK
                            echo json_encode(['message' => 'Post deleted successfully.']);
                        } else {
                            http_response_code(404); // Not Found
                            echo json_encode(['message' => 'Post not found.']);
                        }
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(['message' => 'Error deleting post: ' . $stmt->error]);
                    }
    
                    // Close the statement
                    $stmt->close();
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(['message' => 'Database error: ' . $conn->error]);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(['message' => 'Invalid request. Please provide a valid post ID.']);
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['message' => 'Invalid request method. Only DELETE allowed.']);
        }
    }
    public function get_user_position() {
        global $conn; // Assuming you are using $conn
    
        header('Content-Type: application/json'); // Ensure the response is JSON
    
        // Check if the request method is GET and the student ID is provided
        $student_id = $_GET['student_id'] ?? null; // Use $_GET instead of $_POST for GET requests
    
        if ($student_id) {
            // Prepare the SQL statement to get the user's position
            $sql = "SELECT position FROM users WHERE student_id = ?";
    
            if ($stmt = $conn->prepare($sql)) {
                // Bind the parameter
                $stmt->bind_param('s', $student_id); // Use 's' for string if student_id is a string
    
                // Execute the statement
                if ($stmt->execute()) {
                    // Get the result
                    $result = $stmt->get_result();
    
                    // Check if a row was returned
                    if ($row = $result->fetch_assoc()) {
                        http_response_code(200); // OK
                        echo json_encode(['position' => $row['position']]);
                    } else {
                        http_response_code(404); // Not Found
                        echo json_encode(['message' => 'User not found.']);
                    }
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(['message' => 'Error retrieving user position.']);
                }
    
                // Close the statement
                $stmt->close();
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(['message' => 'Database error.']);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Invalid request. Please provide a valid student ID.']);
        }
    }
    public function send_contact() {
        try {
            header('Content-Type: application/json');
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $name = htmlspecialchars(strip_tags(trim($_POST['name'])));
                $subject = htmlspecialchars(strip_tags(trim($_POST['subject'])));
                $email = htmlspecialchars(strip_tags(trim($_POST['email'])));
                $message = htmlspecialchars(strip_tags(trim($_POST['message'])));
    
                if (!empty($name) && !empty($subject) && !empty($email) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $to = 'info.piyuhub@gmail.com';
                    $email_subject = "PiyuHub | New Contact Form Submission: $subject";
                    $email_body = "Name: $name\nEmail: $email\nMessage:\n$message";
                    $headers = "From: $name <$email>\r\n";
                    $headers .= "Reply-To: $email\r\n";
    
                    if (mail($to, $email_subject, $email_body, $headers)) {
                        echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Unable to send message. Please try again later.']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields correctly.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred.']);
        }
    }
    public function load_users() {
        global $conn;
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET");
    
        // Get the current user's ID from the GET parameter
        $current_user_id = isset($_GET['current_user_id']) ? intval($_GET['current_user_id']) : 0;
    
        // Prepare the SQL query to exclude the current user and include unread message count
        $query = "
            SELECT 
                u.id, 
                CONCAT(u.fname, ' ', u.lname) AS full_name, 
                u.profile_picture, 
                u.college,
                (SELECT COUNT(*) 
                 FROM messages 
                 WHERE 
                     recipient_id = ? AND sender_id = u.id AND is_read = 0
                ) AS message_count 
            FROM 
                users u
            WHERE 
                u.status = 'approved' AND u.id != ?
            ORDER BY 
                message_count DESC, full_name ASC
        ";
    
        // Prepare and execute the statement
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $current_user_id, $current_user_id); // Bind the current user's ID
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Fetch the results
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    
        // Return the users as a JSON response
        echo json_encode(['success' => true, 'users' => $users]);
    
        // Close the statement
        $stmt->close();
    }
    
    
    
    public function get_conversation() {
        global $conn;
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET");
    
        $sender_id = $_GET['sender_id'];
        $recipient_id = $_GET['recipient_id'];
    
        // Fetch messages first
        $query = "
            SELECT 
                m.id, 
                m.sender_id, 
                m.recipient_id, 
                m.message, 
                m.created_at,
                CONCAT(u.fname, ' ', u.lname) AS sender_name
            FROM 
                messages m
            JOIN 
                users u ON m.sender_id = u.id
            WHERE 
                (m.sender_id = ? AND m.recipient_id = ?) 
                OR (m.sender_id = ? AND m.recipient_id = ?)
            ORDER BY 
                m.created_at ASC
        ";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $sender_id, $recipient_id, $recipient_id, $sender_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    
        // Mark messages from the other user as read
        $updateQuery = "
            UPDATE messages 
            SET is_read = 1 
            WHERE 
                sender_id = ? AND recipient_id = ? AND is_read = 0
        ";
    
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ii", $recipient_id, $sender_id); // Update messages sent by the other user
        $updateStmt->execute();
        $updateStmt->close(); // Close the update statement
    
        echo json_encode(['success' => true, 'messages' => $messages]);
        $stmt->close(); // Close the message fetch statement
    }
    
    
    
    public function message() {
        global $conn;
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: POST");
    
        $data = json_decode(file_get_contents("php://input"), true);
        $sender_id = $data['sender_id'];
        $recipient_id = $data['user_id'];
        $message = $data['message'];
    
        if (empty($message) || empty($recipient_id)) {
            echo json_encode(['success' => false, 'message' => 'Message content and recipient are required.']);
            return;
        }
    
        $query = "
            INSERT INTO messages (sender_id, recipient_id, message, created_at)
            VALUES (?, ?, ?, NOW())
        ";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $sender_id, $recipient_id, $message);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
        }
    
        $stmt->close();
    }
    public function get_messages() {
        global $conn;
        header('Content-Type: application/json');
    
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
    
            // Fetch unread messages for the user as recipient
            $query = "SELECT * FROM messages WHERE recipient_id = ? AND is_read = 0 ORDER BY created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
    
            echo json_encode($messages);
            $stmt->close();
        } else {
            echo json_encode(['message' => 'User ID not provided']);
        }
    }
    
    public function chatbot() {
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: Content-Type");

        $input_message = $_POST['message'] ?? '';

        if (empty($input_message)) {
            echo json_encode(['error' => 'Message is required.']);
            return;
        }

        // Call Python backend
        $ch = curl_init('http://localhost:5000/chat');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $input_message]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code !== 200) {
            echo json_encode(['error' => 'Chatbot service unavailable.']);
            return;
        }

        curl_close($ch);
        echo $response;
    }
    
}
?>
