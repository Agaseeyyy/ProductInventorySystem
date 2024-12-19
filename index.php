<?php
require_once 'php/auth_functions.php';
require_once 'db_connection.php';
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
      }

      // Redirect to prevent form resubmission
      header("Location: index.php");
      exit();
  }
}

// Fetch dashboard metrics
function getDashboardMetrics($conn) {
    $metrics = [];

    // Total Products
    $product_query = "SELECT COUNT(*) as total_products FROM products";
    $product_result = $conn->query($product_query);
    $metrics['total_products'] = $product_result->fetch_assoc()['total_products'];

    // Total Categories
    $category_query = "SELECT COUNT(*) as total_categories FROM categories";
    $category_result = $conn->query($category_query);
    $metrics['total_categories'] = $category_result->fetch_assoc()['total_categories'];

    // Total Suppliers
    $supplier_query = "SELECT COUNT(*) as total_suppliers FROM suppliers";
    $supplier_result = $conn->query($supplier_query);
    $metrics['total_suppliers'] = $supplier_result->fetch_assoc()['total_suppliers'];

    // Low Stock Products (less than 10 items)
    $low_stock_query = "SELECT COUNT(*) as low_stock_count FROM products WHERE quantity_stock < 10";
    $low_stock_result = $conn->query($low_stock_query);
    $metrics['low_stock_products'] = $low_stock_result->fetch_assoc()['low_stock_count'];

    // Recently Added Products (last 7 days)
    $recent_products_query = "SELECT p.product_name, p.created_at, p.price, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.category_id 
                            WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                            ORDER BY p.created_at DESC 
                            LIMIT 5";
    $recent_products_result = $conn->query($recent_products_query);
    $metrics['recent_products_list'] = [];
    while ($row = $recent_products_result->fetch_assoc()) {
        $metrics['recent_products_list'][] = $row;
    }
    
    $recent_products_query = "SELECT COUNT(*) as recent_products FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $recent_products_result = $conn->query($recent_products_query);
    $metrics['recent_products'] = $recent_products_result->fetch_assoc()['recent_products'];

    // Top 5 Low Stock Products
    $top_low_stock_query = "SELECT product_name, quantity_stock, category_id FROM products WHERE quantity_stock < 10 ORDER BY quantity_stock ASC LIMIT 5";
    $top_low_stock_result = $conn->query($top_low_stock_query);
    $metrics['top_low_stock'] = [];
    while ($row = $top_low_stock_result->fetch_assoc()) {
        $metrics['top_low_stock'][] = $row;
    }

    return $metrics;
}

$dashboard_metrics = getDashboardMetrics($conn);
// Fetch products, categories, and suppliers
$products = getAllProducts();
$categories = getAllCategories();
$suppliers = getAllSuppliers();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include "includes/tailwind_css.php" ?>
  <title>Inventory - Dashboard</title>
</head>
<body class="sans-serif">
  <?php include "includes/nav.php" ?>
  <?php include "includes/add_product.php" ?>

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
      
      <!-- DASHBOARD HEADER -->
      <div class="flex flex-row items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Inventory Dashboard</h2>
        <div class="flex space-x-4">
          <button onclick="openModal('add-product')" class="transition duration-300 right-0 flex items-center text-xs h-8 gap-1 shadow-[0_5px_5px_rgba(0,0,0,0.15)] px-3 py-2 text-white rounded-md bg-green-500 hover:bg-green-700 hover:shadow-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Product
          </button>
          <a href="product.php" class="flex-row gap-1 right-0 flex items-center text-xs h-8  shadow-[0_5px_5px_rgba(0,0,0,0.15)] px-3 py-2 text-white bg-yellow-500 rounded transition duration-300 hover:bg-yellow-600">
          <svg class="text-white size-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12c.263 0 .524-.06.767-.175a2 2 0 0 0 .65-.491c.186-.21.333-.46.433-.734.1-.274.15-.568.15-.864a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 12 9.736a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 16 9.736c0 .295.052.588.152.861s.248.521.434.73a2 2 0 0 0 .649.488 1.809 1.809 0 0 0 1.53 0 2.03 2.03 0 0 0 .65-.488c.185-.209.332-.457.433-.73.1-.273.152-.566.152-.861 0-.974-1.108-3.85-1.618-5.121A.983.983 0 0 0 17.466 4H6.456a.986.986 0 0 0-.93.645C5.045 5.962 4 8.905 4 9.736c.023.59.241 1.148.611 1.567.37.418.865.667 1.389.697Zm0 0c.328 0 .651-.091.94-.266A2.1 2.1 0 0 0 7.66 11h.681a2.1 2.1 0 0 0 .718.734c.29.175.613.266.942.266.328 0 .651-.091.94-.266.29-.174.537-.427.719-.734h.681a2.1 2.1 0 0 0 .719.734c.289.175.612.266.94.266.329 0 .652-.091.942-.266.29-.174.536-.427.718-.734h.681c.183.307.43.56.719.734.29.174.613.266.941.266a1.819 1.819 0 0 0 1.06-.351M6 12a1.766 1.766 0 0 1-1.163-.476M5 12v7a1 1 0 0 0 1 1h2v-5h3v5h7a1 1 0 0 0 1-1v-7m-5 3v2h2v-2h-2Z"/>
          </svg>
            Manage Products
          </a>
        </div>
      </div>

      <!-- DASHBOARD METRICS -->
      <section class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <!-- Total Products -->
        <div class="p-6 bg-white rounded-lg shadow-md">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-500">Total Products</h3>
              <p class="text-2xl font-bold text-green-600"><?php echo $dashboard_metrics['total_products']; ?></p>
            </div>
            <i class="text-3xl text-green-400 fas fa-box"></i>
          </div>
        </div>

        <!-- Total Categories -->
        <div class="p-6 bg-white rounded-lg shadow-md">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-500">Total Categories</h3>
              <p class="text-2xl font-bold text-blue-600"><?php echo $dashboard_metrics['total_categories']; ?></p>
            </div>
            <i class="text-3xl text-blue-400 fas fa-tags"></i>
          </div>
        </div>

        <!-- Total Suppliers -->
        <div class="p-6 bg-white rounded-lg shadow-md">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-500">Total Suppliers</h3>
              <p class="text-2xl font-bold text-purple-600"><?php echo $dashboard_metrics['total_suppliers']; ?></p>
            </div>
            <i class="text-3xl text-purple-400 fas fa-truck"></i>
          </div>
        </div>
      </section>

      <!-- ADDITIONAL METRICS -->
      <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <!-- Low Stock Products -->
        <div class="p-6 bg-white border border-gray-100 shadow-lg rounded-xl">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-red-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="text-red-600 size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-800">Low Stock Products</h3>
            </div>
            <span class="px-3 py-1.5 text-red-600 bg-red-100 rounded-full text-sm font-medium">
              <?php echo $dashboard_metrics['low_stock_products']; ?> Products
            </span>
          </div>
          
          <?php if (!empty($dashboard_metrics['top_low_stock'])): ?>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-200">
                    <th class="py-3 text-sm font-semibold text-left text-gray-600">Product</th>
                    <th class="py-3 text-sm font-semibold text-right text-gray-600">Stock Level</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($dashboard_metrics['top_low_stock'] as $product): ?>
                    <tr class="transition-colors duration-200 border-b border-gray-100 hover:bg-gray-50">
                      <td class="py-3 text-sm font-medium text-gray-800"><?php echo htmlspecialchars($product['product_name']); ?></td>
                      <td class="py-3 text-right">
                        <span class="<?php echo $product['quantity_stock'] <= 5 ? 'bg-red-100 text-red-700' : 'bg-red-100 text-red-700'; ?> px-2.5 py-1 rounded-full text-xs font-medium">
                          <?php echo htmlspecialchars($product['quantity_stock']); ?> units
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="py-8 text-center">
              <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="mt-4 text-gray-500">No low stock products found.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Recently Added Products -->
        <div class="p-6 bg-white border border-gray-100 shadow-lg rounded-xl mb-14">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-green-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="text-green-600 size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-800">Recently Added</h3>
            </div>
            <span class="px-3 py-1.5 text-green-600 bg-green-100 rounded-full text-sm font-medium">
              <?php echo $dashboard_metrics['recent_products']; ?> Products
            </span>
          </div>
          
          <?php if (!empty($dashboard_metrics['recent_products_list'])): ?>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-200">
                    <th class="py-3 text-sm font-semibold text-left text-gray-600">Product</th>
                    <th class="py-3 text-sm font-semibold text-left text-gray-600">Category</th>
                    <th class="py-3 text-sm font-semibold text-right text-gray-600">Price</th>
                    <th class="py-3 text-sm font-semibold text-right text-gray-600">Added</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($dashboard_metrics['recent_products_list'] as $product): ?>
                    <tr class="transition-colors duration-200 border-b border-gray-100 hover:bg-gray-50">
                      <td class="py-3 text-sm font-medium text-gray-800"><?php echo htmlspecialchars($product['product_name']); ?></td>
                      <td class="py-3">
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                          <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                      </td>
                      <td class="py-3 font-medium text-right text-gray-900">₱<?php echo number_format($product['price'], 2); ?></td>
                      <td class="py-3 text-sm text-right text-gray-500">
                        <?php echo date('M d, Y', strtotime($product['created_at'])); ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="py-8 text-center">
              <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
              </svg>
              <p class="mt-4 text-gray-500">No products added in the last 7 days.</p>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </main>

  <?php include "includes/footer.php" ?>

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</body>
<script src="javascripts/script.js?v=1.0"></script>
<script>
  window.addEventListener('click', function(event) {
  const modal = document.getElementById(modalName + "-modal");
  modal.classList.add('hidden');
});

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