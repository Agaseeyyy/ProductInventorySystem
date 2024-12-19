<?php
require_once 'php/auth_functions.php';
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Handle logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    logoutUser();
    header("Location: login.php");
    exit();
}

// Calculate account age
$query = "SELECT created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$created_at = $user['created_at'];

// Calculate days since account creation
$account_creation_date = new DateTime($created_at);
$current_date = new DateTime();
$account_age = $current_date->diff($account_creation_date)->days;
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Profile</title>
  <?php include "includes/tailwind_css.php" ?>
</head>
<body class="sans-serif">
  <?php include "includes/nav.php" ?>

  <section class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
      <h1 class="mb-6 text-2xl font-bold text-center">Profile</h1>
      <form action="" method="POST" class="space-y-4">
        <!-- Username -->
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <p class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <p class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
        </div>
        <!-- Full Name -->
        <div>
          <label for="fullname" class="block text-sm font-medium text-gray-700">Full Name</label>
          <p class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm"><?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
        </div>
        <!-- Account Creation Date -->
        <div>
          <label for="created_at" class="block text-sm font-medium text-gray-700">Account Created</label>
          <p class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm">
            <?php 
            echo htmlspecialchars($created_at) . " (". $account_age . " days ago)"; 
            ?>
          </p>
        </div>
        <!-- Edit Profile page -->
        <a href="edit_profile.php" class="text-xs transition duration-300 text-rose-500 hover:underline">Edit profile?</a>
        
        <!-- Logout Button -->
        <button type="submit" name="logout" class="w-full px-4 py-2 text-white transition duration-300 rounded-md shadow-md bg-rose-500 hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500">
          Log out
        </button>
      </form>
    </div>
  </section>

  <?php include "includes/footer.php" ?>
</body>
</html>