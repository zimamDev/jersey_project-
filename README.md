# Interactive Jersey Customization and Ordering System

Final Year Project — HNDIT4052 ICT Project (Individual)
SLIATE ATI Anuradhapura | Mr. Zimam | ANU/IT/2324/F/238

Plain **PHP (OOP) + MySQL (PDO) + Bootstrap 5** — no framework (Laravel not used).

## Tech Stack
- PHP 8+ (Object-Oriented, no framework)
- MySQL / MariaDB (via PDO)
- Bootstrap 5 + Font Awesome (CDN)
- Chart.js (CDN) for the admin sales report
- Vanilla JavaScript for the live front/back SVG jersey preview

## What's New in This Version
- **Official-look customizer**: front/back toggle preview — the logo sits on the
  chest (front) and the player's name + number are printed on the back, just like
  a real club jersey. Four selectable design patterns (Plain, Sash, Hoops, Stripes)
  with a custom pattern color.
- **Ready-Made Shop** (`shop.php`): customers can browse jerseys the admin has
  photographed and listed, then buy one by simply entering their name, number and
  size (`buy_readymade.php`) — no need to design from scratch.
- **Admin catalog management** (`admin/manage_jerseys.php`, `edit_jersey.php`):
  add / edit / hide / delete both Custom Templates and Ready-Made products, each
  with an uploaded product photo, price, description and default color.
- **Dedicated "Add Ready-Made Jersey" upload page** (`admin/add_readymade_jersey.php`):
  a focused, drag-and-drop-style photo upload screen just for publishing new
  Ready-Made jerseys straight to the Shop, with a live photo preview and a running
  list of everything currently published. Linked from the sidebar, dashboard, and
  the Manage Jerseys page.
- **Admin customer management** (`admin/manage_users.php`, `user_detail.php`):
  see every registered customer, how many jerseys they've designed/bought, how
  much they've spent, and drill into their full order history.
- **Admin sales reports** (`admin/reports.php`): revenue trend (last 14 days),
  order-status breakdown, Custom vs Ready-Made revenue split, and a top-selling
  jerseys table — all backed by Chart.js visualizations.
- **Colorful, photo-backed UI**: new stadium and jersey-rack photography on the
  hero, shop and auth pages, gradient stat cards, and consistent page banners.

## Folder Structure
```
jersey_project/
├── admin/                  Admin panel
│   ├── dashboard.php        Stats overview
│   ├── manage_orders.php    Update order status
│   ├── manage_jerseys.php   Add / hide / delete jerseys (Custom + Ready-Made)
│   ├── add_readymade_jersey.php  Dedicated Ready-Made photo upload page
│   ├── edit_jersey.php      Edit a single jersey
│   ├── manage_users.php     All customers + spend/jersey counts
│   ├── user_detail.php      One customer's jerseys & orders
│   ├── reports.php          Sales report (Chart.js)
│   └── includes/sidebar.php
├── assets/
│   ├── css/style.css        Colorful theme + background photography
│   └── js/
├── classes/                 OOP classes
│   ├── User.php             Registration / login (FR-01)
│   ├── Jersey.php           Templates, Ready-Made catalog, customization, uploads
│   ├── Order.php             Order placement, tracking, sales reporting
│   └── Admin.php             Admin auth, order/user management, dashboard stats
├── config/
│   └── Database.php          PDO Singleton connection class
├── database/
│   └── schema.sql             Full MySQL schema + seed data (+ upgrade notes)
├── includes/
│   ├── header.php / footer.php
│   └── session.php            Auth helpers
├── uploads/                   Uploaded jersey logos
│   └── jerseys/                Uploaded Ready-Made / catalog product photos
├── index.php                  Landing page
├── register.php / login.php / logout.php
├── customize.php               Front/back jersey customizer with live preview
├── shop.php                    Ready-Made jersey catalog
├── buy_readymade.php           Buy a Ready-Made jersey (name + number only)
├── place_order.php             Order confirmation
├── my_orders.php                 Customer order history
└── track_order.php               Order tracking
```

## Setup Instructions (XAMPP / WAMP)

1. Copy the `jersey_project` folder into `htdocs` (XAMPP) or `www` (WAMP).
2. Start Apache and MySQL from the XAMPP/WAMP control panel.
3. Open phpMyAdmin (http://localhost/phpmyadmin) and import `database/schema.sql`.
   This creates the `jersey_system` database with all tables and seed data
   (3 sample Custom Templates are pre-loaded; add Ready-Made jerseys from the
   admin panel).
   - **Upgrading an existing v1 database?** See the `ALTER TABLE` statements at
     the bottom of `database/schema.sql` instead of re-importing everything.
4. Check `config/Database.php` — default credentials are `root` / *(empty password)*,
   which matches a default XAMPP install. Edit if your MySQL setup differs.
5. Make sure `uploads/` and `uploads/jerseys/` are writable by the web server
   (they already exist in this package).
6. Visit `http://localhost/jersey_project/index.php` in your browser.
7. Register a new customer account, or log in to the admin panel at
   `http://localhost/jersey_project/admin/login.php` using:
   - Username: `admin`
   - Password: `admin123`
8. From the admin panel, click **Add Ready-Made Jersey** in the sidebar (or on the
   dashboard) to upload a jersey photo, price and description — it immediately
   appears in the customer-facing **Ready-Made Shop**.

## Key Features Implemented (mapped to SRS)
- FR-01 User Registration & Login (password hashing with `password_hash`)
- FR-02 Jersey Customization (color, pattern, size, name, number)
- FR-03 Live Front/Back Jersey Preview (SVG + JavaScript, updates instantly)
- FR-04 Logo Upload Management (MIME + size validation, stored in `/uploads`)
- FR-05 Order Placement (auto-generated order number, quantity, delivery info)
- FR-06 Order Tracking (visual status timeline: Pending → Processing → Completed → Delivered)
- FR-07 Admin Order Management (dashboard stats + order status updates)
- FR-08 Admin Jersey Catalog Management (add/edit/hide/delete Custom & Ready-Made jerseys)
- FR-09 Admin Customer Management (view all users and their jersey/order activity)
- FR-10 Admin Sales Reports (revenue trend, status breakdown, top sellers)
- FR-11 Ready-Made Jersey Shop (browse & buy pre-designed jerseys with just name + number)

## OOP Design Notes
- `Database` class uses the **Singleton** pattern for a single shared PDO connection.
- `User`, `Jersey`, `Order`, `Admin` are separate model classes, each responsible for
  one part of the domain (single-responsibility principle).
- All SQL queries use **prepared statements** to prevent SQL injection.
- Passwords are stored using `password_hash()` / verified with `password_verify()`.

## Security Notes for viva / demonstration
- Input is escaped with `htmlspecialchars()` via the `e()` helper before output.
- File uploads (logos & jersey photos) are validated by MIME type and size, and
  renamed with `uniqid()` to prevent overwrite/collision attacks.
- Sessions are used for both customer and admin authentication, kept separate
  (`user_id` vs `admin_id` session keys).

## Testing Performed
This build was smoke-tested end-to-end against a live MySQL/MariaDB instance and
PHP's built-in server, covering: registration → login → jersey customization →
order placement → order tracking, the Ready-Made Shop purchase flow, and the full
admin workflow (login, add/edit/hide a Ready-Made jersey with photo upload, order
status updates, customer list & detail view, and the sales report page). All pages
returned HTTP 200 with no PHP warnings/errors in the output.
