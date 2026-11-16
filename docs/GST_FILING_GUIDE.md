# GST Filing Mapping Guide (GSTR-1 & GSTR-3B)

This document explains how each column from the WHMCS GST Invoice Report maps directly to official Indian GST return tables, and how to use the **1-Click GSTR-1 JSON Export** feature.

---

## ⚡ 1-Click "Download GSTR-1 JSON" (Direct Upload to GST Portal)

The report now features a green **Download GSTR-1 JSON (GST Portal)** button at the top of the filters panel.

### What the JSON Payload Generates:
1. **`b2b` (Section 4)**: 
   - Grouped by recipient GSTIN (`ctin`).
   - Generates invoice numbers (`inum`), dates (`idt` in `DD-MM-YYYY`), total values (`val`), Place of Supply (`pos` 2-digit code), Reverse Charge (`rchrg = 'N'`), and item tax breakdown (`rt = 18%`, taxable value `txval`, `iamt`, `camt`, `samt`).
2. **`b2cs` (Section 7 - B2C Others)**:
   - Aggregated by Place of Supply (`pos`), Supply Type (`INTRA` vs `INTER`), and Rate (`18%`).
3. **`hsn` (Section 12 - HSN/SAC Summary)**:
   - Aggregates invoice lines into SAC codes (`998319`, `998315`, `998314`, `998313`) with taxable values and tax splits.

### How to Upload:
1. In your WHMCS Admin, open the report, pick your **Financial Month**, and click **Download GSTR-1 JSON**.
2. Save the generated `GSTR1_<GSTIN>_<MMYYYY>.json` file.
3. Log into **[services.gst.gov.in](https://services.gst.gov.in)** &rarr; **Returns Dashboard**.
4. Select the Return Filing Period &rarr; click **Prepare Offline** &rarr; **Upload**.
5. Upload the JSON file. The portal automatically populates your B2B, B2C, and HSN tables without manual Excel data-entry!

---

## 📋 Manual Table-by-Table Reference

### Table 4: B2B Invoices (Taxable outward supplies to registered persons)
Filter report by: **`B2B / B2C: B2B`**

| GSTR-1 Field | WHMCS Report Column | Notes |
|---|---|---|
| **GSTIN/UIN of Recipient** | Client GSTIN | Must be valid 15-character GSTIN |
| **Receiver Name** | Client / Company | Client company name or full name |
| **Invoice Number** | Invoice # | WHMCS sequential invoice number |
| **Invoice Date** | Invoice Date | Date of invoice generation |
| **Invoice Value** | Total Invoice (INR) | Gross amount including GST |
| **Place of Supply (POS)** | Place of Supply | Client's state |
| **Supply Type** | Computed | Intra-state (CGST+SGST) / Inter-state (IGST) |
| **Rate** | 18% | Standard rate for hosting/domain IT services |
| **Taxable Value** | Taxable Value (INR) | Subtotal before tax |
| **Integrated Tax** | IGST (INR) | Populated when POS &ne; Home State |
| **Central Tax** | CGST (INR) | Populated when POS = Home State |
| **State Tax** | SGST (INR) | Populated when POS = Home State |

---

### Table 7: B2C (Others) (Supplies to unregistered persons)
Filter report by: **`B2B / B2C: B2C`**

Summarize by **Place of Supply**:
- Group taxable values by client state.
- Apply 18% GST (CGST+SGST or IGST based on location).

---

### Table 12: HSN-wise Summary of Outward Supplies

The report includes the detected **HSN/SAC** codes:

| SAC Code | Description | Typical WHMCS Line Items |
|---|---|---|
| **`998319`** | Other professional, technical & business services | Domain Registrations, Renewals, Transfers, DNS addons |
| **`998315`** | Hosting and infrastructure provisioning | Shared, VPS, Dedicated, Cloud Hosting |
| **`998314`** | IT software design and development | Website design, Web application development, SEO, AMC |
| **`998313`** | IT consulting & support services | General consulting, setup fees, miscellaneous IT support |

---

## 2. GSTR-3B Mapping

### Table 3.1: Details of Outward Supplies and inward supplies liable to reverse charge

| Section | Value from Report |
|---|---|
| **(a) Outward taxable supplies (other than zero rated, nil rated and exempted)** | - **Total Taxable Value**: Sum of `Taxable Value (INR)`<br>- **Integrated Tax**: Sum of `IGST (INR)`<br>- **Central Tax**: Sum of `CGST (INR)`<br>- **State/UT Tax**: Sum of `SGST (INR)` |
| **(b) Outward taxable supplies (zero rated)** | Export invoices to overseas clients (if 0% tax applied) |
