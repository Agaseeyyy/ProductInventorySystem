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

// Handle supplier actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_supplier':
                $supplier_name = $_POST['supplier_name'] ?? '';
                $contact_num = $_POST['contact_num'] ?? '';
                $address = $_POST['address'] ?? '';
                $result = addSupplier($supplier_name, $contact_num, $address);

                if ($result) {
                  $_SESSION['message'] = "Supplier added successfully!";
              } else {
                  $_SESSION['error'] = "Supplier already exists or could not be added.";
              }
                break;

            case 'edit_supplier':
                $supplier_id = $_POST['supplier_id'] ?? '';
                $supplier_name = $_POST['supplier_name'] ?? '';
                $contact_num = $_POST['contact_num'] ?? '';
                $address = $_POST['address'] ?? '';
                $result = updateSupplier($supplier_id,$supplier_name, $contact_num, $address);

                if ($result) {
                  $_SESSION['message'] = "Supplier updated successfully!";
                } else {
                  $_SESSION['error'] = "Supplier already exists or could not be edited.";
                }
                break;

            case 'delete_supplier':
                $supplier_id = $_POST['supplier_id'];
                $result = deleteSupplier($supplier_id);
                
                if ($result['success']) {
                    $_SESSION['message'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
                break;

            case 'generate_supplier_report':
                require_once 'vendor/autoload.php'; 

                $filters = [
                    'supplier_name' => $_POST['supplier_name'] ?? null,
                    'min_products' => $_POST['min_products'] ?? null,
                ];

                // Remove null filters
                $filters = array_filter($filters);

                $reportData = generateSupplierReport($filters);

                // Determine export type
                $exportType = $_POST['export_type'] ?? 'pdf';

                if ($exportType === 'pdf') {
                    $_SESSION['message'] = "Supplier PDF report generated successfully!";
                    exportSupplierReportToPDF($reportData);
                } elseif ($exportType === 'csv') {
                    $_SESSION['message'] = "Supplier CSV report generated successfully!";
                    exportSupplierReportToCSV($reportData);
                }
                break;
        }

        // Redirect to prevent form resubmission
        header("Location: supplier.php");
        exit();
    }
}

// Fetch suppliers
$itemsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$suppliers = getAllSuppliers();
// Pagenation
if (isset($_GET['entries']) && $_GET['entries'] === 'all') {
  // If 'all' is selected, show all products without pagination
  $itemsPerPage = null;
} else {
  // Otherwise, set the number of items per page (default to 10 if not set)
  $itemsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
}
$totalSuppliers = count($suppliers);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// If entries per page is not 'all', calculate pagination
if ($itemsPerPage !== 'all') {
    $start = ($currentPage - 1) * $itemsPerPage;
    $displaySuppliers = array_slice($suppliers, $start, $itemsPerPage);
} else {
    // If 'all' is selected, display all catego$suppliers
    $displaySuppliers = $suppliers;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include "includes/tailwind_css.php" ?>
  <title>Inventory - Supplier</title>
</head>
<body class="sans-serif">
  <?php include "includes/nav.php" ?>
  <?php include "includes/add_supplier.php" ?>
  <?php include "includes/edit_supplier.php" ?>
  <?php include "includes/supplier_generate_report.php" ?>
  
    
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
        <h2 class="text-xl font-semibold text-gray-800">Supplier List</h2> 
        <div class="flex gap-3 text-sm">
          <!-- Show entries -->

        <div>
            <div class="flex items-center gap-2 px-3 text-sm text-gray-600">
              <label for="entries-suppliers">Show</label>
              <select id="entries-suppliers" class="px-2 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
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
          <?php if (!empty($suppliers)): ?>
            <div class="relative mb-4 min-w-96">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
                <input 
                  type="text" 
                  id="supplier-search-input" 
                  placeholder="Search suppliers (e.g., name, id, address)" 
                  class="w-full px-3 py-2 pl-10 border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500"
                >
            </div>
          <?php endif ?>
          <!-- Add Supplier Button -->
          <button onclick="openModal('add-supplier')" class="right-0 flex items-center text-xs h-8 gap-1 shadow-[0_5px_5px_rgba(0,0,0,0.15)] px-3 py-2 text-white rounded-md bg-green-500 transition duration-300 hover:bg-green-700 hover:shadow-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Supplier
          </button>
          <!-- Generate Report Button -->
          <?php if (!empty($suppliers)): ?>
            <button onclick="openModal('generate-report-supplier')" class="flex items-center text-xs gap-1 shadow-[0_5px_5px_rgba(0,0,0,0.15)] h-8 px-3 py-2 text-white rounded-md bg-yellow-500 transition duration-300 hover:bg-yellow-700 hover:shadow-none">
              <svg class="size-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10V4a1 1 0 0 0-1-1H9.914a1 1 0 0 0-.707.293L5.293 7.207A1 1 0 0 0 5 7.914V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2M10 3v4a1 1 0 0 1-1 1H5m5 6h9m0 0-2-2m2 2-2 2"/>
              </svg>
              Generate Report
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Table Wrapper -->
      <section class="mt-6 mb-8">
        <div class="overflow-hidden bg-white rounded-lg shadow-lg">
          
          <div class="overflow-x-auto">
            <table id="suppliers-table" class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">
                    <button onclick="toggleSortIcon(this)" 
                          class="flex items-center justify-center w-full h-full gap-1 transition duration-300 rounded-md hover:text-rose-600">
                      <span>Supplier ID</span>
                      <svg class="w-4 h-4 icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-width="2" d="M8 9l4-4 4 4M16 15l-4 4-4-4" />
                      </svg>
                    </button>
                  </th>
                  <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">
                    <button onclick="toggleSortIcon(this)" 
                          class="flex items-center justify-center w-full h-full gap-1 transition duration-300 rounded-md hover:text-rose-600">
                      <span>Name</span>
                      <svg class="w-4 h-4 icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-width="2" d="M8 9l4-4 4 4M16 15l-4 4-4-4" />
                      </svg>
                    </button>
                  </th>
                  <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">
                    <button onclick="toggleSortIcon(this)" 
                          class="flex items-center justify-center w-full h-full gap-1 transition duration-300 rounded-md hover:text-rose-600">
                      <span>Contact No.</span>
                      <svg class="w-4 h-4 icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-width="2" d="M8 9l4-4 4 4M16 15l-4 4-4-4" />
                      </svg>
                    </button>
                  </th>
                  <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">
                    <button onclick="toggleSortIcon(this)" 
                          class="flex items-center justify-center w-full h-full gap-1 transition duration-300 rounded-md hover:text-rose-600">
                      <span>Address</span>
                      <svg class="w-4 h-4 icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-width="2" d="M8 9l4-4 4 4M16 15l-4 4-4-4" />
                      </svg>
                    </button>
                  </th>
                  <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">Actions</th>
                </tr>
              </thead>
                <tbody class="divide-y divide-gray-100">
                  <?php if (!empty($suppliers)): ?>
                    <?php foreach ($displaySuppliers as $supplier): ?>
                      <tr class="transition duration-300 hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-800 border-r border-gray-100">
                          <span class="font-medium text-gray-700">
                            <?php echo htmlspecialchars($supplier['supplier_id']); ?>
                          </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 border-r">
                          <span class="px-2 py-1 text-xs font-medium text-purple-600 bg-purple-100 rounded-full">
                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                          </span>
                        </td>                                  
                        <td class="px-4 py-3 text-center text-gray-600 border-r"><?php echo htmlspecialchars($supplier['contact_num']); ?></td>
                        <td class="px-4 py-3 text-gray-600 border-r"><?php echo htmlspecialchars($supplier['address']); ?></td>
                        <td class="px-4 py-3">
                          <div class="flex items-center justify-center gap-2">
                            <!-- Edit Button -->
                            <button onclick="openEditModal(
                              <?php echo htmlspecialchars(json_encode($supplier['supplier_id'])); ?>, 
                              <?php echo htmlspecialchars(json_encode($supplier['supplier_name'])); ?>, 
                              <?php echo htmlspecialchars(json_encode($supplier['contact_num'])); ?>,
                              <?php echo htmlspecialchars(json_encode($supplier['address'])); ?>
                            )" 
                            class="p-1 text-blue-600 transition-colors duration-300 rounded-lg hover:bg-blue-100">
                              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                              </svg>
                            </button>
                            <!-- Delete Button -->
                            <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                              <input type="hidden" name="action" value="delete_supplier">
                              <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($supplier['supplier_id']); ?>">
                              <button type="submit" class="p-1 text-red-600 transition-colors duration-300 rounded-lg hover:bg-red-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="py-8 text-center text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="font-medium">No suppliers found</p>
                        <p class="mt-1 text-sm">Add some suppliers to see them listed here.</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

        </div>
        <div class="pb-10 mt-4 text-sm text-gray-500">
          Showing <?php echo min($itemsPerPage, $totalSuppliers); ?> of <?php echo $totalSuppliers; ?> entries
        </div>    
      </section>

    </div>
  </main>

  <?php include "includes/footer.php" ?>
</body>
<script src="javascripts/script.js?v=<?php echo time(); ?>"></script>
<script>
function openEditModal(supplierId, supplierName, contactNum, address) {
  // Set values in edit modal
  document.getElementById('edit-supplier-id').value = supplierId;
  document.getElementById('edit-supplier-name').value = supplierName;
  document.getElementById('edit-contact-num').value = contactNum;
  document.getElementById('edit-address').value = address;
  // Open the edit modal
  openModal('edit-supplier');
}

</script>
</html>