# Changelog

All notable changes to the **WHMCS Statutory Tax & Audit Suite** will be documented in this file.

## [1.4.0] - 2026-11-16
### Added
- **1-Click GSTR-1 JSON Generator (`Download GSTR-1 JSON`)**:
  - Implements the official **GST Offline Tool JSON v2.0 Schema** directly inside `gst_invoice_report.php`.
  - Automatically exports:
    - `b2b` section grouped by recipient GSTIN with invoice number, date (DD-MM-YYYY), taxable value, and tax breakdown.
    - `b2cs` section aggregated by 2-digit Place of Supply (POS) and tax rates.
    - `hsn` section aggregating outward supplies by SAC code (`998319`, `998315`, `998314`, `998313`).
  - Single-click browser download of `GSTR1_<GSTIN>_<MMYYYY>.json` ready for direct upload to `services.gst.gov.in`.
- **State to 2-Digit GST Code Converter**:
  - Added helper function `w3GetGstStateCode()` resolving Indian states/UTs to official 2-digit GST state codes (e.g. Kerala -> `32`, Delhi -> `07`, Maharashtra -> `27`).

---

## [1.3.0] - 2026-11-16
### Added
- **Financial Year Audit Report (`financial_year_audit_report.php`)**:
  - Automated Financial Year selection (April 1 to March 31).
  - Detailed Statutory Audit columns including Sales Turnover, Output Tax, Credit applied, Gateway charges (MDR), and Net Realized Revenue.
  - Integration with `tblaccounts` to extract real transaction IDs and fees.
  - Payment Gateway dropdown filter (e.g. HDFC SmartGateway, CCAvenue, Stripe, PayPal) for direct bank and aggregator reconciliation.
- **Audit Documentation (`docs/AUDIT_REPORT_GUIDE.md`)**:
  - Complete documentation detailing accounting definitions, turnover reconciliation formulas, and CA audit workflows.

---

## [1.2.0] - 2026-11-16
### Added
- **PHP 8.2 through 8.5+ Compatibility**:
  - Enforced strict typing (`declare(strict_types=1);`).
  - Added null-safe property checks (`?? ''`, `?? 0.0`) avoiding dynamic property and null-to-scalar deprecations in PHP 8.2+.
  - Modernized string evaluation using PHP 8 native `str_contains()`.
- **Latest WHMCS Compatibility**:
  - Dynamic fallback for request parameters: uses `App::getFromRequest()` when available, falls back to sanitized superglobals.
  - Multi-version date range parsing supporting latest `Carbon::parseDateRangeValue()` with standard fallback for older setups.
  - Comprehensive HTML escaping (`htmlspecialchars` with `ENT_QUOTES`) across all table cells to comply with modern WHMCS security standards.

---

## [1.1.0] - 2026-11-16
### Added
- **HSN / SAC Code Resolution**: Auto-detection of SAC codes (`998319` for domains, `998315` for hosting, `998314` for web design, `998313` for IT services).
- **Expanded Status Filter**: Added support for `Paid`, `Unpaid`, `All`, `Cancelled`, `Refunded`, and `Payment Pending`.
- **B2B / B2C Filter**: One-click toggle between All invoices, B2B only, and B2C only.
- **Summary Cards**: Header dashboard cards for Invoices count, Taxable base, CGST, SGST, IGST, and Total invoice value.

---

## [1.0.0] - Initial Release
- Initial release of the GST invoice report for WHMCS.
- Automatic Intra/Inter-state detection and tax splitting.
- Support for `tblclients.tax_id` as GSTIN.
- Clickable invoice references with direct links to WHMCS admin invoice view.
