# Installation & Setup Guide

This guide walks you through setting up the **GST Invoice Report** on your WHMCS installation.

---

## Prerequisites
- WHMCS version 7.x or 8.x
- PHP 7.4, 8.1, or 8.2 with `pdo_mysql` and `json` extensions
- Administrator access with **Reports** permission

---

## Step 1: File Upload

Upload `gst_invoice_report.php` into your WHMCS reports directory:

```
<WHMCS_DOCUMENT_ROOT>/modules/reports/gst_invoice_report.php
```

### Examples by Environment:
- **cPanel / Linux**: `/home/username/public_html/modules/reports/gst_invoice_report.php`
- **Windows / IIS / Plesk**: `C:\inetpub\vhosts\yourdomain.com\httpdocs\modules\reports\gst_invoice_report.php`
- **Docker**: `/var/www/html/modules/reports/gst_invoice_report.php`

---

## Step 2: Set File Permissions
Ensure the file has read permissions for the web server user:
```bash
chmod 644 modules/reports/gst_invoice_report.php
```

---

## Step 3: Accessing the Report in WHMCS

1. Open your browser and log in to the **WHMCS Admin Area**.
2. From the main menu, navigate to **Reports** &rarr; **More Reports** (or go to `admin/reports.php`).
3. In the list of available reports, click on **GST Invoice Report (GSTR-1 / GSTR-3B)**.

---

## Step 4: Configuring Parameters

1. **Date Range**: Select the calendar month or custom date period.
2. **Seller Home State**:
   - Default is set to **Kerala**.
   - If your GST registered state is different (e.g. *Maharashtra*, *Delhi*, *Karnataka*), type your state name in the text field.
3. **Invoice Status**:
   - `Paid`: Recommended for calculating actual tax liability received (Cash basis).
   - `All`: For viewing total turnover including pending accounts receivable.
4. **B2B / B2C Filter**:
   - `All`: View complete invoice ledger.
   - `B2B`: Only clients with a GSTIN.
   - `B2C`: Retail / consumer clients without GSTIN.
5. Click **Generate Report**.

---

## Step 5: Exporting for Tax Filing
At the bottom of the report page:
- Click **CSV Export** to download the clean spreadsheet for filing via the GST Offline Tool or your Chartered Accountant (CA).

