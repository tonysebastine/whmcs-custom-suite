# WHMCS Custom Suite

Repository of custom reports, modules, gateways, and hooks for WHMCS.

---

## Repository Structure

```
whmcs-custom-suite/
├── modules/
│   ├── reports/
│   │   ├── gst_invoice_report.php          # GST report (GSTR-1, GSTR-3B, HSN/SAC, JSON export)
│   │   └── financial_year_audit_report.php # Statutory FY audit ledger (Apr 1 - Mar 31)
│   ├── addons/                             # Custom addon modules
│   ├── gateways/                           # Custom payment gateways
│   └── registrars/                         # Custom registrar modules
├── includes/
│   └── hooks/                              # Custom action & filter hooks
├── templates/                              # Theme overrides (.tpl)
└── docs/                                   # Technical documentation
    ├── INSTALLATION.md                     # Deployment steps
    ├── GST_FILING_GUIDE.md                 # GSTR-1 & GSTR-3B mapping guide
    ├── AUDIT_REPORT_GUIDE.md               # CA audit & reconciliation guide
    └── CHANGELOG.md                        # Version history
```

---

## Included Modules

### 1. GST Invoice Report (`gst_invoice_report.php`)

Report for filing Indian GST returns (GSTR-1 and GSTR-3B).

| Feature | Implementation |
|---|---|
| **Tax Splitting** | Computes CGST + SGST (Intra-state) vs IGST (Inter-state) based on client location vs seller state (default: Kerala). |
| **B2B / B2C** | Classifies B2B using `tblclients.tax_id` (GSTIN); others treated as B2C. |
| **HSN/SAC Mapping** | Auto-detects line item types (`998319` domains, `998315` hosting, `998314` design/dev, `998313` other IT). |
| **GSTR-1 JSON Export** | 1-click download matching the official GST Offline Tool schema v2.0 (`b2b`, `b2cs`, `hsn` sections). |
| **Filters** | Date range, invoice status (`Paid`, `Unpaid`, `All`, `Cancelled`, `Refunded`), invoice type (`All`, `B2B`, `B2C`), and seller state. |

---

### 2. Financial Year Audit Report (`financial_year_audit_report.php`)

Auditing ledger aligned with the Indian Financial Year cycle (April 1 to March 31).

| Metric | Source / Formula |
|---|---|
| **Turnover** | Sum of taxable line items (`tblinvoices.subtotal`) |
| **Output Tax** | Total tax collected (`tblinvoices.tax + tax2`) |
| **Credit Deductions** | Advance / wallet funds applied (`tblinvoices.credit`) |
| **Gateway Charges** | Transaction fees logged in `tblaccounts.fees` |
| **Net Bank Realization** | `Amount In - Fees - Amount Out` |
| **Filters** | Financial Year dropdown (past 6 years), invoice status, and payment gateway. |

---

## Quick Start

### Installation

Copy report files to your WHMCS root directory:

```bash
cp -r modules/reports/* /path/to/whmcs/modules/reports/
```

### Accessing Reports

1. Log in to the WHMCS Admin panel (`/admin` or custom admin path).
2. Go to **Reports** &rarr; **More Reports**.
3. Select **GST Invoice Report (GSTR-1 / GSTR-3B)** or **Financial Year Audit Report**.

---

## Compatibility

| Environment | Supported Versions |
|---|---|
| **WHMCS** | 7.8, 8.0, 8.5, 8.10, 8.13, and latest releases |
| **PHP** | 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5+ (`declare(strict_types=1);` compliant) |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |

---

## Documentation

- [Installation & Deployment Guide](docs/INSTALLATION.md)
- [GST Return Mapping Guide (GSTR-1 & 3B)](docs/GST_FILING_GUIDE.md)
- [Financial Year Audit & Ledger Guide](docs/AUDIT_REPORT_GUIDE.md)
- [Changelog](docs/CHANGELOG.md)

---

## License

MIT
