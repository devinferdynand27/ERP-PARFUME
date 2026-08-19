# UI Design System & Rules

## 1. Purpose

This document defines the mandatory UI/UX rules for the CAVA Admin Dashboard.

All pages, components, forms, tables, dashboards, modals, notifications, and other interfaces MUST follow this design system.

The visual reference is based on the provided CAVA Admin Dashboard screenshots.

The overall design direction is:

* Clean
* Minimal
* Professional
* Elegant
* Modern
* Premium
* Corporate
* Spacious
* Easy to scan
* Desktop-first admin dashboard

The interface should feel like a premium SaaS/admin application rather than a generic Bootstrap dashboard.

---

# 2. UI Framework

The project MUST use:

* shadcn/ui
* Tailwind CSS
* Lucide Icons

Prefer shadcn/ui components whenever an appropriate component exists.

Do NOT recreate components that already exist in shadcn/ui unless there is a strong reason.

Preferred components include:

* Button
* Input
* Select
* Dropdown Menu
* Dialog
* Alert Dialog
* Sheet
* Card
* Badge
* Table
* Tabs
* Tooltip
* Popover
* Calendar
* Checkbox
* Radio Group
* Switch
* Skeleton
* Toast
* Sonner
* Pagination
* Breadcrumb
* Separator

Use Tailwind CSS for layout and styling.

---

# 3. Core Visual Direction

The UI must visually follow these principles:

### Primary characteristics

* White background
* Very light gray page background
* Dark navy/black text
* Thin gray borders
* Minimal shadows
* Moderate border radius
* Generous spacing
* Strong visual hierarchy
* Simple iconography
* High readability

### Avoid

DO NOT use:

* Heavy gradients
* Neon colors
* Excessive glassmorphism
* Excessive shadows
* Excessive rounded/pill elements
* Large decorative illustrations
* Random colors
* Excessive animations
* Excessive borders
* Generic Bootstrap styling
* Overly colorful dashboards
* Huge typography
* Excessively dense layouts

The UI should look calm and premium.

---

# 4. Color System

Use a neutral monochrome palette as the primary visual system.

## Background

```text
Page background:
#F8FAFC

Surface:
#FFFFFF

Subtle surface:
#F8FAFC

Muted surface:
#F1F5F9
```

## Text

```text
Primary:
#0F172A

Secondary:
#64748B

Muted:
#94A3B8

Disabled:
#CBD5E1
```

## Border

```text
Default:
#E2E8F0

Subtle:
#F1F5F9

Strong:
#CBD5E1
```

## Primary

The primary action color is black / near-black.

```text
Primary:
#050505

Primary hover:
#171717

Primary foreground:
#FFFFFF
```

Examples:

* Main buttons
* Active sidebar item
* Important actions
* Pagination active state
* Primary CTA

---

# 5. Semantic Colors

Use colors only when they communicate meaning.

## Success

```text
Success:
#22C55E

Success background:
#DCFCE7

Success text:
#15803D
```

Use for:

* Active
* Available
* Stock available
* Successful transaction
* Barang masuk
* Positive status

## Danger

```text
Danger:
#EF4444

Danger background:
#FEE2E2

Danger text:
#DC2626
```

Use for:

* Delete
* Error
* Barang keluar
* Stock minimum
* Failed transaction

## Warning

```text
Warning:
#F59E0B

Warning background:
#FEF3C7

Warning text:
#B45309
```

Use for:

* Warning
* Pending
* Attention required

## Information

```text
Info:
#3B82F6

Info background:
#DBEAFE

Info text:
#1D4ED8
```

Use sparingly.

---

# 6. Color Usage Rules

Do not introduce additional colors without a clear semantic purpose.

The application should primarily use:

* Black
* White
* Gray
* Green
* Red
* Yellow
* Blue

Color should communicate meaning, not decoration.

Do not make every card a different color.

Do not use colorful gradients for dashboard statistics.

---

# 7. Typography

Preferred font:

```text
Inter
```

If Inter is unavailable, use the project's existing sans-serif font.

Typography must be clean and highly readable.

## Page Title

```text
font-size: 28px - 32px
font-weight: 700
line-height: 1.2
color: #0F172A
```

Example:

```text
Dashboard
Master Produk
Laporan Stok
```

## Page Description

```text
font-size: 14px - 16px
font-weight: 400
color: #64748B
```

## Section Title

```text
font-size: 18px - 20px
font-weight: 600
color: #0F172A
```

## Body

```text
font-size: 14px - 15px
color: #334155
```

## Small Text

```text
font-size: 12px - 13px
color: #64748B
```

Do not use excessive font weights.

Preferred weights:

* 400
* 500
* 600
* 700

---

# 8. Layout

The application uses a fixed admin dashboard structure.

```text
┌───────────────────────────────────────────────┐
│ Sidebar │ Top Header                         │
│         ├─────────────────────────────────────┤
│         │                                     │
│         │ Main Content                        │
│         │                                     │
│         │                                     │
└───────────────────────────────────────────────┘
```

## Sidebar

Desktop sidebar:

```text
width: 252px - 260px
```

The sidebar should remain visually stable.

Use:

```text
background: #FFFFFF
border-right: 1px solid #E2E8F0
```

Do not use a shadow-heavy sidebar.

---

# 9. Sidebar

The sidebar contains:

1. Brand
2. Navigation groups
3. Navigation items

Example:

```text
CAVA
PARFUMS | LUXURY
FRAGRANCES

Dashboard

MASTER DATA
  Master Produk
  Master Ukuran Botol
  Master Varian Aroma

TRANSAKSI
  Barang Masuk
  Barang Keluar

LAPORAN
  Laporan Barang Masuk
  Laporan Barang Keluar
  Laporan Stok
  Laporan Keuntungan
```

## Brand

Brand should be positioned at the top.

The CAVA logo should remain visually dominant.

Do not modify the logo unnecessarily.

---

# 10. Sidebar Navigation

Normal navigation item:

```text
background: transparent
color: #475569
```

Hover:

```text
background: #F8FAFC
color: #0F172A
```

Active:

```text
background: #050505
color: #FFFFFF
```

Active navigation should use:

```text
border-radius: 8px
```

Recommended item height:

```text
40px - 44px
```

Icons:

Use Lucide Icons.

Recommended icon size:

```text
18px
```

Do not use different icon styles.

---

# 11. Navigation Group Titles

Navigation group titles such as:

```text
MASTER DATA
TRANSAKSI
LAPORAN
```

should be:

```text
font-size: 11px - 12px
font-weight: 600
letter-spacing: 0.12em
text-transform: uppercase
color: #94A3B8
```

They should have enough vertical spacing from the navigation items.

---

# 12. Top Header

The top header should be:

```text
height: approximately 70px
background: #FFFFFF
border-bottom: 1px solid #E2E8F0
```

Right-side content:

```text
Administrator
Logout button
```

Keep the header simple.

Do not add unnecessary elements.

---

# 13. Main Content

Main content background:

```text
#F8FAFC
```

Desktop padding:

```text
32px - 40px
```

Tablet:

```text
24px
```

Mobile:

```text
16px
```

Maximum content width should normally be:

```text
1280px - 1440px
```

Do not stretch content unnecessarily on very large screens.

---

# 14. Page Header

Each page should have a consistent header.

Example:

```text
Master Produk
Kelola katalog produk parfum

                              + Tambah Produk
```

Structure:

```text
Page title
Page description

                    Page action
```

Page action should be aligned to the right on desktop.

On mobile, stack the action below the title.

---

# 15. Buttons

Use shadcn/ui Button.

## Primary Button

```text
background: #050505
color: #FFFFFF
border-radius: 8px
```

Example:

```text
+ Tambah Produk
Refresh
Simpan
```

## Secondary Button

```text
background: #FFFFFF
border: 1px solid #E2E8F0
color: #334155
```

## Destructive Button

Use shadcn destructive variant.

```text
background: #EF4444
color: #FFFFFF
```

Use destructive buttons primarily for:

* Delete
* Remove
* Cancel destructive actions

Do not use red buttons for normal actions.

---

# 16. Button Size

Preferred:

```text
height: 40px
padding: 0 16px
border-radius: 8px
```

Small:

```text
height: 36px
```

Large:

```text
height: 44px
```

Do not create oversized buttons.

---

# 17. Icon Buttons

Icon-only buttons should use shadcn Button with icon variant.

Example:

```text
Edit
Delete
More
Refresh
Search
```

Recommended:

```text
36px × 36px
```

Every icon-only button must have:

* Tooltip
* Accessible aria-label

Example:

```text
aria-label="Edit product"
```

---

# 18. Cards

Cards should follow the visual style in the reference screenshots.

Default:

```text
background: #FFFFFF
border: 1px solid #E2E8F0
border-radius: 12px
box-shadow: none
```

Optional extremely subtle shadow may be used when necessary.

Avoid:

```text
large shadows
colored cards
gradient cards
```

---

# 19. Dashboard Statistic Cards

Statistic cards should be simple and compact.

Example:

```text
TOTAL PRODUK
6

Aktif di katalog
```

Card structure:

```text
Label
Value
Supporting text
Optional status badge
```

Typography:

Label:

```text
11px - 12px
uppercase
letter-spacing
color: #94A3B8
```

Value:

```text
28px - 32px
font-weight: 700
```

Supporting text:

```text
12px - 14px
color: #64748B
```

---

# 20. Highlighted Statistic Card

The first/important statistic may use dark styling.

Example:

```text
background: #050505
color: #FFFFFF
```

This should be used sparingly.

Do not make every statistic card black.

Recommended:

```text
1 highlighted card
+
4-6 neutral cards
```

---

# 21. Status Badges

Use shadcn Badge.

Badges should be compact.

## Success

```text
background: #DCFCE7
color: #15803D
```

## Danger

```text
background: #FEE2E2
color: #DC2626
```

## Warning

```text
background: #FEF3C7
color: #B45309
```

Badge radius:

```text
6px - 8px
```

Avoid excessive pill-shaped badges unless the component is specifically designed as a status chip.

---

# 22. Tables

Tables are a major part of the admin dashboard.

Use shadcn/ui Table.

Reference structure:

```text
┌───────────────────────────────────────────────┐
│ Search              Filter             6 data │
├───────────────────────────────────────────────┤
│ PRODUK │ AROMA │ HARGA MODAL │ HARGA JUAL ... │
├───────────────────────────────────────────────┤
│ Product row                                    │
│ Product row                                    │
│ Product row                                    │
└───────────────────────────────────────────────┘
```

Table must be:

* Clean
* Spacious
* Easy to scan
* Minimal

---

# 23. Table Header

Table header:

```text
background: #F8FAFC
color: #64748B
font-size: 12px
font-weight: 600
text-transform: uppercase
```

Do not use dark table headers.

---

# 24. Table Rows

Rows should use:

```text
background: #FFFFFF
border-bottom: 1px solid #F1F5F9
```

Hover:

```text
background: #F8FAFC
```

Avoid zebra-striping unless necessary.

---

# 25. Product Table

For product tables, the product identity should be visually stronger than secondary information.

Example:

```text
[ C ]  Cedar Wood
       PRD–U1V2W3X4
```

Product name:

```text
font-weight: 600
color: #0F172A
```

Product code:

```text
font-size: 12px
color: #94A3B8
```

---

# 26. Currency Formatting

Currency should follow Indonesian format.

Example:

```text
Rp 55.000
Rp 130.000
```

Use consistent formatting everywhere.

Do not mix:

```text
55000
Rp55.000
Rp 55,000
```

The standard is:

```text
Rp 55.000
```

---

# 27. Search and Filter

Search/filter area should be visually separated from the table.

Preferred:

```text
Search input
+
Select filter
+
optional additional filters
```

Inputs should use shadcn/ui Input.

Input:

```text
height: 40px
border: 1px solid #E2E8F0
border-radius: 8px
```

Focus:

Use the default shadcn focus ring.

Do not create custom glowing focus effects.

---

# 28. Forms

All forms should use shadcn/ui components.

Preferred structure:

```text
Label
Input
Helper text / error
```

Example:

```text
Nama Produk
[ Cedar Wood                         ]

Aroma
[ Cedar Wood                         ]

Harga Modal
[ Rp 55.000                          ]
```

Form spacing should be:

```text
16px - 24px
```

---

# 29. Modal / Dialog

Use shadcn Dialog.

Dialog should be:

* Clean
* White
* Moderate radius
* Minimal shadow
* Clear title
* Clear description
* Clear action buttons

Example:

```text
Tambah Produk

Tambahkan produk parfum baru.

Nama Produk
[........................]

Aroma
[........................]

Harga Modal
[........................]

             Batal     Simpan
```

Do not create huge full-screen modals unless the workflow requires it.

---

# 30. Confirmation Dialog

Destructive operations must require confirmation.

Example:

```text
Hapus Produk?

Apakah Anda yakin ingin menghapus
produk Cedar Wood?

              Batal     Hapus
```

Delete button must use destructive styling.

---

# 31. Loading State

Every asynchronous page/component should have a loading state.

Preferred:

* Skeleton
* Spinner for short actions

Use shadcn Skeleton.

Avoid replacing the entire page with a spinner when skeleton loading is possible.

---

# 32. Empty State

Every list/table should have an empty state.

Example:

```text
Belum Ada Produk

Belum ada produk parfum yang tersedia.

+ Tambah Produk
```

Keep empty states simple.

Do not use excessive illustrations.

---

# 33. Error State

Errors should be clear and actionable.

Example:

```text
Gagal Memuat Data

Data produk tidak dapat dimuat.
Silakan coba lagi.

[ Coba Lagi ]
```

Do not expose raw API errors to users.

---

# 34. Toast / Notification

Use shadcn-compatible toast/notification system.

Success:

```text
Produk berhasil ditambahkan.
```

Error:

```text
Gagal menyimpan produk.
```

Notification should be:

* Short
* Clear
* Actionable

Avoid verbose notifications.

---

# 35. Dashboard Charts

Charts must follow the same visual language.

Preferred:

* Dark navy/black
* Green for positive/success
* Red for negative/danger
* Gray grid
* Minimal labels

Avoid colorful multi-color charts.

Charts should prioritize readability over decoration.

---

# 36. Dashboard Layout

The dashboard should follow the reference composition:

```text
Page Header

Statistic Cards

┌──────────────────────────┬──────────────────┐
│ Inventory Volume         │ Recent Activity  │
│                          │                  │
│ Chart                    │ Activity list   │
└──────────────────────────┴──────────────────┘

┌──────────────────────────────────────────────┐
│ Produk Stok Minimum                          │
│                                              │
│ Table                                        │
└──────────────────────────────────────────────┘
```

Recommended proportions:

```text
Main chart:
70% - 75%

Activity:
25% - 30%
```

---

# 37. Recent Activity

Activity items should use:

```text
Icon
Title
Description
Timestamp
```

Use semantic icon backgrounds.

Example:

Barang Masuk:

```text
green icon background
```

Barang Keluar:

```text
red icon background
```

Warning:

```text
neutral/yellow icon background
```

Keep activity rows separated with subtle borders.

---

# 38. Stock Indicators

Stock should be immediately understandable.

Example:

```text
● 45
```

Healthy:

```text
green
```

Low:

```text
red
```

Do not rely only on color.

The number and/or status text must also communicate the state.

---

# 39. Pagination

Pagination should be compact.

Active page:

```text
background: #050505
color: #FFFFFF
```

Inactive:

```text
background: #FFFFFF
border: 1px solid #E2E8F0
```

Use shadcn pagination where possible.

---

# 40. Border Radius

Use a consistent radius scale.

```text
sm: 6px
md: 8px
lg: 12px
xl: 16px
```

Default:

```text
8px - 12px
```

Do not use extremely rounded cards.

Avoid:

```text
rounded-full
```

unless it is specifically needed for:

* Avatar
* Status chip
* Circular icon
* Progress indicator

---

# 41. Shadows

The design should be border-driven rather than shadow-driven.

Preferred:

```text
border: 1px solid #E2E8F0
```

Use shadows only when necessary:

* Dropdown
* Popover
* Dialog
* Floating element

Avoid heavy card shadows.

---

# 42. Spacing System

Use Tailwind's spacing scale consistently.

Preferred values:

```text
4
8
12
16
20
24
32
40
48
64
```

Do not use arbitrary spacing unless required.

Avoid excessive spacing that makes the dashboard feel empty.

---

# 43. Responsive Design

Desktop reference:

```text
Sidebar visible
Top header visible
Multi-column dashboard
```

Tablet:

```text
Sidebar may collapse
Cards wrap
Charts resize
```

Mobile:

```text
Sidebar becomes drawer/sheet
Cards become stacked
Tables become horizontally scrollable or responsive cards
Page actions stack
```

Never allow desktop layouts to overflow horizontally on mobile.

---

# 44. Mobile Sidebar

Use shadcn Sheet for mobile navigation when appropriate.

Do not create a completely separate navigation implementation.

The desktop sidebar and mobile sidebar should share the same navigation data.

---

# 45. Icons

Use Lucide Icons consistently.

Examples:

```text
LayoutDashboard
Package
FlaskConical
ArrowDownToBox
ArrowUpFromBox
FileText
BarChart3
TrendingUp
Search
Plus
Pencil
Trash2
RefreshCw
Calendar
ChevronDown
AlertTriangle
LogOut
```

Do not mix icon libraries.

Do not use emojis as UI icons.

---

# 46. Icon Rules

Icons should:

* Be simple
* Have consistent stroke width
* Use 16px - 20px
* Align vertically with text

Do not use oversized icons unnecessarily.

---

# 47. Animation

Animation should be subtle.

Preferred duration:

```text
150ms
200ms
300ms
```

Use animation for:

* Hover
* Focus
* Dialog
* Dropdown
* Sidebar
* Loading
* Page transitions

Avoid:

* Bouncing elements
* Excessive parallax
* Large movement
* Decorative animation

The interface should feel smooth, not flashy.

---

# 48. Accessibility

Every interface must consider accessibility.

Requirements:

* Proper labels
* Keyboard navigation
* Focus states
* aria-label for icon-only buttons
* Sufficient contrast
* Accessible form errors
* Do not rely only on color
* Images require meaningful alt text

Do not remove focus indicators.

---

# 49. Component Reusability

Before creating a new UI component:

1. Search the existing component library.
2. Check shadcn/ui.
3. Check existing project components.
4. Reuse if possible.
5. Extend an existing component if appropriate.
6. Only create a new component when necessary.

Do not create:

```text
ProductButton
ProductDeleteButton
ProductSaveButton
```

if a generic reusable Button can handle the use case.

Prefer:

```text
Button
```

with appropriate variants.

---

# 50. Component Architecture

Recommended:

```text
components/
├── ui/
│   ├── button.tsx
│   ├── input.tsx
│   ├── card.tsx
│   ├── table.tsx
│   ├── dialog.tsx
│   └── ...
│
├── layout/
│   ├── sidebar
│   ├── header
│   └── page-header
│
├── dashboard/
│   ├── stat-card
│   ├── inventory-chart
│   └── recent-activity
│
└── products/
    ├── product-table
    ├── product-form
    └── product-dialog
```

Keep generic UI components separate from business-specific components.

---

# 51. Important AI Rules

When AI generates or modifies UI, it MUST follow this document.

Before generating UI:

1. Inspect existing components.
2. Inspect existing styles.
3. Inspect the project's shadcn configuration.
4. Reuse existing components.
5. Follow this design system.
6. Maintain visual consistency with the provided CAVA dashboard reference.

AI MUST NOT:

* Introduce random colors.
* Introduce random fonts.
* Introduce another UI framework.
* Introduce another icon library.
* Replace shadcn/ui unnecessarily.
* Create excessive rounded elements.
* Add excessive shadows.
* Add unnecessary gradients.
* Redesign existing pages without instruction.
* Change established spacing without reason.
* Create duplicate components.
* Create inconsistent button styles.
* Create inconsistent table styles.

---

# 52. UI Modification Rule

When asked to modify an existing page:

DO NOT redesign the entire page.

Only modify the requested area.

Preserve:

* Existing layout
* Existing components
* Existing spacing
* Existing typography
* Existing colors
* Existing interaction patterns

unless the user explicitly asks for a redesign.

---

# 53. New Page Rule

When creating a new page, the page MUST look like it belongs to the same application.

It must reuse:

* Sidebar
* Header
* Page header
* Buttons
* Cards
* Tables
* Forms
* Dialogs
* Badges
* Spacing
* Typography
* Colors

The new page should never look like it came from a different template.

---

# 54. Design Reference

The uploaded CAVA Admin Dashboard screenshots are the primary visual reference.

The visual characteristics to preserve are:

* White sidebar
* Thin gray borders
* Black active navigation
* White content cards
* Very light gray page background
* Black primary buttons
* Dark navy typography
* Green success indicators
* Red danger indicators
* Minimal shadows
* Medium rounded corners
* Spacious layout
* Clean table design
* Compact status badges
* Simple Lucide-style icons
* Premium SaaS/admin aesthetic

When uncertain about a visual decision, prefer the simpler and more minimal solution.

---

# 55. Golden Rule

The UI should look like:

> "A premium, clean, modern inventory management SaaS."

It should NOT look like:

> "A generic admin template."

Consistency is more important than adding visual effects.

When choosing between a more complex design and a simpler design, choose the simpler design if both solve the same UX problem.
