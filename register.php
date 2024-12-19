<?php
require_once 'php/auth_functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $fullname = $_POST['fullname'];
    $password = $_POST['password'];

    // Validate input (you can add more robust validation)
    if (empty($username) || empty($email) || empty($fullname) || empty($password)) {
        $register_error = "All fields are required";
    } else {
        // Attempt to register user
        if (registerUser($username, $email, $fullname, $password)) {
            // Automatically log in the user after successful registration
            $register_success = "Registered successfuly! Redirecting to login page. . .";
        } else {
            $register_error = "Registration failed. Username or email might already exist.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <?php include "includes/tailwind_css.php" ?>
</head>
<body class="sans-serif">
  <?php include "includes/nav.php" ?>

  <section class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
      <h1 class="mb-6 text-2xl font-bold text-center">Register</h1>
      
      <?php 
      // Display error message if registration fails
      if (isset($register_error)) {
          echo "<p class='mb-4 text-center text-red-500'>$register_error</p>";
      } else if (isset($register_success)) {  
        echo "<p class='mb-4 text-center text-green-500'>$register_success</p>";
        echo "<script>
            setTimeout(() => {
                window.location.href = 'login.php'; // Redirect to login page
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
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" id="email" name="email" required class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" placeholder="Enter your email">
        </div>
        <!-- Full Name -->
        <div>
          <label for="fullname" class="block text-sm font-medium text-gray-700">Full Name</label>
          <input type="text" id="fullname" name="fullname" required class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" placeholder="Enter your full name">
        </div>
        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input type="password" id="password" name="password" required class="w-full px-4 py-2 mt-1 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500" placeholder="Enter your password">
        </div>
        <!-- Login page -->
        <p class="text-xs">Already registered? <a href="login.php" class="transition duration-300 text-rose-500 hover:underline">Log in</a></p>
        <!-- Submit Button -->
        <button type="submit" class="w-full px-4 py-2 text-white transition duration-300 rounded-md shadow-md bg-rose-500 hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500">
          Register
        </button>
      </form>
    </div>
  </section>

  <?php include "includes/footer.php" ?>
</body>
</html>