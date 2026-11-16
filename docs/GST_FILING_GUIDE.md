# GST Filing Guide (GSTR-1 & GSTR-3B)

## GSTR-1 JSON Export

Click the **Download GSTR-1 JSON (GST Portal)** button in the report header.

- Generates `GSTR1_<GSTIN>_<MMYYYY>.json` matching GST Offline Tool schema v2.0.
- Contains:
  - `b2b` section grouped by client GSTIN with invoice numbers, dates (`DD-MM-YYYY`), taxable values, POS codes, and tax rates.
  - `b2cs` section aggregated by 2-digit POS code and tax rates.
  - `hsn` summary aggregated by SAC code (`998319`, `998315`, `998314`, `998313`).

### Portal Upload Steps
1. Download the JSON for your filing month.
2. Log in at `services.gst.gov.in` -> **Returns Dashboard**.
3. Select Period -> **Prepare Offline** -> **Upload**.
4. Select the `.json` file.

## Manual Field Mapping

### Table 4: B2B Invoices
Filter by `B2B / B2C: B2B`.

- **GSTIN**: Client GSTIN (`tblclients.tax_id`)
- **Invoice Number / Date**: WHMCS invoice number and date
- **Taxable Value**: Subtotal
- **Tax Breakdown**: CGST + SGST (Intra-state) or IGST (Inter-state)

### Table 7: B2C Invoices
Filter by `B2B / B2C: B2C`. Summarize by Place of Supply.

### Table 12: HSN Summary
- `998319`: Domains and domain addons
- `998315`: Hosting and server infrastructure
- `998314`: Web design, development, SEO, AMC
- `998313`: Other IT consulting and services
