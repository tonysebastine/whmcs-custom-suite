# Changelog

All notable changes to the **WHMCS GST Invoice Report** will be documented in this file.

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
