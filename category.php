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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_category':
                $name = $_POST['title'];
                $result = addCategory($name);
                if ($result) {
                    $_SESSION['message'] = "Category added successfully!";
                } else {
                    $_SESSION['error'] = "Category already exists or could not be added.";
                }
                break;

            case 'edit_category':
                $category_ID = $_POST['category_ID'];
                $name = $_POST['title'];
                $result = updateCategory($category_ID, $name);
                if ($result) {
                    $_SESSION['message'] = "Category updated successfully!";
                } else {
                    $_SESSION['error'] = "Category name already exists or could not be updated.";
                }
                break;

            case 'delete_category':
                $category_ID = $_POST['category_ID'];
                $result = deleteCategory($category_ID);
                
                if ($result['success']) {
                    $_SESSION['message'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
                break;

            case 'generate_category_report':
                require_once 'vendor/autoload.php'; 

                $filters = [
                    'category_name' => $_POST['category_name'] ?? null,
                    'min_products' => $_POST['min_products'] ?? null,
                ];

                // Remove null filters
                $filters = array_filter($filters);

                $reportData = generateCategoryReport($filters);

                // Determine export type
                $exportType = $_POST['export_type'] ?? 'pdf';

                if ($exportType === 'pdf') {
                    $_SESSION['message'] = "Supplier PDF report generated successfully!";
                    exportCategoryReportToPDF($reportData);
                } elseif ($exportType === 'csv') {
                    $_SESSION['message'] = "Supplier CSV report generated successfully!";
                    exportCategoryReportToCSV($reportData);
                }
        }
        
        // Redirect to prevent form resubmission
        header("Location: category.php");
        exit();
    }
}

// Get all categories
$itemsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$categories = getAllCategories();
// Pagenation
if (isset($_GET['entries']) && $_GET['entries'] === 'all') {
  // If 'all' is selected, show all products without pagination
  $itemsPerPage = null;
} else {
  // Otherwise, set the number of items per page (default to 10 if not set)
  $itemsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
}
$totalCategories = count($categories);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// If entries per page is not 'all', calculate pagination
if ($itemsPerPage !== 'all') {
    $start = ($currentPage - 1) * $itemsPerPage;
    $displayCategories = array_slice($categories, $start, $itemsPerPage);
} else {
    // If 'all' is selected, display all catego$categories
    $displayCategories = $categories;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include "includes/tailwind_css.php" ?>
  <title>Inventory - Category</title>
</head>
<body class="sans-serif">
  <?php include "includes/nav.php" ?>
  <?php include "includes/add_category.php" ?>
  <?php include "includes/edit_category.php" ?>
  <?php include "includes/category_generate_report.php" ?>
    
  <main class="min-h-screen pt-10 bg-gray-100">
    <div class="container px-2 max-w-[1536px] 2xl:max-w-[1280px] xl:max-w-[1024px]">
      
      <!-- Message Display -->
      <?php if (isset($_SESSION['message'])): ?>
        <div class="p-3 mb-4 text-green-800 bg-green-200 rounded">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 text-red-800 bg-red-200 rounded">
            <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
            ?>
        </div>
      <?php endif; ?>
      
      <!-- BUTTONS -->
      <div class="flex flex-row justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800">Category List</h2>
        <div class="flex gap-3 text-sm">
          <!-- Show entries -->
          <div>
            <div class="flex items-center gap-2 px-3 text-sm text-gray-600">
              <label for="entries-categories">Show</label>
              <select id="entries-categories" class="px-2 py-2 border rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-rose-500">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">all</option>
              </select>
              <span>entries</span>
            </div>
          </div>
          <!-- Search Bar -->
          <?php if (!empty($categories)): ?>
            <div class="relative mb-4 min-w-96">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
                <input 
                  type="text" 
                  id="category-search-input" 
                  placeholder="Search categories (e.g., name, category id)" 
                  class="w-full px-3 py-2 pl-10 border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500"
                >
            </div>
          <?php endif ?>
          <!-- Add Category Button -->
          <button onclick="openModal('add-category')" class="h-9 right-0 flex items-center text-xs gap-1 shadow-[0_5px_5px_rgba(0,0,0,0.15)] px-3 text-white rounded-md transition duration-300 bg-green-500 hover:bg-green-700 hover:shadow-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Category
          </button>
          <!-- Generate Category Button -->
          <?php if (!empty($categories)): ?>
            <button onclick="openModal('generate-report-category')" class="flex items-center text-xs gap-1 shadow-[0_5px_5px_rgba(0,0,0,0.15)] h-9 px-3 text-white rounded-md bg-yellow-500 transition duration-300 hover:bg-yellow-700 hover:shadow-none">
              <svg class="size-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10V4a1 1 0 0 0-1-1H9.914a1 1 0 0 0-.707.293L5.293 7.207A1 1 0 0 0 5 7.914V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2M10 3v4a1 1 0 0 1-1 1H5m5 6h9m0 0-2-2m2 2-2 2"/>
              </svg>
              Generate Report
            </button>
          <?php endif ?>
        </div>

      </div>

      <!-- Table Wrapper -->
      <section class="mt-6 mb-8">
        <div class="overflow-hidden bg-white rounded-lg shadow-lg">
          <div class="overflow-x-auto">
            <table id="categories-table" class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">
                    <button onclick="toggleSortIcon(this)" 
                            class="flex items-center justify-center w-full h-full gap-1 transition duration-300 rounded-md hover:text-rose-600">
                      <span>Category ID</span>
                      <svg class="w-4 h-4 icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path d="M8 9l4-4 4 4M16 15l-4 4-4-4" />
                      </svg>
                    </button>
                  </th>
                  <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">
                    <button onclick="toggleSortIcon(this)" 
                            class="flex items-center justify-center w-full h-full gap-1 transition duration-300 rounded-md hover:text-rose-600">
                      <span>Name</span>
                      <svg class="w-4 h-4 icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path d="M8 9l4-4 4 4M16 15l-4 4-4-4" />
                      </svg>
                    </button>
                  </th>
                  <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">Actions</th>
                </tr>
              </thead>
              
              <tbody class="divide-y divide-gray-100">
                <?php if (!empty($categories)): ?>
                  <?php foreach ($displayCategories as $category): ?>
                    <tr class="transition duration-300 hover:bg-gray-50">
                      <td class="px-4 py-3 text-center text-gray-800 border-r border-gray-100">
                        <span class="font-medium text-gray-700">
                          <?php echo htmlspecialchars($category['category_ID']); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3 text-gray-800 border-r border-gray-100">
                        <span class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full">
                          <?php echo htmlspecialchars($category['name']); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                          <!-- Edit Button -->
                          <button onclick="openEditModal(<?php echo $category['category_ID']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')" 
                                  class="p-1 text-blue-600 transition-colors duration-300 rounded-lg hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                          </button>
                          <!-- Delete Button -->
                          <button onclick="confirmDelete(<?php echo $category['category_ID']; ?>)" 
                                  class="p-1 text-red-600 transition-colors duration-300 rounded-lg hover:bg-red-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3" class="py-8 text-center text-gray-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                      </svg>
                      <p class="font-medium">No categories found</p>
                      <p class="mt-1 text-sm">Add some categories to see them listed here.</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="pb-10 mt-4 text-sm text-gray-500">
          Showing <?php echo min($itemsPerPage, $totalCategories); ?> of <?php echo $totalCategories; ?> entries
        </div>    
      </section>

    </div>
  </main>

  <?php include "includes/footer.php" ?>
</body>

<script src="javascripts/script.js?v=<?php echo time(); ?>"></script>
<script>
function openEditModal(categoryId, categoryName) {
  // Populate edit modal with current category details
  document.getElementById('edit-category-id').value = categoryId;
  document.getElementById('edit-category-name').value = categoryName;
  openModal('edit-category');
}

function confirmDelete(categoryId) {
  if (confirm('Are you sure you want to delete this category?')) {
    // Create a form to submit delete request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete_category';
    form.appendChild(actionInput);

    const categoryInput = document.createElement('input');
    categoryInput.type = 'hidden';
    categoryInput.name = 'category_ID';
    categoryInput.value = categoryId;
    form.appendChild(categoryInput);

    document.body.appendChild(form);
    form.submit();
  }
}

</script>
</html>