/**
 * sims.js  —  SIMS Student Information Management System
 * Lightweight vanilla JS, no dependencies.
 */
(function () {
  'use strict';

  /* ============================================================
     1. MOBILE NAVIGATION TOGGLE
  ============================================================ */
  const hamburger = document.getElementById('hamburger');
  const mobileNav = document.getElementById('mobile-nav');

  if (hamburger && mobileNav) {
    hamburger.addEventListener('click', function () {
      const open = hamburger.getAttribute('aria-expanded') === 'true';
      hamburger.setAttribute('aria-expanded', String(!open));
      mobileNav.hidden = open;
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
      if (!mobileNav.hidden &&
          !hamburger.contains(e.target) &&
          !mobileNav.contains(e.target)) {
        mobileNav.hidden = true;
        hamburger.setAttribute('aria-expanded', 'false');
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !mobileNav.hidden) {
        mobileNav.hidden = true;
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.focus();
      }
    });
  }

  /* ============================================================
     2. HEADER SCROLL SHADOW
  ============================================================ */
  const header = document.getElementById('site-header');
  if (header) {
    const onScroll = function () {
      header.classList.toggle('scrolled', window.scrollY > 12);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // run once on load
  }

  /* ============================================================
     3. SCROLL-REVEAL  (.fade-up elements)
  ============================================================ */
  if ('IntersectionObserver' in window) {
    const revealEls = document.querySelectorAll('.fade-up');

    const revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -32px 0px' });

    revealEls.forEach(function (el) {
      revealObserver.observe(el);
    });
  } else {
    // Fallback: just show everything
    document.querySelectorAll('.fade-up').forEach(function (el) {
      el.classList.add('visible');
    });
  }

  /* ============================================================
     4. STAT COUNTER ANIMATION
  ============================================================ */
  function animateCounter(el) {
    const target = parseInt(el.dataset.target || el.textContent, 10);
    if (isNaN(target) || target === 0) return;

    el.textContent = '0';

    const duration = 900;
    const startTime = performance.now();

    function tick(now) {
      const elapsed  = now - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // ease-out cubic
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(eased * target).toLocaleString();
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  if ('IntersectionObserver' in window) {
    const counters = document.querySelectorAll('.sc-val');
    const counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.6 });

    counters.forEach(function (el) {
      // Store original value as data-target before observer fires
      el.dataset.target = el.textContent.trim();
      counterObserver.observe(el);
    });
  }

  /* ============================================================
     5. FORM FOCUS CLASS  (.is-focused on .form-group)
  ============================================================ */
  document.querySelectorAll('.form-control').forEach(function (input) {
    const group = input.closest('.form-group');
    if (!group) return;
    input.addEventListener('focus', function () { group.classList.add('is-focused'); });
    input.addEventListener('blur',  function () { group.classList.remove('is-focused'); });
  });

  /* ============================================================
     6. AUTO-DISMISS SUCCESS ALERTS  (5 s fade-out)
  ============================================================ */
  document.querySelectorAll('.alert-success').forEach(function (alert) {
    const delay  = 5000;
    const fadeDur = 500;

    setTimeout(function () {
      alert.style.transition = 'opacity ' + fadeDur + 'ms ease, transform ' + fadeDur + 'ms ease, max-height ' + fadeDur + 'ms ease, margin-bottom ' + fadeDur + 'ms ease, padding ' + fadeDur + 'ms ease';
      alert.style.opacity   = '0';
      alert.style.transform = 'translateY(-6px)';

      setTimeout(function () {
        alert.style.maxHeight     = '0';
        alert.style.marginBottom  = '0';
        alert.style.paddingTop    = '0';
        alert.style.paddingBottom = '0';
        alert.style.overflow      = 'hidden';
        setTimeout(function () { alert.remove(); }, fadeDur);
      }, fadeDur);
    }, delay);
  });

  /* ============================================================
     7. DYNAMIC SELECT PREVIEW
     When a select with data-preview-target changes,
     update a sibling element with the selected option text.
     (Optional progressive enhancement — no elements use it yet
      but keeps the hook available for future use.)
  ============================================================ */
  document.querySelectorAll('select[data-preview-target]').forEach(function (sel) {
    const targetId = sel.dataset.previewTarget;
    const preview  = document.getElementById(targetId);
    if (!preview) return;

    sel.addEventListener('change', function () {
      const opt = sel.options[sel.selectedIndex];
      preview.textContent = opt ? opt.text : '';
    });
  });

  /* ============================================================
     8. CURRENT YEAR FALLBACK  (.js-year spans)
  ============================================================ */
  document.querySelectorAll('.js-year').forEach(function (el) {
    el.textContent = new Date().getFullYear();
  });

})();
