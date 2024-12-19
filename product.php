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

// Handle AJAX requests for adding categories and suppliers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
  $response = ['success' => false, 'message' => ''];

  try {
      switch ($_POST['ajax_action']) {
          case 'add_category':
              $name = $_POST['category_name'] ?? '';
              $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
              $stmt->bind_param("s", $name);
              
              if ($stmt->execute()) {
                  $response['success'] = true;
                  $response['id'] = $stmt->insert_id;
                  $response['name'] = $name;
                  $response['message'] = "Category added successfully!";
              } else {
                  $response['message'] = "Error adding category.";
              }
              $stmt->close();
              break;

          case 'add_supplier':
              $name = $_POST['supplier_name'] ?? '';
              $contact_num = $_POST['contact_num'] ?? '';
              $address = $_POST['address'] ?? '';
              
              $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_num, address) VALUES (?, ?, ?)");
              $stmt->bind_param("sss", $name, $contact_num, $address);
              
              if ($stmt->execute()) {
                  $response['success'] = true;
                  $response['id'] = $stmt->insert_id;
                  $response['name'] = $name;
                  $response['message'] = "Supplier added successfully!";
              } else {
                  $response['message'] = "Error adding supplier.";
              }
              $stmt->close();
              break;
      }
  } catch (Exception $e) {
      $response['message'] = $e->getMessage();
  }

  // Send JSON response
  header('Content-Type: application/json');
  echo json_encode($response);
  exit();
}

// Handle product, category, and supplier actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $category_id = $_POST['product_category'] ?? '';
                $supplier_id = $_POST['product_supplier'] ?? '';
                $product_name = $_POST['product_name'] ?? '';
                $price = $_POST['product_price'] ?? 0;
                $quantity_stock = $_POST['quantity_stocks'] ?? 0;
                $description = $_POST['product_description'] ?? '';
                $result = addProduct($category_id, $supplier_id, $product_name, $price, $quantity_stock, $description);

                if ($result) {
                    $_SESSION['message'] = "Product added successfully!";
                } else {
                    $_SESSION['error'] = "Product already exists or could not be added.";
                }
                break;

            case 'edit_product':
                $product_id = $_POST['product_id'] ?? '';
                $category_id = $_POST['product_category'] ?? '';
                $supplier_id = $_POST['product_supplier'] ?? '';
                $product_name = $_POST['product_name'] ?? '';
                $price = $_POST['product_price'] ?? 0;
                $quantity_stock = $_POST['quantity_stocks'] ?? 0;
                $description = $_POST['product_description'] ?? '';
                $result = updateProduct($product_id, $category_id, $supplier_id, $product_name, $price, $quantity_stock, $description);
              
                if ($result) {
                    $_SESSION['message'] = "Product updated successfully!";
                } else {
                    $_SESSION['error'] = "Product already exists or could not be updated.";
                }
                break;

            case 'delete_product':
              $product_id = $_POST['product_id'] ?? '';
              $result = deleteProduct($product_id);

              if ($result) {
                  $_SESSION['message'] = "Product deleted successfully!";
              } else {
                  $_SESSION['error'] = "Failed to delete product.";
              }
              break;

            case 'generate_product_report':
                require_once 'vendor/autoload.php'; 

                $filters = [
                    'category' => $_POST['category'] ?? null,
                    'supplier' => $_POST['supplier'] ?? null,
                    'min_price' => $_POST['min_price'] ?? null,
                    'max_price' => $_POST['max_price'] ?? null,
                    'stock_level' => $_POST['stock_level'] ?? null
                ];

                // Remove null filters
                $filters = array_filter($filters);

                $reportData = generateProductReport($filters);

                // Determine export type
                $exportType = $_POST['export_type'] ?? 'pdf';

                if ($exportType === 'pdf') {
                    $_SESSION['message'] = "Product PDF report generated successfully!";
                    exportProductReportToPDF($reportData);
                    
                } elseif ($exportType === 'csv') {
                    $_SESSION['message'] = "Product CSV report generated successfully!";
                    exportProductReportToCSV($reportData);
                }
        }

        // Redirect to prevent form resubmission
        header("Location: product.php");
        exit();
    }
}

// Fetch products, categories, and suppliers
$products = getAllProducts();
$categories = getAllCategories();
$suppliers = getAllSuppliers();

// Pagenation
if (isset($_GET['entries']) && $_GET['entries'] === 'all') {
  // If 'all' is selected, show all products without pagination
  $itemsPerPage = null;
} else {
  // Otherwise, set the number of items per page (default to 10 if not set)
  $itemsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
}
$totalProducts = count($products);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// If entries per page is not 'all', calculate pagination
if ($itemsPerPage !== 'all') {
    $start = ($currentPage - 1) * $itemsPerPage;
    $displayProducts = array_slice($products, $start, $itemsPerPage);
} else {
    // If 'all' is selected, display all products
    $displayProducts = $products;
}

?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include "includes/tailwind_css.php" ?>
  <title>Inventory - Product</title>
</head>
<body class="sans-serif">
  <?php include "includes/nav.php" ?>
  <?php include "includes/add_product.php" ?>
  <?php include "includes/edit_product.php" ?>
  <?php include "includes/product_generate_report.php" ?>
  
    
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
      <h2 class="text-xl font-semibold text-gray-800">Products Inventory</h2>
      
        <!-- Show entries -->
        <div class="flex gap-3 text-sm ">
          <div>
            <div class="flex items-center gap-2 px-3 text-sm text-gray-600">
              <label for="entries-products">Show</label>
              <select id="entries-products" class="px-2 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
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
          <?php if (!empty($products)): ?>
            <div class="relative mb-4 min-w-96">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
                <input 
                  type="text" 
                  id="product-search-input" 
                  placeholder="Search products (e.g., names, categories, prices)" 
                  class="w-full px-3 py-2 pl-10 border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500"
                >
            </div>
          <?php endif ?>
          <!-- Add Product Button -->
          
          <button onclick="openModal('add-product')" class="h-9 right-0 flex items-center text-xs gap-1 shadow-[0_5px_5px_rgba(0,0,0,0.15)] px-3 text-white rounded-md transition duration-300 bg-green-500 hover:bg-green-700 hover:shadow-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Product
          </button>
         
          <!-- Generate Report Button -->
          <?php if (!empty($products)): ?>
            <div>
            <button onclick="openModal('generate-report-product')" class="flex items-center text-xs gap-1 shadow-[0_5px_5px_rgba(0,0,0,0.15)] h-9 px-3 py-2 text-white rounded-md bg-yellow-500 transition duration-300 hover:bg-yellow-700 hover:shadow-none">
              <svg class="size-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10V4a1 1 0 0 0-1-1H9.914a1 1 0 0 0-.707.293L5.293 7.207A1 1 0 0 0 5 7.914V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2M10 3v4a1 1 0 0 1-1 1H5m5 6h9m0 0-2-2m2 2-2 2"/>
              </svg>
              Generate Report
            </button>
            </div>
          <?php endif ?>
        </div>
      </div>

      <section class="mt-6 mb-8">
        <!-- Table Wrapper  -->
        <div class="overflow-hidden bg-white rounded-lg shadow-lg">
          <div class="overflow-x-auto">
            <table id="products-table" class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 ">
                  <?php
                  $headers = [
                    'Product Name' => true,
                    'Price' => true,
                    'Quantity Stocks' => true,
                    'Date Added' => true,
                    'Description' => false,
                    'Category' => true,
                    'Supplier' => true,
                    'Actions' => false
                  ];

                  foreach ($headers as $header => $sortable): ?>
                    <th class="px-4 py-3 font-medium text-center text-gray-600 border-b">
                        <?php if ($sortable): ?>
                          <button onclick="toggleSortIcon(this)" 
                                  class="flex items-center justify-center w-full h-full gap-1 transition duration-300 rounded-md hover:text-rose-600">
                            <span><?php echo $header; ?></span>
                            <svg class="w-4 h-4 icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-width="2" d="M8 9l4-4 4 4M16 15l-4 4-4-4" />
                            </svg>
                          </button>
                        <?php else: ?>
                            <?php echo $header; ?>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
              </tr>
              </thead>
              
              <tbody class="divide-y divide-gray-100">
                <?php if (!empty($products)): ?>
                  <?php foreach ($displayProducts as $product): ?>
                    <tr class="transition duration-300 hover:bg-gray-50">
                      <td class="px-4 py-3 font-medium text-gray-800 border-r border-gray-100">
                        <?php echo htmlspecialchars($product['product_name']); ?>
                      </td>
                      <td class="px-4 py-3 text-center border-r border-gray-100">
                        <span class="font-medium text-gray-700">₱ <?php echo number_format($product['price'], 2); ?></span>
                      </td>
                      <td class="px-4 py-3 text-center border-r border-gray-100 ">
                        <?php
                        $stockLevel = intval($product['quantity_stock']);
                        $stockClass = $stockLevel <= 10 ? 'bg-red-100 text-red-800' : 
                                    ($stockLevel <= 30 ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-green-100 text-green-800');
                        ?>
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $stockClass; ?>">
                          <?php echo htmlspecialchars($product['quantity_stock']); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3 text-center text-gray-600 border-r border-gray-100">
                        <?php echo date('M d, Y', strtotime($product['date_stored'])); ?>
                      </td>
                      <td class="px-4 py-3 text-gray-600 border-r border-gray-100">
                        <div class="max-w-xs truncate" title="<?php echo htmlspecialchars($product['description']); ?>">
                          <?php echo htmlspecialchars($product['description']); ?>
                        </div>
                      </td>
                      <td class="px-4 py-3 border-r border-gray-100">
                        <span class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full">
                          <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3 text-gray-600 border-r border-gray-100">
                        <span class="px-2 py-1 text-xs font-medium text-purple-600 bg-purple-100 rounded-full">
                          <?php echo htmlspecialchars($product['supplier_name']); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                          <!-- Edit Button -->
                          <button onclick="openEditModal(<?php 
                            echo htmlspecialchars(json_encode([
                              'product_id' => $product['product_id'],
                              'product_name' => $product['product_name'],
                              'price' => $product['price'],
                              'quantity_stock' => $product['quantity_stock'],
                              'description' => $product['description'],
                              'category_name' => $product['category_name'],
                              'supplier_name' => $product['supplier_name']
                            ])); 
                          ?>)" 
                          class="p-1 text-blue-600 transition-colors duration-300 rounded-lg hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                          </button>
                          <!-- Delete Button -->
                          <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
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
                    <td colspan="8" class="py-8 text-center text-gray-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                      </svg>
                      <p class="font-medium">No products found</p>
                      <p class="mt-1 text-sm">Add some products to see them listed here.</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
            
          </div>
        </div>
        <div class="pb-10 mt-4 text-sm text-gray-500">
          Showing <?php echo min($itemsPerPage, $totalProducts); ?> of <?php echo $totalProducts; ?> entries
        </div>          
      </section>
      
    </div>
  </main>

  <?php include "includes/footer.php" ?>
</body>
<script src="javascripts/script.js?v=<?php echo time(); ?>"></script>
<script>
  

  function openEditModal(productData) {
    // Set values in edit modal
    document.getElementById('edit-product-id').value = productData.product_id;
    document.getElementById('edit-product-name').value = productData.product_name;
    document.getElementById('edit-product-price').value = productData.price;
    document.getElementById('edit-quantity-stocks').value = productData.quantity_stock;
    document.getElementById('edit-product-description').value = productData.description;
    
    // Update category and supplier dropdowns
    const categorySelect = document.getElementById('edit-product-category');
    const supplierSelect = document.getElementById('edit-product-supplier');
    
    // Find and select the matching category and supplier
    Array.from(categorySelect.options).forEach((option, index) => {
        if (option.text === productData.category_name) {
            categorySelect.selectedIndex = index;
        }
    });
    
    Array.from(supplierSelect.options).forEach((option, index) => {
        if (option.text === productData.supplier_name) {
            supplierSelect.selectedIndex = index;
        }
    });
    
    // Open the edit modal
    openModal('edit-product');
}


document.addEventListener('DOMContentLoaded', function() {
  // Category form submission
  document.querySelector('#add-category-modal form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const form = e.target;
      const formData = new FormData(form);
      formData.append('ajax_action', 'add_category');
     
      fetch("", {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              // Add new category to dropdown
              const categorySelect = document.getElementById('product_category');
              const newOption = document.createElement('option');
              newOption.value = data.id;
              newOption.textContent = data.name;
              newOption.selected = true;
              categorySelect.appendChild(newOption);

              // Show success message
              showNotification(data.message, 'success');

              // Close modal
              closeModal('add-category');
          } else {
              showNotification(data.message, 'error');
          }
      })
      .catch(error => {
          console.error('Error:', error);
          showNotification('An error occurred', 'error');
      });
  });

  // Supplier form submission
  document.querySelector('#add-supplier-modal form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const form = e.target;
      const formData = new FormData(form);
      formData.append('ajax_action', 'add_supplier');

      fetch("", {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              // Add new supplier to dropdown
              const supplierSelect = document.getElementById('product_supplier');
              const newOption = document.createElement('option');
              newOption.value = data.id;
              newOption.textContent = data.name;
              newOption.selected = true;
              supplierSelect.appendChild(newOption);

              // Show success message
              showNotification(data.message, 'success');

              // Close modal
              closeModal('add-supplier');
          } else {
              showNotification(data.message, 'error');
          }
      })
      .catch(error => {
          console.error('Error:', error);
          showNotification('An error occurred', 'error');
      });
  });

  // Notification function
  function showNotification(message, type) {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `fixed top-4 right-4 z-50 p-4 rounded ${
          type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
      }`;
      notification.textContent = message;

      // Add to body
      document.body.appendChild(notification);

      // Remove after 3 seconds
      setTimeout(() => {
          notification.remove();
      }, 3000);
  }
});

</script>
</html>