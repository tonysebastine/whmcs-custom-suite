<?php

/**
 * GST Invoice Report for GSTR-1 & GSTR-3B Filing
 * Compatible with WHMCS 7.x, 8.x, 8.13+ & modern PHP 7.4 through 8.2, 8.3, 8.4, 8.5+
 * Place in: modules/reports/gst_invoice_report.php
 */

declare(strict_types=1);

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "GST Invoice Report (GSTR-1 / GSTR-3B)";
$reportdata["description"] = "Comprehensive GST invoice report with B2B/B2C classification, Place of Supply, and CGST/SGST/IGST breakdown for GST return filing.";
$reportdata["currencyselections"] = true;

// 1. Home State Selection (Intra-state vs Inter-state determination)
$homeState = (string) (class_exists('App') && method_exists('App', 'getFromRequest') 
    ? App::getFromRequest('homestate') 
    : ($_REQUEST['homestate'] ?? ''));
if (trim($homeState) === '') {
    $homeState = 'Kerala'; // Default home state
}

// 2. Status Filter (Paid, Unpaid, All, Cancelled, Refunded, Payment Pending)
$statusFilter = (string) (class_exists('App') && method_exists('App', 'getFromRequest') 
    ? App::getFromRequest('statusfilter') 
    : ($_REQUEST['statusfilter'] ?? ''));
if (trim($statusFilter) === '') {
    $statusFilter = 'Paid';
}

// 2b. Invoice Type Filter (All, B2B, B2C)
$typeFilter = (string) (class_exists('App') && method_exists('App', 'getFromRequest') 
    ? App::getFromRequest('typefilter') 
    : ($_REQUEST['typefilter'] ?? ''));
if (trim($typeFilter) === '') {
    $typeFilter = 'All';
}

// 3. Date Range Selection (Cross-version compatible)
$range = (string) (class_exists('App') && method_exists('App', 'getFromRequest') 
    ? App::getFromRequest('range') 
    : ($_REQUEST['range'] ?? ''));

if (trim($range) === '') {
    if (class_exists(Carbon::class) && method_exists(Carbon::class, 'today')) {
        $today = Carbon::today()->endOfDay();
        $firstOfMonth = Carbon::today()->startOfMonth();
        $range = $firstOfMonth->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
    } else {
        $range = date('01/m/Y') . ' - ' . date('t/m/Y');
    }
}

// Parse date range safely across WHMCS 7.x through 8.x+
if (class_exists(Carbon::class) && method_exists(Carbon::class, 'parseDateRangeValue')) {
    $dateRange = Carbon::parseDateRangeValue($range);
    $queryStartDate = $dateRange['from']->startOfDay()->toDateTimeString();
    $queryEndDate = $dateRange['to']->endOfDay()->toDateTimeString();
} else {
    $parts = explode(' - ', $range);
    $startRaw = trim($parts[0] ?? date('Y-m-01'));
    $endRaw = trim($parts[1] ?? date('Y-m-t'));
    $queryStartDate = date('Y-m-d 00:00:00', strtotime(str_replace('/', '-', $startRaw)));
    $queryEndDate = date('Y-m-d 23:59:59', strtotime(str_replace('/', '-', $endRaw)));
}

$currencyID = isset($currencyid) ? (int) $currencyid : 1;
$isPrintView = isset($print) && (bool) $print;

// Filter UI
if (!$isPrintView) {
    $statusOptions = '';
    $statuses = ['Paid', 'Unpaid', 'All', 'Cancelled', 'Refunded', 'Payment Pending'];
    foreach ($statuses as $opt) {
        $selected = ($statusFilter === $opt) ? 'selected' : '';
        $statusOptions .= "<option value=\"" . htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') . "\" {$selected}>" . htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') . "</option>";
    }

    $typeOptions = '';
    foreach (['All', 'B2B', 'B2C'] as $tOpt) {
        $selected = ($typeFilter === $tOpt) ? 'selected' : '';
        $typeOptions .= "<option value=\"" . htmlspecialchars($tOpt, ENT_QUOTES, 'UTF-8') . "\" {$selected}>" . htmlspecialchars($tOpt, ENT_QUOTES, 'UTF-8') . "</option>";
    }

    $safeReport = htmlspecialchars((string) ($report ?? 'gst_invoice_report'), ENT_QUOTES, 'UTF-8');
    $safeCurrencyId = (int) $currencyID;
    $safeRange = htmlspecialchars($range, ENT_QUOTES, 'UTF-8');
    $safeHomeState = htmlspecialchars($homeState, ENT_QUOTES, 'UTF-8');

    $reportdata['headertext'] = <<<HTML
<form method="post" action="reports.php?report={$safeReport}&currencyid={$safeCurrencyId}">
    <div class="report-filters-wrapper">
        <div class="inner-container">
            <h3>Filters</h3>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputFilterDate">Date Range</label>
                        <div class="form-group date-picker-prepend-icon">
                            <label for="inputFilterDate" class="field-icon">
                                <i class="fal fa-calendar-alt"></i>
                            </label>
                            <input id="inputFilterDate"
                                   type="text"
                                   name="range"
                                   value="{$safeRange}"
                                   class="form-control date-picker-search"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="form-group">
                        <label for="inputHomeState">Seller Home State</label>
                        <input id="inputHomeState"
                               type="text"
                               name="homestate"
                               value="{$safeHomeState}"
                               class="form-control"
                               placeholder="e.g. Kerala"
                        />
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
                <div class="col-md-2 col-sm-6">
                    <div class="form-group">
                        <label for="inputType">B2B / B2C Filter</label>
                        <select id="inputType" name="typefilter" class="form-control">
                            {$typeOptions}
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="form-group" style="padding-top: 25px;">
                        <button type="submit" class="btn btn-primary btn-block">
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
HTML;
}

// Table Headings
$reportdata["tableheadings"] = [
    "Invoice #",
    "Invoice Date",
    "Date Paid",
    "Client / Company",
    "Client GSTIN",
    "HSN/SAC",
    "Type",
    "Place of Supply",
    "Taxable Value (INR)",
    "CGST (INR)",
    "SGST (INR)",
    "IGST (INR)",
    "Total Tax (INR)",
    "Total Invoice (INR)",
    "Status"
];

// Query
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
          ->whereBetween('tblinvoices.datepaid', [$queryStartDate, $queryEndDate]);
} elseif ($statusFilter === 'All') {
    $query->whereBetween('tblinvoices.date', [$queryStartDate, $queryEndDate]);
} else {
    $query->where('tblinvoices.status', '=', $statusFilter)
          ->whereBetween('tblinvoices.date', [$queryStartDate, $queryEndDate]);
}

if ($typeFilter === 'B2B') {
    $query->whereNotNull('tblclients.tax_id')
          ->where('tblclients.tax_id', '!=', '');
} elseif ($typeFilter === 'B2C') {
    $query->where(function ($q) {
        $q->whereNull('tblclients.tax_id')
          ->orWhere('tblclients.tax_id', '=', '');
    });
}

$results = $query->orderBy('tblinvoices.date', 'asc')->get();

// Summaries
$sumTaxable = 0.0;
$sumCGST = 0.0;
$sumSGST = 0.0;
$sumIGST = 0.0;
$sumTotalTax = 0.0;
$sumTotal = 0.0;
$countB2B = 0;
$countB2C = 0;

$normHomeState = strtolower(trim($homeState));

// Helper function to resolve HSN/SAC code from line items (PHP 8.2-8.5+ safe)
if (!function_exists('w3ResolveHsnSac')) {
    function w3ResolveHsnSac(?string $type, ?string $description): string {
        $descLower = strtolower((string) $description);
        $typeSafe = (string) $type;

        if (str_contains($descLower, 'website design') ||
            str_contains($descLower, 'web development') ||
            str_contains($descLower, 'seo') ||
            str_contains($descLower, 'amc')) {
            return '998314'; // IT design and development
        } elseif ($typeSafe === 'Hosting') {
            return '998315'; // Hosting and domain space services
        } elseif ($typeSafe === 'PromoHosting' || $typeSafe === 'DomainHosting' || $typeSafe === 'AddFunds') {
            return '998315'; // Hosting service category
        } elseif (str_contains($typeSafe, 'Domain') || str_contains($typeSafe, 'Addon')) {
            return '998319'; // Other professional, technical and business services (Domains)
        }
        return '998313'; // Default IT infrastructure / consulting services
    }
}

// Preload invoice items for selected invoices to extract HSN/SAC codes
$invoiceIds = $results->pluck('id')->all();
$invoiceHsnMap = [];

if (!empty($invoiceIds)) {
    $items = Capsule::table('tblinvoiceitems')
        ->whereIn('invoiceid', $invoiceIds)
        ->select('invoiceid', 'type', 'description')
        ->get();

    foreach ($items as $item) {
        $code = w3ResolveHsnSac($item->type ?? '', $item->description ?? '');
        if ($code !== '') {
            $invoiceHsnMap[$item->invoiceid][$code] = $code;
        }
    }
}

foreach ($results as $row) {
    $invNumber = (!empty($row->invoicenum)) ? $row->invoicenum : (string) $row->id;
    $invLink = "<a href=\"invoices.php?action=edit&id=" . (int) $row->id . "\" target=\"_blank\">" . htmlspecialchars((string) $invNumber, ENT_QUOTES, 'UTF-8') . "</a>";
    
    $invDate = function_exists('fromMySQLDate') ? fromMySQLDate($row->date) : (string) $row->date;
    $datePaid = (!empty($row->datepaid) && $row->datepaid !== '0000-00-00 00:00:00' && $row->datepaid !== '0000-00-00') 
        ? (function_exists('fromMySQLDate') ? fromMySQLDate($row->datepaid) : (string) $row->datepaid)
        : '-';

    $firstName = trim((string) ($row->firstname ?? ''));
    $lastName = trim((string) ($row->lastname ?? ''));
    $company = trim((string) ($row->companyname ?? ''));
    $clientName = ($company !== '') ? $company : trim("{$firstName} {$lastName}");

    $clientGSTIN = trim((string) ($row->tax_id ?? ''));
    
    // B2B vs B2C
    $isB2B = ($clientGSTIN !== '');
    $gstType = $isB2B ? '<span class="label label-info">B2B</span>' : '<span class="label label-default">B2C</span>';
    if ($isB2B) {
        $countB2B++;
    } else {
        $countB2C++;
    }

    $clientState = trim((string) ($row->client_state ?? ''));
    $clientCountry = trim((string) ($row->client_country ?? ''));
    $pos = ($clientState !== '') ? $clientState : $clientCountry;
    $normClientState = strtolower($clientState);

    $subtotal = (float) ($row->subtotal ?? 0.0);
    $tax1 = (float) ($row->tax ?? 0.0);
    $tax2 = (float) ($row->tax2 ?? 0.0);
    $totalTax = $tax1 + $tax2;
    $total = (float) ($row->total ?? 0.0);

    // Determine CGST + SGST vs IGST
    $cgst = 0.0;
    $sgst = 0.0;
    $igst = 0.0;

    $isIntraState = ($normClientState === $normHomeState && $normHomeState !== '');

    if ($isIntraState) {
        // Both taxes apply (CGST + SGST)
        if ($tax1 > 0 && $tax2 > 0) {
            $cgst = $tax1;
            $sgst = $tax2;
        } elseif ($totalTax > 0) {
            $cgst = round($totalTax / 2, 2);
            $sgst = $totalTax - $cgst;
        }
    } else {
        // Inter-State -> IGST
        $igst = $totalTax;
    }

    $sumTaxable += $subtotal;
    $sumCGST += $cgst;
    $sumSGST += $sgst;
    $sumIGST += $igst;
    $sumTotalTax += $totalTax;
    $sumTotal += $total;

    $hsnCodes = isset($invoiceHsnMap[$row->id]) ? implode(', ', $invoiceHsnMap[$row->id]) : '998313';

    $reportdata["tablevalues"][] = [
        $invLink,
        $invDate,
        $datePaid,
        htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'),
        ($clientGSTIN !== '') ? htmlspecialchars($clientGSTIN, ENT_QUOTES, 'UTF-8') : 'N/A',
        htmlspecialchars($hsnCodes, ENT_QUOTES, 'UTF-8'),
        $gstType,
        htmlspecialchars($pos, ENT_QUOTES, 'UTF-8'),
        number_format($subtotal, 2, '.', ''),
        number_format($cgst, 2, '.', ''),
        number_format($sgst, 2, '.', ''),
        number_format($igst, 2, '.', ''),
        number_format($totalTax, 2, '.', ''),
        number_format($total, 2, '.', ''),
        htmlspecialchars((string) ($row->status ?? ''), ENT_QUOTES, 'UTF-8')
    ];
}

// Summary Box
$formattedTaxable = function_exists('formatCurrency') ? formatCurrency($sumTaxable) : number_format($sumTaxable, 2);
$formattedCGST = function_exists('formatCurrency') ? formatCurrency($sumCGST) : number_format($sumCGST, 2);
$formattedSGST = function_exists('formatCurrency') ? formatCurrency($sumSGST) : number_format($sumSGST, 2);
$formattedIGST = function_exists('formatCurrency') ? formatCurrency($sumIGST) : number_format($sumIGST, 2);
$formattedTotalTax = function_exists('formatCurrency') ? formatCurrency($sumTotalTax) : number_format($sumTotalTax, 2);
$formattedTotal = function_exists('formatCurrency') ? formatCurrency($sumTotal) : number_format($sumTotal, 2);
$totalInvoices = count($results);

if (!$isPrintView) {
    $reportdata['headertext'] .= <<<HTML
<div style="margin-top: 15px; margin-bottom: 20px;">
    <div class="row">
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Invoices</span>
                <div style="font-size:20px; font-weight:bold; color:#333;">{$totalInvoices}</div>
                <small style="color:#888;">B2B: {$countB2B} | B2C: {$countB2C}</small>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Taxable Base</span>
                <div style="font-size:18px; font-weight:bold; color:#007bff;">{$formattedTaxable}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">CGST</span>
                <div style="font-size:18px; font-weight:bold; color:#28a745;">{$formattedCGST}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">SGST</span>
                <div style="font-size:18px; font-weight:bold; color:#28a745;">{$formattedSGST}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">IGST</span>
                <div style="font-size:18px; font-weight:bold; color:#fd7e14;">{$formattedIGST}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 text-center">
            <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:10px;">
                <span style="font-size:12px; color:#6c757d; text-transform:uppercase;">Total Invoice Value</span>
                <div style="font-size:18px; font-weight:bold; color:#17a2b8;">{$formattedTotal}</div>
            </div>
        </div>
    </div>
</div>
HTML;
}
