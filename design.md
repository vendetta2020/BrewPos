# BrewPOS — UI/UX Design System & Specification

> **Design Style**: Artisanal Functional Minimalism for High-Paced Coffee & Beverage POS System  
> **Target Environment**: Touchscreen POS Terminals (Tablets/Desktops) & Kitchen Display Systems (KDS)  
> **Engineering Reference**: Production POS Ergonomics & WCAG AA Contrast Compliance  

---

## 1. Design Philosophy: Artisanal Functional Minimalism

### Production-Grade POS Ergonomics vs Generic Web UIs
Traditional generic web interfaces frequently fail in high-velocity food & beverage environments due to:
- Excessive decorative elements and wasted whitespace that force cashiers to scroll during rush hours.
- Oversized rounded corners and low-contrast grey typography that induce eye fatigue under ambient cafe lighting.
- Missing keyboard-first shortcuts and inefficient multi-step checkout interactions.

### BrewPOS Design Core Guidelines
1. **High Information Density & Ergonomics**: Every pixel serves an operational function. Primary workflows (Search, Category Filter, Add to Cart, Qty Modifier, Quick Pay) must be accessible within 1 click or tap.
2. **Tactile Warmth & Artisanal Palette**: Inspired by specialty craft coffee culture — rich warm obsidian, espresso charcoal, soft porcelain backgrounds, with amber and emerald signal colors.
3. **Typography-First Hierarchy**: Tabular layouts using defined font weights (Medium, SemiBold, Bold) and monospace formatting for numeric figures, order references, timestamps, and currency values.
4. **Touch & Keyboard Optimization**: Touch targets strictly $\ge 44 \times 44\text{px}$. Keyboard shortcuts for POS operations (`/` search, `Esc` clear, `Enter` checkout).
5. **Contextual Feedback & Micro-animations**: Subtle state transitions (`active:scale-95`, border state color shifts) without latency-inducing animations.

---

## 2. Color System & Tokens

### Core Color Palette
| Token Name | Hex Code | Purpose |
| :--- | :--- | :--- |
| `surface-bg` | `#0E0D0D` (Dark) / `#F8F6F0` (Light) | App base background |
| `surface-card` | `#181716` (Dark) / `#FFFFFF` (Light) | Containers, cards, cart sidebar |
| `surface-border` | `#2D2A26` (Dark) / `#E5E0D8` (Light) | Structural borders & dividers |
| `brand-espresso` | `#3C2415` | Accent headers & primary brand badges |
| `brand-amber` | `#D97706` / `#F59E0B` | Active tabs, highlighted elements, pending state |
| `status-emerald` | `#059669` | Success, checkout action, paid status, ready order |
| `status-crimson` | `#DC2626` | Delete, cancel, order delay alert, critical errors |
| `text-primary` | `#F3F0EA` (Dark) / `#1C1917` (Light) | Primary text, titles, amounts |
| `text-secondary` | `#A8A29E` (Dark) / `#78716C` (Light) | Labels, subheadings, item variants |
| `text-muted` | `#78716C` (Dark) / `#A8A29E` (Light) | Secondary metadata, hints, timestamps |

---

## 3. Typography & Sizing Scale

- **Display & Interface Font**: `Plus Jakarta Sans` or `Inter` (Sans-serif, neutral, high legibility).
- **Monospace Font**: `JetBrains Mono` or `ui-monospace` (Used for Order IDs, Prices, Timestamps, Quantities).

---

## 4. Key Component Layout Specifications

### A. POS Terminal Interface (`pos-interface.blade.php`)
- **Left / Center Area (65-70% width)**:
  - Header: Cashier name, active session badge, clock, barcode scanner search input.
  - Category Pills: Horizontal scrollable badges (`All`, `Espresso`, `Manual Brew`, `Non-Coffee`, `Pastry`).
  - Product Grid: 3-4 columns responsive grid with image, title, price tag, and stock indicator pill.
- **Right Area (30-35% width)**:
  - Fixed-height Cart Drawer.
  - Item List with direct qty increment/decrement (`-` / `+`), item remove action, and variant pills (`S / M / L`).
  - Calculation Summary: Subtotal, Tax (11%), Discount, Total Grand Amount.
  - Primary Action Button: Large full-width `Bayar / Checkout` button with shortcut trigger.

### B. Checkout & Payment Modal (`checkout-modal.blade.php`)
- Clean border container with hover & click feedback (`hover:border-amber-600/50 active:scale-[0.98]`).
- **Payment Method Switcher**: Segmented control pills (Tunai, QRIS, Kartu).
- **Cash Input**: Numeric keypad + Quick Cash Denominations (`Uang Pas`, `50.000`, `100.000`, `200.000`).
- **Dynamic QRIS Display**: Centered QR code with countdown timer and automated webhook listener.
- **Action Buttons**: Selesaikan Pembayaran (Complete Payment) + Cetak Struk (Print Receipt).

### C. Kitchen Display System (KDS) (`kds-board.blade.php`)
- High contrast dark theme optimized for kitchen/barista overhead screens.
- Order aging timer badges:
  - `< 1m`: Green tag (`Fresh`)
  - `1 - 3m`: Amber tag (`Wait`)
  - `> 3m`: Red pulsing tag (`Urgent`)
- Fading retention for completed orders (50% opacity, 2-minute persistence) to prevent disorientation during rush hours.
- Interactive status toggle buttons: `Baru` $\to$ `Diracik` $\to$ `Siap` $\to$ `Selesai`.

---

## 5. UI Architecture Breakdown

```
BrewPOS Design Architecture
├── 1. Tailwind Config & Assets Optimization (Colors, Fonts, Utilities)
├── 2. Layout Structure & Header Refactoring (cashier.blade.php, kds.blade.php)
├── 3. Main POS Interface Component (pos-interface.blade.php)
├── 4. Checkout Modal & Payment Flow (checkout-modal.blade.php)
├── 5. KDS Barista Board View (kds-board.blade.php)
└── 6. Cashier Session & Receipt Pages (open.blade.php, close.blade.php, receipt.blade.php)
```

---

## 6. Verification & Quality Standards
- [x] High-contrast legibility compliant with WCAG AA standards.
- [x] Smooth response on Livewire events without layout shifting.
- [x] Mobile/Tablet touchscreen responsive layouts verified.
- [x] Touch targets strictly $\ge 44 \times 44\text{px}$ for all operational buttons.
