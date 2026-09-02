=== BYOT Accounting ===
Contributors: byot
Tags: accounting, invoice, sales, purchases, expenses, finance, VAT, bookkeeping
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete primary accounting system for WordPress. Manage sales, purchases, expenses, and financial reports.

== Description ==

**BYOT Accounting** is an all-in-one WordPress plugin for managing basic accounting for small businesses, freelancers, and agencies. It requires no external subscriptions and runs directly in your WordPress database.

= Main Features =

* **Sales Invoices** – Issue, edit, track status (Paid / Unpaid / Cancelled), automatic VAT calculation.
* **Purchase Invoices** – Supplier records, tax ID, payment status (Paid / Unpaid), due dates.
* **Expense Register** – Record expenses by category (transport, rent, utilities, salaries, marketing, others).
* **Financial Dashboard** – Total income, expenses, purchases, and estimated net profit in real time.
* **Transaction History** – Last 10 operations listed centrally in the Dashboard.
* **AJAX Interface** – Quick save and delete without page reload.
* **Security** – Nonce verification, input sanitization, capability check (`manage_options`).

= Who is it for? =

* Small businesses and LLCs
* Freelancers who want to keep simple records
* Agencies or online shops that need an internal register

= Technical Features =

* Custom database tables in WordPress (`wp_byot_sales`, `wp_byot_purchases`, `wp_byot_expenses`)
* Responsive design, natively integrated into the WordPress Admin style
* OOP (Object Oriented Programming) code, structured in separate classes
* Text domain ready for translations (`byot-accounting`)
* No external dependencies (does not use SaaS services)

== Installation ==

1. Download the `byot-accounting.zip` archive.
2. Go to your WordPress admin **Plugins > Add New > Upload Plugin**.
3. Select the `.zip` file and click **Install Now**.
4. Activate the plugin.
5. Access the **BYOT Accounting** menu from the left sidebar.

Or manually:

1. Unzip the file into `/wp-content/plugins/`.
2. Make sure the folder structure is: `/wp-content/plugins/byot-accounting/byot-accounting.php`.
3. Activate from the **Plugins** menu.

= Troubleshooting =

If the plugin does not create the tables, deactivate and reactivate it. Tables are created automatically on activation via `register_activation_hook`.

== Frequently Asked Questions ==

= Why doesn't the BYOT Accounting menu appear? =

Make sure you are logged in with an account that has the **Administrator** role. The plugin checks for the `manage_options` capability.

= Can I export data to Excel/CSV? =

In the current version (1.0.0) CSV export is not included. You can manually export tables directly from phpMyAdmin. This feature will be added in a future update.

= Can it generate PDF invoices? =

Version 1.0.0 manages financial records in digital format. PDF generation is planned for v2.0.

= Does it work with WooCommerce? =

The plugin is independent. It does not interfere with WooCommerce, but it also does not automatically synchronize orders. You can manually add WooCommerce sales as sales invoices.

= Is it multisite compatible? =

Yes, tables are created per-site in the standard multisite configuration.



1. **Main Dashboard** – Overview with income, expenses, and net profit.
2. **Sales Module** – Add and list issued invoices with VAT and status.
3. **Purchases Module** – Supplier records and received invoices.
4. **Expenses Module** – Multiple categories and payment methods.
5. **Edit Interface** – Quick form with AJAX save.

== Changelog ==

= 1.0.0 - 2024-06-12 =
* Initial release.
* Create custom tables: sales, purchases, expenses.
* Dashboard with statistics and recent history.
* Admin interface with AJAX for save/delete.
* Expense category support.
* Payment/collection status for invoices.

== Upgrade Notice ==

= 1.0.0 =
First stable version. Backup is recommended before activation on production sites.

== Arbitrary section ==

= File Structure =

/byot-accounting/
├── byot-accounting.php          # Main file
├── readme.txt                   # This file
├── includes/
│   ├── class-activator.php      # Create tables on activation
│   ├── class-deactivator.php    # Cleanup on deactivation
│   ├── class-admin.php          # Admin menu & enqueue assets
│   ├── class-ajax-handler.php   # AJAX endpoints
│   ├── class-dashboard.php      # Dashboard logic
│   ├── class-database.php       # Abstract DB queries
│   ├── class-expenses.php       # Expenses UI & logic
│   ├── class-purchases.php      # Purchases UI & logic
│   └── class-sales.php          # Sales UI & logic
└── assets/
    ├── css/
    │   └── admin.css            # Admin panel styles
    └── js/
        └── admin.js             # AJAX handler & interactivity
