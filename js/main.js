/**
 * さくらねっとHP — main.js v3.0.0
 * MIMEYOI-inspired: Cover / Nav / c-word / Hero Slider / Scroll Animate
 */
(function () {
  'use strict';

  /* -------------------------------------------------------------------------
     1. Cover Loader
     body.is-loading → remove on window load → fade out .l-cover
  ------------------------------------------------------------------------- */
  function initCover() {
    var cover = document.querySelector('.l-cover');
    if (!cover) return;

    document.body.classList.add('is-loading');

    window.addEventListener('load', function () {
      setTimeout(function () {
        document.body.classList.remove('is-loading');
      }, 200);
    });
  }

  /* -------------------------------------------------------------------------
     2. Header — scroll class + hamburger nav
  ------------------------------------------------------------------------- */
  function initHeader() {
    var header  = document.querySelector('.l-header');
    var toggle  = document.querySelector('.l-header_toggle');
    var nav     = document.querySelector('.l-nav');

    if (!header) return;

    // Scroll detection → .is-scrolled
    var onScroll = function () {
      if (window.scrollY > 20) {
        header.classList.add('is-scrolled');
      } else {
        header.classList.remove('is-scrolled');
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Hamburger toggle
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('is-open');
      toggle.classList.toggle('is-open', isOpen);
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      document.body.style.overflow = isOpen ? 'hidden' : '';

      // Animate nav links when opened
      if (isOpen) {
        var words = nav.querySelectorAll('.c-word');
        words.forEach(function (w, i) {
          w.style.setProperty('--order', i);
          w.classList.remove('is-visible');
          w.classList.add('is-hidden');
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              w.classList.remove('is-hidden');
              w.classList.add('is-visible');
            });
          });
        });
      }
    });

    // Close nav on overlay click
    nav.addEventListener('click', function (e) {
      if (e.target === nav) closeNav();
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeNav();
    });

    function closeNav() {
      nav.classList.remove('is-open');
      toggle.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }
  }

  /* -------------------------------------------------------------------------
     3. c-word — Text Animation (IntersectionObserver)
     Each .c-word[data-order] fades up in sequence on scroll into view
  ------------------------------------------------------------------------- */
  function initWordAnimation() {
    var words = document.querySelectorAll('.c-word.js-word');
    if (!words.length || !window.IntersectionObserver) return;

    // Group words by their parent container
    var groups = new Map();
    words.forEach(function (w) {
      var parent = w.closest('[data-word-group]') || w.parentElement;
      if (!groups.has(parent)) groups.set(parent, []);
      groups.get(parent).push(w);
    });

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var groupWords = groups.get(entry.target) || [];
        groupWords.forEach(function (w) {
          var order = parseInt(w.dataset.order || '0', 10);
          w.style.setProperty('--order', order);
          w.classList.add('is-visible');
        });
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.2 });

    groups.forEach(function (_, parent) {
      observer.observe(parent);
    });
  }

  /* -------------------------------------------------------------------------
     4. Hero Slider
     .p-home_hero_list — auto slide every 5s, manual control via
     .p-home_hero_control_item and .p-home_hero_dot
  ------------------------------------------------------------------------- */
  function initHeroSlider() {
    var list     = document.querySelector('.p-home_hero_list');
    var items    = document.querySelectorAll('.p-home_hero_item');
    var controls = document.querySelectorAll('.p-home_hero_control_item');
    var dots     = document.querySelectorAll('.p-home_hero_dot');

    if (!list || items.length < 2) return;

    var current  = 0;
    var total    = items.length;
    var timer    = null;
    var INTERVAL = 10000;

    function goTo(index) {
      current = (index + total) % total;
      list.style.transform = 'translateX(-' + (current * 100) + '%)';

      controls.forEach(function (c, i) {
        c.classList.toggle('is-active', i === current);
      });
      dots.forEach(function (d, i) {
        d.classList.toggle('is-active', i === current);
      });
    }

    function startAuto() {
      clearInterval(timer);
      timer = setInterval(function () {
        goTo(current + 1);
      }, INTERVAL);
    }

    // Control click
    controls.forEach(function (c, i) {
      c.addEventListener('click', function () {
        goTo(i);
        startAuto();
      });
    });

    // Dot click
    dots.forEach(function (d, i) {
      d.addEventListener('click', function () {
        goTo(i);
        startAuto();
      });
    });

    // Touch swipe
    var touchStartX = 0;
    list.addEventListener('touchstart', function (e) {
      touchStartX = e.touches[0].clientX;
    }, { passive: true });

    list.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) > 50) {
        goTo(dx < 0 ? current + 1 : current - 1);
        startAuto();
      }
    }, { passive: true });

    // Init
    goTo(0);
    startAuto();
  }

  /* -------------------------------------------------------------------------
     5. Scroll Animation (.js-anim → .is-visible)
  ------------------------------------------------------------------------- */
  function initScrollAnim() {
    var elems = document.querySelectorAll('.js-anim');
    if (!elems.length || !window.IntersectionObserver) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    elems.forEach(function (el) { observer.observe(el); });
  }

  /* -------------------------------------------------------------------------
     6. Smooth Scroll for anchor links
  ------------------------------------------------------------------------- */
  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var id = a.getAttribute('href').slice(1);
        if (!id) return;
        var target = document.getElementById(id);
        if (!target) return;
        e.preventDefault();
        var offset = 80; // header height
        var top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
      });
    });
  }

  /* -------------------------------------------------------------------------
     7. Nav hover image (overlay nav image update)
  ------------------------------------------------------------------------- */
  function initNavImage() {
    var navItems  = document.querySelectorAll('.l-nav_gnav a[data-img]');
    var navImage  = document.querySelector('.l-nav_image img');
    var navTitle  = document.querySelector('.l-nav_image_info h3');
    var navCaption = document.querySelector('.l-nav_image_info p');

    if (!navItems.length || !navImage) return;

    navItems.forEach(function (item) {
      item.addEventListener('mouseenter', function () {
        var img = item.dataset.img;
        var title = item.dataset.title;
        var caption = item.dataset.caption;
        if (img) navImage.src = img;
        if (title && navTitle) navTitle.textContent = title;
        if (caption && navCaption) navCaption.textContent = caption;
      });
    });
  }

  /* -------------------------------------------------------------------------
     Init all
  ------------------------------------------------------------------------- */
  function init() {
    initCover();
    initHeader();
    initHeroSlider();
    initWordAnimation();
    initScrollAnim();
    initSmoothScroll();
    initNavImage();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
