<?php
// Security Headers
function cors() {
  if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
  }

  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
      header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
      header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
  }
}

cors();

require 'config.php';
require 'router.php';
require 'web/auth.php';
require 'web/home.php';
require 'admin/admin_auth.php';
require 'admin/admin_list.php';



// Initialize Router
$router = new Router();

// Post Requests
$router->post('/api/auth/register', 'AuthWeb@register');
$router->post('/api/auth/login', 'AuthWeb@login');
$router->post('/api/auth/forgot', 'AuthWeb@forgot');
$router->post('/api/auth/reset_password', 'AuthWeb@reset_password');
$router->post('/api/auth/change_email_auth', 'AuthWeb@change_email_auth');
$router->post('/api/auth/change_email', 'AuthWeb@change_email');
$router->post('/api/home/post_concern', 'PostWeb@post_concern');
$router->post('/api/home/post_concern_lost', 'PostWeb@post_concern_lost');
$router->post('/api/home/post_concern_college', 'PostWeb@post_concern_college');
$router->post('/api/home/add_comment', 'PostWeb@add_comment');
$router->post('/api/home/get_comments', 'PostWeb@get_comments');
$router->post('/api/home/load_account', 'PostWeb@load_account');
$router->post('/api/home/upload_profile', 'PostWeb@upload_profile');
$router->post('/api/home/get_posts', 'PostWeb@get_posts');
$router->post('/api/auth/check', 'AuthWeb@check');
$router->post('/api/admin_auth/login', 'AdminAuth@login');
$router->post('/api/admin_auth/logout', 'AdminAuth@logout');
$router->post('/api/admin_list/pending', 'AdminList@pending');
$router->post('/api/admin_list/process', 'AdminList@process');
$router->post('/api/admin_list/approved', 'AdminList@approved');
$router->post('/api/admin_list/blocked', 'AdminList@blocked');
$router->post('/api/admin_list/delete', 'AdminList@delete');
$router->post('/api/home/delete_post', 'PostWeb@delete_post');
$router->post('/api/home/get_user_position', 'PostWeb@get_user_position');
$router->post('/api/home/get_posts_by_position', 'PostWeb@get_posts_by_position');
$router->post('/api/home/delete_post_college', 'PostWeb@delete_post_college');
$router->post('/api/home/get_comments_college', 'PostWeb@get_comments_college');
$router->post('/api/home/get_notifications', 'PostWeb@get_notifications');
$router->post('/api/home/mark_notification_as_read', 'PostWeb@markNotificationAsRead');
$router->post('/api/home/add_comment_college', 'PostWeb@add_comment_college');
$router->post('/api/home/send_contact', 'PostWeb@send_contact');
$router->post('/api/home/get_posts_lost', 'PostWeb@get_posts_lost');
$router->post('/api/home/get_comments_lost', 'PostWeb@get_comments_lost');
$router->post('/api/home/add_comment_lost', 'PostWeb@add_comment_lost');
$router->post('/api/home/delete_post_lost', 'PostWeb@delete_post_lost');
$router->post('/api/home/message', 'PostWeb@message');
$router->post('/api/home/load_users', 'PostWeb@load_users');
$router->post('/api/home/get_conversation', 'PostWeb@get_conversation');
$router->post('/api/home/get_messages', 'PostWeb@get_messages');
$router->post('/api/home/chatbot', 'PostWeb@chatbot');



// Get Requests
$router->get('/api/auth/register', 'AuthWeb@register');
$router->get('/api/auth/login', 'AuthWeb@login');
$router->get('/api/auth/forgot', 'AuthWeb@forgot');
$router->get('/api/auth/reset_password', 'AuthWeb@reset_password');
$router->get('/api/auth/change_email_auth', 'AuthWeb@change_email_auth');
$router->get('/api/auth/change_email', 'AuthWeb@change_email');
$router->get('/api/home/load_account', 'PostWeb@load_account');
$router->get('/api/home/upload_profile', 'PostWeb@upload_profile');
$router->get('/api/admin_auth/login', 'AdminAuth@login');
$router->get('/api/admin_list/pending', 'AdminList@pending');
$router->get('/api/admin_list/process', 'AdminList@process');
$router->get('/api/admin_list/approved', 'AdminList@approved');
$router->get('/api/admin_list/blocked', 'AdminList@blocked');
$router->get('/api/admin_list/delete', 'AdminList@delete');
$router->get('/api/home/check_updates', 'PostWeb@check_updates');
$router->get('/api/home/get_posts', 'PostWeb@get_posts');
$router->get('/api/home/get_posts_by_name', 'PostWeb@get_posts_by_name');
$router->get('/api/home/get_posts_lost', 'PostWeb@get_posts_lost');
$router->get('/api/home/add_comment', 'PostWeb@add_comment');
$router->get('/api/home/get_comments', 'PostWeb@get_comments');
$router->get('/api/home/delete_post', 'PostWeb@delete_post');
$router->get('/api/home/get_user_position', 'PostWeb@get_user_position');
$router->get('/api/home/get_posts_by_position', 'PostWeb@get_posts_by_position');
$router->get('/api/home/post_concern_college', 'PostWeb@post_concern_college');
$router->get('/api/home/post_concern_lost', 'PostWeb@post_concern_lost');
$router->get('/api/home/delete_post_college', 'PostWeb@delete_post_college');
$router->get('/api/home/get_comments_college', 'PostWeb@get_comments_college');
$router->get('/api/home/add_comment_college', 'PostWeb@add_comment_college');
$router->get('/api/home/get_notifications', 'PostWeb@get_notifications');
$router->get('/api/home/mark_notification_as_read', 'PostWeb@mark_notification_as_read');
$router->get('/api/home/get_comments_lost', 'PostWeb@get_comments_lost');
$router->get('/api/home/add_comment_lost', 'PostWeb@add_comment_lost');
$router->get('/api/home/load_users', 'PostWeb@load_users');
$router->get('/api/home/get_conversation', 'PostWeb@get_conversation');
$router->get('/api/home/get_messages', 'PostWeb@get_messages');
$router->get('/api/home/chatbot', 'PostWeb@chatbot');
// Put Requests
$router->put('/api/user/register', 'AuthWeb@register');

// Delete Requests
$router->delete('/api/user/register', 'AuthWeb@register');
$router->delete('/api/home/delete_post', 'PostWeb@delete_post');
$router->delete('/api/home/delete_post_lost', 'PostWeb@delete_post_lost');
$router->delete('/api/home/delete_post_college', 'PostWeb@delete_post_college');
$router->delete('/api/admin_list/delete', 'AdminList@delete');
// Dispatch the request
$router->dispatch();
?>
