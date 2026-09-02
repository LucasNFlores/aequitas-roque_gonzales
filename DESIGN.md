---
name: Prestige Legal
colors:
  surface: '#f9f9f8'
  surface-dim: '#dadad9'
  surface-bright: '#f9f9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f3'
  surface-container: '#eeeeed'
  surface-container-high: '#e8e8e7'
  surface-container-highest: '#e2e2e2'
  on-surface: '#1a1c1c'
  on-surface-variant: '#45464d'
  inverse-surface: '#2f3130'
  inverse-on-surface: '#f1f1f0'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#735c00'
  on-secondary: '#ffffff'
  secondary-container: '#fed65b'
  on-secondary-container: '#745c00'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#1e1b19'
  on-tertiary-container: '#888380'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#ffe088'
  secondary-fixed-dim: '#e9c349'
  on-secondary-fixed: '#241a00'
  on-secondary-fixed-variant: '#574500'
  tertiary-fixed: '#e9e1dd'
  tertiary-fixed-dim: '#ccc5c2'
  on-tertiary-fixed: '#1e1b19'
  on-tertiary-fixed-variant: '#4a4643'
  background: '#f9f9f8'
  on-background: '#1a1c1c'
  surface-variant: '#e2e2e2'
typography:
  display-lg:
    fontFamily: Playfair Display
    fontSize: 64px
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Playfair Display
    fontSize: 48px
    fontWeight: '600'
    lineHeight: '1.2'
  headline-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.2'
  headline-md:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '500'
    lineHeight: '1.3'
  headline-sm:
    fontFamily: Playfair Display
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Montserrat
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Montserrat
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-md:
    fontFamily: Montserrat
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: 0.1em
  label-sm:
    fontFamily: Montserrat
    fontSize: 12px
    fontWeight: '500'
    lineHeight: '1.2'
    letterSpacing: 0.05em
spacing:
  unit: 8px
  container-max: 1280px
  gutter: 24px
  margin-desktop: 64px
  margin-mobile: 20px
---

## Brand & Style
The design system is engineered to evoke an atmosphere of exclusivity, historical authority, and modern precision. It serves high-net-worth clients and corporate entities who value discretion and excellence. 

The aesthetic direction is **Refined Luxury**, a blend of classical editorial layouts and modern high-end digital interfaces. It utilizes a "Paper and Gold" metaphor: 
- **Minimalism:** Massive amounts of negative space to convey clarity and focus.
- **Editorial Influence:** High-contrast typography and intentional asymmetric balance.
- **Prestige Details:** Thin metallic strokes and serif-driven hierarchy that mirrors traditional legal parchment and gold-leaf embossing.

## Colors
The palette is rooted in deep, authoritative tones and warm, metallic accents to differentiate from standard corporate blues.

- **Midnight Navy (#0F172A):** Used for primary navigation, deep headers, and high-importance interaction states. It represents stability and depth.
- **Champagne Gold (#D4AF37):** Reserved for primary calls to action, active indicators, and thin decorative borders. It should be used sparingly to maintain its "precious" quality.
- **Paper White (#FAFAF9):** The foundational background color, providing a softer, more luxurious feel than pure white.
- **Deep Stone (#1C1917):** Primary text color, offering high legibility with a warmer, more organic feel than black.

## Typography
The typographic hierarchy relies on the tension between the expressive **Playfair Display** and the architectural **Montserrat**.

- **Headlines:** Use Playfair Display for all semantic headings. For high-end marketing sections, use italicized serif weights to emphasize specific words.
- **Body:** Montserrat provides a clean, neutral counterpoint. Ensure generous line-height (1.6) to facilitate readability of complex legal text.
- **Captions & Labels:** Always use Montserrat in medium or semi-bold weights. All-caps styling with increased letter spacing (0.1em) is preferred for secondary navigation and small labels to reinforce the luxury brand feel.

## Layout & Spacing
This design system utilizes a **Fixed Grid** model for desktop to ensure content remains centered and readable, reflecting a controlled, professional environment.

- **Rhythm:** An 8px base unit governs all padding and margins. 
- **Desktop:** 12-column grid with a 1280px max-width. Use "Airy" margins (64px+) to prevent the interface from feeling cluttered.
- **Mobile:** 4-column grid with 20px side margins.
- **Sectioning:** Vertical spacing between major sections should be aggressive (96px to 160px) to allow the brand elements to "breathe."

## Elevation & Depth
Depth is created through **Tonal Layering** and **Subtle Shadows** rather than heavy gradients.

- **Surfaces:** Use `#FFFFFF` for cards and containers sitting on the `#FAFAF9` background.
- **Shadows:** Utilize "Ambient Shadows"—extremely low opacity (4-8%) with a large blur radius (24px-48px). The shadow should feel like a soft glow rather than a hard drop.
- **Borders:** Thin (1px) borders in Champagne Gold are used to define the most important structural elements, such as the main navigation bar or primary call-out cards.
- **Glassmorphism:** Use sparingly for fixed navigation backgrounds (10px blur, 80% opacity white) to maintain context while scrolling.

## Shapes
The shape language is **Sharp and Architectural**. 

- **Corners:** 0px radius (Sharp) is the default for buttons, cards, and input fields. This communicates precision, formality, and legal "edge."
- **Dividers:** Horizontal and vertical rules should be 1px thick. Use a 20% opacity of Midnight Navy for standard dividers, and 100% Champagne Gold for high-impact section breaks.

## Components

- **Buttons:** 
  - *Primary:* Solid Midnight Navy background with white text. No border. Sharp corners.
  - *Secondary:* Transparent background with a 1px Champagne Gold border.
  - *Interaction:* On hover, the primary button should transition to a subtle gold-leaf gradient or a gold border.
- **Input Fields:** 
  - Underline style preferred. A 1px Deep Stone bottom border that turns Champagne Gold on focus. Label text should be Label-MD (uppercase).
- **Cards:** 
  - Flat white background with a 1px soft-stone border. On hover, apply a subtle ambient shadow and a top-edge 2px gold accent line.
- **Chips/Tags:** 
  - Small, rectangular, with a very light Midnight Navy tint (5% opacity) and dark navy text.
- **Icons:** 
  - Ultra-thin (1pt stroke) monoline icons. Avoid filled icons; stick to geometric, minimalist representations.
- **Legal Tables:** 
  - High density with clear 1px horizontal dividers. Header rows should use the Midnight Navy background with Gold text.
