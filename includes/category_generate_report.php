<!-- Generate Category Report Modal -->
<div id="generate-report-category-modal" class="fixed top-0 left-0 z-50 flex items-center justify-center hidden w-full h-full transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm">
  <div class="relative w-full max-w-2xl px-8 py-6 mx-4 transition-all transform bg-white shadow-2xl rounded-xl">
    <div class="absolute top-4 right-4">
      <button onclick="closeModal('generate-report-category')" class="text-gray-400 transition-colors hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <h2 class="mb-4 text-lg font-bold">Generate Category Report</h2>
    <form id="generate-report-category" method="POST" action="">
      <input type="hidden" name="action" value="generate_category_report">

      <!-- Grid Layout for Form Elements -->
      <div class="grid grid-cols-2 gap-4">
        <!-- Category Name -->
        <div>
          <label for="category-name" class="block mb-2 text-sm font-medium text-gray-600">Category Name</label>
          <input type="text" id="category-name" name="category_name" placeholder="Enter category name (optional)" 
                class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
        </div>

        <!-- Minimum Number of Products -->
        <div>
          <label for="min-products" class="block mb-2 text-sm font-medium text-gray-600">Minimum Number of Products</label>
          <input type="number" id="min-products" name="min_products" placeholder="Minimum product count (optional)" 
                class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
        </div>

        <!-- Report Format -->
        <div class="col-span-2">
          <label for="export-type" class="block mb-2 text-sm font-medium text-gray-600">Report Format</label>
          <select id="export-type" name="export_type" 
                class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
            <option value="pdf">PDF</option>
            <option value="csv">CSV</option>
          </select>
        </div>
      </div>

      <!-- Buttons -->
      <div class="flex justify-end mt-6 space-x-3">
          <button type="button" onclick="closeModal('generate-report-category')" 
                  class="text-xs px-3 py-2 text-white bg-gray-400 rounded-md transition duration-300 hover:bg-gray-500 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Cancel
          </button>
          <button type="submit" class="text-xs px-3 py-2 text-white bg-yellow-500 rounded-md transition duration-300 hover:bg-yellow-700 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Generate Report
          </button>
      </div>
    </form>
  </div>
</div>
