/**
 * Main JavaScript File
 * ProFix Masters - Service Marketplace Platform
 */

// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const navbarToggler = document.getElementById('navbarToggler');
    const navbarMenu = document.getElementById('navbarMenu');
    
    if (navbarToggler && navbarMenu) {
        navbarToggler.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInside = navbarMenu.contains(event.target) || navbarToggler.contains(event.target);
            if (!isClickInside && navbarMenu.classList.contains('active')) {
                navbarMenu.classList.remove('active');
            }
        });
    }
});

/**
 * Confirm Dialog
 * @param {string} message - Confirmation message
 * @param {function} callback - Callback function if confirmed
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Show loading spinner
 */
function showLoading(element) {
    if (element) {
        element.innerHTML = '<div class="spinner"></div>';
        element.disabled = true;
    }
}

/**
 * Hide loading spinner
 */
function hideLoading(element, originalText) {
    if (element) {
        element.innerHTML = originalText;
        element.disabled = false;
    }
}

/**
 * Format currency
 * @param {number} amount
 * @returns {string}
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

/**
 * Format date
 * @param {string} dateString
 * @returns {string}
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Debounce function for search inputs
 * @param {function} func
 * @param {number} wait
 * @returns {function}
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Show toast notification
 * @param {string} message
 * @param {string} type - success, error, warning, info
 */
function showToast(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible`;
    alert.style.position = 'fixed';
    alert.style.top = '80px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.style.minWidth = '300px';
    alert.style.animation = 'slideIn 0.3s ease';
    
    alert.innerHTML = `
        ${message}
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

/**
 * Image Preview
 * @param {HTMLInputElement} input - File input element
 * @param {HTMLElement} previewContainer - Container to show preview
 */
function previewImages(input, previewContainer) {
    const files = input.files;
    previewContainer.innerHTML = '';
    
    if (files.length === 0) return;
    
    Array.from(files).forEach((file, index) => {
        if (!file.type.startsWith('image/')) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}">
                <button type="button" class="image-preview-remove" onclick="removePreviewImage(this, ${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

/**
 * Remove preview image
 * @param {HTMLElement} button
 * @param {number} index
 */
function removePreviewImage(button, index) {
    button.closest('.image-preview').remove();
}

/**
 * Filter table rows
 * @param {HTMLInputElement} input - Search input
 * @param {HTMLTableElement} table - Table element
 */
function filterTable(input, table) {
    const filter = input.value.toUpperCase();
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
                const textValue = cell.textContent || cell.innerText;
                if (textValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

/**
 * Initialize search functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('.table-search input');
    searchInputs.forEach(input => {
        const table = input.closest('.table-container').querySelector('.data-table');
        if (table) {
            input.addEventListener('keyup', debounce(function() {
                filterTable(input, table);
            }, 300));
        }
    });
});

/**
 * Modal management
 */
class Modal {
    constructor(modalId) {
        this.modal = document.getElementById(modalId);
        if (this.modal) {
            this.init();
        }
    }
    
    init() {
        const closeBtn = this.modal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }
        
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
    }
    
    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
}

/**
 * Auto-dismiss alerts after 5 seconds
 */
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

/**
 * Set minimum date for date inputs (today)
 */
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(input => {
        if (!input.min) {
            input.min = today;
        }
    });
});

/**
 * Character counter for textareas
 */
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        counter.style.fontSize = '0.875rem';
        counter.style.color = 'var(--text-light)';
        counter.style.marginTop = '0.25rem';
        counter.style.textAlign = 'right';
        
        const updateCounter = () => {
            const remaining = textarea.maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
        };
        
        updateCounter();
        textarea.addEventListener('input', updateCounter);
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
    });
});
