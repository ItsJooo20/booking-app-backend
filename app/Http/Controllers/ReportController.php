<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\FacilityItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FacilityCategory;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
        $facilities = Facility::with('category')->get();
        $categories = FacilityCategory::all();
        $facilityItems = FacilityItem::all();
        
        // Set default date range values
        $dateRanges = [
            'day' => now()->format('Y-m-d'),
            'range' => [
                'start' => now()->startOfWeek()->format('Y-m-d'),
                'end' => now()->endOfWeek()->format('Y-m-d')
            ],
            'month' => now()->format('Y-m'),
            'year' => now()->format('Y')
        ];
        
        return view('admin.reports-index', compact('users', 'facilities', 'categories', 'dateRanges', 'facilityItems'));
    }

    public function generate(Request $request)
    {
        // Custom validation rule for max 1 month difference
        Validator::extend('max_month_difference', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $startDate = Carbon::parse($data['date_range_start']);
            $endDate = Carbon::parse($value);
            
            // Calculate the difference in days
            $diffInDays = $endDate->diffInDays($startDate);
            
            // Return true if difference is 31 days or less
            return $diffInDays <= 31;
        }, 'Done');
    
        $request->validate([
            'date_range_type' => 'required|in:day,range,month,year',
            'date_day' => 'required_if:date_range_type,day|date',
            'date_range_start' => 'required_if:date_range_type,range|date',
            'date_range_end' => 'required_if:date_range_type,range|date|after_or_equal:date_range_start|max_month_difference',
            'date_month' => 'required_if:date_range_type,month|date_format:Y-m',
            'date_year' => 'required_if:date_range_type,year|date_format:Y',
            'status' => 'nullable|in:pending,approved,rejected,completed,cancelled',
            'user_id' => 'nullable|exists:users,id',
            'facility_id' => 'nullable|exists:facilities,id',
            'category_id' => 'nullable|exists:facility_category,id',
            'facilityItems_id' => 'nullable|exists:facility_item,id',
        ]);

        $query = Booking::with(['user', 'facilityItem.facility.category']);

        // Apply date filters based on selected range type
        switch($request->date_range_type) {
            case 'day':
                $date = Carbon::parse($request->date_day);
                $query->whereDate('start_datetime', $date);
                $dateText = $date->format('F j, Y');
                break;
                
            case 'range':
                $start = Carbon::parse($request->date_range_start)->startOfDay();
                $end = Carbon::parse($request->date_range_end)->endOfDay();
                $query->whereBetween('start_datetime', [$start, $end]);
                $dateText = $start->format('M j, Y') . ' to ' . $end->format('M j, Y');
                break;
                
            case 'month':
                $month = Carbon::createFromFormat('Y-m', $request->date_month);
                $query->whereBetween('start_datetime', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth()
                ]);
                $dateText = $month->format('F Y');
                break;
                
            case 'year':
                $year = Carbon::createFromFormat('Y', $request->date_year);
                $query->whereBetween('start_datetime', [
                    $year->copy()->startOfYear(),
                    $year->copy()->endOfYear()
                ]);
                $dateText = $year->format('Y');
                break;
        }

        // Apply other filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->facility_id) {
            $query->whereHas('facilityItem', function($q) use ($request) {
                $q->where('facility_id', $request->facility_id);
            });
        }

        if ($request->category_id) {
            $query->whereHas('facilityItem.facility', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        if ($request->facility_item_id) {
            $query->where('facility_item_id', $request->facility_item_id);
        }

        // if ($request->facilityItems_id) {
        //     $query->whereHas('item_code', function($q) use ($request) {
        //         $q->where('facilityItems_id', $request->facilityItems_id); //not done yet
        //     });
        // }

        $bookings = $query->get();
        
        return view('admin.reports-result', [
            'bookings' => $bookings,
            'filters' => $request->all(),
            'dateText' => $dateText
        ]);
    }

    // public function generate(Request $request)
    // {
    //     $request->validate([
    //         'date_range' => 'required|in:day,week,month,year',
    //         'date' => 'required|date',
    //         'status' => 'nullable|in:pending,approved,rejected,completed,cancelled',
    //         'user_id' => 'nullable|exists:users,id',
    //         'facility_id' => 'nullable|exists:facilities,id',
    //         'category_id' => 'nullable|exists:facility_category,id'
    //     ]);

    //     $date = Carbon::parse($request->date);
    //     $query = Booking::with(['user', 'facilityItem.facility.category']);

    //     // Apply date filters
    //     switch($request->date_range) {
    //         case 'day':
    //             $query->whereDate('start_datetime', $date);
    //             break;
    //         case 'week':
    //             $query->whereBetween('start_datetime', [$date->startOfWeek(), $date->endOfWeek()]);
    //             break;
    //         case 'month':
    //             $query->whereBetween('start_datetime', [$date->startOfMonth(), $date->endOfMonth()]);
    //             break;
    //         case 'year':
    //             $query->whereBetween('start_datetime', [$date->startOfYear(), $date->endOfYear()]);
    //             break;
    //     }

    //     // Apply other filters
    //     if ($request->status) {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->user_id) {
    //         $query->where('user_id', $request->user_id);
    //     }

    //     if ($request->facility_id) {
    //         $query->whereHas('facilityItem', function($q) use ($request) {
    //             $q->where('facility_id', $request->facility_id);
    //         });
    //     }

    //     if ($request->category_id) {
    //         $query->whereHas('facilityItem.facility', function($q) use ($request) {
    //             $q->where('category_id', $request->category_id);
    //         });
    //     }

    //     $bookings = $query->get();
        
    //     // Get most requested items
    //     $mostRequested = FacilityItem::withCount(['bookings' => function($q) use ($request, $date) {
    //         $this->applyDateFilter($q, $request->date_range, $date);
    //     }])
    //     ->orderBy('bookings_count', 'desc')
    //     ->take(5)
    //     ->get();

    //     // Get most booked facilities
    //     // $mostBooked = Facility::withCount(['bookings' => function($q) use ($request, $date) {
    //     //     $this->applyDateFilter($q, $request->date_range, $date);
    //     // }])
    //     // ->orderBy('bookings_count', 'desc')
    //     // ->take(5)
    //     // ->get();

    //     // return view('admin.reports-result', compact('bookings', 'mostRequested', 'mostBooked', 'request'));
    //     return view('admin.reports-result', compact('bookings', 'mostRequested', 'request'));
    // }

    public function downloadPdf(Request $request)
    {
        // Reuse the generate method logic to get data
        $data = $this->generate($request)->getData();
        
        $pdf = Pdf::loadView('admin.reports-pdf', (array)$data);
        return $pdf->download('bookings-report-'.now()->format('YmdHis').'.pdf');
    }

    private function applyDateFilter($query, $range, $date)
    {
        switch($range) {
            case 'day':
                $query->whereDate('start_datetime', $date);
                break;
            case 'week':
                $query->whereBetween('start_datetime', [$date->startOfWeek(), $date->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('start_datetime', [$date->startOfMonth(), $date->endOfMonth()]);
                break;
            case 'year':
                $query->whereBetween('start_datetime', [$date->startOfYear(), $date->endOfYear()]);
                break;
        }
    }
}