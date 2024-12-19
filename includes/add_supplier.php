<!-- Add Supplier Modal -->
<div id="add-supplier-modal" class="fixed top-0 left-0 z-50 flex items-center justify-center hidden w-full h-full transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm">

  <div class="relative w-full max-w-2xl px-8 py-6 mx-4 transition-all transform bg-white shadow-2xl rounded-xl">
    <div class="absolute top-4 right-4">
      <button onclick="closeModal('add-supplier')" class="text-gray-400 transition-colors hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <h2 class="mb-4 text-lg font-bold">Add New Supplier</h2>
    <form id="add-supplier" method="POST" action="">
      <input type="hidden" name="action" value="add_supplier">
      
      <!-- Supplier Name -->
      <div class="mb-4">
        <label for="supplier-name" class="block mb-1 text-sm font-medium text-gray-600">Supplier Name</label>
        <input type="text" id="supplier-name" name="supplier_name" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter supplier name" />
      </div>

      <!-- Contact Number -->
      <div class="mb-4">
        <label for="contact-num" class="block mb-1 text-sm font-medium text-gray-600">Contact Number</label>
        <input type="text" id="contact-num" name="contact_num" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter contact number" />
      </div>

      <!-- Address -->
      <div class="mb-4">
        <label for="address" class="block mb-1 text-sm font-medium text-gray-600">Address</label>
        <input type="text" id="address" name="address" required class="w-full p-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Enter supplier address" />
      </div>

      <!-- Buttons -->
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
