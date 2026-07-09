/**
 * SkelbyForsamlinghus - UI Component Library
 * Common interactive components and utilities
 */

'use strict';

/**
 * Modal Component
 * Handles opening and closing modals
 */
const ModalComponent = {
  open(selector) {
    const modal = document.querySelector(selector);
    if (modal) {
      modal.classList.add('show');
      document.body.style.overflow = 'hidden';
      this.trapFocus(modal);
    }
  },

  close(selector) {
    const modal = document.querySelector(selector);
    if (modal) {
      modal.classList.remove('show');
      document.body.style.overflow = '';
    }
  },

  trapFocus(modal) {
    const focusableElements = modal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    modal.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        if (e.shiftKey) {
          if (document.activeElement === firstElement) {
            lastElement.focus();
            e.preventDefault();
          }
        } else {
          if (document.activeElement === lastElement) {
            firstElement.focus();
            e.preventDefault();
          }
        }
      }
    });
  },

  init() {
    // Close modal on close button
    document.querySelectorAll('.modal-close').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        const modal = e.target.closest('.modal');
        this.close(`#${modal.id}`);
      });
    });

    // Close modal on background click
    document.querySelectorAll('.modal').forEach((modal) => {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          this.close(`#${modal.id}`);
        }
      });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const modal = document.querySelector('.modal.show');
        if (modal) {
          this.close(`#${modal.id}`);
        }
      }
    });
  }
};

/**
 * Alert Component
 * Handles dismissible alerts
 */
const AlertComponent = {
  dismiss(selector) {
    const alert = document.querySelector(selector);
    if (alert) {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 300ms ease-in-out';
      setTimeout(() => {
        alert.remove();
      }, 300);
    }
  },

  init() {
    document.querySelectorAll('.alert-close').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        const alert = e.target.closest('.alert');
        this.dismiss(`#${alert.id}`);
      });
    });
  }
};

/**
 * Form Validation Component
 * Handles client-side form validation
 */
const FormValidator = {
  rules: {
    required: (value) => {
      return value.trim() !== '';
    },
    email: (value) => {
      const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return regex.test(value);
    },
    minLength: (value, min) => {
      return value.length >= min;
    },
    maxLength: (value, max) => {
      return value.length <= max;
    },
    pattern: (value, pattern) => {
      const regex = new RegExp(pattern);
      return regex.test(value);
    },
    phone: (value) => {
      const regex = /^[\d\s\-\+\(\)]+$/;
      return value === '' || regex.test(value);
    }
  },

  validate(form) {
    const fields = form.querySelectorAll('[data-validate]');
    let isValid = true;

    fields.forEach((field) => {
      const rules = field.dataset.validate.split('|');
      let fieldValid = true;
      let errorMessage = '';

      for (const rule of rules) {
        let ruleName = rule;
        let ruleValue = null;

        if (rule.includes(':')) {
          [ruleName, ruleValue] = rule.split(':');
        }

        const validator = this.rules[ruleName];
        if (validator) {
          const isFieldValid = ruleValue
            ? validator(field.value, ruleValue)
            : validator(field.value);

          if (!isFieldValid) {
            fieldValid = false;
            errorMessage = field.dataset[`error${ruleName}`] || `Field failed ${ruleName} validation`;
            break;
          }
        }
      }

      if (!fieldValid) {
        this.showError(field, errorMessage);
        isValid = false;
      } else {
        this.clearError(field);
      }
    });

    return isValid;
  },

  showError(field, message) {
    field.classList.add('form-control', 'error');
    let errorElement = field.nextElementSibling;

    if (!errorElement || !errorElement.classList.contains('form-text')) {
      errorElement = document.createElement('div');
      errorElement.className = 'form-text error';
      field.parentNode.insertBefore(errorElement, field.nextSibling);
    }

    errorElement.textContent = message;
  },

  clearError(field) {
    field.classList.remove('error');
    const errorElement = field.nextElementSibling;

    if (errorElement && errorElement.classList.contains('form-text')) {
      errorElement.remove();
    }
  },

  init() {
    document.querySelectorAll('form[data-validate-form]').forEach((form) => {
      form.addEventListener('submit', (e) => {
        if (!this.validate(form)) {
          e.preventDefault();
        }
      });

      // Real-time validation
      const fields = form.querySelectorAll('[data-validate]');
      fields.forEach((field) => {
        field.addEventListener('blur', () => {
          this.validate(form);
        });
      });
    });
  }
};

/**
 * Menu Toggle Component
 * Handles mobile menu toggle
 */
const MenuToggle = {
  init() {
    const toggleBtn = document.querySelector('.menu-toggle');
    const menu = document.querySelector('.nav-primary');

    if (toggleBtn && menu) {
      toggleBtn.addEventListener('click', () => {
        menu.classList.toggle('show');
        toggleBtn.setAttribute('aria-expanded',
          menu.classList.contains('show') ? 'true' : 'false'
        );
      });

      // Close menu when clicking on a link
      menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
          menu.classList.remove('show');
          toggleBtn.setAttribute('aria-expanded', 'false');
        });
      });

      // Close menu when clicking outside
      document.addEventListener('click', (e) => {
        if (!e.target.closest('.header-content')) {
          menu.classList.remove('show');
          toggleBtn.setAttribute('aria-expanded', 'false');
        }
      });
    }
  }
};

/**
 * Scroll to Top Button
 * Shows/hides button and scrolls to top
 */
const ScrollToTop = {
  init() {
    const scrollBtn = document.querySelector('.scroll-to-top');

    if (!scrollBtn) {
      const btn = document.createElement('button');
      btn.className = 'scroll-to-top btn btn-primary';
      btn.setAttribute('aria-label', 'Scroll to top');
      btn.innerHTML = '↑';
      document.body.appendChild(btn);
    } else {
      window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
          scrollBtn.classList.add('show');
        } else {
          scrollBtn.classList.remove('show');
        }
      });

      scrollBtn.addEventListener('click', () => {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    }
  }
};

/**
 * AJAX Form Submission
 * Submits forms via AJAX without page reload
 */
const AjaxForm = {
  submit(form) {
    const formData = new FormData(form);
    const method = form.method.toUpperCase();
    const action = form.action;

    fetch(action, {
      method: method,
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then((response) => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          // Handle success
          if (form.dataset.redirectAfter) {
            window.location.href = form.dataset.redirectAfter;
          } else {
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success mt-4';
            successAlert.textContent = data.message || 'Success!';
            form.parentNode.insertBefore(successAlert, form);
          }
        } else {
          // Handle form errors
          if (data.errors) {
            Object.entries(data.errors).forEach(([field, message]) => {
              const input = form.querySelector(`[name="${field}"]`);
              if (input) {
                FormValidator.showError(input, message);
              }
            });
          }
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
  },

  init() {
    document.querySelectorAll('form[data-ajax-submit]').forEach((form) => {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (FormValidator.validate(form)) {
          this.submit(form);
        }
      });
    });
  }
};

/**
 * Initialize all components
 */
document.addEventListener('DOMContentLoaded', () => {
  ModalComponent.init();
  AlertComponent.init();
  FormValidator.init();
  MenuToggle.init();
  ScrollToTop.init();
  AjaxForm.init();

  // Log that UI components are initialized
  console.log('SkelbyForsamlinghus UI components initialized');
});

/**
 * Utility Functions
 */
const UI = {
  /**
   * Show loading spinner
   */
  showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    spinner.id = 'app-loading';
    document.body.appendChild(spinner);
  },

  /**
   * Hide loading spinner
   */
  hideLoading() {
    const spinner = document.getElementById('app-loading');
    if (spinner) spinner.remove();
  },

  /**
   * Show toast notification
   */
  toast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.bottom = 'var(--spacing-4)';
    toast.style.right = 'var(--spacing-4)';
    toast.style.zIndex = '9999';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.remove();
    }, duration);
  },

  /**
   * Debounce function
   */
  debounce(func, delay) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), delay);
    };
  },

  /**
   * Format date
   */
  formatDate(date, format = 'DD/MM/YYYY') {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return format
      .replace('DD', day)
      .replace('MM', month)
      .replace('YYYY', year);
  }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    ModalComponent,
    AlertComponent,
    FormValidator,
    MenuToggle,
    ScrollToTop,
    AjaxForm,
    UI
  };
}
