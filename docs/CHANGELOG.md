# Changelog

## [1.4.0] - 2026-11-16
- Added GSTR-1 v2.0 JSON export button to `gst_invoice_report.php`.
- Added state-to-2-digit GST code mapping helper.

## [1.3.0] - 2026-11-16
- Added `financial_year_audit_report.php` for Indian financial year (April-March) audit reporting.
- Added payment gateway filtering and fee tracking via `tblaccounts`.

## [1.2.0] - 2026-11-16
- Updated compatibility for PHP 7.4 through PHP 8.5+.
- Added strict types and null coalescing on database fields.

## [1.1.0] - 2026-11-16
- Added HSN/SAC code detection (`998319`, `998315`, `998314`, `998313`).
- Added status and B2B/B2C filters.

## [1.0.0] - Initial Release
- Initial GST invoice report for WHMCS.
