# WHMCS Custom Suite

[![WHMCS Compatibility](https://img.shields.io/badge/WHMCS-7.x%20%7C%208.x%20%7C%20Latest-blue.svg)](https://www.whmcs.com/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.0%20%7C%208.1%20%7C%208.2%20%7C%208.3%20%7C%208.4%20%7C%208.5%2B-green.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-orange.svg)](LICENSE)

A modular, production-ready repository for **custom WHMCS modules, reports, hooks, addons, and registrar/gateway extensions**.

Designed to serve as a central, organized home for all custom WHMCS enhancements—starting with statutory tax compliance, financial year audit ledgers, and extensible for future custom development.

---

## 📁 Repository Architecture

```
whmcs-custom-suite/
├── modules/
│   ├── reports/                                # Custom WHMCS Admin Reports
│   │   ├── gst_invoice_report.php              # GST Invoice Report (GSTR-1 & GSTR-3B)
│   │   └── financial_year_audit_report.php     # Statutory Financial Year Audit Report
│   ├── addons/                                 # Custom Addon Modules (place new addons here)
│   ├── gateways/                               # Custom Payment Gateways
│   └── registrars/                             # Custom Domain Registrars
├── includes/
│   └── hooks/                                  # Custom Action & Filter Hooks
├── templates/                                  # Custom Theme Template overrides (.tpl)
├── docs/                                       # Complete documentation & guides
│   ├── INSTALLATION.md                         # General setup and deployment guide
│   ├── GST_FILING_GUIDE.md                     # GSTR-1 & GSTR-3B tax return mapping
│   ├── AUDIT_REPORT_GUIDE.md                   # CA statutory audit & ledger guide
│   └── CHANGELOG.md                            # Version and release history
├── .gitignore
└── README.md                                   # Suite overview
```

---

## 🌟 Included Components

### 1. Reports (`modules/reports/`)

#### A. GST Invoice Report (`gst_invoice_report.php`)
- **Automated Tax Splitting**: Computes **CGST + SGST** (Intra-state) vs **IGST** (Inter-state) based on client location vs configured seller home state (*Kerala*).
- **B2B / B2C Segregation**: Automatically checks native `tblclients.tax_id` for GSTIN numbers.
- **Dynamic HSN / SAC Mapping**: Auto-assigns SAC codes (`998319` for domains, `998315` for hosting, `998314` for web design/development, `998313` for IT support).
- **Date & Status Filters**: Filter by calendar date range, status (`Paid`, `Unpaid`, `All`, `Cancelled`, `Refunded`), and invoice type.

#### B. Financial Year Audit Report (`financial_year_audit_report.php`)
- **Indian Financial Year Cycle**: One-click selection of statutory fiscal years (April 1 &ndash; March 31).
- **Full Audit Trail**: Reconciles Taxable Sales Turnover, Total Output Tax, Credit applied from wallet, and Gateway processing charges (MDR).
- **Payment Gateway Payout Reconciliation**: Filter by specific gateway (e.g. HDFC SmartGateway, CCAvenue, Stripe, PayPal) to match monthly settlement payout sheets.

---

## 🚀 Quick Deployment to WHMCS

Copy the contents of `modules/`, `includes/`, or `templates/` directly into your WHMCS root directory:

```bash
# Example: Deploy all custom reports
cp -r modules/reports/* /path/to/whmcs/modules/reports/
```

---

## 📋 Standards & Compatibility

All code in this suite adheres to:
- **Strict Typing**: `declare(strict_types=1);` compliant.
- **PHP Compatibility**: Fully verified across **PHP 7.4 through PHP 8.2, 8.3, 8.4, and 8.5+** with zero dynamic property or null deprecation notices.
- **WHMCS Support**: Works seamlessly on **WHMCS 7.8, 8.0 through 8.13+ and latest WHMCS releases**.
- **Security**: Strict context-aware HTML escaping (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`) on all dynamic inputs and outputs.

---

## 📄 License

Distributed under the **MIT License**.
