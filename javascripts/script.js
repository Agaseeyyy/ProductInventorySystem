console.log("Script.js goo brrrr!");
function closeModal(modalName) {
  const modal = document.getElementById(modalName + "-modal");
  modal.classList.add("hidden");
}

function openModal(modalName) {
  const modal = document.getElementById(modalName + "-modal");
  modal.classList.remove("hidden");
}


document.addEventListener('click', function(event) {
  const modalPrefixes = ['add', 'edit', 'generate-report'];
  const modalTypes = ['supplier', 'category', 'product'];

  modalTypes.forEach(type => {
    modalPrefixes.forEach(prefix => {
      const modalId = `${prefix}-${type}-modal`;
      const modal = document.getElementById(modalId);
      
      if (modal && event.target === modal) {
        modal.classList.add('hidden');
      }
    });
  });
});

// Function to compare values for sorting
function compareValues(a, b, isAsc = true) {
  // Remove currency symbols and commas for price columns
  if (typeof a === 'string' && a.includes('₱')) {
      a = parseFloat(a.replace('₱', '').replace(/,/g, '').trim());
      b = parseFloat(b.replace('₱', '').replace(/,/g, '').trim());
  }
  
  // Handle numeric strings
  if (!isNaN(a) && !isNaN(b)) {
      a = parseFloat(a);
      b = parseFloat(b);
  }
  
  // Compare dates
  if (isValidDate(a) && isValidDate(b)) {
      a = new Date(a);
      b = new Date(b);
  }

  if (a < b) return isAsc ? -1 : 1;
  if (a > b) return isAsc ? 1 : -1;
  return 0;
}

// Helper function to check if a string is a valid date
function isValidDate(dateStr) {
  const date = new Date(dateStr);
  return date instanceof Date && !isNaN(date);
}

// Function to get the current sort state from the icon
function getSortState(icon) {
  if (icon.getAttribute('data-sort-state') === 'asc') return 'desc';
  if (icon.getAttribute('data-sort-state') === 'desc') return 'none';
  return 'asc';
}

// Function to update sort icon appearance
function updateSortIcon(icon, state) {
  // Reset icon to default state
  icon.innerHTML = '<path d="M8 9l4-4 4 4M16 15l-4 4-4-4" />';
  
  // Update icon based on state
  switch(state) {
      case 'asc':
          icon.innerHTML = '<path d="M8 9l4-4 4 4" />';
          break;
      case 'desc':
          icon.innerHTML = '<path d="M16 15l-4 4-4-4" />';
          break;
  }
  
  icon.setAttribute('data-sort-state', state);
}

// Main function to handle sorting
function toggleSortIcon(button) {
  const table = button.closest('table');
  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));
  const columnIndex = button.closest('th').cellIndex;
  const icon = button.querySelector('.icon');
  
  // Reset all other sort icons in the table
  table.querySelectorAll('th .icon').forEach(otherIcon => {
      if (otherIcon !== icon) {
          otherIcon.removeAttribute('data-sort-state');
          otherIcon.innerHTML = '<path d="M8 9l4-4 4 4M16 15l-4 4-4-4" />';
      }
  });
  
  // Get and update sort state
  const sortState = getSortState(icon);
  updateSortIcon(icon, sortState);
  
  // Skip sorting if state is 'none'
  if (sortState === 'none') {
      // Reload the page to reset to default order
      window.location.reload();
      return;
  }
  
  // Sort the rows
  rows.sort((rowA, rowB) => {
      const cellA = rowA.cells[columnIndex].textContent.trim();
      const cellB = rowB.cells[columnIndex].textContent.trim();
      return compareValues(cellA, cellB, sortState === 'asc');
  });
  
  // Clear and re-append sorted rows
  rows.forEach(row => tbody.appendChild(row));
  
  // Handle no-results-row if it exists
  const noResultsRow = tbody.querySelector('#no-results-row');
  if (noResultsRow) {
      tbody.appendChild(noResultsRow);
  }
}

// Search function
function initializeTableSearch(searchInputId, tableId, config = {}) {
  const searchInput = document.getElementById(searchInputId);
  const table = document.getElementById(tableId);
  
  if (!searchInput || !table) return;
  
  const defaultConfig = {
      noResultsMessage: 'No items found matching your search.',
      searchDelay: 200,  // Debounce delay in milliseconds
      caseSensitive: false,
      excludeColumns: [] // Array of column indices to exclude from search
  };
  
  // Merge default config with provided config
  const finalConfig = { ...defaultConfig, ...config };
  let searchTimeout;
  
  searchInput.addEventListener('input', function() {
      // Clear existing timeout
      if (searchTimeout) {
          clearTimeout(searchTimeout);
      }
      
      // Set new timeout for debouncing
      searchTimeout = setTimeout(() => {
          const searchTerm = finalConfig.caseSensitive ? 
              this.value.trim() : 
              this.value.toLowerCase().trim();
          
          const tableRows = table.querySelectorAll('tbody tr');
          let hasVisibleRows = false;
          
          tableRows.forEach(row => {
              if (row.id === 'no-results-row') return;
              
              let rowText = Array.from(row.cells)
                  .filter((cell, index) => !finalConfig.excludeColumns.includes(index))
                  .map(cell => {
                      const text = cell.textContent || cell.innerText;
                      return finalConfig.caseSensitive ? text : text.toLowerCase();
                  })
                  .join(' ');
              
              const isVisible = rowText.includes(searchTerm);
              row.style.display = isVisible ? '' : 'none';
              
              if (isVisible) {
                  hasVisibleRows = true;
              }
          });
          
          // Handle no results scenario
          const noResultsRow = table.querySelector('#no-results-row');
          if (!hasVisibleRows) {
              if (!noResultsRow) {
                  const tbody = table.querySelector('tbody');
                  const numColumns = table.querySelector('thead tr').cells.length;
                  
                  const noResultsRow = document.createElement('tr');
                  noResultsRow.id = 'no-results-row';
                  
                  const noResultsCell = document.createElement('td');
                  noResultsCell.setAttribute('colspan', numColumns);
                  noResultsCell.classList.add('py-4', 'text-center');
                  noResultsCell.textContent = finalConfig.noResultsMessage;
                  
                  noResultsRow.appendChild(noResultsCell);
                  tbody.appendChild(noResultsRow);
              }
          } else if (noResultsRow) {
              noResultsRow.remove();
          }
      }, finalConfig.searchDelay);
  });
}

// Initialize search for each table when document is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Configure and initialize suppliers table search
  if (document.getElementById('suppliers-table')) {
      initializeTableSearch('supplier-search-input', 'suppliers-table', {
          noResultsMessage: 'No suppliers found matching your search.',
          excludeColumns: [4] // Exclude Actions column
      });
  }
  
  // Configure and initialize categories table search
  if (document.getElementById('categories-table')) {
      initializeTableSearch('category-search-input', 'categories-table', {
          noResultsMessage: 'No categories found matching your search.',
          excludeColumns: [2] // Exclude Actions column
      });
  }
  
  // Configure and initialize products table search
  if (document.getElementById('products-table')) {
      initializeTableSearch('product-search-input', 'products-table', {
          noResultsMessage: 'No products found matching your search.',
          excludeColumns: [7] // Exclude Actions column
      });
  }
});


// Entries
document.addEventListener('DOMContentLoaded', function() {
    // Function to update URL parameters
    function updateURLParameter(url, param, value) {
        const regex = new RegExp(`([?&])${param}=.*?(&|$)`, 'i');
        const separator = url.indexOf('?') !== -1 ? '&' : '?';
        
        if (url.match(regex)) {
            return url.replace(regex, `$1${param}=${value}$2`);
        }
        
        return `${url}${separator}${param}=${value}`;
    }

    // Function to handle showing entries
    function handleShowEntries(tableId, selectId) {
        const select = document.getElementById(selectId);
        
        if (!select) return;

        // Set initial value from URL if exists or default to 10
        const urlParams = new URLSearchParams(window.location.search);
        const entriesParam = urlParams.get('entries');
        if (entriesParam) {
            select.value = entriesParam;
        } else {
            select.value = '10'; // Default to 10 if no entries param in URL
        }

        select.addEventListener('change', function() {
            const entriesPerPage = this.value;
            let newUrl;

            if (entriesPerPage === 'all') {
                newUrl = updateURLParameter(window.location.href, 'entries', 'all');
            } else {
                newUrl = updateURLParameter(window.location.href, 'entries', entriesPerPage);
            }
            
            // Reset to page 1 when changing entries per page
            newUrl = updateURLParameter(newUrl, 'page', 1);
            window.location.href = newUrl;
        });
    }

    // Initialize table
    handleShowEntries('products-table', 'entries-products');
    handleShowEntries('categories-table', 'entries-categories');
    handleShowEntries('suppliers-table', 'entries-suppliers');
});



