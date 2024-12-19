<?php
require_once 'php/auth_functions.php';
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (loginUser($username, $password)) {
        // Redirect to home page after successful login
        $login_success = "Logged in successfuly! Redirecting to home page. . .";
    } else {
        // Set error message
        $login_error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <?php include "includes/tailwind_css.php" ?>
</head>
<body class="sans-serif">
  <?php include "includes/nav.php" ?>

  <section class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
      <h1 class="mb-6 text-2xl font-bold text-center">Log in</h1>
      
      <?php 
      // Display error message if login fails
      if (isset($login_error)) {
          echo "<p class='mb-4 text-center text-red-500'>$login_error</p>";
      } else if (isset($login_success)) {  
        echo "<p class='mb-4 text-center text-green-500'>$login_success</p>";
        echo "<script>
            setTimeout(() => {
                window.location.href = 'index.php'; // Redirect to login page
            }, 2000);
        </script>";
      }
      ?>
      
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
        <!-- Username -->
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <input type="text" id="username" name="username" required class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" placeholder="Enter your username">
        </div>
        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input type="password" id="password" name="password" required class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" placeholder="Enter your password">
        </div>
        <!-- Register page -->
        <p class="text-xs">Don't have an account? <a href="register.php" class="transition duration-300 text-rose-500 hover:underline">Register</a></p>
        <!-- Submit Button -->
        <button type="submit" class="w-full px-4 py-2 text-white transition duration-300 rounded-md shadow-md bg-rose-500 hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500">
          Log in
        </button>
      </form>
    </div>
  </section>

  <?php include "includes/footer.php" ?>
</body>
</html>