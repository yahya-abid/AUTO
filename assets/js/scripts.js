// Main JavaScript file for Car Rental System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize modals
    initModals();
    
    // Initialize forms
    initForms();
    
    // Initialize date pickers
    initDatePickers();
    
    // Initialize charts
    initCharts();
    
    // Initialize sidebar toggle
    initSidebarToggle();
});

// Tooltip functionality
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.textContent = tooltipText;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            
            this.dataset.tooltipId = tooltip.id = 'tooltip-' + Date.now();
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltipId = this.dataset.tooltipId;
            if (tooltipId) {
                const tooltip = document.getElementById(tooltipId);
                if (tooltip) {
                    tooltip.remove();
                }
            }
        });
    });
}

// Modal functionality
function initModals() {
    // Open modal buttons
    const openModalButtons = document.querySelectorAll('[data-modal-target]');
    const closeModalButtons = document.querySelectorAll('[data-close-modal]');
    const modals = document.querySelectorAll('.modal');
    
    openModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Escape key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
}

// Form validation
function initForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Add real-time validation
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name || field.id;
    
    clearFieldError(field);
    
    if (field.required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Phone validation
    if (field.type === 'tel' && value) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
    // Password validation
    if (field.type === 'password' && value && field.hasAttribute('data-min-length')) {
        const minLength = parseInt(field.getAttribute('data-min-length'));
        if (value.length < minLength) {
            showFieldError(field, `Password must be at least ${minLength} characters`);
            return false;
        }
    }
    
    // Date validation
    if (field.type === 'date' && value) {
        const selectedDate = new Date(value);
        const today = new Date();
        
        if (field.hasAttribute('data-min-date-today')) {
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                showFieldError(field, 'Date cannot be in the past');
                return false;
            }
        }
        
        if (field.hasAttribute('data-min-date')) {
            const minDate = new Date(field.getAttribute('data-min-date'));
            if (selectedDate < minDate) {
                showFieldError(field, `Date must be after ${minDate.toLocaleDateString()}`);
                return false;
            }
        }
    }
    
    return true;
}

function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    let errorElement = field.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains('error-message')) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }
    
    errorElement.textContent = message;
    errorElement.style.color = '#e74c3c';
    errorElement.style.fontSize = '0.85rem';
    errorElement.style.marginTop = '0.25rem';
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.classList.contains('error-message')) {
        errorElement.remove();
    }
}

// Date picker enhancement
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Set minimum date to today if not set
        if (!input.min && input.hasAttribute('data-min-date-today')) {
            const today = new Date().toISOString().split('T')[0];
            input.min = today;
        }
        
        // Update related date inputs
        if (input.hasAttribute('data-related-date')) {
            const relatedId = input.getAttribute('data-related-date');
            const relatedInput = document.getElementById(relatedId);
            
            if (relatedInput) {
                input.addEventListener('change', function() {
                    const minDate = new Date(this.value);
                    minDate.setDate(minDate.getDate() + 1);
                    relatedInput.min = minDate.toISOString().split('T')[0];
                    
                    if (relatedInput.value && new Date(relatedInput.value) < minDate) {
                        relatedInput.value = '';
                    }
                });
            }
        }
    });
}

// Chart initialization
function initCharts() {
    const chartContainers = document.querySelectorAll('[data-chart]');
    
    chartContainers.forEach(container => {
        const chartType = container.getAttribute('data-chart');
        const chartData = JSON.parse(container.getAttribute('data-chart-data') || '{}');
        
        switch(chartType) {
            case 'revenue':
                renderRevenueChart(container, chartData);
                break;
            case 'utilization':
                renderUtilizationChart(container, chartData);
                break;
            case 'bookings':
                renderBookingsChart(container, chartData);
                break;
        }
    });
}

function renderRevenueChart(container, data) {
    // Simple bar chart using CSS
    const chartHtml = `
        <div class="chart-bars">
            ${data.labels.map((label, index) => `
                <div class="chart-bar">
                    <div class="bar-label">${label}</div>
                    <div class="bar-container">
                        <div class="bar" style="height: ${data.values[index]}%"></div>
                    </div>
                    <div class="bar-value">$${data.values[index]}</div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = chartHtml;
}

// Sidebar toggle for admin
function initSidebarToggle() {
    const toggleBtn = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.admin-sidebar');
    const mainContent = document.querySelector('.admin-main');
    
    if (toggleBtn && sidebar && mainContent) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Save state in localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
        
        // Load saved state
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    }
}

// AJAX helper functions
function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        if (method === 'POST' && data) {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error(xhr.statusText));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        xhr.send(data);
    });
}

// Table sorting
function initTableSorting() {
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const columnIndex = Array.from(this.parentNode.children).indexOf(this);
            const sortKey = this.getAttribute('data-sort');
            const isAscending = this.classList.contains('sort-asc');
            
            // Reset other headers
            sortableHeaders.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Toggle sort direction
            this.classList.toggle('sort-asc', !isAscending);
            this.classList.toggle('sort-desc', isAscending);
            
            // Sort table rows
            sortTable(table, columnIndex, !isAscending);
        });
    });
}

function sortTable(table, columnIndex, ascending = true) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();
        
        // Try to compare as numbers
        const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return ascending ? aNum - bNum : bNum - aNum;
        }
        
        // Compare as strings
        return ascending 
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
    });
    
    // Reappend rows in sorted order
    rows.forEach(row => tbody.appendChild(row));
}

// Export functionality
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    rows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('th, td');
        
        cells.forEach(cell => {
            // Skip action buttons
            if (!cell.closest('.actions')) {
                rowData.push(`"${cell.textContent.trim().replace(/"/g, '""')}"`);
            }
        });
        
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = filename || 'export.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Print functionality
function printPage(selector = null) {
    const originalContent = document.body.innerHTML;
    let printContent;
    
    if (selector) {
        const element = document.querySelector(selector);
        printContent = element ? element.innerHTML : originalContent;
    } else {
        printContent = originalContent;
    }
    
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload(); // Reload to restore original content
}

// Notification system
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Add styles if not present
    if (!document.querySelector('#notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem;
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 9999;
                min-width: 300px;
                max-width: 400px;
                animation: slideIn 0.3s ease;
            }
            .notification-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .notification-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: #666;
            }
            .notification-info { border-left: 4px solid #3498db; }
            .notification-success { border-left: 4px solid #2ecc71; }
            .notification-warning { border-left: 4px solid #f39c12; }
            .notification-error { border-left: 4px solid #e74c3c; }
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    // Close button
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });
    
    // Auto remove
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }
    
    return notification;
}

// File upload preview
function initFileUploadPreview() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        const previewId = input.getAttribute('data-preview');
        const previewContainer = document.getElementById(previewId);
        
        if (previewContainer) {
            input.addEventListener('change', function(e) {
                const file = this.files[0];
                if (!file) return;
                
                if (!file.type.match('image.*')) {
                    showNotification('Please select an image file', 'error');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" 
                             style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                    `;
                };
                reader.readAsDataURL(file);
            });
        }
    });
}

// Initialize everything when DOM is loaded
window.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    initModals();
    initForms();
    initDatePickers();
    initCharts();
    initSidebarToggle();
    initTableSorting();
    initFileUploadPreview();
});