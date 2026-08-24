---
name: Serene Stone & Silver
colors:
  surface: '#faf8fd'
  surface-dim: '#dbd9de'
  surface-bright: '#faf8fd'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f3f7'
  surface-container: '#efedf2'
  surface-container-high: '#e9e7ec'
  surface-container-highest: '#e3e2e6'
  on-surface: '#1b1b1f'
  on-surface-variant: '#44474f'
  inverse-surface: '#2f3034'
  inverse-on-surface: '#f2f0f4'
  outline: '#757780'
  outline-variant: '#c5c6d0'
  surface-tint: '#485e8d'
  primary: '#000619'
  on-primary: '#ffffff'
  primary-container: '#001d4a'
  on-primary-container: '#7086b9'
  inverse-primary: '#b0c6fc'
  secondary: '#5a5f62'
  on-secondary: '#ffffff'
  secondary-container: '#dce0e3'
  on-secondary-container: '#5f6366'
  tertiary: '#120300'
  on-tertiary: '#ffffff'
  tertiary-container: '#3c1100'
  on-tertiary-container: '#bb7558'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d9e2ff'
  primary-fixed-dim: '#b0c6fc'
  on-primary-fixed: '#001a43'
  on-primary-fixed-variant: '#2f4674'
  secondary-fixed: '#dfe3e6'
  secondary-fixed-dim: '#c3c7ca'
  on-secondary-fixed: '#181c1f'
  on-secondary-fixed-variant: '#43474a'
  tertiary-fixed: '#ffdbcd'
  tertiary-fixed-dim: '#ffb597'
  on-tertiary-fixed: '#360f00'
  on-tertiary-fixed-variant: '#6f371f'
  background: '#faf8fd'
  on-background: '#1b1b1f'
  surface-variant: '#e3e2e6'
  silver-leaf: '#C0C0C0'
  deep-navy: '#000C1F'
  zen-white: '#FDFDFD'
  mist-gray: '#94A3B8'
typography:
  headline-display:
    fontFamily: EB Garamond
    fontSize: 48px
    fontWeight: '500'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: EB Garamond
    fontSize: 32px
    fontWeight: '500'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: EB Garamond
    fontSize: 28px
    fontWeight: '500'
    lineHeight: 36px
  headline-md:
    fontFamily: EB Garamond
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
  max-width: 1280px
---

## Brand & Style

The design system is centered on the "Modern Zen" aesthetic—a fusion of high-end craftsmanship and spiritual tranquility. It targets a premium audience seeking sophisticated garden decor and indoor sanctuaries. The interface must evoke a sense of calm, using expansive negative space to allow the intricate details of the statues to breathe.

The style is **Minimalist with Tactile accents**. By combining clean, geometric layouts with subtle metallic gradients and stone-like textures, the system mirrors the physical materials of the products (cement, marble, and plaster). The experience is meditative, emphasizing slow discovery rather than hurried commerce.

## Colors

The palette is anchored by a deep **Navy Blue**, representing wisdom and depth, and **Prata (Silver)**, representing clarity and the metallic finish of premium craftsmanship. 

- **Primary (Navy):** Used for primary typography, structural icons, and deep backgrounds to provide a grounded, authoritative feel.
- **Secondary (Prata/Silver):** Used for accents, borders, and subtle gradients that mimic the reflection of light on polished stone or metal.
- **Neutral (Zen White/Mist):** The "Zen White" provides a non-pure white background that reduces eye strain, while "Mist Gray" handles secondary information and disabled states.
- **Functional:** Interactive elements leverage Navy for strength, while feedback states should remain muted and understated to maintain the serene atmosphere.

## Typography

This design system uses a sophisticated typographic pairing to balance heritage and modernity.

**EB Garamond** (Serif) is the display face. Its classical proportions and elegant serifs reflect the 25-year history of the brand and the timeless nature of the statues. It should be used for all headers and editorial callouts.

**Manrope** (Sans-serif) is the functional face. It is highly legible and modern, providing a clean contrast to the serif headings. It is used for body copy, product specs, and navigation. 

**Formatting Note:** Product reference numbers (e.g., "Buda Ref: 016") should always use the `label-sm` style with increased letter spacing to give them a "catalog-archival" appearance.

## Layout & Spacing

The layout philosophy follows a **Fixed Grid with Fluid Internal Gutters**. To maintain a "Zen" feeling, the system mandates aggressive use of whitespace—sections should be separated by large vertical gaps (80px to 120px) to prevent visual clutter.

- **Grid:** 12-column system for desktop, 4-column for mobile.
- **Alignment:** Centralized content containers with wide margins to create a "gallery" feel.
- **Rhythm:** An 8px linear scale guides all internal component spacing, ensuring a disciplined but airy composition. 
- **Reflow:** On mobile, product grids collapse to 1 or 2 columns maximum to ensure product photography remains impactful.

## Elevation & Depth

Depth in this design system is created through **Tonal Layers** and **Backdrop Blurs** rather than traditional heavy shadows.

- **Surfaces:** Main content lives on a "Zen White" base. Cards and secondary containers use a subtle "Prata" tint or a 1px border in `secondary-color`.
- **Shadows:** Use only "Ambient Shadows"—extremely soft, low-opacity (#000000 @ 4%), and large blur radii. They should feel like a soft glow rather than a physical shadow.
- **Interactive Depth:** When hovering over product cards, a subtle scale-up (1.02x) combined with a slightly deeper ambient shadow indicates interactivity without breaking the calm aesthetic.

## Shapes

The shape language is **Soft and Architectural**. 

We avoid sharp, aggressive corners to maintain the "Zen" personality, but we also avoid fully rounded/pill shapes which can feel too "tech" or casual. A standard `0.25rem` (4px) radius is used for primary UI elements like buttons and input fields, while larger containers like cards use `0.5rem` (8px). This creates a look that feels carved and intentional, similar to a stone plinth.

## Components

### Buttons
- **Primary:** Deep Navy background, white Manrope text (semi-bold). No border.
- **Secondary:** Transparent background with a 1px Prata/Silver border. 
- **Style:** All caps with `0.05em` letter spacing to evoke a premium, editorial feel.

### Cards
- **Product Gallery:** Borderless with a light gray background tint. Images should have a consistent aspect ratio (4:5) to accommodate tall Buddha statues. Title and Reference number are placed below the image with generous padding.

### Inputs & Selects
- Underlined style or very light 1px borders. Focus states should transition the border color to Navy smoothly. Typography inside inputs should be `body-md`.

### Chips / Tags
- Used for categories like "Lançamentos" or "Mármore". These are small, non-rounded rectangles with a light Prata background and Navy text.

### Navigation
- A centered, minimalist top bar. Navigation links are in `label-sm` style. The logo should be centered or top-left, scaled to maintain its intricate detail without dominating the viewport.

### Dividers
- Use thin, 1px horizontal lines in `secondary-color` to separate major content blocks. Do not use dotted or dashed lines; keep them solid and continuous to represent stability.