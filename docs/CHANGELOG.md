# Changelog

All notable changes to the **WHMCS GST Invoice Report** will be documented in this file.

## [1.1.0] - 2026-11-16
### Added
- **HSN / SAC Code Resolution**: Auto-detection of SAC codes (`998319` for domains, `998315` for hosting, `998314` for web design, `998313` for IT services).
- **Expanded Status Filter**: Added support for `Paid`, `Unpaid`, `All`, `Cancelled`, `Refunded`, and `Payment Pending`.
- **B2B / B2C Filter**: One-click toggle between All invoices, B2B only, and B2C only.
- **Summary Cards**: Header dashboard cards for Invoices count, Taxable base, CGST, SGST, IGST, and Total invoice value.

### Fixed
- Fixed date-range parsing using WHMCS 8.x native `Carbon::parseDateRangeValue()`.
- Fixed Intra-state / Inter-state tax allocation logic based on seller's home state.

---

## [1.0.0] - Initial Release
- Initial release of the GST invoice report for WHMCS.
- Automatic Intra/Inter-state detection and tax splitting.
- Support for `tblclients.tax_id` as GSTIN.
- Clickable invoice references with direct links to WHMCS admin invoice view.
