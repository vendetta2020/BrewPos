# BrewPOS — UI/UX Design System & Specification

> **Design Style**: Functional Minimalism (Anti-AI Slop) for High-Paced Coffee & Beverage POS System  
> **Target Environment**: Touchscreen POS Terminals (Tablets/Desktops) & Kitchen Display Systems (KDS)  
> **Author**: Antigravity AI (UI/UX Pro Max Edition)

---

## 1. Design Philosophy: Anti-AI Slop & Functional Minimalism

### 🚫 What is "AI Slop" in POS Interfaces?
- Overuse of generic bright purple/blue gradient backgrounds and floating cards.
- Excessive, oversized rounded corners (`rounded-3xl`) on functional data components.
- Low-contrast light grey text on dark or white backgrounds, causing eye strain in fast-paced cafe environments.
- Decorative emojis substituted for functional iconography.
- Wasted whitespace and inefficient layout density requiring excessive scrolling during peak rush hours.

### ✨ BrewPOS Minimalist Design Core Guidelines
1. **High Information Density & Ergonomics**: Every pixel serves a function. Key actions (Search, Category Filter, Add to Cart, Qty Modifier, Quick Pay) must be accessible within 1 click/tap.
2. **Tactile Warmth & Artisanal Palette**: Inspired by specialty craft coffee culture — rich warm obsidian, espresso charcoal, soft porcelain backgrounds, with amber and emerald signal colors.
3. **Typography-First Hierarchy**: Clear tabular layout using sharp font weights (Medium, SemiBold, Bold) and monospace formatting for numbers, order numbers, and currency values.
4. **Touch & Keyboard Optimization**: Touch targets strictly $\ge 44 \times 44\text{px}$. Keyboard shortcuts for POS operations (`/` search, `Esc` clear, `Enter` checkout).
5. **Contextual Feedback & Micro-animations**: Subtle state transitions (`active:scale-95`, border state color shifts) without distracting animations.

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

---

## 3. Typography & Formatting

- **Primary Font**: `Plus Jakarta Sans` or `Inter` (Clean sans-serif with high legibility at small sizes).
- **Monospace Font**: `JetBrains Mono` or `ui-monospace` (Used for Order IDs, Prices, Timestamps, Quantities).
- **Hierarchy Scale**:
  - `Display / Total`: 28px - 32px (Bold / Monospace)
  - `Header`: 18px - 20px (SemiBold)
  - `Body / Item Title`: 14px - 15px (Medium)
  - `Meta / Label`: 12px - 13px (Regular / SemiBold uppercase)

---

## 4. Key Component Design Specifications

### A. Top Navigation & Session Bar (`cashier.blade.php`)
- Integrated session badge: `#SES-102 (Terbuka)` with live indicator dot.
- Cashier user avatar badge + quick session drawer (Buka Sesi / Tutup Sesi).
- Dark warm header (`#181716`) with high contrast text.

### B. POS Catalog & Filter Bar (`pos-interface.blade.php`)
- **Search Bar**: Sticky header with clean search input, reset button, and autofocus.
- **Category Pills**: Horizontal scrollable pills with active amber pill badge (`bg-amber-600 text-white shadow-sm`).
- **Product Card**:
  - Clean border container with hover & click feedback (`hover:border-amber-600/50 active:scale-[0.98]`).
  - Crisp image/icon block + product name + bold price badge + category tag.
  - Visual stock status indicator (In Stock vs Out of Stock disabled state).

### C. POS Cart Drawer & Sticky Summary
- **Cart Header**: Item counter pill (`3 Items`) + Clear Cart action button.
- **Cart Item Row**:
  - Compact layout: Product title, size/variant badge, item price, item total.
  - Stepper controls: Minus button, quantity input, Plus button.
  - Item notes input / trigger (e.g. "Less sugar, extra ice").
- **Cart Summary Footer**:
  - Subtotal, Tax/Discount breakdown.
  - Grand Total prominently displayed in large monospace bold numbers.
  - Primary **Bayar / Checkout** button with Emerald gradient accent (`bg-emerald-600 hover:bg-emerald-500`).

### D. Checkout & Payment Modal (`checkout-modal.blade.php`)
- **Payment Method Switcher**: Segmented control pills (Tunai, QRIS, Transfer, Kartu).
- **Quick Nominal Presets**: Quick cash buttons (`Pas`, `50.000`, `100.000`, `200.000`).
- **Change Calculation**: Real-time display of Kembalian (Change) in green if sufficient, red if insufficient.
- **Action Buttons**: Selesaikan Pembayaran (Complete Payment) + Cetak Struk (Print Receipt).

### E. KDS Board (Kitchen Display System) (`kds-board.blade.php`)
- High contrast dark theme optimized for kitchen/barista overhead screens.
- Order aging timer badges:
  - `< 5m`: Green tag (`Fresh`)
  - `5 - 10m`: Yellow tag (`Warning`)
  - `> 10m`: Red pulsing tag (`Urgent`)
- Interactive item checklist (Barista can check off completed items).
- Status toggle buttons (Racik / Ready / Done).

---

## 5. UI Implementation Roadmap & Breakdown

```
BrewPOS UI Redesign
├── 1. Tailwind Config & Assets Optimization (Colors, Fonts, Utilities)
├── 2. Layout Structure & Header Refactoring (cashier.blade.php, kds.blade.php)
├── 3. Main POS Interface Component (pos-interface.blade.php)
├── 4. Checkout Modal & Payment Flow (checkout-modal.blade.php)
├── 5. KDS Barista Board View (kds-board.blade.php)
└── 6. Cashier Session & Receipt Pages (open.blade.php, close.blade.php, receipt.blade.php)
```

---

## 6. Verification & Quality Checklist
- [ ] No generic purple gradients or AI slop placeholders.
- [ ] High-contrast legibility tested for dark & light components.
- [ ] Smooth response on Livewire events without layout shifting.
- [ ] Mobile/Tablet touchscreen responsive layouts verified.
