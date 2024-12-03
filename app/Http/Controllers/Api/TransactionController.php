<?php

namespace App\Http\Controllers\Api;

use Throwable;
use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function getAllTransaction()
    {
        try {
            $currentUserId = Auth::user()->id;

            $transactionData = Transaction::where("user_id", "=", $currentUserId)->get();

            return Utils::responseHelper(200, true, "data retrieved", data: $transactionData);
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public static function getTransaction($id)
    {
        try {
            $currentUserId = Auth::user()->id;

            $transactionData = Transaction::where("user_id", "=", $currentUserId, "and")->where("transaction_id", "=", $id);

            return Utils::responseHelper(200, true, "data retrieved", data: $transactionData);
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public function createTransaction(Request $request)
    {
        try {
            $validateRequest = Validator::make(
                $request->all(),
                [
                    'note' => 'required',
                    'type' => 'required',
                    'amount' => 'required',
                ]
            );

            if ($validateRequest->fails()) {
                return Utils::responseHelper(200, false, "validation error", $validateRequest->errors());
            }

            $currentUserId = Auth::user()->id;

            Transaction::create([
                'note' => $request->note,
                'type' => $request->type,
                'amount' => $request->amount,
                'user_id' => $currentUserId,
            ]);

            return Utils::responseHelper(200, true, "data created");
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public static function editTransaction(Request $request, $id)
    {
        try {
            $validateRequest = Validator::make(
                $request->all(),
                [
                    'note' => 'required',
                    'type' => 'required',
                    'amount' => 'required',
                ]
            );

            if ($validateRequest->fails()) {
                return Utils::responseHelper(200, false, "validation error", $validateRequest->errors());
            }

            $currentUserId = Auth::user()->id;

            if ($currentUserId == Transaction::find($id)->user_id) {
                Transaction::find($id)->update([
                    'note' => $request->note,
                    'type' => $request->type,
                    'amount' => $request->amount,
                    'user_id' => $currentUserId,
                ]);
            } else {
                return Utils::responseHelper(403, false, "forbidden");
            }

            return Utils::responseHelper(200, true, "data updated");
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public static function deleteTransaction($id)
    {
        try {
            $currentUserId = Auth::user()->id;

            if ($currentUserId == Transaction::find($id)->user_id) {
                Transaction::find($id)->delete();
            } else {
                return Utils::responseHelper(403, false, "forbidden");
            }

            return Utils::responseHelper(200, true, "data deleted");
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public static function getWeeklyTransaction()
    {
        try {
            $currentUserId = Auth::user()->id;

            $sixDaysAgo = Carbon::now()->subDays(6); // 6 days ago
            $tomorrow = Carbon::now()->addDay(1); // Tomorrow

            $results = DB::table('transactions')
                ->select(DB::raw('DAYNAME(updated_at) as daysname'), DB::raw('DAY(updated_at) as day'), DB::raw('SUM(amount) AS total'))
                ->where('user_id', $currentUserId)
                ->whereBetween('updated_at', [$sixDaysAgo, $tomorrow])
                ->groupBy(DB::raw('DAYNAME(updated_at)'))
                ->groupBy(DB::raw('DAY(updated_at)'))
                ->get();

            return Utils::responseHelper(200, true, "data retrieved", data: $results);
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public static function getMonthlyTransaction()
    {
        try {
            $currentUserId = Auth::user()->id;

            $twelveMonthsAgo = Carbon::now()->subMonth(12)->startOfMonth();
            $currentMonth = Carbon::now();

            $results = DB::table('transactions')
                ->select(DB::raw('MONTHNAME(updated_at) as monthsname'), DB::raw('MONTH(updated_at) as month'), DB::raw('SUM(amount) AS total'))
                ->where('user_id', $currentUserId)
                ->whereBetween('updated_at', [$twelveMonthsAgo, $currentMonth])
                ->groupBy(DB::raw('MONTHNAME(updated_at)'))
                ->groupBy(DB::raw('MONTH(updated_at)'))
                ->get();

            return Utils::responseHelper(200, true, "data retrieved", data: $results);
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public static function getWeeklyTransactionByType()
    {
        try {
            $currentUserId = Auth::user()->id;

            $sixDaysAgo = Carbon::now()->subDays(6);
            $tomorrow = Carbon::now()->addDay(1);

            $results = DB::table('transactions')
                ->select(DB::raw('type'), DB::raw('SUM(amount) AS total'))
                ->where('user_id', $currentUserId)
                ->whereBetween('updated_at', [$sixDaysAgo, $tomorrow])
                ->groupBy(DB::raw('type'))
                ->get();

            return Utils::responseHelper(200, true, "data retrieved", data: $results);
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public static function getTransactionByDate(Request $request)
    {
        try {

            $currentUserId = Auth::user()->id;
            $from = Carbon::parse($request->from);
            $to = Carbon::parse($request->to)->addDays(1);

            $transactionData = Transaction::whereBetween("updated_at", [$from, $to])->where("user_id", $currentUserId)->get();

            return Utils::responseHelper(200, true, "data retrieved", data: $transactionData);
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }
}
