# Demo notes (what I built + why)

Orders

View orders at /admin/orders — table of all orders for admins only. Read-only so nobody edits orders by accident through this screen.

Export at /admin/orders/export — same data but downloadable as CSV or Excel, you choose columns. Handy for accounting or checking sales offline.

Homepage CMS

/admin/cms/homepage — change the browser title + the hero / welcome / about blocks on the home URL.

Title lives in pages table (slug home). The rest is site_settings with keys like cms_home_* so we dont hardcode copy in PHP.

Defaults still come from app.php if a key isnt in the DB (merge in SettingsRepository).

Dance CMS

/admin/cms/dance — Dance text and image filenames in dance_settings, merged with config/dance.php so missing DB rows dont break the page.

Rich text + uploads

TinyMCE in the admin for long text. Browsers send HTML; we sanitize with HTML Purifier (HtmlSanitizer.php) because stored HTML is dangerous if you echo it raw (XSS).

Upload is POST /admin/cms/upload — about image goes under public/images/cms/home/, Dance hero files under public/images/dance/. CSRF checked, MIME checked, max size, admin only.

Files if he asks where it lives

Orders: AdminOrdersController, OrdersList, OrderRepository
Export: AdminOrderExportController, OrderExportService
Home: AdminHomepageController, SettingsRepository, HomepageEdit, hero welcome about partials
Dance: AdminDanceController, DanceSettingsRepository, DanceEdit
Upload: AdminCmsUploadController
Routes: public/index.php

Before demo

composer install (needs htmlpurifier), migrations run, admin logged in, save CMS once and refresh home and dance to prove it stuck.

Git

Commit composer.json and composer.lock. Dont commit vendor or .env.
