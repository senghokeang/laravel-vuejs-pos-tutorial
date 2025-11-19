<?php

namespace App\Http\Controllers;

use App\Exports\ExportDataToExcel;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // Sale Summary
    public function saleSummary(Request $request)
    {
        $from_date = null;
        $to_date = null;
        if ($request->from_date)
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
        if ($request->to_date)
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));

        try {
            $data = Order::join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
                ->select(DB::raw("product_categories.name,sum((order_details.qty * order_details.unit_price*order_details.discount/100) + (order_details.qty * order_details.unit_price * (1-order_details.discount/100) * orders.discount/100)) as discount, sum(order_details.qty * order_details.unit_price) as total"))
                ->when($from_date, function ($query) use ($from_date) {
                    $query->where('orders.created_at', '>=', $from_date);
                })
                ->when($to_date, function ($query) use ($to_date) {
                    $query->where('orders.created_at', '<=', $to_date);
                })
                ->groupBy(DB::raw('product_categories.name'))
                ->orderBy('product_categories.name', 'DESC')
                ->get();
            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        // return back to compoment
        return response()->json($response);
    }

    public function productSummary(Request $request)
    {
        // get param value
        $product_name = $request->product_name;
        $product_category_id = $request->product_category_id ?? 0;
        $from_date = null;
        $to_date = null;
        if ($request->from_date)
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
        if ($request->to_date)
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
        $sortBy = $request->sortBy ?? 'qty';
        $orderBy = $request->orderBy ?? 'desc';

        // select from table with filter
        try {
            $data = Order::join('order_details', 'order_details.order_id', '=', 'orders.id')->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
                ->selectRaw('order_details.description,order_details.product_category_id,product_categories.name AS category_name,sum(order_details.qty) AS qty')
                ->when($product_name, function ($query) use ($product_name) {
                    $query->where('order_details.description', 'like', '%' . $product_name . '%');
                })
                ->when($product_category_id > 0, function ($query) use ($product_category_id) {
                    $query->where('order_details.product_category_id',  $product_category_id);
                })
                ->when($from_date, function ($query) use ($from_date) {
                    $query->where('orders.created_at', '>=', $from_date);
                })
                ->when($to_date, function ($query) use ($to_date) {
                    $query->where('orders.created_at', '<=', $to_date);
                })
                ->groupBy('order_details.description', 'order_details.product_category_id', 'product_categories.name')
                ->orderBy($sortBy, $orderBy)
                ->paginate(50);
            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        // return back to compoment
        return response()->json($response);
    }

    public function exportProductSummary(Request $request)
    {
        // get param value
        $product_name = $request->product_name;
        $product_category_id = $request->product_category_id ?? 0;
        $from_date = null;
        $to_date = null;
        if ($request->from_date)
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
        if ($request->to_date)
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
        $sortBy = $request->sortBy ?? 'qty';
        $orderBy = $request->orderBy ?? 'desc';

        // select from table with filter
        try {
            $data = Order::join('order_details', 'order_details.order_id', '=', 'orders.id')->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
                ->selectRaw('order_details.description,order_details.product_category_id,product_categories.name AS category_name,sum(order_details.qty) AS qty')
                ->when($product_name, function ($query) use ($product_name) {
                    $query->where('order_details.description', 'like', '%' . $product_name . '%');
                })
                ->when($product_category_id > 0, function ($query) use ($product_category_id) {
                    $query->where('order_details.product_category_id',  $product_category_id);
                })
                ->when($from_date, function ($query) use ($from_date) {
                    $query->where('orders.created_at', '>=', $from_date);
                })
                ->when($to_date, function ($query) use ($to_date) {
                    $query->where('orders.created_at', '<=', $to_date);
                })
                ->groupBy('order_details.description', 'order_details.product_category_id', 'product_categories.name')
                ->orderBy($sortBy, $orderBy)
                ->get();

            // Optionally add headers (first row)
            $exportData = [];
            $exportData[] = ['ID', 'Product Name', 'Product Category', 'Quantity']; // Header row
            foreach ($data as $index => $value) {
                $exportData[] = [$index + 1, $value->description, $value->category_name, $value->qty];
            }
            return Excel::download(new ExportDataToExcel($exportData), 'Product Summary Report.xlsx');
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
    }

    public function saleHistory(Request $request)
    {
        // get param value
        $invoice_no = $request->invoice_no;
        $from_date = null;
        $to_date = null;
        if ($request->from_date)
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
        if ($request->to_date)
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
        $sortBy = $request->sortBy ?? 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
        try {
            $data = Order::join('tables', 'tables.id', '=', 'orders.table_id')
                ->join('users', 'users.id', '=', 'orders.created_by_id')
                ->select(
                    'orders.invoice_no',
                    'tables.name AS table_name',
                    'orders.grand_total',
                    'orders.total_discount',
                    'orders.net_amount',
                    'orders.id',
                    'orders.created_at',
                    DB::raw('users.username AS cashier')
                )
                ->when($invoice_no, function ($query) use ($invoice_no) {
                    $query->where('orders.invoice_no', 'like', '%' . $invoice_no . '%');
                })
                ->when($from_date, function ($query) use ($from_date) {
                    $query->where('orders.created_at', '>=', $from_date);
                })
                ->when($to_date, function ($query) use ($to_date) {
                    $query->where('orders.created_at', '<=', $to_date);
                })
                ->orderBy($sortBy, $orderBy)
                ->paginate(50);

            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        // return back to compoment
        return response()->json($response);
    }

    public function saleHistorySummary(Request $request)
    {
        // get param value
        $invoice_no = $request->invoice_no;
        $from_date = null;
        $to_date = null;
        if ($request->from_date)
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
        if ($request->to_date)
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
        try {
            $data = Order::select(DB::raw("sum(grand_total) as grand_total, sum(total_discount) as total_discount, sum(net_amount) as net_amount"))
                ->when($invoice_no, function ($query) use ($invoice_no) {
                    $query->where('invoice_no', 'like', '%' . $invoice_no . '%');
                })
                ->when($from_date, function ($query) use ($from_date) {
                    $query->where('created_at', '>=', $from_date);
                })
                ->when($to_date, function ($query) use ($to_date) {
                    $query->where('created_at', '<=', $to_date);
                })
                ->first();

            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        // return back to compoment
        return response()->json($response);
    }

    public function exportSaleHistory(Request $request)
    {
        // get param value
        $invoice_no = $request->invoice_no;
        $from_date = null;
        $to_date = null;
        if ($request->from_date)
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
        if ($request->to_date)
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
        $sortBy = $request->sortBy ?? 'qty';
        $orderBy = $request->orderBy ?? 'desc';
        try {
            $data = Order::join('tables', 'tables.id', '=', 'orders.table_id')
                ->join('users', 'users.id', '=', 'orders.created_by_id')
                ->select(
                    'orders.invoice_no',
                    'tables.name AS table_name',
                    'orders.grand_total',
                    'orders.total_discount',
                    'orders.net_amount',
                    'orders.id',
                    'orders.created_at',
                    DB::raw('users.username AS cashier')
                )
                ->when($invoice_no, function ($query) use ($invoice_no) {
                    $query->where('orders.invoice_no', 'like', '%' . $invoice_no . '%');
                })
                ->when($from_date, function ($query) use ($from_date) {
                    $query->where('orders.created_at', '>=', $from_date);
                })
                ->when($to_date, function ($query) use ($to_date) {
                    $query->where('orders.created_at', '<=', $to_date);
                })
                ->orderBy($sortBy, $orderBy)
                ->get();
            // Optionally add headers (first row)
            $exportData = [];
            $exportData[] = ['ID', 'Invoice No', 'Table No', 'Total Amount', 'Total Discount', 'Net Amount', 'Date', 'Cashier']; // Header row
            foreach ($data as $index => $value) {
                $exportData[] = [$index + 1, $value->invoice_no, $value->table_name, $value->grand_total, $value->total_discount, $value->net_amount, date('d-M-Y H:i:s', strtotime($value->created_at)), $value->cashier];
            }
            return Excel::download(new ExportDataToExcel($exportData), 'Sale History Report.xlsx');
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
    }
    public function showOrderDetail($id)
    {
        try {
            $data = Order::with(['order_details', 'operator'])->join('tables', 'tables.id', '=', 'orders.table_id')
                ->join('users', 'users.id', '=', 'orders.created_by_id')
                ->select(
                    'orders.total',
                    'orders.net_amount',
                    'orders.discount',
                    'orders.invoice_no',
                    'tables.name AS table_name',
                    'orders.created_at',
                    'orders.id',
                    'orders.created_by_id',
                    'orders.receive_amount',
                    DB::raw('users.username AS cashier')
                )
                ->find($id);

            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        // return back to compoment
        return response()->json($response);
    }
}
