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

// Handle profile update
$update_success = false;
$update_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $fullname = $_POST['fullname'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($fullname)) {
        $update_error = "Username, email, and full name are required.";
    } else {
        // Check if password is provided
        if (!empty($password)) {
            // Update profile with new password
            $result = updateProfile($username, $email, $fullname, $password);
        } else {
            // Update profile without changing password
            $result = updateProfile($username, $email, $fullname);
        }

        if ($result) {
            $update_success = true;
        } else {
            $update_error = "Failed to update profile. Username or email might already exist.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <?php include "includes/tailwind_css.php" ?>
</head>
<body class="sans-serif">
  <?php include "includes/nav.php" ?>

  <section class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
      <h1 class="mb-6 text-2xl font-bold text-center">Edit Profile</h1>
      
      <?php 
      // Display success or error messages
      if ($update_success) {
          echo "<p class='mb-4 text-center text-green-500'>Profile updated successfully! Redirecting to home page. . . </p>";
          echo "<script>
          setTimeout(() => {
              window.location.href = 'index.php'; // Redirect to login page
          }, 2000);
          </script>";
      }
      if (!empty($update_error)) {
          echo "<p class='mb-4 text-center text-red-500'>$update_error</p>";
      }
      ?>
      
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
        <!-- Username -->
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <input type="text" id="username" name="username" required 
                 value="<?php echo htmlspecialchars($_SESSION['username']); ?>"
                 class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" 
                 placeholder="Enter your username">
        </div>
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" id="email" name="email" required 
                 value="<?php echo htmlspecialchars($_SESSION['email']); ?>"
                 class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" 
                 placeholder="Enter your email">
        </div>
        <!-- Full Name -->
        <div>
          <label for="fullname" class="block text-sm font-medium text-gray-700">Full Name</label>
          <input type="text" id="fullname" name="fullname" required 
                 value="<?php echo htmlspecialchars($_SESSION['fullname']); ?>"
                 class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" 
                 placeholder="Enter your full name">
        </div>
        <!-- Password (Optional) -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">New Password (Optional)</label>
          <input type="password" id="password" name="password" 
                 class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" 
                 placeholder="Leave blank to keep current password">
        </div>
        <!-- Home page -->
        <p class="text-xs">Go back to main page? <a href="index.php" class="transition duration-300 text-rose-500 hover:underline">Home</a></p>
        <!-- Submit Button -->
        <button type="submit" class="w-full px-4 py-2 text-white transition duration-300 rounded-md shadow-md bg-rose-500 hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500">
          Save Changes
        </button>
      </form>
    </div>
  </section>

  <?php include "includes/footer.php" ?>
</body>
</html>