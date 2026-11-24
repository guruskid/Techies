<style>
  :root {
    color-scheme: light;
    --brand: #c84e35;
    --brand-strong: #a83b27;
    --ink: #172b43;
    --paper: #f4f6f8;
    --line: #dfe5eb;
    --muted: #607184;
    --surface: #ffffff;
    --shadow-soft: 0 2px 8px rgb(23 43 67 / 4%), 0 18px 42px -34px rgb(23 43 67 / 28%);
  }

  html { scroll-behavior: smooth; }
  body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: var(--paper); color: #29394b; }
  h1, h2, h3, h4, .font-display { font-family: Manrope, Inter, ui-sans-serif, sans-serif; letter-spacing: -.025em; }
  [x-cloak] { display: none !important; }
  :focus-visible { outline: 2px solid var(--brand); outline-offset: 3px; }

  /* Keep the existing utility based pages, but give their shared surfaces one visual language. */
  [class*="border-slate-100"], [class*="border-slate-200"] { border-color: var(--line) !important; }
  .bg-white.rounded-2xl, .bg-white.rounded-xl { box-shadow: var(--shadow-soft); }
  .card-lift { transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
  .card-lift:hover { transform: translateY(-2px); box-shadow: 0 18px 38px -28px rgb(23 43 67 / 40%); border-color: #cbd5df; }
  .btn-glow { box-shadow: 0 8px 20px -12px rgb(200 78 53 / 58%); }

  /* Shared public and workspace chrome */
  body > header, header.sticky { box-shadow: 0 1px 0 rgb(23 43 67 / 4%); }
  header.sticky nav a[aria-current="page"] { position: relative; }
  main { min-height: 60vh; }
  footer { background: #172b43 !important; }
  footer a { transition: color .16s ease; }

  /* Reading surfaces */
  .prose-tech { max-width: 70ch; color: #526477; line-height: 1.8; }
  .prose-tech h2, .prose-tech h3 { color: var(--ink); margin-top: 2rem; margin-bottom: .75rem; }
  .prose-tech h2 { font-size: 1.5rem; font-weight: 750; }
  .prose-tech h3 { font-weight: 700; }
  .prose-tech p { margin-bottom: 1rem; }
  .prose-tech ul { list-style: disc; padding-left: 1.25rem; margin-bottom: 1rem; }
  .prose-tech ol { list-style: decimal; padding-left: 1.25rem; margin-bottom: 1rem; }
  .prose-tech blockquote { border-left: 3px solid var(--brand); padding-left: 1rem; color: #394d62; font-style: italic; margin: 1.25rem 0; }
  .prose-tech a { color: var(--brand-strong); text-decoration: underline; text-underline-offset: 3px; }
  .prose-tech img { border-radius: .9rem; margin: 1.25rem 0; }

  .tech-grid { background-image: linear-gradient(rgb(255 255 255 / 5%) 1px, transparent 1px), linear-gradient(90deg, rgb(255 255 255 / 5%) 1px, transparent 1px); background-size: 44px 44px; }
  .tech-grid-dark { background-image: linear-gradient(rgb(23 43 67 / 4%) 1px, transparent 1px), linear-gradient(90deg, rgb(23 43 67 / 4%) 1px, transparent 1px); background-size: 44px 44px; }
  .glass { background: rgb(255 255 255 / 6%); border: 1px solid rgb(255 255 255 / 12%); backdrop-filter: blur(16px); }
  .text-gradient { background: linear-gradient(92deg, #fff 10%, #f6d5cd 55%, #efaa9c 100%); -webkit-background-clip: text; background-clip: text; color: transparent; }
  .aurora { background: radial-gradient(42rem 42rem at 82% 18%, rgb(200 78 53 / 18%), transparent 62%), radial-gradient(34rem 34rem at 12% 82%, rgb(90 129 148 / 14%), transparent 60%), linear-gradient(180deg, #14273c 0%, #172b43 60%, #1b3048 100%); }
  .section-label { font-size: .72rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--brand-strong); }

  @media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
  }
</style>
