<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Services\Waiter\TableCloseService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class TableSettlementController extends Controller
{
    public function __construct(private readonly TableCloseService $tables)
    {
    }

    public function settle(Request $request, Table $table): JsonResponse
    {
        $data = $request->validate(['method' => ['required', 'string', 'in:cash,card,upi,razorpay']]);

        return $this->run(fn () => $this->tables->settle($table, Auth::user(), $data['method']), 'Payment recorded — table is free again');
    }

    public function close(Table $table): JsonResponse
    {
        return $this->run(fn () => $this->tables->closeUnpaid($table, Auth::user()), 'Table closed — free for the next guest');
    }

    private function run(callable $action, string $message): JsonResponse
    {
        try {
            $action();
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => $message, 'redirect' => route('waiter.tables')]);
    }
}
