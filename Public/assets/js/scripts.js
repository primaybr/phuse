// Phuse Framework JavaScript v1.2.3
// Lightweight Bootstrap-compatible component library

class Phuse {
  static components = {};

  // Per-element state store (avoids closure-captured stale data)
  static _store = new WeakMap();
  static _get(el)       { return this._store.get(el) || {}; }
  static _set(el, data) { this._store.set(el, { ...this._get(el), ...data }); }

  // ── Modal ─────────────────────────────────────────────────────────────────
  static modal(element) {
    const isStatic = element.dataset.backdrop === 'static';

    const mkBackdrop = () => {
      let bd = document.getElementById('phuse-modal-backdrop');
      if (!bd) {
        bd = document.createElement('div');
        bd.id = 'phuse-modal-backdrop';
        bd.className = 'modal-backdrop fade';
        document.body.appendChild(bd);
      }
      return bd;
    };

    const doHide = () => {
      element.classList.remove('show');
      const bd = document.getElementById('phuse-modal-backdrop');
      if (bd) { bd.classList.remove('show'); }
      setTimeout(() => {
        element.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        if (bd) bd.remove();
        element._escHandler && document.removeEventListener('keydown', element._escHandler);
      }, 220);
    };

    return {
      show() {
        const bd = mkBackdrop();
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        element.style.display = 'block';

        requestAnimationFrame(() => requestAnimationFrame(() => {
          bd.classList.add('show');
          element.classList.add('show');
        }));

        // Close on backdrop click (unless data-backdrop="static")
        element._bdHandler = (e) => {
          if (e.target === element && !isStatic) doHide();
        };
        element.addEventListener('click', element._bdHandler);

        // Close on Escape key
        element._escHandler = (e) => {
          if (e.key === 'Escape' && !isStatic) doHide();
        };
        document.addEventListener('keydown', element._escHandler);
      },
      hide() {
        element.removeEventListener('click', element._bdHandler);
        doHide();
      }
    };
  }

  // ── Dropdown ──────────────────────────────────────────────────────────────
  static dropdown(element) {
    const menu = element.nextElementSibling;
    return {
      toggle() {
        const open = menu.classList.contains('show');
        document.querySelectorAll('.dropdown-menu.show').forEach(m => {
          m.classList.remove('show');
          const b = m.previousElementSibling;
          if (b) b.setAttribute('aria-expanded', 'false');
        });
        if (!open) {
          menu.classList.add('show');
          element.setAttribute('aria-expanded', 'true');
        }
      }
    };
  }

  // ── Alert ─────────────────────────────────────────────────────────────────
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

  // ── Button toggle ─────────────────────────────────────────────────────────
  static button() {
    return {
      toggle(el) {
        el.classList.toggle('active');
        const input = el.querySelector('input[type="checkbox"], input[type="radio"]');
        if (input) input.checked = el.classList.contains('active');
      }
    };
  }

  // ── Carousel ──────────────────────────────────────────────────────────────
  // State stored in WeakMap so the same index persists across multiple
  // handler calls on the same DOM element.
  static carousel(element) {
    if (!Phuse._get(element).carouselReady) {
      Phuse._set(element, { carouselIndex: 0, carouselReady: true });
    }
    const state   = Phuse._get(element);
    const items   = Array.from(element.querySelectorAll('.carousel-item'));
    const dots    = Array.from(element.querySelectorAll('.carousel-indicators button, .carousel-indicators [data-slide-to]'));

    const activate = (raw) => {
      const next = ((raw % items.length) + items.length) % items.length;
      const prev = state.carouselIndex;
      if (next === prev) return;

      items[prev].classList.remove('active');
      if (dots[prev]) dots[prev].classList.remove('active');

      items[next].classList.add('active');
      if (dots[next]) dots[next].classList.add('active');

      state.carouselIndex = next;
      Phuse._set(element, state);
    };

    return {
      next()      { activate(state.carouselIndex + 1); },
      prev()      { activate(state.carouselIndex - 1); },
      goTo(index) { activate(index); }
    };
  }

  // ── Offcanvas ─────────────────────────────────────────────────────────────
  static offcanvas(element) {
    const mkBackdrop = () => {
      let bd = document.getElementById('phuse-offcanvas-backdrop');
      if (!bd) {
        bd = document.createElement('div');
        bd.id = 'phuse-offcanvas-backdrop';
        bd.className = 'offcanvas-backdrop fade';
        bd.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1040;opacity:0;transition:opacity .3s;';
        document.body.appendChild(bd);
        bd.addEventListener('click', () => Phuse.offcanvas(element).hide());
      }
      return bd;
    };

    return {
      show() {
        const bd = mkBackdrop();
        document.body.style.overflow = 'hidden';
        // Force reflow then animate
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            bd.style.opacity = '1';
            element.classList.add('show');
          });
        });
      },
      hide() {
        element.classList.remove('show');
        const bd = document.getElementById('phuse-offcanvas-backdrop');
        if (bd) {
          bd.style.opacity = '0';
          setTimeout(() => {
            bd.remove();
            document.body.style.overflow = '';
          }, 300);
        } else {
          document.body.style.overflow = '';
        }
      }
    };
  }

  // ── Popover ───────────────────────────────────────────────────────────────
  static popover(element) {
    const place = (pop) => {
      const rect = element.getBoundingClientRect();
      const sx = window.pageXOffset, sy = window.pageYOffset;

      // Measure off-screen (force layout reflow)
      pop.style.cssText += ';visibility:hidden;display:block;';
      const pw = pop.offsetWidth;
      void pop.offsetHeight; // trigger reflow so dimensions are correct
      pop.style.visibility = '';

      pop.style.position = 'absolute';
      pop.style.left = Math.max(8, rect.left + sx + rect.width / 2 - pw / 2) + 'px';
      pop.style.top  = (rect.bottom + sy + 10) + 'px';
    };

    const create = () => {
      // Close any open popovers
      document.querySelectorAll('.phuse-popover').forEach(p => p.remove());
      document.querySelectorAll('[data-popover-open]').forEach(el => el.removeAttribute('data-popover-open'));

      const title   = element.getAttribute('data-title') || element.getAttribute('title') || '';
      const content = element.getAttribute('data-content') || '';
      const id      = 'popover-' + Date.now();

      element.setAttribute('aria-describedby', id);
      element.setAttribute('data-popover-open', '1');

      const pop = document.createElement('div');
      pop.id        = id;
      pop.className = 'popover fade bs-tooltip-bottom phuse-popover';
      pop.setAttribute('role', 'tooltip');
      pop.innerHTML =
        `<div class="popover-arrow" style="left:50%;transform:translateX(-50%);"></div>` +
        (title   ? `<h3 class="popover-header">${title}</h3>` : '') +
        `<div class="popover-body">${content}</div>`;
      document.body.appendChild(pop);

      place(pop);
      requestAnimationFrame(() => pop.classList.add('show'));
    };

    const destroy = () => {
      const id = element.getAttribute('aria-describedby');
      if (id) {
        const pop = document.getElementById(id);
        if (pop) {
          pop.classList.remove('show');
          setTimeout(() => pop.remove(), 200);
        }
      }
      element.removeAttribute('aria-describedby');
      element.removeAttribute('data-popover-open');
    };

    return {
      show()   { create(); },
      hide()   { destroy(); },
      toggle() { element.hasAttribute('data-popover-open') ? destroy() : create(); }
    };
  }

  // ── ScrollSpy ─────────────────────────────────────────────────────────────
  static scrollSpy(element) {
    const targetSel = element.dataset.target || element.dataset.bsTarget;
    const navEl     = targetSel ? document.querySelector(targetSel) : null;
    const navItems  = navEl ? Array.from(navEl.querySelectorAll('a[href^="#"]')) : [];
    const sections  = navItems.map(a => element.querySelector(a.getAttribute('href'))).filter(Boolean);
    const offset    = parseInt(element.dataset.offset || '0', 10);

    const update = () => {
      const top = element.scrollTop;
      let active = -1;
      sections.forEach((s, i) => { if (s.offsetTop - offset <= top + 4) active = i; });
      navItems.forEach(a => a.classList.remove('active'));
      if (active >= 0) navItems[active].classList.add('active');
    };

    element.addEventListener('scroll', update);
    update();
    return { update };
  }

  // ── Tooltip ───────────────────────────────────────────────────────────────
  static tooltip(element) {
    const position = (tip, placement) => {
      const rect = element.getBoundingClientRect();
      const sx = window.pageXOffset, sy = window.pageYOffset;
      const tw = tip.offsetWidth, th = tip.offsetHeight;

      tip.style.position = 'absolute';
      switch (placement) {
        case 'bottom':
          tip.style.left = (rect.left + sx + rect.width / 2 - tw / 2) + 'px';
          tip.style.top  = (rect.bottom + sy + 8) + 'px';
          break;
        case 'left':
          tip.style.left = (rect.left + sx - tw - 8) + 'px';
          tip.style.top  = (rect.top + sy + rect.height / 2 - th / 2) + 'px';
          break;
        case 'right':
          tip.style.left = (rect.right + sx + 8) + 'px';
          tip.style.top  = (rect.top + sy + rect.height / 2 - th / 2) + 'px';
          break;
        default: // top
          tip.style.left = (rect.left + sx + rect.width / 2 - tw / 2) + 'px';
          tip.style.top  = (rect.top + sy - th - 8) + 'px';
      }
    };

    return {
      show() {
        // Remove stale tooltip if any
        const old = Phuse._get(element).tooltip;
        if (old && old.parentNode) old.remove();

        const placement = element.getAttribute('data-placement') || 'top';
        const text = element.dataset.titleBak
          || element.getAttribute('data-title')
          || element.getAttribute('title')
          || '';

        // Suppress native browser tooltip
        if (element.getAttribute('title')) {
          element.dataset.titleBak = element.getAttribute('title');
          element.removeAttribute('title');
        }

        const placementClass = {
          top: 'bs-tooltip-top', bottom: 'bs-tooltip-bottom',
          left: 'bs-tooltip-start', right: 'bs-tooltip-end'
        }[placement] || 'bs-tooltip-top';

        // Detect color variant from the trigger button's class
        const variant = ['danger','success','primary','warning','info']
          .find(v => element.classList.contains(`btn-${v}`));

        const tip = document.createElement('div');
        tip.className = `tooltip fade ${placementClass}${variant ? ' tooltip-' + variant : ''}`;
        tip.setAttribute('role', 'tooltip');
        tip.innerHTML = `<div class="tooltip-arrow"></div><div class="tooltip-inner">${text}</div>`;
        document.body.appendChild(tip);
        Phuse._set(element, { tooltip: tip });

        requestAnimationFrame(() => {
          position(tip, placement);
          tip.classList.add('show');
        });
      },
      hide() {
        const tip = Phuse._get(element).tooltip;
        if (tip) {
          tip.classList.remove('show');
          setTimeout(() => { if (tip.parentNode) tip.remove(); }, 200);
          Phuse._set(element, { tooltip: null });
        }
        // Restore native title
        if (element.dataset.titleBak) {
          element.setAttribute('title', element.dataset.titleBak);
          delete element.dataset.titleBak;
        }
      }
    };
  }

  // ── Toast ─────────────────────────────────────────────────────────────────
  static toast(message, type = 'info', duration = 4000) {
    // Fixed top-right container
    let container = document.getElementById('phuse-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'phuse-toast-container';
      container.style.cssText = [
        'position:fixed', 'top:1rem', 'right:1rem', 'z-index:9999',
        'display:flex', 'flex-direction:column', 'gap:.5rem',
        'min-width:280px', 'max-width:360px', 'pointer-events:none'
      ].join(';');
      document.body.appendChild(container);
    }

    // Per-type configuration
    const cfg = {
      success: { icon: 'pi-check-circle',    headerBg: '#157347', bodyBg: '#198754', text: '#fff', label: 'Success' },
      error:   { icon: 'pi-x-circle',        headerBg: '#b02a37', bodyBg: '#dc3545', text: '#fff', label: 'Error'   },
      danger:  { icon: 'pi-x-circle',        headerBg: '#b02a37', bodyBg: '#dc3545', text: '#fff', label: 'Error'   },
      warning: { icon: 'pi-alert-triangle',  headerBg: '#cc6200', bodyBg: '#fd7e14', text: '#fff', label: 'Warning' },
      info:    { icon: 'pi-info',            headerBg: '#0aa2c0', bodyBg: '#0dcaf0', text: '#fff', label: 'Info'    },
    };
    const c = cfg[type] || cfg.info;

    const wrap = document.createElement('div');
    wrap.style.cssText = [
      'pointer-events:auto', 'border-radius:.5rem', 'overflow:hidden',
      'box-shadow:0 4px 16px rgba(0,0,0,.25)',
      'transition:opacity .3s ease,transform .3s ease',
      'opacity:0', 'transform:translateX(110%)'
    ].join(';');

    wrap.innerHTML = `
      <div style="
        display:flex;align-items:center;gap:.5rem;
        padding:.5rem .75rem;
        background:${c.headerBg};
        border-bottom:1px solid rgba(0,0,0,.15);
      ">
        <i class="pi ${c.icon}" style="color:${c.text};font-size:1rem;flex-shrink:0;"></i>
        <strong style="flex:1;font-size:.875rem;color:${c.text};">${c.label}</strong>
        <button type="button" aria-label="Close"
          style="background:none;border:none;cursor:pointer;padding:2px;display:flex;align-items:center;color:${c.text};opacity:.8;pointer-events:auto;"
          onclick="
            var w=this.closest('[style]');
            w.style.opacity='0';w.style.transform='translateX(110%)';
            setTimeout(function(){w.remove();},300);
          ">
          <i class="pi pi-x" style="font-size:.875rem;"></i>
        </button>
      </div>
      <div style="
        padding:.625rem .75rem;font-size:.875rem;
        background:${c.bodyBg};color:${c.text};
      ">${message}</div>
    `;

    container.appendChild(wrap);

    // Slide in
    requestAnimationFrame(() => requestAnimationFrame(() => {
      wrap.style.opacity   = '1';
      wrap.style.transform = 'translateX(0)';
    }));

    // Auto-dismiss
    const dismiss = () => {
      wrap.style.opacity   = '0';
      wrap.style.transform = 'translateX(110%)';
      setTimeout(() => {
        if (wrap.parentNode) wrap.remove();
        if (container.children.length === 0) container.remove();
      }, 300);
    };
    setTimeout(dismiss, duration);

    return wrap;
  }

  // ── Accordion ─────────────────────────────────────────────────────────────
  // Structure: .accordion-button > h2.accordion-header > div.accordion-item > div.accordion-body
  static accordion(button) {
    const item = button.closest('.accordion-item');
    const body = item ? item.querySelector('.accordion-body') : null;
    if (!body) return { toggle() {} };

    const open = () => {
      button.classList.remove('collapsed');
      body.style.overflow     = 'hidden';
      body.style.paddingTop   = '';   // restore CSS-defined padding
      body.style.paddingBottom = '';
      body.style.maxHeight    = body.scrollHeight + 'px';
      // After transition, remove max-height constraint so content can grow
      body.addEventListener('transitionend', () => {
        if (!button.classList.contains('collapsed')) {
          body.style.maxHeight = 'none';
          body.style.overflow  = '';
        }
      }, { once: true });
    };

    const close = () => {
      // Lock current height, then animate to 0 + zero out padding so border-box
      // doesn't let padding bleed through max-height:0
      body.style.overflow     = 'hidden';
      body.style.maxHeight    = body.scrollHeight + 'px';
      requestAnimationFrame(() => {
        body.style.maxHeight     = '0';
        body.style.paddingTop    = '0';
        body.style.paddingBottom = '0';
      });
      button.classList.add('collapsed');
    };

    return {
      toggle() {
        button.classList.contains('collapsed') ? open() : close();
      }
    };
  }

  // ── Dark Mode ─────────────────────────────────────────────────────────────
  // Reads localStorage, applies data-theme to <html>, injects toggle button.
  static darkMode() {
    const KEY = 'phuse-theme';

    const apply = (dark) => {
      document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
      const btn = document.getElementById('phuse-theme-toggle');
      if (btn) {
        const icon = btn.querySelector('.pi');
        if (icon) icon.className = dark ? 'pi pi-sun' : 'pi pi-moon';
        btn.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
      }
    };

    // Resolve initial preference: saved > system preference > light
    const saved       = localStorage.getItem(KEY);
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark      = saved === 'dark' || (!saved && prefersDark);
    apply(isDark);

    // Inject toggle button (once)
    if (!document.getElementById('phuse-theme-toggle')) {
      const btn = document.createElement('button');
      btn.id   = 'phuse-theme-toggle';
      btn.type = 'button';
      btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
      btn.innerHTML = `<i class="pi ${isDark ? 'pi-sun' : 'pi-moon'}"></i>`;
      document.body.appendChild(btn);

      btn.addEventListener('click', () => {
        const nowDark = document.documentElement.getAttribute('data-theme') !== 'dark';
        localStorage.setItem(KEY, nowDark ? 'dark' : 'light');
        apply(nowDark);
      });
    }
  }

  // ── Tabs ──────────────────────────────────────────────────────────────────
  static tabs() {
    return {
      show(targetId, triggerEl) {
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
        document.querySelectorAll('[data-toggle="tab"]').forEach(l => l.classList.remove('active'));
        const pane = document.getElementById(targetId);
        if (pane) pane.classList.add('show', 'active');
        if (triggerEl) triggerEl.classList.add('active');
      }
    };
  }

  // ── Event delegation ──────────────────────────────────────────────────────
  static on(eventType, selector, callback) {
    document.addEventListener(eventType, function(e) {
      if (e.target.nodeType !== Node.ELEMENT_NODE) return;
      const el = e.target.matches(selector) ? e.target : e.target.closest(selector);
      if (el) callback.call(el, e);
    });
  }

  // ── Init ──────────────────────────────────────────────────────────────────
  static init() {

    // Dark mode toggle (must run first to avoid layout shift)
    Phuse.darkMode();

    // Modal
    this.on('click', '[data-toggle="modal"]', function() {
      const t = document.querySelector(this.dataset.target);
      if (t) Phuse.modal(t).show();
    });
    this.on('click', '[data-dismiss="modal"], .modal .close', function() {
      const m = this.closest('.modal');
      if (m) Phuse.modal(m).hide();
    });

    // Dropdown
    this.on('click', '.dropdown-toggle', function(e) {
      e.preventDefault();
      Phuse.dropdown(this).toggle();
    });
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
      }
    });

    // Alert dismiss
    this.on('click', '[data-dismiss="alert"]', function() {
      Phuse.alert(this).close();
    });

    // Button toggle
    this.on('click', '[data-toggle="button"]', function() {
      Phuse.button().toggle(this);
    });

    // Carousel - prev/next arrows
    this.on('click', '[data-slide]', function(e) {
      e.preventDefault();
      const carousel = this.closest('.carousel');
      if (!carousel) return;
      const c = Phuse.carousel(carousel);
      this.dataset.slide === 'next' ? c.next() : c.prev();
    });
    // Carousel - indicator dots
    this.on('click', '[data-slide-to]', function(e) {
      e.preventDefault();
      const carousel = this.closest('.carousel');
      if (!carousel) return;
      Phuse.carousel(carousel).goTo(parseInt(this.dataset.slideTo, 10));
    });

    // Offcanvas open
    this.on('click', '[data-toggle="offcanvas"]', function() {
      const t = document.querySelector(this.dataset.target);
      if (t) Phuse.offcanvas(t).show();
    });
    // Offcanvas close button
    this.on('click', '[data-dismiss="offcanvas"]', function() {
      const panel = this.closest('.offcanvas');
      if (panel) Phuse.offcanvas(panel).hide();
    });

    // Popover - toggle on click, close on outside click
    this.on('click', '[data-toggle="popover"]', function(e) {
      e.preventDefault();
      e.stopPropagation();
      Phuse.popover(this).toggle();
    });
    document.addEventListener('click', (e) => {
      if (!e.target.closest('[data-toggle="popover"]') && !e.target.closest('.phuse-popover')) {
        document.querySelectorAll('[data-popover-open]').forEach(el => Phuse.popover(el).hide());
      }
    });

    // Tooltip - show on hover
    // mouseover/mouseout bubble; mouseenter/leave do not - use over/out for delegation
    this.on('mouseover', '[data-toggle="tooltip"]', function() {
      Phuse.tooltip(this).show();
    });
    this.on('mouseout', '[data-toggle="tooltip"]', function() {
      Phuse.tooltip(this).hide();
    });

    // ScrollSpy
    document.querySelectorAll('[data-spy="scroll"]').forEach(el => {
      try { Phuse.scrollSpy(el); } catch (err) { console.warn('ScrollSpy:', err); }
    });

    // Accordion
    this.on('click', '.accordion-button', function() {
      Phuse.accordion(this).toggle();
    });

    // Tabs
    this.on('click', '[data-toggle="tab"]', function(e) {
      e.preventDefault();
      const target = this.getAttribute('data-target') || this.getAttribute('href')?.replace(/^#/, '');
      if (target) Phuse.tabs().show(target, this);
    });
  }
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => Phuse.init());
} else {
  Phuse.init();
}

if (typeof module !== 'undefined' && module.exports) module.exports = Phuse;
if (typeof window !== 'undefined') window.Phuse = Phuse;
