---
name: Zen Earth & Stone
colors:
  surface: '#fcf9f2'
  surface-dim: '#dcdad3'
  surface-bright: '#fcf9f2'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3ec'
  surface-container: '#f0eee7'
  surface-container-high: '#ebe8e1'
  surface-container-highest: '#e5e2db'
  on-surface: '#1c1c18'
  on-surface-variant: '#54433f'
  inverse-surface: '#31312c'
  inverse-on-surface: '#f3f0ea'
  outline: '#87736e'
  outline-variant: '#dac1bb'
  surface-tint: '#934936'
  primary: '#531909'
  on-primary: '#ffffff'
  primary-container: '#702e1d'
  on-primary-container: '#f4977f'
  inverse-primary: '#ffb4a2'
  secondary: '#82542d'
  on-secondary: '#ffffff'
  secondary-container: '#fdbf90'
  on-secondary-container: '#784c26'
  tertiary: '#442500'
  on-tertiary: '#ffffff'
  tertiary-container: '#613907'
  on-tertiary-container: '#dda369'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdbd2'
  primary-fixed-dim: '#ffb4a2'
  on-primary-fixed: '#3c0800'
  on-primary-fixed-variant: '#763221'
  secondary-fixed: '#ffdcc3'
  secondary-fixed-dim: '#f7ba8a'
  on-secondary-fixed: '#2f1500'
  on-secondary-fixed-variant: '#663d18'
  tertiary-fixed: '#ffdcbd'
  tertiary-fixed-dim: '#f7bb7e'
  on-tertiary-fixed: '#2c1600'
  on-tertiary-fixed-variant: '#663d0b'
  background: '#fcf9f2'
  on-background: '#1c1c18'
  surface-variant: '#e5e2db'
  terracotta: '#8E4432'
  clay-brown: '#5D4037'
  siena: '#A47148'
  sand-beige: '#D9A066'
  linen: '#F4F1EA'
  aged-paper: '#E8E2D2'
  glass-surface: rgba(253, 253, 253, 0.7)
typography:
  headline-display:
    fontFamily: Eb Garamond
    fontSize: 48px
    fontWeight: '500'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Eb Garamond
    fontSize: 32px
    fontWeight: '500'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Eb Garamond
    fontSize: 28px
    fontWeight: '500'
    lineHeight: 36px
  headline-md:
    fontFamily: Eb Garamond
    fontSize: 24px
    fontWeight: '500'
    lineHeight: 32px
  body-lg:
    fontFamily: Manrope
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Manrope
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-sm:
    fontFamily: Manrope
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.1em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 8px
  gutter: 24px
  margin-mobile: 20px
  margin-desktop: 64px
  section-gap: 104px
  max-width: 1280px
---

## Brand & Style
The brand identity is rooted in **organic sophistication** and **timeless craft**. It targets a high-end audience seeking tranquility, natural beauty, and artisanal quality for their living spaces. 

The design style is a blend of **Minimalism** and **Glassmorphism**, specifically tailored for a luxury "Natural/Zen" aesthetic. It emphasizes breathability through heavy whitespace and wide section gaps, while using frosted glass panels to soften the interface and allow rich, earthy product photography to bleed through. The emotional response should be one of "calm authority"—trustworthy, premium, and deeply connected to nature.

## Colors
The palette is inspired by raw materials: clay, terracotta, and sand. 

- **Primary (Terracotta/Deep Earth):** Used for headlines, primary buttons, and high-contrast accents. It provides the "weight" in the design.
- **Secondary (Siena/Wood):** Used for sub-navigation, secondary text, and decorative elements like separators.
- **Neutral (Linen/Bone):** The background is a soft, off-white (#fcf9f2) that feels warmer and more organic than pure white, reducing eye strain and reinforcing the "aged paper" feel.
- **Named Accents:** Higher saturation tones like `sand-beige` are reserved for subtle highlighting or interactive states.

## Typography
The system uses a high-contrast pairing between a **Classical Serif** and a **Contemporary Sans**.

- **Eb Garamond (Serif):** Used for all headlines to evoke a sense of history, literature, and premium craftsmanship. It should always appear with slightly tighter letter-spacing for large displays.
- **Manrope (Sans-Serif):** Chosen for its balanced, geometric yet friendly proportions. It handles all functional text, body copy, and UI labels, ensuring modern legibility.
- **Styling Note:** Labels should always be uppercase with generous tracking (letter-spacing) to create a "gallery-like" archival feel.

## Layout & Spacing
The system utilizes a **Fixed-Width Grid** with generous vertical rhythm.

- **Content Width:** Contained to a maximum of 1280px to maintain readability on ultra-wide screens.
- **Section Spacing:** A standardized `section-gap` of 104px ensures that distinct narrative blocks (Hero, Gallery, About) are clearly separated by breathing room.
- **Bento Grid:** For categories, use a multi-span grid where items can span 2 columns to create visual hierarchy (e.g., "Featured" vs "Standard" categories).
- **Mobile Adaptation:** Side margins shrink to 20px, and section gaps should be reduced to 64px to maintain momentum.

## Elevation & Depth
Hierarchy is established through **Glassmorphism** and **Subtle Tonal Layering** rather than aggressive shadows.

- **Surface Layers:** The base layer is `background`. Overlays use `glass-panel` styling: a semi-transparent white (#fdfdfd at 70% opacity) with a 12px backdrop blur and a very soft 1px border.
- **Shadows:** Only one "Zen Shadow" is permitted—an extremely diffused `0 10px 40px rgba(0,0,0,0.04)`. It should feel like an ambient occlusion rather than a light source shadow.
- **Interactive Depth:** Cards should use scale-up transforms (105%) on hover rather than increasing shadow depth, keeping the UI feeling light and ethereal.

## Shapes
The shape language is primarily **Soft-Rectangular**.

- **Base Radius:** Elements like buttons and smaller UI components use a 0.125rem (2px) radius, appearing nearly sharp but slightly softened.
- **Container Radius:** Larger containers like Glass Panels or Gallery Cards use `rounded-xl` (0.5rem) to feel more inviting and modern.
- **Interactive Elements:** Navigation icons and small status badges (e.g., "New") use full pill-rounding to distinguish them from structural content.

## Components

### Buttons
- **Primary:** Solid `primary` color, white text, uppercase `label-sm` font, tracking-wider. No rounding or minimal 2px rounding.
- **Outline:** 1px border of `secondary` color, transparent background, used for secondary actions like "View Location".

### Cards & Imagery
- **Product Cards:** Aspect ratio of 4:5. Use `zen-shadow` and `rounded-sm`. On hover, the image should scale slightly, and the title color should shift to `surface-tint`.
- **Bento Cards:** Use 1px internal borders or subtle gradients to ensure text remains legible over photography.

### Navigation
- **Top Bar:** Fixed, `surface-container-lowest` background with a subtle bottom shadow. Links are uppercase `body-md` with an active state indicated by a 2px bottom border in the `primary` color.

### Inputs & Fields
- **Search/Person/Bag Icons:** Use `Material Symbols Outlined`. Interactive states should show a soft circular background hover effect in `surface-container-low`.