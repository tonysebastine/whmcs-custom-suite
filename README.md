# WHMCS Custom Suite

Custom reports, hooks, modules, and utilities for WHMCS installations.

## Contents

- `modules/reports/gst_invoice_report.php` - GST report with B2B/B2C tagging, HSN/SAC codes, state-wise tax split (CGST+SGST vs IGST), and direct GSTR-1 JSON export.
- `modules/reports/financial_year_audit_report.php` - Annual financial year audit report covering turnover, output tax, gateway charges, and net bank receipts.
- `modules/addons/` - Custom addon modules.
- `modules/gateways/` - Custom payment gateways.
- `modules/registrars/` - Custom registrar modules.
- `includes/hooks/` - Custom event hooks.
- `templates/` - Custom template overrides.

## Reports Overview

### 1. GST Invoice Report (`gst_invoice_report.php`)
- Determines CGST/SGST vs IGST based on client state vs seller home state (default: Kerala).
- Identifies B2B invoices using `tblclients.tax_id` (GSTIN).
- Maps services to SAC codes (`998319` domains, `998315` hosting, `998314` web design/dev, `998313` other IT).
- Includes "Download GSTR-1 JSON" button matching the GST Offline Tool schema (GSTR-1 v2.0).
- Filter by date range, invoice status (`Paid`, `Unpaid`, `All`, `Cancelled`, `Refunded`), and invoice type (`All`, `B2B`, `B2C`).

### 2. Financial Year Audit Report (`financial_year_audit_report.php`)
- Indian financial year selection (April 1 to March 31).
- Columns: Inv #, Inv Date, Date Paid, Client Name, GSTIN, State/POS, Taxable Subtotal, Tax 1, Tax 2, Total Tax, Credit Applied, Gross Total, Gateway, Transaction ID, Gateway Fee, Net Received, Status.
- Filter by financial year, invoice status, and payment gateway.

## Installation

Copy report files to your WHMCS root:

```bash
cp modules/reports/gst_invoice_report.php /path/to/whmcs/modules/reports/
cp modules/reports/financial_year_audit_report.php /path/to/whmcs/modules/reports/
```

Access via WHMCS Admin: **Reports** -> **More Reports**.

## Compatibility

- WHMCS 7.8 through 8.13+
- PHP 7.4 through 8.5+
- MySQL 5.7+ / MariaDB 10.3+

## License

MIT
