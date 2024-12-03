    <?php
    class AdminList{
        public function pending(){
            // Set response headers
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json");
            header("Access-Control-Allow-Methods: GET");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
            global $conn;
        
            // Fetch the list of pending users, including the 'id' field
            $query = "SELECT id, id_pic, fname, lname, id_no, college, email, position, status FROM users WHERE status = 'pending'";
            $result = $conn->query($query);
        
            $response = array();
        
            if ($result->num_rows > 0) {
                $users = array();
        
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
        
                $response['status'] = 'success';
                $response['users'] = $users;
            } else {
                $response['status'] = 'error';
                $response['message'] = 'No pending users found.';
            }
        
            echo json_encode($response);
        }
        public function approved(){
            // Set response headers
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json");
            header("Access-Control-Allow-Methods: GET");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
            global $conn;
        
            // Fetch the list of pending users, including the 'id' field
            $query = "SELECT id, id_pic, fname, lname, id_no, college, email, position, status FROM users WHERE status = 'approved'";
            $result = $conn->query($query);
        
            $response = array();
        
            if ($result->num_rows > 0) {
                $users = array();
        
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
        
                $response['status'] = 'success';
                $response['users'] = $users;
            } else {
                $response['status'] = 'error';
                $response['message'] = 'No pending users found.';
            }
        
            echo json_encode($response);
        }
        public function blocked(){
            // Set response headers
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json");
            header("Access-Control-Allow-Methods: GET");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
            global $conn;
        
            // Fetch the list of pending users, including the 'id' field
            $query = "SELECT id, id_pic, fname, lname, id_no, college, email, position, status FROM users WHERE status = 'blocked'";
            $result = $conn->query($query);
        
            $response = array();
        
            if ($result->num_rows > 0) {
                $users = array();
        
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
        
                $response['status'] = 'success';
                $response['users'] = $users;
            } else {
                $response['status'] = 'error';
                $response['message'] = 'No pending users found.';
            }
        
            echo json_encode($response);
        }
        public function process(){
            // Set response headers
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json");
            header("Access-Control-Allow-Methods: POST");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
            global $conn;
        
            // Get the raw POST data (JSON)
            $data = json_decode(file_get_contents("php://input"), true);
        
            // Validate received data
            if (!isset($data['user_id'], $data['position'], $data['status'], $data['email'], $data['full_name'])) {
                http_response_code(400); // Bad Request
                $response = array('status' => 'error', 'message' => 'Incomplete data received.');
                echo json_encode($response);
                return;
            }
        
            $user_id = $data['user_id'];
            $position = $data['position'];
            $status = $data['status'];
            $user_email = $data['email'];
            $full_name = $data['full_name'];
        
            // Determine the email content based on the status
            if ($status == 'approved') {
                $fb_link = "https://www.facebook.com/profile.php?id=61566942153854";
                $subject = "Your PiyuHub Account Approved";
                $message = "
                    <html>
                    <head>
                        <style>
                            .bold { font-weight: bold; }
                        </style>
                    </head>
                    <body>
                        <p class='bold'>Dear $full_name,</p>
                        <p>Welcome to PiyuHub! Your account has been approved. You can now login and enjoy PiyuHub!</p>
                        <p>Details: </p>
                        <p>Name: $full_name</p>
                        <p>email: $user_email</p>
                        <p>Position: $position</p>
                        <p class='bold'>Position can be changed as Representative, SSC, Org, and Admin. If you are part of any of this, please don't hesitate to contact us if you want to change your position.</p>
                        <p><a href='$fb_link'>Support our facebook page!</a></p>
                        <p>Regards,<br/>
                        PiyuHub Team
                        </p>
                    </body>
                    </html>
                ";
            } elseif ($status == 'blocked') {
                $fb_link = "https://www.facebook.com/profile.php?id=61566942153854";
                $subject = "Your PiyuHub Account Blocked";
                $message = "
                    <html>
                    <head>
                        <style>
                            .bold { font-weight: bold; }
                        </style>
                    </head>
                    <body>
                        <p class='bold'>Dear $full_name,</p>
                        <p>Your account has been blocked due to incorrect information. Please sign up again with the correct details.</p>
                        <p>Details: </p>
                        <p>Name: $full_name</p>
                        <p>email: $user_email</p>
                        <p>Position: $position</p>
                        <p class='bold'>Position can be changed as Representative, SSC, Org, and Admin. If you are part of any of this, please don't hesitate to contact us if you want to change your position.</p>
                        <p><a href='$fb_link'>Support our facebook page!</a></p>
                        <p>Regards,<br/>
                        PiyuHub Team
                        </p>
                    </body>
                    </html>
                ";
            }
            elseif ($status == 'pending') {
                $fb_link = "https://www.facebook.com/profile.php?id=61566942153854";
                $subject = "Your PiyuHub Account Is Pending";
                $message = "
                    <html>
                    <head>
                        <style>
                            .bold { font-weight: bold; }
                        </style>
                    </head>
                    <body>
                        <p class='bold'>Dear $full_name,</p>
                        <p>Your account is currently under review. Please wait while our team verifies your details.</p>
                        <p>Details: </p>
                        <p>Name: $full_name</p>
                        <p>email: $user_email</p>
                        <p>Position: $position</p>
                        <p class='bold'>Position can be changed as Representative, SSC, Org, and Admin. If you are part of any of this, please don't hesitate to contact us if you want to change your position.</p>
                        <p><a href='$fb_link'>Support our facebook page!</a></p>
                        <p>Regards,<br/>
                        PiyuHub Team
                        </p>
                    </body>
                    </html>
                ";
            } else {
                http_response_code(400); // Bad Request
                $response = array('status' => 'error', 'message' => 'Invalid status value.');
                echo json_encode($response);
                return;
            }
        
            // Update user status and position in the database
            $sql = "UPDATE users SET position = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
        
            if (!$stmt) {
                http_response_code(500); // Internal Server Error
                $response = array('status' => 'error', 'message' => 'Failed to prepare SQL statement: ' . $conn->error);
                echo json_encode($response);
                return;
            }
        
            $stmt->bind_param('ssi', $position, $status, $user_id);
            
            if ($stmt->execute()) {
                // Prepare email headers
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= 'From: info.piyuhub@gmail.com' . "\r\n"; // Replace with your email
                $headers .= 'Reply-To: info.piyuhub@gmail.com' . "\r\n";
        
                // Send the email to the user
                $emailSent = mail($user_email, $subject, $message, $headers);
        
                if ($emailSent) {
                    // Optionally send an email to the admin
                    $to_admin = "info.piyuhub@gmail.com"; // Replace with the actual admin email
                    $admin_subject = "Account Status Changed";
                    $admin_message = "
                        <html>
                        <head>
                            <style>
                                .bold { font-weight: bold; }
                            </style>
                        </head>
                        <body>
                            <p class='bold'>An account status has been updated.</p>
                            <p><strong>User ID:</strong> {$user_id}</p>
                            <p><strong>Full Name:</strong> {$full_name}</p>
                            <p><strong>New Status:</strong> {$status}</p>
                            <p><strong>New Position:</strong> {$position}</p>
                        </body>
                        </html>
                    ";
                    mail($to_admin, $admin_subject, $admin_message, $headers);
        
                    // Respond with success
                    $response['status'] = 'success';
                    $response['message'] = 'User status updated and email sent successfully.';
                } else {
                    // Respond with partial success
                    $response['status'] = 'error';
                    $response['message'] = 'User status updated but email failed to send.';
                }
            } else {
                // Respond with SQL execution error
                http_response_code(500); // Internal Server Error
                $response['status'] = 'error';
                $response['message'] = 'Failed to update the user: ' . $stmt->error;
            }
        
            // Return response as JSON
            echo json_encode($response);
        
            // Close the statement and connection
            $stmt->close();
            $conn->close();
        }
        public function delete() {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json");
            header("Access-Control-Allow-Methods: POST");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
            global $conn; // Assuming you have a mysqli instance stored in $conn
        
            // Check if the request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
                return;
            }
        
            // Get the user_id from the request body
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['user_id'])) {
                http_response_code(400); // Bad Request
                echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format or missing user_id']);
                return;
            }
        
            $user_id = $data['user_id'];
        
            if (empty($user_id)) {
                echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
                return;
            }
        
            try {
                // Retrieve user's email and full name for notification
                $email_stmt = $conn->prepare('SELECT email, fname, lname FROM users WHERE id = ?');
                $email_stmt->bind_param('i', $user_id);
                $email_stmt->execute();
                $email_stmt->bind_result($user_email, $first_name, $last_name);
                $email_stmt->fetch();
                $email_stmt->close();
        
                // Create full name
                $full_name = $first_name . ' ' . $last_name;
        
                // Prepare the delete query using a ? placeholder
                $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
                $stmt->bind_param('i', $user_id); // Bind the user_id as an integer
        
                // Execute the query
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        // User deleted successfully
                        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
                        
                        // Prepare email headers
                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                        $headers .= 'From: info.piyuhub@gmail.com' . "\r\n"; // Replace with your email
                        $headers .= 'Reply-To: info.piyuhub@gmail.com' . "\r\n";
        
                        // Prepare the email content
                        $fb_link = "https://www.facebook.com/profile.php?id=61566942153854";
                        $subject = "Your PiyuHub Account Deleted";
                        $message = "
                            <html>
                            <head>
                                <style>
                                    .bold { font-weight: bold; }
                                </style>
                            </head>
                            <body>
                                <p class='bold'>Dear $full_name,</p>
                                <p>Your account has been deleted due to inappropriate actions. If you believe this is a mistake, please contact support.</p>
                                <p><a href='$fb_link'>Support our facebook page!</a></p>
                                <p>Regards,<br/>
                                PiyuHub Team
                                </p>    
                            </body>
                            </html>
                        ";
        
                        // Send the email to the user
                        if (!empty($user_email)) {
                            mail($user_email, $subject, $message, $headers);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'User not found']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
                }
        
                // Close the statement
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }
        
        
        
              
         
    }
