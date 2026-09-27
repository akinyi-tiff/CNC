/* ==========================================================================
   Ananki Visviva CNC Company - Master JavaScript
   Features:
   - Mobile navigation menu drawer
   - Filterable gallery tabs
   - Form submission feedback (Quotes, Machine Inquiry, Course Registration)
   - Smooth anchor scrolling
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
  // --- 1. Mobile Navigation Toggle ---
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const navLinks = document.getElementById('navLinks');

  if (mobileMenuBtn && navLinks) {
    mobileMenuBtn.addEventListener('click', () => {
      navLinks.classList.toggle('show');
      const isExpanded = navLinks.classList.contains('show');
      mobileMenuBtn.setAttribute('aria-expanded', isExpanded);
      mobileMenuBtn.innerHTML = isExpanded ? '✕' : '☰';
    });
  }

  // --- 2. Active Page Highlighting ---
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  const navItems = document.querySelectorAll('.nav-link');

  navItems.forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPath || (currentPath === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

  // --- 3. Filterable Portfolio Gallery ---
  const filterButtons = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');

  if (filterButtons.length > 0 && galleryItems.length > 0) {
    filterButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Remove active class from all buttons
        filterButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const filterValue = btn.getAttribute('data-filter');

        galleryItems.forEach(item => {
          const category = item.getAttribute('data-category');
          if (filterValue === 'all' || category === filterValue) {
            item.style.display = 'block';
            item.style.opacity = '0';
            setTimeout(() => {
              item.style.opacity = '1';
            }, 50);
          } else {
            item.style.display = 'none';
          }
        });
      });
    });
  }

  // --- 4. Interactive Form Submission Handlers ---
  const forms = document.querySelectorAll('form[data-handle-submit="true"]');

  forms.forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.innerHTML : 'Submit';
      
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Sending...';
      }

      // Simulate network request feedback
      setTimeout(() => {
        const formResponse = document.createElement('div');
        formResponse.className = 'form-success-msg';
        formResponse.style.cssText = `
          background-color: #d1fae5;
          color: #065f46;
          padding: 1rem 1.25rem;
          border-radius: 8px;
          margin-top: 1rem;
          font-weight: 600;
          border: 1px solid #6ee7b7;
        `;
        formResponse.innerHTML = `
          ✓ Thank you! Your request has been received. Our team at Eastern Bypass, Ruai will contact you via WhatsApp/Phone shortly.
        `;

        form.reset();
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }

        // Avoid duplicate messages
        const existingMsg = form.querySelector('.form-success-msg');
        if (existingMsg) existingMsg.remove();
        
        form.appendChild(formResponse);

        // Auto remove message after 7 seconds
        setTimeout(() => {
          formResponse.remove();
        }, 7000);
      }, 800);
    });
  });

  // --- 5. Smooth Scrolling for Internal Anchors ---
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId !== '#') {
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          e.preventDefault();
          targetElement.scrollIntoView({
            behavior: 'smooth'
          });
        }
      }
    });
  });
});

// --- 6. Load Shared Footer ---
const footerTarget = document.getElementById('footer-placeholder') || document.querySelector('.site-footer');

if (footerTarget) {
    fetch('footer.html')
        .then(response => {
            if (!response.ok) {
                throw new Error('Could not load footer.html');
            }
            return response.text();
        })
        .then(footer => {
          footerTarget.outerHTML = footer;
        })
        .catch(error => {
            console.error('Footer loading error:', error);
        });
}