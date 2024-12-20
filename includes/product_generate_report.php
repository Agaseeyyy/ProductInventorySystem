<!-- Generate Report Modal -->
<div id="generate-report-product-modal" class="fixed top-0 left-0 z-50 flex items-center justify-center hidden w-full h-full transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm">

  <div class="relative w-full max-w-2xl px-8 py-6 mx-4 transition-all transform bg-white shadow-2xl rounded-xl">
    <div class="absolute top-4 right-4">
      <button onclick="closeModal('generate-report-product')" class="text-gray-400 transition-colors hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <h2 class="mb-4 text-lg font-bold">Generate Product Report</h2>
    <form id="generate-report-product" method="POST" action="">
      <input type="hidden" name="action" value="generate_product_report">
      
      <div class="grid grid-cols-2 gap-4">
        <!-- Category Filter -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-600">Category</label>
          <select name="category" class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo htmlspecialchars($category['name']); ?>">
                <?php echo htmlspecialchars($category['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Supplier Filter -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-600">Supplier</label>
          <select name="supplier" class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
            <option value="">All Suppliers</option>
            <?php foreach ($suppliers as $supplier): ?>
              <option value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>">
                <?php echo htmlspecialchars($supplier['supplier_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Price Range -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-600">Minimum Price</label>
          <input type="number" name="min_price" step="0.01" class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Minimum Price (optional)">
        </div>
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-600">Maximum Price</label>
          <input type="number" name="max_price" step="0.01" class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Maximum Price (optional)">
        </div>

        <!-- Stock Level -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-600">Stock Level</label>
          <select name="stock_level" class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
            <option value="">All Stock Levels</option>
            <option value="low">Low Stock (< 10)</option>
            <option value="medium">Medium Stock (10-50)</option>
            <option value="high">High Stock (> 50)</option>
          </select>
        </div>

        <!-- Export Type -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-600">Report Format</label>
          <select name="export_type" class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
            <option value="pdf">PDF</option>
            <option value="csv">CSV</option>
          </select>
        </div>
      </div>

      <!-- Buttons -->
      <div class="flex justify-end mt-6 space-x-3">
          <button type="button" onclick="closeModal('generate-report-product')" class="text-xs px-3 py-2 text-white bg-gray-400 rounded-md transition duration-300 hover:bg-gray-500 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Cancel
          </button>
          <button type="submit" onclick="closeModal('generate-report-product')" class="text-xs px-3 py-2 text-white bg-yellow-500 rounded-md transition duration-300 hover:bg-yellow-700 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Generate Report
          </button>
      </div>
    </form>
  </div>
</div>
