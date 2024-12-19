<!-- Add Product Modal -->
<div id="add-product-modal" class="fixed top-0 left-0 z-50 flex items-center justify-center hidden w-full h-full transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm">

  <div class="relative w-full max-w-2xl px-8 py-6 mx-4 transition-all transform bg-white shadow-2xl rounded-xl">
    <div class="absolute top-4 right-4">
      <button onclick="closeModal('add-product')" class="text-gray-400 transition-colors hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <h2 class="mb-4 text-lg font-bold">Add New Product</h2>
    <form id="add-product" method="POST" action="">
      <input type="hidden" name="action" value="add_product">
      <div class="mb-4">
          <label for="product-name" class="block mb-1 text-sm font-medium text-gray-600">Product Name</label>
          <input type="text" id="product-name" name="product_name" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter product name" />
      </div>
      <div class="mb-4">
          <label for="product-price" class="block mb-1 text-sm font-medium text-gray-600">Price</label>
          <input type="number" id="product-price" name="product_price" step="0.01" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter product price" />
      </div>
      <div class="mb-4">
          <label for="quantity-stocks" class="block mb-1 text-sm font-medium text-gray-600">Quantity Stocks</label>
          <input type="number" id="quantity-stocks" name="quantity_stocks" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter number of stock" />
      </div>
      <div class="mb-4">
          <label for="product_description" class="block mb-1 text-sm font-medium text-gray-600">Description</label>
          <input type="text" id="product_description" name="product_description" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter product description" />
      </div>
      <div class="mb-4">
          <label for="product_category" class="block mb-1 text-sm font-medium text-gray-600">Category</label>
          <div class="flex items-center gap-2">
              <select id="product_category" name="product_category" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
                  <option value="" disabled selected>Select a Category</option>
                  <?php foreach($categories as $category): ?>
                      <option value="<?php echo $category['category_ID']; ?>">
                          <?php echo htmlspecialchars($category['name']); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
              <button type="button" onclick="openModal('add-category')" class="p-2 text-white transition duration-300 bg-green-500 rounded-md hover:bg-green-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </button>
          </div>
      </div>

      <!-- Supplier -->
      <div class="mb-4">
          <label for="product_supplier" class="block mb-1 text-sm font-medium text-gray-600">Supplier</label>
          <div class="flex items-center gap-2">
              <select id="product_supplier" name="product_supplier" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
                  <option value="" disabled selected>Select a Supplier</option>
                  <?php foreach($suppliers as $supplier): ?>
                      <option value="<?php echo $supplier['supplier_id']; ?>">
                          <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
              <button type="button" onclick="openModal('add-supplier')" class="p-2 text-white transition duration-300 bg-green-500 rounded-md hover:bg-green-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </button>
          </div>
      </div>

      <!-- Buttons -->
      <div class="flex justify-end space-x-3">
          <button type="button" onclick="closeModal('add-product')" class="text-xs px-3 py-2 text-white bg-gray-400 rounded-md transition duration-300 hover:bg-gray-500 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Cancel
          </button>
          <button type="submit" class="text-xs px-3 py-2 text-white bg-green-500 rounded-md transition duration-300 hover:bg-green-700 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Add
          </button>
      </div>
    </form>
  </div>
</div>

<!-- Add Category Modal -->
<div id="add-category-modal" class="fixed top-0 left-0 z-50 flex items-center justify-center hidden w-full h-full transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm">
  <div class="relative w-full max-w-md px-8 py-6 mx-4 transition-all transform bg-white rounded-lg shadow-2xl">
    <div class="absolute top-4 right-4">
      <button onclick="closeModal('add-category')" class="text-gray-400 transition-colors hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <h2 class="mb-4 font-bold">Add New Category</h2>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_category">
      <div class="mb-4">
          <label for="category-name" class="block mb-1 text-sm font-medium text-gray-600">Category Name</label>
          <input type="text" id="category-name" name="category_name" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter category name" />
      </div>
      <div class="flex justify-end space-x-3">
          <button type="button" onclick="closeModal('add-category')" class="text-xs px-3 py-2 text-white bg-gray-400 rounded-md transition duration-300 hover:bg-gray-500 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Cancel
          </button>
          <button type="submit" class="text-xs px-3 py-2 text-white bg-green-500 rounded-md transition duration-300 hover:bg-green-700 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Add
          </button>
      </div>
    </form>
  </div>
</div>

<!-- Add Supplier Modal -->
<div id="add-supplier-modal" class="fixed top-0 left-0 z-50 flex items-center justify-center hidden w-full h-full transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm">
  <div class="relative w-full max-w-md px-8 py-6 mx-4 transition-all transform bg-white rounded-lg shadow-2xl">
    <div class="absolute top-4 right-4">
      <button onclick="closeModal('add-supplier')" class="text-gray-400 transition-colors hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <h2 class="mb-4 font-bold">Add New Supplier</h2>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_supplier">
      <div class="mb-4">
          <label for="supplier-name" class="block mb-1 text-sm font-medium text-gray-600">Supplier Name</label>
          <input type="text" id="supplier-name" name="supplier_name" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter supplier name" />
      </div>
      <div class="mb-4">
          <label for="contact-num" class="block mb-1 text-sm font-medium text-gray-600">Contact Number</label>
          <input type="text" id="contact-num" name="contact_num" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter contact number" />
      </div>
      <div class="mb-4">
          <label for="address" class="block mb-1 text-sm font-medium text-gray-600">Address</label>
          <input type="text" id="address" name="address" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter supplier address" />
      </div>
      <div class="flex justify-end space-x-3">
          <button type="button" onclick="closeModal('add-supplier')" class="text-xs px-3 py-2 text-white bg-gray-400 rounded-md transition duration-300 hover:bg-gray-500 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Cancel
          </button>
          <button type="submit" class="text-xs px-3 py-2 text-white bg-green-500 rounded-md transition duration-300 hover:bg-green-700 shadow-[0_5px_5px_rgba(0,0,0,0.15)]">
              Add
          </button>
      </div>
    </form>
  </div>
</div>