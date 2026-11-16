# Financial Year Audit & Statutory Ledger Guide

This document explains the accounting and tax audit methodology implemented in **Financial Year Audit Report** (`financial_year_audit_report.php`).

---

## 1. What is the Financial Year Audit Report?

For businesses in India and financial jurisdictions following an **April 1 &ndash; March 31 Financial Year cycle**, annual company audits, Income Tax (ITR) filing, and GST annual reconciliation (GSTR-9/9C) require reconciling:
1. **Total Billed Revenue (Turnover)**.
2. **Total Tax Output Liability (GST)**.
3. **Credit Applied from Balances / Advance Deposits**.
4. **Payment Gateway Deduction Charges (MDR / Commission Fees)**.
5. **Net Cash Realized into Bank Accounts**.

---

## 2. Table Column Mapping for Auditors & CAs

| Column | Description | Audit Significance |
|---|---|---|
| **Inv #** | Invoice Number (with admin hyperlink) | Voucher/Invoice reference trail |
| **Inv Date** | Invoice Generation Date | Accrual accounting revenue date |
| **Date Paid** | Realization Timestamp | Cash accounting revenue date |
| **Client Details** | Customer or Registered Company Name | Debtor entity identity |
| **Tax ID / GSTIN** | Customer's GSTIN/PAN | B2B counter-party validation |
| **State / POS** | Place of Supply | Jurisdiction of supply |
| **Taxable Amount** | Base invoice subtotal | Core Sales Turnover (excludes tax) |
| **Tax 1 (CGST/IGST)** | Level 1 Tax component | Central / Integrated tax output |
| **Tax 2 (SGST)** | Level 2 Tax component | State tax output |
| **Total Tax** | Total GST collected | Output tax liability payable |
| **Credit Applied** | Funds used from credit wallet | Non-cash settlement adjustment |
| **Gross Total** | Gross invoice total | Total Accounts Receivable (AR) billed |
| **Payment Gateway** | Gateway used (e.g. HDFC, CCAvenue, PayPal, Stripe) | Channel verification |
| **Transaction ID** | Gateway Settlement Reference ID | Bank statement cross-matching |
| **Gateway Fee** | Commission / MDR deducted by gateway | Direct operating expense deduction |
| **Net Received** | Net amount after gateway fee deduction | Actual cash credited to merchant account |
| **Status** | Status of the invoice (`Paid`, `Unpaid`, etc.) | Debtors aging / unpaid tracking |

---

## 3. Financial Reconciliation Formulas

$$\text{Gross Invoiced} = \text{Taxable Turnover} + \text{Total Output GST}$$

$$\text{Net Bank Realized} = \text{Gross Invoiced} - \text{Credit Applied} - \text{Gateway Fee}$$

---

## 4. How to Use for Annual Audit & Tax Filing

1. Log into your WHMCS Admin panel.
2. Navigate to **Reports &rarr; More Reports &rarr; Financial Year Audit Report**.
3. Select the desired **Financial Year** from the dropdown (e.g. `FY 2025-2026 (Apr 2025 - Mar 2026)`).
4. Select Status:
   - Choose **`Paid`** for audited realized income (standard for P&L and GST).
   - Choose **`All`** to verify total book turnover and accounts receivable.
5. Optionally filter by **Payment Gateway** (e.g., *HDFC SmartGateway* or *CCAvenue*) to reconcile month-by-month settlement payout sheets.
6. Click **Generate Audit Report** &rarr; use the **CSV Export** button at the bottom to hand off clean, formatted workbooks to your Chartered Accountant (CA).

