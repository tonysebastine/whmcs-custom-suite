<?php

/**
 * Financial Year Audit Report (FY Audit & Tax Ledger)
 * Designed for WHMCS 7.x, 8.x, 8.13+ & modern PHP 7.4 through 8.2, 8.3, 8.4, 8.5+
 * Place in: modules/reports/financial_year_audit_report.php
 */

declare(strict_types=1);

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Financial Year Audit Report";
$reportdata["description"] = "Comprehensive statutory audit ledger for Chartered Accountants (CA) & tax filing covering sales turnover, GST collected, gateway transaction fees, refunds, and receivables by Financial Year.";
$reportdata["currencyselections"] = true;

// 1. Determine Financial Year (April 1 to March 31)
// If today is before April, default current FY is (Year-1)-Year, else Year-(Year+1)
$currentMonth = (int) date('n');
$currentCalendarYear = (int) date('Y');
$defaultStartYear = ($currentMonth < 4) ? ($currentCalendarYear - 1) : $currentCalendarYear;

$selectedFY = (string) (class_exists('App') && method_exists('App', 'getFromRequest') 
    ? App::getFromRequest('financial_year') 
    : ($_REQUEST['financial_year'] ?? ''));

if (trim($selectedFY) === '' || !preg_match('/^(\d{4})-(\d{4})$/', $selectedFY, $matches)) {
    $startYear = $defaultStartYear;
    $endYear = $startYear + 1;
    $selectedFY = "{$startYear}-{$endYear}";
} else {
    $startYear = (int) $matches[1];
    $endYear = (int) $matches[2];
}

$fyStartDate = "{$startYear}-04-01 00:00:00";
$fyEndDate = "{$endYear}-03-31 23:59:59";

// 2. Gateway Filter
$gatewayFilter = (string) (class_exists('App') && method_exists('App', 'getFromRequest') 
    ? App::getFromRequest('gateway') 
    : ($_REQUEST['gateway'] ?? ''));

// 3. Status Filter (Paid, All, Refunded, Cancelled)
$statusFilter = (string) (class_exists('App') && method_exists('App', 'getFromRequest') 
    ? App::getFromRequest('statusfilter') 
    : ($_REQUEST['statusfilter'] ?? ''));
if (trim($statusFilter) === '') {
    $statusFilter = 'Paid';
}

$currencyID = isset($currencyid) ? (int) $currencyid : 1;
$isPrintView = isset($print) && (bool) $print;

// Fetch active gateways for filter dropdown
$availableGateways = Capsule::table('tblpaymentgateways')
    ->where('setting', '=', 'name')
    ->select('gateway', 'value')
    ->orderBy('value', 'asc')
    ->get();

// Build Filter UI
if (!$isPrintView) {
    // Generate FY dropdown options for last 6 years + 1 upcoming
    $fyOptions = '';
    for ($y = ($currentCalendarYear + 1); $y >= ($currentCalendarYear - 5); $y--) {
        $fyVal = "{$y}-" . ($y + 1);
        $selected = ($selectedFY === $fyVal) ? 'selected' : '';
        $fyOptions .= "<option value=\"{$fyVal}\" {$selected}>FY {$fyVal} (Apr {$y} - Mar " . ($y + 1) . ")</option>";
    }

    $gwOptions = '<option value="">All Payment Gateways</option>';
    foreach ($availableGateways as $gw) {
        $selected = ($gatewayFilter === $gw->gateway) ? 'selected' : '';
        $gwName = htmlspecialchars((string) ($gw->value ?: $gw->gateway), ENT_QUOTES, 'UTF-8');
        $gwVal = htmlspecialchars((string) $gw->gateway, ENT_QUOTES, 'UTF-8');
        $gwOptions .= "<option value=\"{$gwVal}\" {$selected}>{$gwName}</option>";
    }

    $statusOptions = '';
    foreach (['Paid', 'All', 'Unpaid', 'Refunded', 'Cancelled'] as $st) {
        $selected = ($statusFilter === $st) ? 'selected' : '';
        $statusOptions .= "<option value=\"{$st}\" {$selected}>{$st}</option>";
    }

    $safeReport = htmlspecialchars((string) ($report ?? 'financial_year_audit_report'), ENT_QUOTES, 'UTF-8');
    $safeCurrencyId = (int) $currencyID;

    $reportdata['headertext'] = <<<HTML
<form method="post" action="reports.php?report={$safeReport}&currencyid={$safeCurrencyId}">
    <div class="report-filters-wrapper">
        <div class="inner-container">
            <h3>Statutory Audit & Tax Filters</h3>
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="inputFY">Financial Year</label>
                        <select id="inputFY" name="financial_year" class="form-control">
                            {$fyOptions}
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputStatus">Invoice Status</label>
                        <select id="inputStatus" name="statusfilter" class="form-control">
                            {$statusOptions}
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputGW">Payment Gateway</label>
                        <select id="inputGW" name="gateway" class="form-control">
                            {$gwOptions}
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="form-group" style="padding-top: 25px;">
                        <button type="submit" class="btn btn-primary btn-block">
                            Generate Audit Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
HTML;
}

// Table Headings (Standard Statutory Audit Format)
$reportdata["tableheadings"] = [
    "Inv #",
    "Inv Date",
    "Date Paid",
    "Client Details",
    "Tax ID / GSTIN",
    "State / POS",
    "Taxable Amount",
    "Tax 1 (CGST/IGST)",
    "Tax 2 (SGST)",
    "Total Tax",
    "Credit Applied",
    "Gross Total",
    "Payment Gateway",
    "Transaction ID",
    "Gateway Fee",
    "Net Received",
    "Status"
];

// Main Invoices Query
$query = Capsule::table('tblinvoices')
    ->select(
        'tblinvoices.*',
        'tblclients.firstname',
        'tblclients.lastname',
        'tblclients.companyname',
        'tblclients.tax_id',
        'tblclients.state as client_state',
        'tblclients.country as client_country'
    )
    ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
    ->where('tblclients.currency', '=', $currencyID);

if ($statusFilter === 'Paid') {
    $query->where('tblinvoices.status', '=', 'Paid')
          ->whereBetween('tblinvoices.datepaid', [$fyStartDate, $fyEndDate]);
} elseif ($statusFilter === 'All') {
    $query->whereBetween('tblinvoices.date', [$fyStartDate, $fyEndDate]);
} else {
    $query->where('tblinvoices.status', '=', $statusFilter)
          ->whereBetween('tblinvoices.date', [$fyStartDate, $fyEndDate]);
}

if ($gatewayFilter !== '') {
    $query->where('tblinvoices.paymentmethod', '=', $gatewayFilter);
}

$results = $query->orderBy('tblinvoices.date', 'asc')->get();

// Preload Accounts / Transactions for fees, trans IDs, and net amounts
$invoiceIds = $results->pluck('id')->all();
$transMap = [];

if (!empty($invoiceIds)) {
    $transactions = Capsule::table('tblaccounts')
        ->whereIn('invoiceid', $invoiceIds)
        ->select('invoiceid', 'transid', 'fees', 'amountin', 'amountout', 'gateway')
        ->get();

    foreach ($transactions as $t) {
        $invId = (int) $t->invoiceid;
        if (!isset($transMap[$invId])) {
            $transMap[$invId] = [
                'transids' => [],
                'fees' => 0.0,
                'amountin' => 0.0,
                'amountout' => 0.0,
                'gateway' => (string) ($t->gateway ?? '')
            ];
        }
        if (!empty($t->transid)) {
            $transMap[$invId]['transids'][] = (string) $t->transid;
        }
        $transMap[$invId]['fees'] += (float) ($t->fees ?? 0.0);
        $transMap[$invId]['amountin'] += (float) ($t->amountin ?? 0.0);
        $transMap[$invId]['amountout'] += (float) ($t->amountout ?? 0.0);
    }
}

// Totals Accumulator
$sumTaxable = 0.0;
$sumTax1 = 0.0;
$sumTax2 = 0.0;
$sumTotalTax = 0.0;
$sumCredit = 0.0;
$sumGross = 0.0;
$sumFees = 0.0;
$sumNetReceived = 0.0;

foreach ($results as $row) {
    $invId = (int) $row->id;
    $invNumber = (!empty($row->invoicenum)) ? $row->invoicenum : (string) $invId;
    $invLink = "<a href=\"invoices.php?action=edit&id={$invId}\" target=\"_blank\">" . htmlspecialchars((string) $invNumber, ENT_QUOTES, 'UTF-8') . "</a>";
    
    $invDate = function_exists('fromMySQLDate') ? fromMySQLDate($row->date) : (string) $row->date;
    $datePaid = (!empty($row->datepaid) && $row->datepaid !== '0000-00-00 00:00:00' && $row->datepaid !== '0000-00-00') 
        ? (function_exists('fromMySQLDate') ? fromMySQLDate($row->datepaid) : (string) $row->datepaid)
        : '-';

    $firstName = trim((string) ($row->firstname ?? ''));
    $lastName = trim((string) ($row->lastname ?? ''));
    $company = trim((string) ($row->companyname ?? ''));
    $clientName = ($company !== '') ? $company : trim("{$firstName} {$lastName}");

    $clientTaxId = trim((string) ($row->tax_id ?? ''));
    $clientState = trim((string) ($row->client_state ?? ''));
    $clientCountry = trim((string) ($row->client_country ?? ''));
    $pos = ($clientState !== '') ? $clientState : $clientCountry;

    $subtotal = (float) ($row->subtotal ?? 0.0);
    $tax1 = (float) ($row->tax ?? 0.0);
    $tax2 = (float) ($row->tax2 ?? 0.0);
    $totalTax = $tax1 + $tax2;
    $credit = (float) ($row->credit ?? 0.0);
    $total = (float) ($row->total ?? 0.0);

    // Transaction & Gateway details
    $gwName = (string) ($row->paymentmethod ?? '');
    $transIdStr = '-';
    $fee = 0.0;
    $netReceived = 0.0;

    if (isset($transMap[$invId])) {
        $tInfo = $transMap[$invId];
        $transIdStr = !empty($tInfo['transids']) ? implode(', ', array_unique($tInfo['transids'])) : '-';
        $fee = (float) $tInfo['fees'];
        $netReceived = (float) ($tInfo['amountin'] - $tInfo['fees'] - $tInfo['amountout']);
    } else {
        // Fallback for paid invoices where transaction details might not be in tblaccounts
        if ($row->status === 'Paid') {
            $netReceived = $total;
        }
    }

    $sumTaxable += $subtotal;
    $sumTax1 += $tax1;
    $sumTax2 += $tax2;
    $sumTotalTax += $totalTax;
    $sumCredit += $credit;
    $sumGross += $total;
    $sumFees += $fee;
    $sumNetReceived += $netReceived;

    $reportdata["tablevalues"][] = [
        $invLink,
        $invDate,
        $datePaid,
        htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'),
        ($clientTaxId !== '') ? htmlspecialchars($clientTaxId, ENT_QUOTES, 'UTF-8') : 'N/A',
        htmlspecialchars($pos, ENT_QUOTES, 'UTF-8'),
        number_format($subtotal, 2, '.', ''),
        number_format($tax1, 2, '.', ''),
        number_format($tax2, 2, '.', ''),
        number_format($totalTax, 2, '.', ''),
        number_format($credit, 2, '.', ''),
        number_format($total, 2, '.', ''),
        htmlspecialchars($gwName, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($transIdStr, ENT_QUOTES, 'UTF-8'),
        number_format($fee, 2, '.', ''),
        number_format($netReceived, 2, '.', ''),
        htmlspecialchars((string) ($row->status ?? ''), ENT_QUOTES, 'UTF-8')
    ];
}

// Format Summary Totals
$fmtTaxable = function_exists('formatCurrency') ? formatCurrency($sumTaxable) : number_format($sumTaxable, 2);
$fmtTotalTax = function_exists('formatCurrency') ? formatCurrency($sumTotalTax) : number_format($sumTotalTax, 2);
$fmtGross = function_exists('formatCurrency') ? formatCurrency($sumGross) : number_format($sumGross, 2);
$fmtFees = function_exists('formatCurrency') ? formatCurrency($sumFees) : number_format($sumFees, 2);
$fmtNet = function_exists('formatCurrency') ? formatCurrency($sumNetReceived) : number_format($sumNetReceived, 2);
$totalInvoicesCount = count($results);

if (!$isPrintView) {
    $reportdata['headertext'] .= <<<HTML
<div style="margin-top: 15px; margin-bottom: 20px;">
    <div class="row">
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Audit Scope (Invoices)</span>
                <div style="font-size:20px; font-weight:bold; color:#333;">{$totalInvoicesCount}</div>
                <small style="color:#28a745; font-weight:bold;">FY {$selectedFY}</small>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Gross Revenue / Turnover</span>
                <div style="font-size:18px; font-weight:bold; color:#007bff;">{$fmtTaxable}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Total Tax Output</span>
                <div style="font-size:18px; font-weight:bold; color:#28a745;">{$fmtTotalTax}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Total Invoiced (Gross)</span>
                <div style="font-size:18px; font-weight:bold; color:#17a2b8;">{$fmtGross}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Payment Gateway Fees</span>
                <div style="font-size:18px; font-weight:bold; color:#dc3545;">{$fmtFees}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Net Realized Bank Total</span>
                <div style="font-size:18px; font-weight:bold; color:#6f42c1;">{$fmtNet}</div>
            </div>
        </div>
    </div>
</div>
HTML;
}

