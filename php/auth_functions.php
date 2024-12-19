<?php
// Include the database connection and setup script
require_once 'db_connection.php';

session_start();

// Function to register
function registerUser($username, $email, $fullname, $password) {
    global $conn;

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (username, email, fullname, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $fullname, $hashed_password);

    // Execute the statement
    try {
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        // Handle potential duplicate key errors
        return false;
    }
}

// Function to login
function loginUser($username, $password) {
    global $conn;

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            
            return true;
        }
    }

    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to logout
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();
}

// Function to update profile
function updateProfile($username, $email, $fullname, $password = null) {
    global $conn;

    // Check if username or email already exists (excluding current user)
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $check_stmt->bind_param("ssi", $username, $email, $_SESSION['user_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Username or email already exists
        return false;
    }

    if ($password) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare statement with password update
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, fullname = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $email, $fullname, $hashed_password, $_SESSION['user_id']);
    } else {
        // Prepare statement without password update
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, fullname = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $fullname, $_SESSION['user_id']);
    }

    // Execute the statement
    $result = $stmt->execute();
    $stmt->close();

    // Update session variables if update is successful
    if ($result) {
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['fullname'] = $fullname;
    }

    return $result;
}

// Function to add a category
function addCategory($name) {
    global $conn;

    // Check if category already exists
    $check_stmt = $conn->prepare("SELECT * FROM categories WHERE name = ?");
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        return false; // Category already exists
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    // Execute the statement
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function getAllCategories() {
    global $conn;

    $query = "SELECT * FROM categories ORDER BY category_ID";
    $result = $conn->query($query);

    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
}

// Function to update a category
function updateCategory($category_ID, $name) {
    global $conn;

    // Check if category already exists
    $check_stmt = $conn->prepare("SELECT * FROM categories WHERE name = ? AND category_ID != ?");
    $check_stmt->bind_param("si", $name, $category_ID);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        return false; // Category name already exists
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE category_ID = ?");
    $stmt->bind_param("si", $name, $category_ID);

    // Execute the statement
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

// Function to delete a category
function deleteCategory($category_ID) {
    global $conn;

    // First check if category has products
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $check_stmt->bind_param("i", $category_ID);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_row()[0];
    
    if ($count > 0) {
        // Category has products - cannot delete
        $conn->rollback();
        return [
            'success' => false,
            'message' => "Cannot delete category: $count product(s) are still assigned to it. Please remove or reassign these products first."
        ];
    }
    
    // If no products, proceed with deletion
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_ID = ?");
    $stmt->bind_param("i", $category_ID);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $conn->commit();
        return [
            'success' => true,
            'message' => "Category successfully deleted"
        ];
    } else {
        $conn->rollback();
        return [
            'success' => false,
            'message' => "Category not found"
        ];
    }
}

// Function to add a supplier
function addSupplier($supplier_name, $contact_num, $address) {
    global $conn;

    // Check if category already exists
    $check_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplier_name = ?");
    $check_stmt->bind_param("s", $supplier_name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    // Validate input
    if ($result->num_rows > 0 || empty($supplier_name) || empty($contact_num) || empty($address)) {
        return false;
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_num, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $supplier_name, $contact_num, $address);

    try {
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Error adding supplier: " . $e->getMessage());
        return false;
    }
}

// Function to get all suppliers
function getAllSuppliers() {
    global $conn;

    $query = "SELECT * FROM suppliers ORDER BY supplier_id";
    $result = $conn->query($query);

    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    return [];
}

// Function to update a supplier
function updateSupplier($supplier_id, $supplier_name, $contact_num, $address) {
    global $conn;

    // Validate input
    if (empty($supplier_id) || empty($supplier_name) || empty($contact_num) || empty($address)) {
        return false;
    }

    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, contact_num = ?, address = ? WHERE supplier_id = ?");
    $stmt->bind_param("sssi", $supplier_name, $contact_num, $address, $supplier_id);

    try {
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Error updating supplier: " . $e->getMessage());
        return false;
    }
}

// Function to delete a supplier
function deleteSupplier($supplier_id) {
    global $conn;

    // First check if supplier has products
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE supplier_id = ?");
    $check_stmt->bind_param("i", $supplier_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_row()[0];
    
    if ($count > 0) {
        // supplier has products - cannot delete
        $conn->rollback();
        return [
            'success' => false,
            'message' => "Cannot delete supplier: $count product(s) are still assigned to it. Please remove or reassign these products first."
        ];
    }
    
     // If no products, proceed with deletion
     $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
     $stmt->bind_param("i", $supplier_id);
     $stmt->execute();
     
     if ($stmt->affected_rows > 0) {
         $conn->commit();
         return [
             'success' => true,
             'message' => "Supplier successfully deleted"
         ];
     } else {
         $conn->rollback();
         return [
             'success' => false,
             'message' => "Supplier not found"
         ];
     }
}

// Function to add a product
function addProduct($category_id, $supplier_id, $product_name, $price, $quantity_stock, $description) {
    global $conn;
    
    // First check if the exact same product (same name, category and supplier) already exists
    $check_stmt = $conn->prepare("
        SELECT product_id 
        FROM products 
        WHERE product_name = ? 
        AND category_id = ? 
        AND supplier_id = ?
    ");
    $check_stmt->bind_param("sii", $product_name, $category_id, $supplier_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Product with same name, category and supplier already exists
        $check_stmt->close();
        return false;
    }
    $check_stmt->close();
    
    // If no exact match found, proceed with insertion
    $stmt = $conn->prepare("
        INSERT INTO products 
        (category_id, supplier_id, product_name, price, quantity_stock, description) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisdis", $category_id, $supplier_id, $product_name, $price, $quantity_stock, $description);
    
    try {
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}


// Function to get all products with category and supplier names
function getAllProducts() {
    global $conn;
    $query = "
        SELECT 
            p.product_id, 
            p.product_name, 
            p.price, 
            p.quantity_stock, 
            p.date_stored, 
            p.description,
            c.name AS category_name, 
            s.supplier_name
        FROM 
            products p
        JOIN 
            categories c ON p.category_id = c.category_ID
        JOIN 
            suppliers s ON p.supplier_id = s.supplier_id
        ORDER BY 
            p.product_id
    ";
    
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Function to update a product
function updateProduct($product_id, $category_id, $supplier_id, $product_name, $price, $quantity_stock, $description) {
  global $conn;
  $stmt = $conn->prepare("
      UPDATE products 
      SET category_id = ?, 
          supplier_id = ?, 
          product_name = ?, 
          price = ?, 
          quantity_stock = ?, 
          description = ? 
      WHERE product_id = ?
  ");
  $stmt->bind_param("iisdisi", $category_id, $supplier_id, $product_name, $price, $quantity_stock, $description, $product_id);
  
  try {
      $result = $stmt->execute();
      $stmt->close();
      return $result;
  } catch (Exception $e) {
      error_log("Error updating product: " . $e->getMessage());
      return false;
  }
}

// Function to delete a product
function deleteProduct($product_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    
    try {
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting product: " . $e->getMessage());
        return false;
    }
}

// Function to export a product report
function generateProductReport($filters = []) {
    global $conn;

    // Base query
    $query = "
        SELECT 
            p.product_id, 
            p.product_name, 
            p.price, 
            p.quantity_stock, 
            p.date_stored, 
            p.description,
            c.name AS category_name, 
            s.supplier_name
        FROM 
            products p
        JOIN 
            categories c ON p.category_id = c.category_ID
        JOIN 
            suppliers s ON p.supplier_id = s.supplier_id
    ";

    // Apply filters
    $whereConditions = [];
    $types = '';
    $bindParams = [];

    // Filter by category
    if (!empty($filters['category'])) {
        $whereConditions[] = "c.name = ?";
        $types .= 's';
        $bindParams[] = $filters['category'];
    }

    // Filter by supplier
    if (!empty($filters['supplier'])) {
        $whereConditions[] = "s.supplier_name = ?";
        $types .= 's';
        $bindParams[] = $filters['supplier'];
    }

    // Filter by price range
    if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
        $whereConditions[] = "p.price BETWEEN ? AND ?";
        $types .= 'dd';
        $bindParams[] = $filters['min_price'];
        $bindParams[] = $filters['max_price'];
    }

    // Filter by stock level
    if (!empty($filters['stock_level'])) {
        switch ($filters['stock_level']) {
            case 'low':
                $whereConditions[] = "p.quantity_stock < 10";
                break;
            case 'medium':
                $whereConditions[] = "p.quantity_stock BETWEEN 10 AND 50";
                break;
            case 'high':
                $whereConditions[] = "p.quantity_stock > 50";
                break;
        }
    }

    // Add WHERE clause if conditions exist
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }

    // Prepare statement
    $stmt = $conn->prepare($query);

    // Bind parameters if any
    if (!empty($bindParams)) {
        $stmt->bind_param($types, ...$bindParams);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    $totalValue = 0;
    $totalStock = 0;

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
        $totalValue += $row['price'] * $row['quantity_stock'];
        $totalStock += $row['quantity_stock'];
    }

    // Generate report summary
    $reportSummary = [
        'total_products' => count($products),
        'total_stock_value' => $totalValue,
        'total_stock_quantity' => $totalStock,
        'avg_product_price' => $totalValue / (count($products) ?: 1),
        'categories' => array_unique(array_column($products, 'category_name')),
        'suppliers' => array_unique(array_column($products, 'supplier_name'))
    ];

    return [
        'products' => $products,
        'summary' => $reportSummary
    ];
}

// Function to export product report as PDF
function exportProductReportToPDF($reportData) {
    // Check if TCPDF library is installed
    if (!class_exists('TCPDF')) {
        error_log('TCPDF library not installed');
        return false;
    }

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Inventory Management System');
    $pdf->SetTitle('Product Inventory Report');
    $pdf->SetSubject('Product Inventory Details');

    // Add a page
    $pdf->AddPage();

    // Summary section
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 10, 'Product Inventory Report Summary', 0, 1, 'C');
    
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->Cell(0, 6, 'Total Products: ' . $reportData['summary']['total_products'], 0, 1);
    $pdf->Cell(0, 6, 'Total Stock Value: ₱ ' . number_format($reportData['summary']['total_stock_value'], 2), 0, 1);
    $pdf->Cell(0, 6, 'Total Stock Quantity: ' . $reportData['summary']['total_stock_quantity'], 0, 1);
    $pdf->Cell(0, 6, 'Average Product Price: ₱ ' . number_format($reportData['summary']['avg_product_price'], 2), 0, 1);

    // Product details section
    $pdf->Ln(10);
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->Cell(0, 10, 'Product Details', 0, 1, 'C');

    // Table header
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(40, 7, 'Product Name', 1);
    $pdf->Cell(20, 7, 'Price', 1);
    $pdf->Cell(20, 7, 'Quantity', 1);
    $pdf->Cell(40, 7, 'Category', 1);
    $pdf->Cell(40, 7, 'Supplier', 1);
    $pdf->Ln();

    // Product details rows
    $pdf->SetFont('dejavusans', '', 9);
    foreach ($reportData['products'] as $product) {
        $pdf->Cell(40, 6, $product['product_name'], 1);
        $pdf->Cell(20, 6, '₱ ' . number_format($product['price'], 2), 1);
        $pdf->Cell(20, 6, $product['quantity_stock'], 1);
        $pdf->Cell(40, 6, $product['category_name'], 1);
        $pdf->Cell(40, 6, $product['supplier_name'], 1);
        $pdf->Ln();
    }

    // Output PDF
    $filename = 'product_report_' . date('Y-m-d_His') . '.pdf';
    $pdf->Output($filename, 'D');
    return true;
}

// Function to export product report as csv
function exportProductReportToCSV($reportData) {
    // Prepare CSV filename
    $filename = 'product_report_' . date('Y-m-d_His') . '.csv';
    
    // Open file for writing
    $file = fopen($filename, 'w');
    
    // Write CSV headers
    $headers = [
        'Product ID', 
        'Product Name', 
        'Price', 
        'Quantity Stock', 
        'Date Stored', 
        'Category', 
        'Supplier'
    ];
    fputcsv($file, $headers);
    
    // Write product data
    foreach ($reportData['products'] as $product) {
        $rowData = [
            $product['product_id'],
            $product['product_name'],
            $product['price'],
            $product['quantity_stock'],
            $product['date_stored'],
            $product['category_name'],
            $product['supplier_name']
        ];
        fputcsv($file, $rowData);
    }
    
    // Close the file
    fclose($file);
    
    // Force download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filename);
    
    // Optional: Delete the file after sending
    unlink($filename);
    
    return true;
}

// Function to export a category report
function generateCategoryReport($filters = []) {
    global $conn;

    // Base query
    $query = "
        SELECT 
            c.category_ID, 
            c.name AS category_name,
            COALESCE(product_counts.total_products, 0) AS total_products,
            COALESCE(product_counts.total_stock_quantity, 0) AS total_stock_quantity,
            COALESCE(product_counts.total_stock_value, 0) AS total_stock_value
        FROM 
            categories c
        LEFT JOIN (
            SELECT 
                category_id, 
                COUNT(product_id) AS total_products,
                SUM(quantity_stock) AS total_stock_quantity,
                SUM(price * quantity_stock) AS total_stock_value
            FROM 
                products
            GROUP BY 
                category_id
        ) product_counts ON c.category_ID = product_counts.category_id
    ";

    // Apply filters
    $whereConditions = [];
    $types = '';
    $bindParams = [];

    // Filter by name
    if (!empty($filters['category_name'])) {
        $whereConditions[] = "c.name LIKE ?";
        $types .= 's';
        $bindParams[] = '%' . $filters['category_name'] . '%';
    }

    // Filter by product count
    if (!empty($filters['min_products'])) {
        $whereConditions[] = "COALESCE(product_counts.total_products, 0) >= ?";
        $types .= 'i';
        $bindParams[] = $filters['min_products'];
    }

    // Add WHERE clause if conditions exist
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }

    // Prepare statement
    $stmt = $conn->prepare($query);

    // Bind parameters if any
    if (!empty($bindParams)) {
        $stmt->bind_param($types, ...$bindParams);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    $totalCategories = 0;
    $totalProducts = 0;
    $totalStockValue = 0;

    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
        $totalCategories++;
        $totalProducts += $row['total_products'];
        $totalStockValue += $row['total_stock_value'];
    }

    // Generate report summary
    $reportSummary = [
        'total_categories' => $totalCategories,
        'total_products' => $totalProducts,
        'total_stock_value' => $totalStockValue,
        'avg_products_per_category' => $totalCategories > 0 ? $totalProducts / $totalCategories : 0
    ];

    return [
        'categories' => $categories,
        'summary' => $reportSummary
    ];
}

// Function to export category report as PDF
function exportCategoryReportToPDF($reportData) {
    // Check if TCPDF library is installed
    if (!class_exists('TCPDF')) {
        error_log('TCPDF library not installed');
        return false;
    }

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Inventory Management System');
    $pdf->SetTitle('Category Inventory Report');
    $pdf->SetSubject('Category Inventory Details');

    // Add a page
    $pdf->AddPage();

    // Summary section
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 10, 'Category Inventory Report Summary', 0, 1, 'C');
    
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->Cell(0, 6, 'Total Categories: ' . $reportData['summary']['total_categories'], 0, 1);
    $pdf->Cell(0, 6, 'Total Products: ' . $reportData['summary']['total_products'], 0, 1);
    $pdf->Cell(0, 6, 'Total Stock Value: ₱ ' . number_format($reportData['summary']['total_stock_value'], 2), 0, 1);
    $pdf->Cell(0, 6, 'Avg Products per Category: ' . number_format($reportData['summary']['avg_products_per_category'], 2), 0, 1);

    // Category details section
    $pdf->Ln(10);
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->Cell(0, 10, 'Category Details', 0, 1, 'C');

    // Table header
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(60, 7, 'Category Name', 1);
    $pdf->Cell(31, 7, 'Total Products', 1);
    $pdf->Cell(43, 7, 'Total Stock Quantity', 1);
    $pdf->Cell(43, 7, 'Total Stock Value', 1);
    $pdf->Ln();

    // Category details rows
    $pdf->SetFont('dejavusans', '', 9);
    foreach ($reportData['categories'] as $category) {
        $pdf->Cell(60, 6, $category['category_name'], 1);
        $pdf->Cell(31, 6, $category['total_products'], 1);
        $pdf->Cell(43, 6, $category['total_stock_quantity'] ?? 0, 1);
        $pdf->Cell(43, 6, '₱ ' . number_format($category['total_stock_value'] ?? 0, 2), 1);
        $pdf->Ln();
    }

    // Output PDF
    $filename = 'category_report_' . date('Y-m-d_His') . '.pdf';
    $pdf->Output($filename, 'D');
    return true;
}

// Function to export category report as csv
function exportCategoryReportToCSV($reportData) {
    // Prepare CSV filename
    $filename = 'category_report_' . date('Y-m-d_His') . '.csv';
    
    // Open file for writing
    $file = fopen($filename, 'w');
    
    // Write CSV headers
    $headers = [
        'Category ID', 
        'Category Name', 
        'Total Products', 
        'Total Stock Quantity', 
        'Total Stock Value'
    ];
    fputcsv($file, $headers);
    
    // Write category data
    foreach ($reportData['categories'] as $category) {
        $rowData = [
            $category['category_ID'],
            $category['category_name'],
            $category['total_products'],
            $category['total_stock_quantity'] ?? 0,
            $category['total_stock_value'] ?? 0
        ];
        fputcsv($file, $rowData);
    }
    
    // Close the file
    fclose($file);
    
    // Force download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filename);
    
    // Optional: Delete the file after sending
    unlink($filename);
    
    return true;
}

// Function to export a suppplier report
function generateSupplierReport($filters = []) {
    global $conn;

    // Base query
    $query = "
        SELECT 
            s.supplier_id, 
            s.supplier_name,
            s.contact_num,
            s.address,
            COUNT(p.product_id) AS total_products,
            COALESCE(SUM(p.quantity_stock), 0) AS total_stock_quantity,
            COALESCE(SUM(p.price * p.quantity_stock), 0) AS total_stock_value
        FROM 
            suppliers s
        LEFT JOIN 
            products p ON s.supplier_id = p.supplier_id
    ";

    // Apply filters
    $whereConditions = [];
    $types = '';
    $bindParams = [];

    // Filter by supplier name
    if (!empty($filters['supplier_name'])) {
        $whereConditions[] = "s.supplier_name LIKE ?";
        $types .= 's';
        $bindParams[] = '%' . $filters['supplier_name'] . '%';
    }

    // Filter by product count
    if (!empty($filters['min_products'])) {
        $havingConditions[] = "total_products >= ?";
        $types .= 'i';
        $bindParams[] = $filters['min_products'];
    }

    // Add WHERE clause if conditions exist
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }

    // Group by all non-aggregated columns
    $query .= " GROUP BY s.supplier_id, s.supplier_name, s.contact_num, s.address";

    // Add HAVING clause for product count filter
    if (!empty($havingConditions)) {
        $query .= " HAVING " . implode(" AND ", $havingConditions);
    }

    // Prepare statement
    $stmt = $conn->prepare($query);

    // Bind parameters if any
    if (!empty($bindParams)) {
        $stmt->bind_param($types, ...$bindParams);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $suppliers = [];
    $totalSuppliers = 0;
    $totalProducts = 0;
    $totalStockValue = 0;

    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
        $totalSuppliers++;
        $totalProducts += $row['total_products'];
        $totalStockValue += $row['total_stock_value'];
    }

    // Generate report summary
    $reportSummary = [
        'total_suppliers' => $totalSuppliers,
        'total_products' => $totalProducts,
        'total_stock_value' => $totalStockValue,
        'avg_products_per_supplier' => $totalSuppliers > 0 ? $totalProducts / $totalSuppliers : 0
    ];

    return [
        'suppliers' => $suppliers,
        'summary' => $reportSummary
    ];
}

// Function to export category report as PDF
function exportSupplierReportToPDF($reportData) {
    // Check if TCPDF library is installed
    if (!class_exists('TCPDF')) {
        error_log('TCPDF library not installed');
        return false;
    }

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Inventory Management System');
    $pdf->SetTitle('Supplier Inventory Report');
    $pdf->SetSubject('Supplier Inventory Details');

    // Add a page
    $pdf->AddPage();

    // Summary section
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 10, 'Supplier Inventory Report Summary', 0, 1, 'C');
    
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->Cell(0, 6, 'Total Suppliers: ' . $reportData['summary']['total_suppliers'], 0, 1);
    $pdf->Cell(0, 6, 'Total Products: ' . $reportData['summary']['total_products'], 0, 1);
    $pdf->Cell(0, 6, 'Total Stock Value: ₱ ' . number_format($reportData['summary']['total_stock_value'], 2), 0, 1);
    $pdf->Cell(0, 6, 'Avg Products per Supplier: ' . number_format($reportData['summary']['avg_products_per_supplier'], 2), 0, 1);

    // Supplier details section
    $pdf->Ln(10);
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->Cell(0, 10, 'Supplier Details', 0, 1, 'C');

    // Table header
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(40, 7, 'Supplier Name', 1);
    $pdf->Cell(30, 7, 'Contact No.', 1);
    $pdf->Cell(40, 7, 'Address', 1);
    $pdf->Cell(25, 7, 'Total Products', 1);
    $pdf->Cell(35, 7, 'Total Stock Value', 1);
    $pdf->Ln();

    // Supplier details rows
    $pdf->SetFont('dejavusans', '', 9);
    foreach ($reportData['suppliers'] as $supplier) {
        $pdf->Cell(40, 6, $supplier['supplier_name'], 1);
        $pdf->Cell(30, 6, $supplier['contact_num'], 1);
        $pdf->Cell(40, 6, $supplier['address'], 1);
        $pdf->Cell(25, 6, $supplier['total_products'], 1);
        $pdf->Cell(35, 6, '₱ ' . number_format($supplier['total_stock_value'] ?? 0, 2), 1);
        $pdf->Ln();
    }

    // Output PDF
    $filename = 'supplier_report_' . date('Y-m-d_His') . '.pdf';
    $pdf->Output($filename, 'D');
    return true;
}

// Function to export category report as csv
function exportSupplierReportToCSV($reportData) {
    // Prepare CSV filename
    $filename = 'supplier_report_' . date('Y-m-d_His') . '.csv';
    
    // Open file for writing
    $file = fopen($filename, 'w');
    
    // Write CSV headers
    $headers = [
        'Supplier ID', 
        'Supplier Name', 
        'Contact Number', 
        'Address',
        'Total Products', 
        'Total Stock Quantity', 
        'Total Stock Value'
    ];
    fputcsv($file, $headers);
    
    // Write supplier data
    foreach ($reportData['suppliers'] as $supplier) {
        $rowData = [
            $supplier['supplier_id'],
            $supplier['supplier_name'],
            $supplier['contact_num'],
            $supplier['address'],
            $supplier['total_products'],
            $supplier['total_stock_quantity'] ?? 0,
            $supplier['total_stock_value'] ?? 0
        ];
        fputcsv($file, $rowData);
    }
    
    // Close the file
    fclose($file);
    
    // Force download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filename);
    
    // Optional: Delete the file after sending
    unlink($filename);
    
    return true;
}


?>