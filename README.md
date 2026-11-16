# WHMCS Statutory Tax & Audit Suite

[![WHMCS Compatibility](https://img.shields.io/badge/WHMCS-7.x%20%7C%208.x%20%7C%20Latest-blue.svg)](https://www.whmcs.com/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.0%20%7C%208.1%20%7C%208.2%20%7C%208.3%20%7C%208.4%20%7C%208.5%2B-green.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-orange.svg)](LICENSE)

A complete, production-grade **Statutory Audit & Tax Reporting Suite for WHMCS**. Tailored for web hosting companies, domain registrars, and cloud service providers to satisfy Indian GST compliance (**GSTR-1**, **GSTR-3B**, **GSTR-9**) and annual **Financial Year Company Audits** (Chartered Accountant / Tax P&L).

Engineered to be forward-compatible with **latest WHMCS versions** and modern **PHP 8.2, 8.3, 8.4, and PHP 8.5+** runtimes.

---

## 📦 What's Included in this Suite

This repository provides **two native WHMCS report modules**:

1. **`gst_invoice_report.php`** — **GST Invoice Report (GSTR-1 & GSTR-3B)**:
   - Automated **CGST + SGST vs IGST** tax splitting based on seller home state (e.g. *Kerala*).
   - **B2B vs B2C** categorization via `tblclients.tax_id` (GSTIN).
   - Dynamic **HSN / SAC Code Resolution** (`998319` for domains, `998315` for hosting, `998314` for web design/development, `998313` for IT services).
   - Monthly and quarterly date range filtering with real-time KPI cards.

2. **`financial_year_audit_report.php`** — **Financial Year Audit Report (April 1 &ndash; March 31)**:
   - Dedicated statutory fiscal year selection (e.g. `FY 2025-2026`, `FY 2024-2025`, etc.).
   - Reconciles **Taxable Sales Turnover**, **GST Output**, **Credit Wallet deductions**, and **Payment Gateway Fees**.
   - Computes **Net Realized Cash** for bank statement matching.
   - Filterable by individual **Payment Gateways** (e.g., HDFC SmartGateway, CCAvenue, Stripe, PayPal) to audit payment aggregator settlement sheets.

---

## 📁 Repository Directory Structure

```
whmcs-gst-invoice-report/
├── modules/
│   └── reports/
│       ├── gst_invoice_report.php             # GST GSTR-1/3B filing report
│       └── financial_year_audit_report.php    # Statutory Financial Year audit report
├── docs/
│   ├── INSTALLATION.md                        # Step-by-step setup guide
│   ├── GST_FILING_GUIDE.md                    # Table-by-table mapping for GSTR-1 & GSTR-3B
│   ├── AUDIT_REPORT_GUIDE.md                  # CA audit guide & accounting formulas
│   └── CHANGELOG.md                           # Version and release history
├── .gitignore
└── README.md                                  # Complete documentation overview
```

---

## 🚀 Quick Installation (Under 1 Minute)

1. **Clone or Download** this repository:
   ```bash
   git clone https://github.com/<your-username>/whmcs-gst-invoice-report.git
   ```

2. **Copy the Reports into your WHMCS**:
   ```
   whmcs-gst-invoice-report/modules/reports/gst_invoice_report.php
     ===> [WHMCS_ROOT]/modules/reports/gst_invoice_report.php

   whmcs-gst-invoice-report/modules/reports/financial_year_audit_report.php
     ===> [WHMCS_ROOT]/modules/reports/financial_year_audit_report.php
   ```

3. **Access Reports in Admin**:
   - Log into your WHMCS Admin Area (`/gridlock-manager`).
   - Go to **Reports &rarr; More Reports**.
   - You will see:
     - **GST Invoice Report (GSTR-1 / GSTR-3B)**
     - **Financial Year Audit Report**

---

## 📊 Summary of Report Columns

### 1. Financial Year Audit Report Columns
- **Inv #** *(clickable admin link)*
- **Inv Date** *(accrual date)*
- **Date Paid** *(realization date)*
- **Client Details** *(company / personal)*
- **Tax ID / GSTIN**
- **State / POS**
- **Taxable Amount** *(turnover)*
- **Tax 1 (CGST/IGST)**
- **Tax 2 (SGST)**
- **Total Tax**
- **Credit Applied**
- **Gross Total**
- **Payment Gateway**
- **Transaction ID**
- **Gateway Fee** *(aggregator fee)*
- **Net Received** *(bank realization)*
- **Status**

### 2. GST Invoice Report Columns
- **Invoice #**, **Invoice Date**, **Date Paid**, **Client / Company**, **Client GSTIN**, **HSN/SAC Code**, **Type (`B2B`/`B2C`)**, **Place of Supply**, **Taxable Value**, **CGST**, **SGST**, **IGST**, **Total Tax**, **Total Invoice**, **Status**.

---

## 📋 Compatibility Matrix

| Component | Supported Versions |
|---|---|
| **WHMCS** | **7.8 through 8.13+ & Latest WHMCS Releases** |
| **PHP** | **7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5+** |
| **Database** | **MySQL 5.7+ / MariaDB 10.3+** |

---

## 📄 License

Distributed under the **MIT License**.
