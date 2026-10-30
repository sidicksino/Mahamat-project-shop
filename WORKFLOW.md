# FocalPoint: Camera and Photography Store

**Reg. No.** 27578/2024 | **Course:** Web Technology CAT | **Deadline:** before 2 November 2026

The store name "FocalPoint" is a placeholder. To rename it, change it in `includes/header.php` and `includes/footer.php`.

> **Current state:** the PHP and MySQL version is written, but it has not been run yet because this computer has no PHP or MySQL. It must be tested on a computer with XAMPP. [README.md](README.md) has the setup steps and a test checklist.

## The approach

Build the front end now on mock data, in a way that makes the PHP step mostly a swap:

- **All data access goes through one file**, `assets/js/store.js`. Pages never touch mock data directly.
- **Mock products use the same fields as the future MySQL table**, so nothing gets renamed later.
- **Add, edit and delete really work now**, saved in the browser's `localStorage`. This is temporary.
- **The header and footer live in one place**, `layout.js`. They become PHP includes later.

> The brief says all CRUD must be connected to MySQL, so the front end alone is not submittable. Phase 2 has to be finished before the deadline.

## Schedule

| Date | Work |
|---|---|
| Fri 30 Oct | Images, steps 1 to 6 |
| Sat 31 Oct | Steps 7 to 11, front end finished |
| Sun 1 Nov | Phase 2 tested on a computer with XAMPP, submit |

## Project structure

This is the Phase 1 structure, kept for reference. README.md shows the current one.

```text
Shop/
├── index.html              Home
├── products.html           All products, search and filter
├── product.html            One product (?id=3)
├── cart.html               Cart (bonus)
├── login.html              Admin login (bonus)
├── about.html              About and contact
├── admin/
│   ├── index.html          Product table with edit and delete
│   └── product-form.html   Add and edit form (?id=3 means edit)
└── assets/
    ├── css/
    │   ├── base.css        Colours, fonts, reset
    │   ├── layout.css      Header, footer, grid, breakpoints
    │   ├── components.css  Buttons, cards, forms, tables, toasts
    │   └── pages.css       Page-specific styles
    ├── js/
    │   ├── data.js         Mock products
    │   ├── store.js        Data layer: getAll, getById, create, update, remove
    │   ├── layout.js       Injects header and footer
    │   ├── ui.js           Toasts, price formatting, product card
    │   ├── cart.js         Cart logic
    │   ├── auth.js         Mock login and admin page guard
    │   └── pages/          One script per page
    └── images/
        ├── products/
        └── banners/
```

## Phase 1: Front end with mock data

Each step is finished when its check passes.

### Step 1: Images (done)
Generate the 15 images listed at the end of this file and place them in `assets/images/`.
**Check:** every file opens and looks like a consistent set.

### Step 2: Skeleton and design system (done)
Create the folders and empty files. In `base.css`, define colour, spacing and font variables and the reset. Mobile-first, with breakpoints at 640px and 1024px.
**Check:** a test page picks up the fonts and colours.

### Step 3: Mock data and data layer (done)
Write `data.js` with the 13 products and `store.js` on top of it. Seed `localStorage` on the first visit.

```js
{ id: 1, name: "Vista X7 Full-Frame Mirrorless", category: "Cameras",
  price: 3250000, stock: 8, description: "...",
  image: "assets/images/products/vista-x7.jpg", created_at: "2026-10-30" }
```

**Check:** in the browser console, `Store.getAll()` returns 13 products, and a created product is still there after a reload.

### Step 4: Shared layout and UI (done)
Build the responsive header with a mobile menu, cart count and footer in `layout.js`. Build toasts, RWF formatting and the product card in `ui.js`.
**Check:** the same header and footer appear on two test pages, and the menu works at phone width.

### Step 5: Home page (`index.html`) (done)
Hero banner, category tiles, featured products, a short "why buy from us" section.
**Check:** the featured products come from `Store`, not hard-coded HTML.

### Step 6: Product list (`products.html`) (done)
Responsive grid, plus live search, category filter and price sort (bonus). Include an empty state for "no results".
**Check:** search and filter combine correctly, and the layout holds at 375px, 768px and 1280px.

### Step 7: Product details (`product.html`) (done)
Image, name, price, description, stock, Add to Cart, related products. Show a friendly "not found" for a bad id.
**Check:** `product.html?id=999` shows the message, not a broken page.

### Step 8: Cart (`cart.html`, bonus) (done)
Add, change quantity, remove, show total. Saved in `localStorage`. **No payment or checkout**, as the brief excludes them.
**Check:** the count in the header updates and the cart survives a reload.

### Step 9: Admin CRUD (`admin/`) (the core of the marks) (done)
- `index.html`: table with image, name, price, stock, Edit, Delete. Delete asks for confirmation.
- `product-form.html`: one form for add and edit, with validation, image upload and preview (bonus).
- Success and error messages after every action.

**Check:** add, edit and delete a product, and each change shows on the public pages. Invalid input shows an error and saves nothing.

### Step 10: Login and About (done)
`login.html` with a mock admin login that guards `admin/` (bonus). `about.html` with store story and contact form. Mock login is not real security, and Phase 2 replaces it.
**Check:** opening `admin/` while logged out redirects to login.

### Step 11: Polish and test (done)
- Test every page at 375px, 768px and 1280px, with no sideways scrolling.
- Hover and focus states, `alt` text on all images, active link in the nav.
- Clear the console of errors and remove unused code.

**Check:** a full walkthrough from a cleared `localStorage` works.

## Phase 2: PHP and MySQL

The code for steps 1 to 8 is written but not yet run. Steps 9 and 10 need a computer with XAMPP.

1. `database/camera_store.sql` creates the `categories`, `products`, `users` and `messages` tables, seeded from the mock data.
2. `config/db.php` holds the settings and the PDO connection.
3. Pages are now `.php`, with a shared `includes/header.php` and `includes/footer.php`.
4. Product lists and details render from MySQL using PHP loops, with the same card markup.
5. Admin CRUD uses PHP form handling, prepared statements, server-side validation and redirect messages.
6. Image uploads go to `uploads/`, with file type and size checks.
7. Login uses `password_verify` and sessions. The demo login hint moved to README.md.
8. The cart moved into the PHP session. JavaScript still handles live search, the mobile menu, the delete dialog and the image preview.
9. **To do:** clone into `htdocs`, import the SQL and open `setup-check.php`.
10. **To do:** go through the test checklist in README.md, then submit.

## How this maps to the marks

| Criterion | Weight | Covered by |
|---|---|---|
| Functionality | 35% | Steps 6, 7, 9 and Phase 2 |
| User interface | 25% | Steps 2, 4, 11 |
| Code quality | 20% | The structure above, Step 11 |
| Database | 10% | Phase 2 |
| Creativity and extras | 10% | Search and filter, cart, upload, login: all four bonuses |

## Images

All images are generated in Google Flow. Product names are invented, and the images carry no real brands.

### Products (1:1)

Each prompt ends with this shared style:

> Professional e-commerce product photograph, centered, three-quarter view, on a seamless warm light-grey studio backdrop, soft diffused lighting, subtle shadow, sharp focus, photorealistic, generous empty space around the product. No brand names, no logos, no text, no people.

| # | File | Product | Category | Price (RWF) | Subject |
|---|---|---|---|---|---|
| 1 | `vista-x7.jpg` | Vista X7 Full-Frame Mirrorless | Cameras | 3,250,000 | Black full-frame mirrorless camera body with a standard zoom lens attached |
| 2 | `vista-m3.jpg` | Vista M3 Compact Mirrorless | Cameras | 1,150,000 | Small silver and black compact mirrorless camera with a short pancake lens |
| 3 | `titan-d90.jpg` | Titan D90 Professional DSLR | Cameras | 2,400,000 | Large black professional DSLR camera with a deep handgrip and a mid-range lens |
| 4 | `heritage-35.jpg` | Heritage 35 Film Camera | Cameras | 420,000 | Classic 35mm film rangefinder camera with a silver metal top and brown leather body |
| 5 | `snapjoy.jpg` | SnapJoy Instant Camera | Cameras | 165,000 | Rounded pastel mint instant camera with a printed blank photo emerging from the top |
| 6 | `trailgo.jpg` | TrailGo Action Camera | Drones & Action | 480,000 | Small rugged black action camera in a clear waterproof housing |
| 7 | `skylens-mini.jpg` | SkyLens Mini Camera Drone | Drones & Action | 1,350,000 | Compact grey folding quadcopter camera drone with its arms extended |
| 8 | `prozoom-2470.jpg` | ProZoom 24-70mm f/2.8 Lens | Lenses | 1,900,000 | Black professional 24-70mm zoom lens standing upright, with ribbed rings |
| 9 | `clearview-50.jpg` | ClearView 50mm f/1.8 Lens | Lenses | 280,000 | Small black 50mm prime lens at an angle showing the glass front element |
| 10 | `farreach-70200.jpg` | FarReach 70-200mm f/2.8 Lens | Lenses | 2,650,000 | Long white telephoto zoom lens with a black rubber ring and a tripod collar |
| 11 | `stablecarbon.jpg` | StableCarbon Travel Tripod | Studio & Accessories | 310,000 | Carbon-fibre travel tripod with a ball head, legs extended |
| 12 | `nomad-backpack.jpg` | Nomad Camera Backpack | Studio & Accessories | 145,000 | Charcoal canvas camera backpack with tan leather straps and padded compartments, standing upright |
| 13 | `studioglow.jpg` | StudioGlow Softbox Kit | Studio & Accessories | 380,000 | Photo studio softbox light on a black stand, glowing softly |

### Category images (added by you)

`hero-camera.jpg` (Cameras), `lens-85mm.jpg` (Lenses), `camera-drone.jpg` (Drones & Action) and `camera-backpack.jpg` (Studio & Accessories) are the category images, set in `data.js`. `canon-eos-r5.jpg`, `sony-a7iv.jpg` and `fujifilm-xt5.jpg` are not used.

### Banners

| # | File | Size | Prompt |
|---|---|---|---|
| 14 | `banners/hero.jpg` | 16:9 | Moody overhead flat lay of photography gear on a dark slate surface: mirrorless camera, lenses, film rolls, leather strap. Warm directional light, the left half darker and uncluttered for overlaid headline text, photorealistic. No brand names, no logos, no text. |
| 15 | `banners/about.jpg` | 4:3 | Photographer seen from behind holding a camera to their eye on a green hillside at golden hour, overlooking a hilly East African city with terraced slopes and soft morning mist, warm cinematic light, shallow depth of field, photorealistic. No brand names, no logos, no text. |
