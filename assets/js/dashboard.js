/**
 * Dashboard JavaScript
 * ProFix Masters - Service Marketplace Platform
 */

// Dashboard Sidebar Toggle (Mobile)
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.dashboard-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 768) {
                const isClickInside = sidebar.contains(event.target) || sidebarToggle.contains(event.target);
                if (!isClickInside && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }
});

/**
 * Update booking status
 * @param {number} bookingId
 * @param {string} status
 * @param {string} endpoint
 */
function updateBookingStatus(bookingId, status, endpoint) {
    if (!confirm(`Are you sure you want to ${status} this booking?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('booking_id', bookingId);
    formData.append('status', status);
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to update booking status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

/**
 * Delete item with confirmation
 * @param {string} url
 * @param {string} itemName
 */
function deleteItem(url, itemName = 'this item') {
    if (confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
        window.location.href = url;
    }
}

/**
 * Export table data to CSV
 * @param {HTMLTableElement} table
 * @param {string} filename
 */
function exportTableToCSV(table, filename = 'export.csv') {
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Skip action columns
            if (cols[j].classList.contains('actions')) continue;
            
            let data = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    downloadCSV(csv.join('\n'), filename);
}

/**
 * Download CSV file
 * @param {string} csv
 * @param {string} filename
 */
function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

/**
 * Sort table by column
 * @param {HTMLTableElement} table
 * @param {number} column
 * @param {boolean} asc
 */
function sortTable(table, column, asc = true) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const sortedRows = rows.sort((a, b) => {
        const aColText = a.querySelector(`td:nth-child(${column + 1})`).textContent.trim();
        const bColText = b.querySelector(`td:nth-child(${column + 1})`).textContent.trim();
        
        return asc ? aColText.localeCompare(bColText) : bColText.localeCompare(aColText);
    });
    
    // Remove all rows
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    
    // Append sorted rows
    tbody.append(...sortedRows);
}

/**
 * Initialize sortable tables
 */
document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('.data-table.sortable');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        
        headers.forEach((header, index) => {
            if (!header.classList.contains('no-sort')) {
                header.style.cursor = 'pointer';
                header.innerHTML += ' <i class="fas fa-sort" style="font-size: 0.75rem; opacity: 0.5;"></i>';
                
                let asc = true;
                header.addEventListener('click', () => {
                    sortTable(table, index, asc);
                    asc = !asc;
                    
                    // Update icon
                    const icon = header.querySelector('i');
                    icon.className = asc ? 'fas fa-sort-up' : 'fas fa-sort-down';
                });
            }
        });
    });
});

/**
 * Chart data visualization (using simple bar charts)
 * @param {string} canvasId
 * @param {Array} labels
 * @param {Array} data
 * @param {string} color
 */
function createBarChart(canvasId, labels, data, color = '#2563eb') {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const maxValue = Math.max(...data);
    const padding = 40;
    const barWidth = (canvas.width - padding * 2) / data.length - 10;
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw bars
    data.forEach((value, index) => {
        const barHeight = (value / maxValue) * (canvas.height - padding * 2);
        const x = padding + index * (barWidth + 10);
        const y = canvas.height - padding - barHeight;
        
        // Draw bar
        ctx.fillStyle = color;
        ctx.fillRect(x, y, barWidth, barHeight);
        
        // Draw value on top
        ctx.fillStyle = '#111827';
        ctx.font = '12px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(value, x + barWidth / 2, y - 5);
        
        // Draw label
        ctx.save();
        ctx.translate(x + barWidth / 2, canvas.height - 10);
        ctx.rotate(-Math.PI / 4);
        ctx.textAlign = 'right';
        ctx.fillText(labels[index], 0, 0);
        ctx.restore();
    });
}

/**
 * Calculate and display commission
 */
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('booking_amount');
    const commissionPercentInput = document.getElementById('commission_percent');
    const commissionDisplay = document.getElementById('commission_display');
    const providerEarningsDisplay = document.getElementById('provider_earnings_display');
    
    if (amountInput && commissionPercentInput) {
        const calculateCommission = () => {
            const amount = parseFloat(amountInput.value) || 0;
            const percent = parseFloat(commissionPercentInput.value) || 15;
            
            const commission = (amount * percent) / 100;
            const providerEarnings = amount - commission;
            
            if (commissionDisplay) {
                commissionDisplay.textContent = '$' + commission.toFixed(2);
            }
            
            if (providerEarningsDisplay) {
                providerEarningsDisplay.textContent = '$' + providerEarnings.toFixed(2);
            }
        };
        
        amountInput.addEventListener('input', calculateCommission);
        commissionPercentInput.addEventListener('input', calculateCommission);
        
        // Calculate on page load
        calculateCommission();
    }
});

/**
 * Real-time search for dropdowns
 */
function initSearchableDropdown(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;
    
    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-dropdown';
    wrapper.style.position = 'relative';
    
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control';
    searchInput.placeholder = 'Search...';
    
    const optionsList = document.createElement('div');
    optionsList.className = 'dropdown-options';
    optionsList.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 200px;
        overflow-y: auto;
        background: white;
        border: 1px solid var(--border-color);
        border-top: none;
        border-radius: 0 0 8px 8px;
        display: none;
        z-index: 100;
    `;
    
    // Populate options
    Array.from(select.options).forEach(option => {
        if (option.value) {
            const div = document.createElement('div');
            div.textContent = option.text;
            div.style.cssText = 'padding: 0.75rem 1rem; cursor: pointer;';
            div.dataset.value = option.value;
            
            div.addEventListener('click', () => {
                select.value = option.value;
                searchInput.value = option.text;
                optionsList.style.display = 'none';
            });
            
            div.addEventListener('mouseenter', () => {
                div.style.background = 'var(--light-color)';
            });
            
            div.addEventListener('mouseleave', () => {
                div.style.background = 'white';
            });
            
            optionsList.appendChild(div);
        }
    });
    
    searchInput.addEventListener('focus', () => {
        optionsList.style.display = 'block';
    });
    
    searchInput.addEventListener('input', () => {
        const search = searchInput.value.toLowerCase();
        const options = optionsList.querySelectorAll('div');
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            option.style.display = text.includes(search) ? 'block' : 'none';
        });
    });
    
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            optionsList.style.display = 'none';
        }
    });
    
    select.style.display = 'none';
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(searchInput);
    wrapper.appendChild(optionsList);
    wrapper.appendChild(select);
}

/**
 * Auto-update dashboard stats
 * @param {number} interval - Update interval in milliseconds
 */
function autoUpdateStats(interval = 60000) {
    setInterval(() => {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update stat cards
            document.querySelectorAll('.stat-card').forEach((card, index) => {
                const newCard = doc.querySelectorAll('.stat-card')[index];
                if (newCard) {
                    card.querySelector('.stat-info h3').textContent = 
                        newCard.querySelector('.stat-info h3').textContent;
                }
            });
        })
        .catch(error => console.error('Error updating stats:', error));
    }, interval);
}

/**
 * Image upload with preview
 */
document.addEventListener('DOMContentLoaded', function() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
    
    imageInputs.forEach(input => {
        const previewContainer = document.createElement('div');
        previewContainer.className = 'image-preview-container';
        input.parentElement.appendChild(previewContainer);
        
        input.addEventListener('change', function() {
            previewImages(this, previewContainer);
        });
    });
});
