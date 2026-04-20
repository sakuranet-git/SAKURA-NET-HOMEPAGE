/* scroll-animate.js — Phase 3-D v2.9.19 */
(function () {
  if (!window.IntersectionObserver) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var selectors = [
    '.sp-page-title',
    '.unified-cta',
    '.pricing-summary',
    '#hero-flex'
  ].join(',');

  function init() {
    var elements = document.querySelectorAll(selectors);
    if (!elements.length) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.remove('sa-hidden');
          entry.target.classList.add('sa-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -32px 0px' });

    elements.forEach(function (el) {
      el.classList.add('sa-hidden');
      observer.observe(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
