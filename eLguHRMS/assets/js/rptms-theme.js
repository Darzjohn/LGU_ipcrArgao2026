// ============================================================
// RPTMS Theme JS (Auth Pages + Animations)
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
  // Smooth fade-in animation for auth card
  const card = document.querySelector('.auth-card');
  if (card) {
    card.style.opacity = '0';
    setTimeout(() => {
      card.style.transition = 'opacity 0.8s ease-in-out';
      card.style.opacity = '1';
    }, 150);
  }

  // Add subtle float animation to logo
  const logo = document.querySelector('.auth-card img');
  if (logo) {
    setInterval(() => {
      logo.animate([
        { transform: 'translateY(0px)' },
        { transform: 'translateY(-4px)' },
        { transform: 'translateY(0px)' }
      ], {
        duration: 2500,
        iterations: 1
      });
    }, 3000);
  }

  // Fade in footer if available
  const footer = document.querySelector('.rptms-footer');
  if (footer) {
    footer.style.transition = 'opacity 1.2s ease';
    footer.style.opacity = '1';
  }
});
