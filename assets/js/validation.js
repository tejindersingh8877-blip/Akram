/**
 * Form Validation - Client Side
 * ProFix Masters - Service Marketplace Platform
 */

/**
 * Validate email format
 * @param {string} email
 * @returns {boolean}
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate phone number (10-15 digits)
 * @param {string} phone
 * @returns {boolean}
 */
function validatePhone(phone) {
    const regex = /^[0-9]{10,15}$/;
    return regex.test(phone.replace(/[\s\-\(\)]/g, ''));
}

/**
 * Validate password strength
 * Minimum 8 characters, at least one uppercase, one lowercase, one number
 * @param {string} password
 * @returns {object}
 */
function validatePassword(password) {
    const result = {
        valid: true,
        messages: []
    };
    
    if (password.length < 8) {
        result.valid = false;
        result.messages.push('Password must be at least 8 characters long');
    }
    
    if (!/[A-Z]/.test(password)) {
        result.valid = false;
        result.messages.push('Password must contain at least one uppercase letter');
    }
    
    if (!/[a-z]/.test(password)) {
        result.valid = false;
        result.messages.push('Password must contain at least one lowercase letter');
    }
    
    if (!/[0-9]/.test(password)) {
        result.valid = false;
        result.messages.push('Password must contain at least one number');
    }
    
    return result;
}

/**
 * Show field error
 * @param {HTMLElement} field
 * @param {string} message
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    // Remove existing error message
    const existingError = field.parentElement.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    field.parentElement.appendChild(errorDiv);
}

/**
 * Clear field error
 * @param {HTMLElement} field
 */
function clearFieldError(field) {
    field.classList.remove('error');
    const errorDiv = field.parentElement.querySelector('.form-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Validate required field
 * @param {HTMLElement} field
 * @param {string} fieldName
 * @returns {boolean}
 */
function validateRequired(field, fieldName) {
    const value = field.value.trim();
    if (value === '') {
        showFieldError(field, `${fieldName} is required`);
        return false;
    }
    clearFieldError(field);
    return true;
}

/**
 * Validate email field
 * @param {HTMLElement} field
 * @returns {boolean}
 */
function validateEmailField(field) {
    if (!validateRequired(field, 'Email')) return false;
    
    if (!validateEmail(field.value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Validate phone field
 * @param {HTMLElement} field
 * @returns {boolean}
 */
function validatePhoneField(field) {
    if (!validateRequired(field, 'Phone number')) return false;
    
    if (!validatePhone(field.value)) {
        showFieldError(field, 'Please enter a valid phone number (10-15 digits)');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Validate password field
 * @param {HTMLElement} field
 * @returns {boolean}
 */
function validatePasswordField(field) {
    if (!validateRequired(field, 'Password')) return false;
    
    const result = validatePassword(field.value);
    if (!result.valid) {
        showFieldError(field, result.messages[0]);
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Validate confirm password
 * @param {HTMLElement} passwordField
 * @param {HTMLElement} confirmField
 * @returns {boolean}
 */
function validateConfirmPassword(passwordField, confirmField) {
    if (!validateRequired(confirmField, 'Confirm Password')) return false;
    
    if (passwordField.value !== confirmField.value) {
        showFieldError(confirmField, 'Passwords do not match');
        return false;
    }
    
    clearFieldError(confirmField);
    return true;
}

/**
 * Validate file upload
 * @param {HTMLElement} field
 * @param {Array} allowedTypes - e.g., ['image/jpeg', 'image/png']
 * @param {number} maxSize - in bytes
 * @returns {boolean}
 */
function validateFileUpload(field, allowedTypes = [], maxSize = 5242880) {
    const files = field.files;
    
    if (files.length === 0) {
        showFieldError(field, 'Please select a file');
        return false;
    }
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        
        // Check file size
        if (file.size > maxSize) {
            showFieldError(field, `File size must be less than ${maxSize / 1048576}MB`);
            return false;
        }
        
        // Check file type
        if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
            showFieldError(field, 'Invalid file type');
            return false;
        }
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Real-time validation setup
 */
document.addEventListener('DOMContentLoaded', function() {
    // Email fields
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value) validateEmailField(this);
        });
    });
    
    // Phone fields
    const phoneFields = document.querySelectorAll('input[name="phone"]');
    phoneFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value) validatePhoneField(this);
        });
    });
    
    // Password fields with strength indicator
    const passwordFields = document.querySelectorAll('input[type="password"][name="password"]');
    passwordFields.forEach(field => {
        // Add strength indicator
        const strengthDiv = document.createElement('div');
        strengthDiv.className = 'password-strength';
        strengthDiv.style.marginTop = '0.5rem';
        field.parentElement.appendChild(strengthDiv);
        
        field.addEventListener('input', function() {
            const result = validatePassword(this.value);
            let strength = 'weak';
            let color = '#ef4444';
            
            if (result.valid) {
                strength = 'strong';
                color = '#10b981';
            } else if (this.value.length >= 6) {
                strength = 'medium';
                color = '#f59e0b';
            }
            
            strengthDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden;">
                        <div style="width: ${result.valid ? '100' : this.value.length >= 6 ? '66' : '33'}%; height: 100%; background: ${color}; transition: all 0.3s;"></div>
                    </div>
                    <span style="font-size: 0.875rem; color: ${color}; font-weight: 500;">${strength}</span>
                </div>
            `;
        });
        
        field.addEventListener('blur', function() {
            if (this.value) validatePasswordField(this);
        });
    });
    
    // Confirm password fields
    const confirmPasswordFields = document.querySelectorAll('input[name="confirm_password"]');
    confirmPasswordFields.forEach(field => {
        field.addEventListener('blur', function() {
            const passwordField = document.querySelector('input[name="password"]');
            if (this.value && passwordField) {
                validateConfirmPassword(passwordField, this);
            }
        });
    });
    
    // File upload fields
    const fileFields = document.querySelectorAll('input[type="file"]');
    fileFields.forEach(field => {
        field.addEventListener('change', function() {
            clearFieldError(this);
        });
    });
});

/**
 * Form submission validation
 * Add this to forms: onsubmit="return validateForm(this)"
 */
function validateForm(form) {
    let isValid = true;
    
    // Required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        const fieldName = field.getAttribute('data-name') || field.name || 'This field';
        if (!validateRequired(field, fieldName)) {
            isValid = false;
        }
    });
    
    // Email fields
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !validateEmailField(field)) {
            isValid = false;
        }
    });
    
    // Phone fields
    const phoneFields = form.querySelectorAll('input[name="phone"]');
    phoneFields.forEach(field => {
        if (field.value && !validatePhoneField(field)) {
            isValid = false;
        }
    });
    
    // Password fields
    const passwordFields = form.querySelectorAll('input[type="password"][name="password"]');
    passwordFields.forEach(field => {
        if (field.value && !validatePasswordField(field)) {
            isValid = false;
        }
    });
    
    // Confirm password
    const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
    if (confirmPasswordField && confirmPasswordField.value) {
        const passwordField = form.querySelector('input[name="password"]');
        if (passwordField && !validateConfirmPassword(passwordField, confirmPasswordField)) {
            isValid = false;
        }
    }
    
    // Scroll to first error
    if (!isValid) {
        const firstError = form.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
    
    return isValid;
}

/**
 * Price validation
 */
function validatePrice(field) {
    const value = parseFloat(field.value);
    
    if (isNaN(value) || value <= 0) {
        showFieldError(field, 'Please enter a valid price greater than 0');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Number validation
 */
function validateNumber(field, min = 0, max = null) {
    const value = parseInt(field.value);
    
    if (isNaN(value)) {
        showFieldError(field, 'Please enter a valid number');
        return false;
    }
    
    if (value < min) {
        showFieldError(field, `Value must be at least ${min}`);
        return false;
    }
    
    if (max !== null && value > max) {
        showFieldError(field, `Value must not exceed ${max}`);
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Date validation (not in past)
 */
function validateFutureDate(field) {
    const selectedDate = new Date(field.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        showFieldError(field, 'Please select a future date');
        return false;
    }
    
    clearFieldError(field);
    return true;
}
