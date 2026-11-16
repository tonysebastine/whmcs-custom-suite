# Financial Year Audit Report Guide

## Purpose

Generates a statutory sales and tax ledger for Indian financial years (April 1 to March 31).

## Column Definitions

- **Inv #**: WHMCS invoice ID with direct link
- **Inv Date**: Invoice creation date (accrual basis)
- **Date Paid**: Realization date (cash basis)
- **Client Details**: Client company name or full name
- **Tax ID / GSTIN**: Client tax identifier
- **State / POS**: Place of supply
- **Taxable Amount**: Base turnover excluding taxes
- **Tax 1**: CGST (Intra-state) or IGST (Inter-state)
- **Tax 2**: SGST (Intra-state)
- **Total Tax**: Combined tax output
- **Credit Applied**: Invoiced amount paid via client credit balance
- **Gross Total**: Total billed invoice amount
- **Payment Gateway**: Gateway used for payment
- **Transaction ID**: Reference ID from payment processor
- **Gateway Fee**: Transaction fees recorded in `tblaccounts`
- **Net Received**: `Amount In - Fees - Amount Out`
- **Status**: Invoice status

## Usage

1. Open **Reports** -> **More Reports** -> **Financial Year Audit Report**.
2. Select target Financial Year (e.g. `FY 2025-2026`).
3. Set status filter (`Paid` for realized income, `All` for full ledger).
4. Optionally filter by payment gateway.
5. Export to CSV for audit and accounting reconciliation.
