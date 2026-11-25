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
  input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]), select, textarea {
    border-color: var(--line) !important;
    border-radius: .65rem !important;
    background-color: var(--surface);
    color: #29394b;
    transition: border-color .16s ease, box-shadow .16s ease;
  }
  input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):focus,
  select:focus, textarea:focus {
    border-color: #c47766 !important;
    box-shadow: 0 0 0 3px rgb(200 78 53 / 12%);
    outline: none;
  }
  input::placeholder, textarea::placeholder { color: #8b99a8; }
  button, [role="button"] { transition-timing-function: ease; }
  table { border-collapse: separate; border-spacing: 0; }
  th { color: #536579; font-size: .7rem; letter-spacing: .09em; text-transform: uppercase; }
  td, th { border-color: var(--line); }
  tbody tr:hover { background-color: #f8fafb; }

  /* Keep the existing utility based pages, but give their shared surfaces one visual language. */
  [class~="bg-[#FF5C3D]"] { background-color: #c84e35 !important; }
  [class~="bg-[#101E33]"] { background-color: #172b43 !important; }
  [class~="text-[#FF5C3D]"] { color: #c84e35 !important; }
  [class~="text-[#C23A1F]"] { color: #a83b27 !important; }
  [class~="text-[#101E33]"] { color: #172b43 !important; }
  [class~="border-[#FF5C3D]"] { border-color: #c84e35 !important; }
  [class~="hover:bg-[#e94f32]"]:hover { background-color: #a83b27 !important; }
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
  .member-workspace-nav { position: relative; z-index: 30; }
  .member-workspace-nav nav { min-height: 3.25rem; }
  .member-workspace-toggle[aria-expanded="true"] svg,
  .workspace-nav-group[open] > summary svg { transform: rotate(180deg); }
  .member-workspace-toggle[aria-expanded="true"] { border-color: var(--brand); }
  .workspace-nav-group > ul { padding-left: 0; }
  .workspace-nav-group > summary { min-height: 2.25rem; }
  .auth-brand-panel { background-image: radial-gradient(38rem 24rem at 90% 0%, rgb(255 92 61 / 16%), transparent 65%), linear-gradient(145deg, #101e33, #1c334c) !important; }
  .auth-brand-panel img { max-width: 10rem; }

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
  @media (max-width: 640px) {
    .member-workspace-nav nav { overflow: visible; flex-wrap: wrap; }
    .member-workspace-menu { position: absolute; left: 0; right: auto; top: 100%; width: min(18rem, calc(100vw - 2rem)); min-width: 0; }
    .member-workspace-group:last-child .member-workspace-menu { left: auto; right: 0; }
  }
</style>
