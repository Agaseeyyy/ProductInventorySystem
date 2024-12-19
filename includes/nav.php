<?php
require_once 'php/auth_functions.php';
?>

<nav class="sticky shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
  <div class="container px-2 max-w-[1536px] 2xl:max-w-[1280px] xl:max-w-[1024px]">

    <div class="flex flex-row items-center justify-between h-20 gap-5 py-3">
      <a href="index.php" class="p-1 transition-colors duration-300 rounded-lg hover:bg-rose-100">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f43f5e" class="size-8">
          <path d="M5.223 2.25c-.497 0-.974.198-1.325.55l-1.3 1.298A3.75 3.75 0 0 0 7.5 9.75c.627.47 1.406.75 2.25.75.844 0 1.624-.28 2.25-.75.626.47 1.406.75 2.25.75.844 0 1.623-.28 2.25-.75a3.75 3.75 0 0 0 4.902-5.652l-1.3-1.299a1.875 1.875 0 0 0-1.325-.549H5.223Z" />
          <path fill-rule="evenodd" d="M3 20.25v-8.755c1.42.674 3.08.673 4.5 0A5.234 5.234 0 0 0 9.75 12c.804 0 1.568-.182 2.25-.506a5.234 5.234 0 0 0 2.25.506c.804 0 1.567-.182 2.25-.506 1.42.674 3.08.675 4.5.001v8.755h.75a.75.75 0 0 1 0 1.5H2.25a.75.75 0 0 1 0-1.5H3Zm3-6a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-.75.75h-3a.75.75 0 0 1-.75-.75v-3Zm8.25-.75a.75.75 0 0 0-.75.75v5.25c0 .414.336.75.75.75h3a.75.75 0 0 0 .75-.75v-5.25a.75.75 0 0 0-.75-.75h-3Z" clip-rule="evenodd" />
        </svg>
      </a>
      <?php if (isLoggedIn()) : ?>
        <ul class="flex flex-row items-center justify-center gap-10 text-sm">
            <li class="px-2 duration-200 hover:bg-rose-500 hover:py-2 hover:rounded-lg hover:text-white"><a href="index.php">Dashboard</a></li>
            <li class="px-2 duration-200 hover:bg-rose-500 hover:py-2 hover:rounded-lg hover:text-white"><a href="product.php">Products</a></li>
            <li class="px-2 duration-200 hover:bg-rose-500 hover:py-2 hover:rounded-lg hover:text-white"><a href="category.php">Category</a></li>
            <li class="px-2 duration-200 hover:bg-rose-500 hover:py-2 hover:rounded-lg hover:text-white"><a href="supplier.php">Supplier</a></li>
        </ul>
      <?php endif; ?>


      <div class="flex items-center space-x-4">
        <?php if (isLoggedIn()): ?>
          <a href="settings_profile.php" class="text-xl font-bold transition duration-300 text-rose-500 hover:underline hover:text-rose-700">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></a>

          <a class="transition-colors duration-300 rounded-lg hover:bg-rose-100" href="settings_profile.php"> 
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f43f5e" class="size-8 ">
              <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 0 0 0-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 0 0-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 0 0-1.85-1.567h-1.843ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd" />
            </svg>
          </a>
          <a href="logout.php" class="h-8 px-3 py-1 text-sm transition duration-300 border rounded-md text-rose-500 border-rose-500 hover:bg-rose-500 hover:text-white">Log out</a>
        <?php else: ?>
          <a href="" class="text-xl font-bold transition duration-300 text-rose-500 hover:underline hover:text-rose-700">Hello, guest</a>
          <a href="login.php" class="h-8 px-3 py-1 text-sm text-white transition duration-300 rounded-md bg-rose-500 hover:bg-rose-700">Login</a>
          <a href="register.php" class="h-8 px-3 py-1 text-sm transition duration-300 border rounded-md text-rose-500 border-rose-500 hover:bg-rose-500 hover:text-white">Register</a>
        <?php endif; ?>
      </div>
      
    </div>

  </div>
</nav>