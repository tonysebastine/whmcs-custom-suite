<?php

/**
 * GST Invoice Report for GSTR-1 & GSTR-3B Filing
 * Designed for WHMCS 8.x - Native Admin Report Module
 * Place in: modules/reports/gst_invoice_report.php
 */

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "GST Invoice Report (GSTR-1 / GSTR-3B)";
$reportdata["description"] = "Comprehensive GST invoice report with B2B/B2C classification, Place of Supply, and CGST/SGST/IGST breakdown for GST return filing.";
$reportdata["currencyselections"] = true;

// 1. Home State Selection (for Intra-state vs Inter-state determination)
$homeState = App::getFromRequest('homestate');
if (!$homeState) {
    $homeState = 'Kerala'; // Default home state
}

// 2. Status Filter (Paid, Unpaid, All, Cancelled, Refunded, Payment Pending)
$statusFilter = App::getFromRequest('statusfilter');
if (!$statusFilter) {
    $statusFilter = 'Paid';
}

// 2b. Invoice Type Filter (All, B2B, B2C)
$typeFilter = App::getFromRequest('typefilter');
if (!$typeFilter) {
    $typeFilter = 'All';
}

// 3. Date Range Selection
$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $firstOfMonth = Carbon::today()->startOfMonth();
    $range = $firstOfMonth->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}

$dateRange = Carbon::parseDateRangeValue($range);
$queryStartDate = $dateRange['from']->startOfDay()->toDateTimeString();
$queryEndDate = $dateRange['to']->endOfDay()->toDateTimeString();

$currencyID = (int) $currencyid;

// Filter UI
if (!$print) {
    $statusOptions = '';
    $statuses = ['Paid', 'Unpaid', 'All', 'Cancelled', 'Refunded', 'Payment Pending'];
    foreach ($statuses as $opt) {
        $selected = ($statusFilter === $opt) ? 'selected' : '';
        $statusOptions .= "<option value=\"{$opt}\" {$selected}>{$opt}</option>";
    }

    $typeOptions = '';
    foreach (['All', 'B2B', 'B2C'] as $tOpt) {
        $selected = ($typeFilter === $tOpt) ? 'selected' : '';
        $typeOptions .= "<option value=\"{$tOpt}\" {$selected}>{$tOpt}</option>";
    }

    $reportdata['headertext'] = <<<HTML
<form method="post" action="reports.php?report={$report}&currencyid={$currencyid}">
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
                                   value="{$range}"
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
                               value="{$homeState}"
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

// Helper function to resolve HSN/SAC code from line items
if (!function_exists('w3ResolveHsnSac')) {
    function w3ResolveHsnSac($type, $description) {
        $descLower = strtolower($description);
        if (strpos($descLower, 'website design') !== false ||
            strpos($descLower, 'web development') !== false ||
            strpos($descLower, 'seo') !== false ||
            strpos($descLower, 'amc') !== false) {
            return '998314'; // IT design and development
        } elseif ($type === 'Hosting') {
            return '998315'; // Hosting and domain space services
        } elseif ($type === 'PromoHosting' || $type === 'DomainHosting' || $type === 'AddFunds') {
            return '998315'; // Hosting service category
        } elseif (strpos($type, 'Domain') !== false || strpos($type, 'Addon') !== false) {
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
        $code = w3ResolveHsnSac($item->type, $item->description);
        if (!empty($code)) {
            $invoiceHsnMap[$item->invoiceid][$code] = $code;
        }
    }
}

foreach ($results as $row) {
    $invNumber = $row->invoicenum ? $row->invoicenum : $row->id;
    $invLink = "<a href=\"invoices.php?action=edit&id={$row->id}\" target=\"_blank\">{$invNumber}</a>";
    
    $invDate = fromMySQLDate($row->date);
    $datePaid = ($row->datepaid && $row->datepaid !== '0000-00-00 00:00:00') ? fromMySQLDate($row->datepaid) : '-';

    $clientName = trim($row->companyname) ? $row->companyname : trim("{$row->firstname} {$row->lastname}");
    $clientGSTIN = trim($row->tax_id);
    
    // B2B vs B2C
    $isB2B = !empty($clientGSTIN);
    $gstType = $isB2B ? '<span class="label label-info">B2B</span>' : '<span class="label label-default">B2C</span>';
    if ($isB2B) {
        $countB2B++;
    } else {
        $countB2C++;
    }

    $pos = trim($row->client_state) ? $row->client_state : $row->client_country;
    $normClientState = strtolower(trim($row->client_state));

    $subtotal = (float) $row->subtotal;
    $tax1 = (float) $row->tax;
    $tax2 = (float) $row->tax2;
    $totalTax = $tax1 + $tax2;
    $total = (float) $row->total;

    // Determine CGST + SGST vs IGST
    $cgst = 0.0;
    $sgst = 0.0;
    $igst = 0.0;

    $isIntraState = ($normClientState === $normHomeState && !empty($normHomeState));

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
        $clientName,
        $clientGSTIN ? $clientGSTIN : 'N/A',
        $hsnCodes,
        $gstType,
        $pos,
        number_format($subtotal, 2),
        number_format($cgst, 2),
        number_format($sgst, 2),
        number_format($igst, 2),
        number_format($totalTax, 2),
        number_format($total, 2),
        $row->status
    ];
}

// Summary Box
$formattedTaxable = formatCurrency($sumTaxable);
$formattedCGST = formatCurrency($sumCGST);
$formattedSGST = formatCurrency($sumSGST);
$formattedIGST = formatCurrency($sumIGST);
$formattedTotalTax = formatCurrency($sumTotalTax);
$formattedTotal = formatCurrency($sumTotal);
$totalInvoices = count($results);

if (!$print) {
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

