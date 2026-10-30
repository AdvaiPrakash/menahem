---
name: landing-scaffold
description: Scaffold the BASE of a new landing site — folder structure, stack/config, base design system, plumbing, and one starter Hero section. Does NOT generate full page sections (those need a design reference and are built later, one at a time). Use when starting a new landing/teaser/marketing page from scratch, or when a project needs the Laravel + Blade + Tailwind CSS skeleton and content-as-source-of-truth layout.
---

# Landing site scaffold

Bootstraps the **foundation only** — the skeleton every other landing-page skill builds on: `design-system-tokens`, `scroll-motion`, `web-performance`, `seo-metadata`.

## Scope — what this skill builds, and what it deliberately does NOT

You **cannot** build real landing-page sections without a design/style reference (an image, a Figma, a competitor link, or a detailed visual brief). So this skill stops at the base. It sets up:

- ✅ The **folder structure** + config (Laravel, PHP, Blade, Tailwind CSS, Vite, ESLint where applicable).
- ✅ The **base design system** — wire up the main CSS file with the `@theme` token scaffold and fonts (hand off to `design-system-tokens` to fill real values).
- ✅ Base plumbing: Laravel routes, Blade layout, trivial landing page, `SiteShell`, the shared `Reveal`, `content.php` (empty shape), and the global context or shared state if there's a CTA.
- ✅ **One starter section** (a Hero placeholder) so the development server shows something and there's a pattern to copy.

It does **NOT**:

- ❌ Generate the full set of page sections (Vision, Features, Teaser, …).
- ❌ Invent copy, imagery, or layout for sections without a reference.

**After scaffolding, stop.** Tell the user the base is ready and that each further section needs its own design reference. Build sections one at a time, only once the user provides that reference — never batch-generate them blind.

## ⛔ Intake gate — collect ALL of this before writing any file

Do **not** scaffold blindly. Ask the user for every input below first — use the `AskUserQuestion` tool, batching related questions. If an answer is missing, ambiguous, or "I don't know", ASK; never silently assume, never insert placeholder values without flagging them. Only start creating files once the user has confirmed the answers (echo a short summary back and get a yes).

Required inputs:

1. **Project / package name** (e.g. `acme-landing`).
2. **New project or layer onto an existing repo?** (and the target directory).
3. **What is the site?** product / event / teaser / waitlist / portfolio — and single-page or multi-page.
4. **Brand name + tagline / one-line value proposition.**
5. **Key date or deadline**, if the page is time-bound (launch, event date).
6. **Planned sections (names only, for context)** — just so `content.php` and `SiteShell` are shaped sensibly. Do NOT build them now; only the Hero starter is created. Note that each will be built later from its own design reference.
7. **Primary CTA / conversion goal** — lead modal, buy, signup, WhatsApp, newsletter, or none. (Drives whether the lead-form context or shared state is scaffolded.)
8. **Remote image/asset hosts** that need allowlisting or configuration in the asset pipeline (e.g. Unsplash, a CDN) — or "local assets only".
9. **Version policy** — pin to the versions below, or use current latest-stable (in which case verify them first).

If the user wants to delegate look/feel, motion, SEO, etc., note that those are separate skills (`design-system-tokens`, `scroll-motion`, `seo-metadata`) and their own intake will run when invoked — don't guess those answers here.

## Stack (pin these)

- **Laravel** — PHP framework, using the latest stable release verified before scaffolding.
- **PHP** — use a version supported by the selected Laravel release.
- **Blade** — Laravel's server-rendered templating engine.
- **Tailwind CSS v4** — CSS-first config, no `tailwind.config.js` unless the selected setup specifically requires one.
- **Vite** — frontend asset bundling and development server.
- **JavaScript** — for browser-side interactions and UI behavior where required.
- **Motion** — use a compatible animation approach for scroll reveals and other motion effects.
- **Lenis** — for smooth inertia scrolling.
- **Laravel Pint** — PHP code formatting.
- **ESLint** — for JavaScript linting where applicable.

Dependencies and configuration should follow the official installation requirements of the selected Laravel, PHP, Tailwind CSS, and Vite versions. Do not copy Next.js or React dependencies into the Laravel project.

Scripts and commands should follow the selected Laravel setup. At minimum, document the commands needed to run the Laravel development server, the Vite asset server, and the production asset build.

## Folder structure (replicate the Laravel equivalent)

```text
app/
  Http/
    Controllers/
      LandingPageController.php
resources/
  views/
    layouts/
      app.blade.php       # shared layout, fonts, metadata, providers
    components/
      site-shell.blade.php
      navbar.blade.php
      hero.blade.php
      footer.blade.php
      reveal.blade.php
    landing/
      index.blade.php     # composes the starter Hero only
  css/
    app.css               # Tailwind import + @theme tokens
  js/
    app.js                # client-side behavior and motion setup
  content.php             # SINGLE SOURCE OF TRUTH for all copy + image paths
routes/
  web.php                  # landing page route
public/
  images/                  # static assets grouped by section
  build/                   # generated production assets
vite.config.js
package.json
composer.json
```

Add `lead.php` or a small Laravel service/provider only if shared CTA or lead-form state genuinely requires it. Do not create unnecessary global state for a simple static landing page.

## Core conventions

1. **`content.php` is the only place copy and asset URLs live.** Export structured PHP arrays for `HERO`, `EVENT`, `NAV`, `THEMES`, `IMAGES`, and other required content. Blade views and components read from this source rather than hardcoding website copy. This makes re-skinning the page for a new client a single-file edit.

2. **The landing route stays trivial.** `routes/web.php` should define the landing page route and delegate to `LandingPageController` when a controller is needed. The controller passes content to the Blade view. Keep application logic out of the Blade templates.

3. **`SiteShell` composes sections in narrative order** and owns page-level concerns such as smooth-scroll initialization, footer-reveal scaffolding, and the optional lead modal mount. In Laravel, implement this using Blade components and shared JavaScript/CSS where appropriate.

4. **One component per section**, named for the section. Sections read their content from `content.php` and use the shared `Reveal` component for scroll-reveal behavior.

5. **Shared UI state should use the simplest appropriate Laravel-compatible pattern.** Use Alpine.js or plain JavaScript for small client-side interactions if needed. Use Laravel sessions or backend state only when the feature requires server-side persistence. Avoid adding React or a global React context to a Blade-based project.

## Laravel and frontend configuration essentials

- Use the Laravel directory structure and conventions.
- Configure PHP and Laravel versions according to the official compatibility requirements.
- Configure Vite to load `resources/css/app.css` and `resources/js/app.js`.
- Import Tailwind CSS v4 using its CSS-first setup.
- Define design tokens using the `@theme` scaffold in `resources/css/app.css`.
- Keep environment-specific settings in `.env`; do not commit secrets.
- Use Laravel's named routes and Blade escaping conventions.
- Use `@vite` in the shared Blade layout to load the CSS and JavaScript entry points.
- Use Laravel Pint for PHP formatting and ESLint where JavaScript linting is configured.
- Use the package scripts appropriate to the chosen frontend dependencies.

## Vite

Configure Vite using the official Laravel Vite integration and the compatible Tailwind CSS v4 plugin. Do not introduce Next.js configuration files or scripts.

## Steps to scaffold (base only)

1. Create a new Laravel project using the verified stable Laravel and PHP versions, or lay the structure over an existing Laravel repository.
2. Configure `composer.json`, `package.json`, `vite.config.js`, Tailwind CSS v4, and any required Laravel configuration. Do not overwrite existing project configuration blindly.
3. Create the Laravel application structure: route, controller if needed, shared Blade layout, `SiteShell`, `Navbar`/`Footer` base components, `Reveal`, and an empty-shape `content.php`.
4. Set up the **base design system**: run `design-system-tokens` to establish `resources/css/app.css` (`@theme` tokens + fonts). Then `seo-metadata` for the shared Blade layout and `scroll-motion` for the Lenis setup + `Reveal` component.
5. Build **one starter Hero section** as a placeholder so the Laravel development server renders it. `SiteShell` composes only that for now.
6. Verify the production asset build and Laravel application, fix errors, and confirm the development server runs, then **STOP**.

Then report: "Base is ready. Each section from here needs a design reference — give me one (image / Figma / brief) and I'll build that section." Do not proceed to generate more sections without one.

Always confirm the actual latest stable Laravel, PHP, and Tailwind CSS versions before pinning — bump the version choices if newer stable releases exist and verify compatibility.
