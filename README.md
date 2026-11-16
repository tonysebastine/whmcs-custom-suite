# WHMCS GST Invoice Report (GSTR-1 & GSTR-3B)

[![WHMCS Compatibility](https://img.shields.io/badge/WHMCS-7.x%20%7C%208.x%20%7C%20Latest-blue.svg)](https://www.whmcs.com/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.0%20%7C%208.1%20%7C%208.2%20%7C%208.3%20%7C%208.4%20%7C%208.5%2B-green.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-orange.svg)](LICENSE)

A complete, production-ready **GST Invoice Reporting Module for WHMCS**. Designed specifically for Indian web hosting providers, domain registrars, and cloud agencies to simplify monthly and quarterly GST returns (**GSTR-1** and **GSTR-3B**).

Engineered to be fully forward-compatible with **latest WHMCS releases** and modern **PHP 8.2, 8.3, 8.4, and PHP 8.5+** runtimes.

---

## 🌟 Key Features

- **Automated Tax Segregation (Intra vs Inter-State)**:
  - Dynamically calculates **CGST + SGST** (Intra-state) and **IGST** (Inter-state) based on client location relative to your configured seller home state (e.g. *Kerala*).
- **B2B vs B2C Segregation**:
  - Automatically identifies **B2B** clients using WHMCS's native `tblclients.tax_id` (GSTIN/UIN).
  - Categorizes customers without GSTIN as **B2C**.
- **Dynamic HSN / SAC Code Assignment**:
  - Maps line items to official Indian SAC codes:
    - **`998319`**: Domain Registration, Transfers, and Domain Addons.
    - **`998315`**: Web Hosting, Cloud & Server Infrastructure.
    - **`998314`**: Website Design, Development, SEO, and Maintenance (AMC).
    - **`998313`**: General IT Consulting & Support Services.
- **Modern PHP & WHMCS Architecture**:
  - `declare(strict_types=1);` compliant.
  - PHP 8.2+ deprecation-free: zero dynamic property warnings, null-coalescing on all properties.
  - Native `str_contains()` with graceful fallback for legacy PHP.
  - Cross-compatible date parsing across both legacy WHMCS 7.x and latest WHMCS 8.x (`WHMCS\Carbon::parseDateRangeValue`).
  - Context-aware XSS escaping on all client/invoice outputs using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **Flexible Status & Period Filters**:
  - Filter by date range (uses WHMCS admin date-range picker).
  - Filter by invoice status: `Paid` *(default, cash basis)*, `Unpaid`, `All`, `Cancelled`, `Refunded`, or `Payment Pending`.
  - Filter by invoice type: `All`, `B2B only`, or `B2C only`.
- **Summary Metric Cards**:
  - Instant dashboard overview of total invoice counts, B2B/B2C ratio, Taxable Value, CGST, SGST, IGST, and Gross Total.
- **Export Ready**:
  - Built-in one-click **CSV / Excel** export compatible with GST compliance software and CA reconciliation.
- **Zero Database Modification**:
  - Read-only report module. Does not alter your WHMCS database schema or modify tables.

---

## 📁 Repository Directory Structure

```
whmcs-gst-invoice-report/
├── modules/
│   └── reports/
│       └── gst_invoice_report.php      # Main WHMCS report module (PHP 7.4 - 8.5+)
├── docs/
│   ├── INSTALLATION.md                 # Detailed step-by-step setup
│   ├── GST_FILING_GUIDE.md             # How to use data in GSTR-1 & 3B
│   └── CHANGELOG.md                    # Release history
├── .gitignore
├── README.md                           # Documentation overview
```

---

## 🚀 Quick Installation (Under 1 Minute)

1. **Download or Clone** this repository:
   ```bash
   git clone https://github.com/<your-username>/whmcs-gst-invoice-report.git
   ```

2. **Upload the Report File**:
   Copy the report file directly into your WHMCS directory:
   ```
   whmcs-gst-invoice-report/modules/reports/gst_invoice_report.php
     ===> [WHMCS_ROOT]/modules/reports/gst_invoice_report.php
   ```

3. **Access the Report**:
   - Log into your WHMCS Admin Area.
   - Navigate to **Reports &rarr; More Reports**.
   - Select **GST Invoice Report (GSTR-1 / GSTR-3B)**.

---

## ⚙️ Configuration

### Default Seller State
The module is pre-configured with **Kerala** as the default home state. 
- You can change the seller state on-the-fly via the report filter field.
- To set a different permanent default, open `modules/reports/gst_invoice_report.php` and update line 25:
  ```php
  $homeState = 'Maharashtra'; // Or your registered state
  ```

### Customer GSTIN Field
The module natively uses the default WHMCS Tax ID field:
- **Client Profile &rarr; Profile &rarr; Tax ID** (`tblclients.tax_id`).

---

## 📋 Compatibility Matrix

| Component | Supported Versions | Notes |
|---|---|---|
| **WHMCS** | **7.8 – 8.13+ & Latest** | Uses native Capsule ORM, fallback Request helpers |
| **PHP** | **7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5+** | Full strict types, zero deprecation notices |
| **Database** | **MySQL 5.7+ / MariaDB 10.3+** | Standard WHMCS database engines |

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/<your-username>/whmcs-gst-invoice-report/issues).

---

## 📄 License

Distributed under the **MIT License**.
