/**
 * search.js — SIMS Search System
 * Handles live suggestions (AJAX) and keyboard navigation.
 * All DB queries are performed server-side via search_api.php.
 */
(function () {
  'use strict';

  /* ── Utility ─────────────────────────────────────────────── */
  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /** Wrap matching text in <mark> */
  function highlight(text, query) {
    if (!query) return escHtml(text);
    var escaped  = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var regex    = new RegExp('(' + escaped + ')', 'gi');
    return escHtml(text).replace(regex, '<mark>$1</mark>');
  }

  /* ── Live suggestions on search.php ─────────────────────── */
  var mainInput   = document.getElementById('search-main-input');
  var suggestions = document.getElementById('search-suggestions');
  var typeSelect  = document.getElementById('search-type');
  var debounceTimer;

  if (mainInput && suggestions) {

    var apiBase = mainInput.getAttribute('data-api') || 'search_api.php';
    var focusIdx = -1;  // keyboard-navigation index

    function fetchSuggestions(q) {
      var type = typeSelect ? typeSelect.value : 'all';
      if (q.length < 2) { hideSuggestions(); return; }

      suggestions.innerHTML = '<div class="search-sug-loading">Searching…</div>';
      suggestions.classList.add('visible');
      focusIdx = -1;

      var url = apiBase + '?q=' + encodeURIComponent(q) +
                '&type=' + encodeURIComponent(type) +
                '&limit=5&format=suggestions';

      fetch(url, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) { renderSuggestions(data, q); })
        .catch(function () {
          suggestions.innerHTML = '<div class="search-sug-empty">Search unavailable.</div>';
        });
    }

    function renderSuggestions(data, q) {
      if (!data.results || data.total === 0) {
        suggestions.innerHTML =
          '<div class="search-sug-empty">No matches for &ldquo;' + escHtml(q) + '&rdquo;.</div>';
        return;
      }

      var html = '';
      var groups = {
        students:    'Students',
        courses:     'Courses',
        instructors: 'Instructors',
        departments: 'Departments',
        classrooms:  'Classrooms',
        classes:     'Classes',
        enrollments: 'Enrollments',
      };

      Object.keys(groups).forEach(function (group) {
        var items = data.results[group];
        if (!items || items.length === 0) return;
        html += '<div class="search-sug-group-label">' + groups[group] + '</div>';
        items.forEach(function (item) {
          // Build href to full search page with this item pre-selected
          var href = 'search.php?q=' + encodeURIComponent(q) +
                     '&type=' + encodeURIComponent(group);
          html += '<a class="search-sug-item" href="' + href + '">' +
            '<span class="search-sug-name">' + highlight(item.name, q) + '</span>' +
            '<span class="search-sug-meta">' + escHtml(item.meta || '') + '</span>' +
            '</a>';
        });
      });

      suggestions.innerHTML = html;
    }

    function hideSuggestions() {
      suggestions.classList.remove('visible');
      suggestions.innerHTML = '';
      focusIdx = -1;
    }

    /* Input event — debounced 280ms */
    mainInput.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      var q = this.value.trim();
      if (q.length < 2) { hideSuggestions(); return; }
      debounceTimer = setTimeout(function () { fetchSuggestions(q); }, 280);
    });

    /* Keyboard navigation */
    mainInput.addEventListener('keydown', function (e) {
      var items = suggestions.querySelectorAll('.search-sug-item');
      if (!items.length) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        focusIdx = Math.min(focusIdx + 1, items.length - 1);
        updateFocus(items);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        focusIdx = Math.max(focusIdx - 1, -1);
        updateFocus(items);
      } else if (e.key === 'Enter' && focusIdx >= 0) {
        e.preventDefault();
        items[focusIdx].click();
      } else if (e.key === 'Escape') {
        hideSuggestions();
        mainInput.blur();
      }
    });

    function updateFocus(items) {
      items.forEach(function (el, i) {
        el.classList.toggle('focused', i === focusIdx);
        if (i === focusIdx) {
          mainInput.value = el.querySelector('.search-sug-name').textContent;
        }
      });
    }

    /* Close on outside click */
    document.addEventListener('click', function (e) {
      if (!mainInput.contains(e.target) && !suggestions.contains(e.target)) {
        hideSuggestions();
      }
    });

    /* Re-fetch when type changes */
    if (typeSelect) {
      typeSelect.addEventListener('change', function () {
        var q = mainInput.value.trim();
        if (q.length >= 2) fetchSuggestions(q);
      });
    }
  }

  /* ── Chip filter clicks → update select + resubmit ──────── */
  document.querySelectorAll('.search-chip[data-type]').forEach(function (chip) {
    chip.addEventListener('click', function () {
      var type = this.getAttribute('data-type');
      if (typeSelect) {
        typeSelect.value = type;
        typeSelect.dispatchEvent(new Event('change'));
      }
      // Update active chip style
      document.querySelectorAll('.search-chip').forEach(function (c) {
        c.classList.toggle('search-chip--active', c === chip);
      });
      // Auto-submit if there is a query
      var form = document.getElementById('search-form');
      if (form && mainInput && mainInput.value.trim()) {
        form.submit();
      }
    });
  });

  /* ── Sync chip active state with select on load ──────────── */
  if (typeSelect) {
    document.querySelectorAll('.search-chip[data-type]').forEach(function (chip) {
      if (chip.getAttribute('data-type') === typeSelect.value) {
        chip.classList.add('search-chip--active');
      }
    });
  }

  /* ── Admin topbar search (search_api redirect) ───────────── */
  var topbarForm = document.getElementById('topbar-search-form');
  if (topbarForm) {
    topbarForm.addEventListener('submit', function (e) {
      var inp = this.querySelector('input[name="q"]');
      if (!inp || !inp.value.trim()) {
        e.preventDefault();
        inp && inp.focus();
      }
      // Otherwise let the form submit to search.php
    });
  }

  /* ── Public header search (points to admin search page) ──── */
  var pubHeaderForm = document.getElementById('pub-header-search');
  if (pubHeaderForm) {
    pubHeaderForm.addEventListener('submit', function (e) {
      var inp = this.querySelector('input[name="q"]');
      if (!inp || !inp.value.trim()) {
        e.preventDefault();
        inp && inp.focus();
      }
    });
  }

})();
