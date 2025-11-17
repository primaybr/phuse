// Phuse Framework JavaScript - Inspired by Bootstrap and Material-UI
// A lightweight utility library for component interactions

class Phuse {
  static components = {};

  // Modal functionality
  static modal(element) {
    const modal = {
      show() {
        element.style.display = 'block';
        element.classList.add('show');
        document.body.classList.add('modal-open');
      },
      hide() {
        element.classList.remove('show');
        setTimeout(() => {
          element.style.display = 'none';
          document.body.classList.remove('modal-open');
        }, 300);
      }
    };
    return modal;
  }

  // Dropdown functionality
  static dropdown(element) {
    const menu = element.nextElementSibling;
    return {
      toggle() {
        const isOpen = menu.classList.contains('show');
        this.closeAll();
        if (!isOpen) {
          menu.classList.add('show');
          element.setAttribute('aria-expanded', 'true');
        }
      },
      close() {
        menu.classList.remove('show');
        element.setAttribute('aria-expanded', 'false');
      },
      closeAll() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
          menu.classList.remove('show');
          const btn = menu.previousElementSibling;
          if (btn) btn.setAttribute('aria-expanded', 'false');
        });
      }
    };
  }

  // Alert functionality
  static alert(element) {
    return {
      close() {
        const alert = element.closest('.alert');
        if (alert) {
          alert.classList.remove('show');
          setTimeout(() => alert.remove(), 300);
        }
      }
    };
  }

  // Button functionality
  static button() {
    return {
      toggle(element) {
        element.classList.toggle('active');
        const input = element.querySelector('input[type="checkbox"], input[type="radio"]');
        if (input) {
          input.checked = element.classList.contains('active');
        }
      }
    };
  }

  // Carousel functionality
  static carousel(element) {
    let currentIndex = 0;
    const items = element.querySelectorAll('.carousel-item');
    const indicators = element.querySelectorAll('.carousel-indicator');

    return {
      next() {
        this.goTo((currentIndex + 1) % items.length);
      },
      prev() {
        this.goTo((currentIndex - 1 + items.length) % items.length);
      },
      goTo(index) {
        items[currentIndex].classList.remove('active');
        indicators[currentIndex]?.classList.remove('active');
        currentIndex = index;
        items[currentIndex].classList.add('active');
        indicators[currentIndex]?.classList.add('active');
      }
    };
  }

  // Offcanvas functionality
  static offcanvas(element) {
    return {
      show() {
        element.classList.add('show');
        document.body.style.overflow = 'hidden';
      },
      hide() {
        element.classList.remove('show');
        document.body.style.overflow = '';
      }
    };
  }

  // Popover functionality
  static popover(element) {
    return {
      show() {
        const popover = document.createElement('div');
        popover.className = 'popover fade show';
        popover.innerHTML = `
          <div class="popover-arrow"></div>
          <h3 class="popover-header">${element.dataset.title || ''}</h3>
          <div class="popover-body">${element.dataset.content || ''}</div>
        `;

        document.body.appendChild(popover);
        element.setAttribute('aria-describedby', popover.id = 'popover-' + Date.now());

        // Position popover
        const rect = element.getBoundingClientRect();
        popover.style.left = rect.left + window.pageXOffset + 'px';
        popover.style.top = rect.bottom + window.pageYOffset + 'px';
      },
      hide() {
        const popover = document.querySelector(`[aria-describedby="${element.getAttribute('aria-describedby')}"]`);
        if (popover) {
          popover.classList.remove('show');
          setTimeout(() => popover.remove(), 300);
        }
      }
    };
  }

  // ScrollSpy functionality
  static scrollSpy(element, options = {}) {
    const navItems = element.querySelectorAll('a[href^="#"]');
    const sections = Array.from(navItems).map(item => document.querySelector(item.getAttribute('href')));

    const update = () => {
      const scrollY = window.pageYOffset;

      sections.forEach((section, index) => {
        if (section) {
          const rect = section.getBoundingClientRect();
          if (scrollY >= rect.top + window.pageYOffset - 100) {
            navItems.forEach(item => item.parentElement.classList.remove('active'));
            navItems[index]?.parentElement.classList.add('active');
          }
        }
      });
    };

    window.addEventListener('scroll', update);
    update();

    return { update };
  }

  // Tooltip functionality
  static tooltip(element) {
    let tooltip;

    return {
      show() {
        tooltip = document.createElement('div');
        tooltip.className = 'tooltip fade show';
        tooltip.innerHTML = `<div class="tooltip-arrow"></div><div class="tooltip-inner">${element.dataset.title || element.title}</div>`;

        document.body.appendChild(tooltip);
        element.setAttribute('aria-describedby', tooltip.id = 'tooltip-' + Date.now());

        // Position tooltip
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + window.pageXOffset + (rect.width / 2) + 'px';
        tooltip.style.top = rect.top + window.pageYOffset - 10 + 'px';
      },
      hide() {
        if (tooltip) {
          tooltip.classList.remove('show');
          setTimeout(() => {
            if (tooltip.parentNode) tooltip.parentNode.removeChild(tooltip);
            tooltip = null;
          }, 300);
        }
      }
    };
  }

  // Toast notification
  static toast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} animate-pulse`;
    toast.innerHTML = `
      <div class="toast-header">
        <strong>${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
        <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
      </div>
      <div class="toast-body">${message}</div>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('show');
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, duration);
    }, 100);

    return toast;
  }

  // Accordion functionality
  static accordion(element) {
    return {
      toggle() {
        const panel = element.nextElementSibling;
        const isOpen = element.classList.contains('active');

        document.querySelectorAll('.accordion-button').forEach(btn => {
          btn.classList.remove('active');
          btn.nextElementSibling.style.maxHeight = '0';
        });

        if (!isOpen) {
          element.classList.add('active');
          panel.style.maxHeight = panel.scrollHeight + 'px';
        }
      }
    };
  }

  // Tab functionality
  static tabs() {
    return {
      show(targetId) {
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));

        document.getElementById(targetId).classList.add('show', 'active');
        event.target.classList.add('active');
      }
    };
  }

  // Event delegation
  static on(event, selector, callback) {
    document.addEventListener(event, function(e) {
      if (e.target.matches(selector) || e.target.closest(selector)) {
        callback.call(e.target, e);
      }
    });
  }

  // Initialize all components
  static init() {
    // Modal triggers
    this.on('click', '[data-toggle="modal"]', function() {
      const target = document.querySelector(this.dataset.target);
      if (target) {
        const modal = new Phuse.modal(target);
        modal.show();
      }
    });

    // Modal close
    this.on('click', '[data-dismiss="modal"], .modal .close', function() {
      const modal = this.closest('.modal');
      const phuseModal = new Phuse.modal(modal);
      phuseModal.hide();
    });

    // Dropdown triggers
    this.on('click', '.dropdown-toggle', function(e) {
      e.preventDefault();
      const dropdown = new Phuse.dropdown(this);
      dropdown.toggle();
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
          menu.classList.remove('show');
        });
      }
    });

    // Alert dismiss
    this.on('click', '[data-dismiss="alert"]', function() {
      const alert = new Phuse.alert(this);
      alert.close();
    });

    // Button toggle
    this.on('click', '[data-toggle="button"]', function() {
      const button = new Phuse.button();
      button.toggle(this);
    });

    // Carousel controls
    this.on('click', '[data-slide]', function(e) {
      e.preventDefault();
      const carousel = this.closest('.carousel');
      if (carousel) {
        const phuseCarousel = new Phuse.carousel(carousel);
        const action = this.dataset.slide;
        if (action === 'next') phuseCarousel.next();
        else if (action === 'prev') phuseCarousel.prev();
      }
    });

    this.on('click', '[data-slide-to]', function(e) {
      e.preventDefault();
      const carousel = this.closest('.carousel');
      if (carousel) {
        const phuseCarousel = new Phuse.carousel(carousel);
        const index = parseInt(this.dataset.slideTo);
        phuseCarousel.goTo(index);
      }
    });

    // Offcanvas triggers
    this.on('click', '[data-toggle="offcanvas"]', function() {
      const target = document.querySelector(this.dataset.target);
      if (target) {
        const offcanvas = new Phuse.offcanvas(target);
        offcanvas.show();
      }
    });

    this.on('click', '[data-dismiss="offcanvas"]', function() {
      const offcanvas = this.closest('.offcanvas');
      const phuseOffcanvas = new Phuse.offcanvas(offcanvas);
      phuseOffcanvas.hide();
    });

    // Popover triggers
    this.on('click', '[data-toggle="popover"]', function(e) {
      e.preventDefault();
      const popover = new Phuse.popover(this);
      if (this.hasAttribute('aria-describedby')) {
        popover.hide();
        this.removeAttribute('aria-describedby');
      } else {
        popover.show();
      }
    });

    // Close popovers when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('[data-toggle="popover"]') && !e.target.closest('.popover')) {
        document.querySelectorAll('.popover').forEach(popover => popover.remove());
        document.querySelectorAll('[aria-describedby]').forEach(el => el.removeAttribute('aria-describedby'));
      }
    });

    // Tooltip triggers
    this.on('mouseenter', '[data-toggle="tooltip"]', function() {
      const tooltip = new Phuse.tooltip(this);
      tooltip.show();
    });

    this.on('mouseleave', '[data-toggle="tooltip"]', function() {
      const tooltip = new Phuse.tooltip(this);
      Phuse.tooltip(this).hide();
    });

    // ScrollSpy initialization
    document.querySelectorAll('[data-spy="scroll"]').forEach(element => {
      new Phuse.scrollSpy(element);
    });

    // Accordion triggers
    this.on('click', '.accordion-button', function() {
      const accordion = new Phuse.accordion(this);
      accordion.toggle();
    });

    // Tab triggers
    this.on('click', '.nav-link', function(e) {
      e.preventDefault();
      const target = this.getAttribute('data-target') || this.getAttribute('href').substring(1);
      const tabs = new Phuse.tabs();
      tabs.show(target);
    });
  }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => Phuse.init());
} else {
  Phuse.init();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Phuse;
}
if (typeof window !== 'undefined') {
  window.Phuse = Phuse;
}
