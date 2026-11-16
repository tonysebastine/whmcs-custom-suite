# Installation & Setup

## Setup Instructions

1. Copy the report files to your WHMCS reports directory:
   ```
   whmcs-custom-suite/modules/reports/*.php -> <WHMCS_ROOT>/modules/reports/
   ```

2. Verify file permissions:
   ```bash
   chmod 644 modules/reports/gst_invoice_report.php
   chmod 644 modules/reports/financial_year_audit_report.php
   ```

3. Open your WHMCS Admin Area (`/admin` or custom admin path).

4. Navigate to **Reports** -> **More Reports** and select:
   - **GST Invoice Report (GSTR-1 / GSTR-3B)**
   - **Financial Year Audit Report**

5. Filter by your target date range, state, and status, then click **Generate Report** or use the export buttons.
