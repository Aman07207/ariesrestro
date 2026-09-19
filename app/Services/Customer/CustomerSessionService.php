<?php

namespace App\Services\Customer;

use App\Enums\SessionStatus;
use App\Models\Hotel;
use App\Models\OrderSession;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CustomerSessionService
{
    public const SESSION_COOKIE = 'aries_session_token';

    public const SESSION_TTL_HOURS = 4;

    /**
     * Finds or creates the table's active session, assigns this browser a member
     * number via an httpOnly cookie (never localStorage, per the brief's client-side
     * security guidance), and queues the cookies onto the response.
     */
    public function startSession(Request $request, Hotel $hotel, string $tableUuid): OrderSession
    {
        $table = Table::where('hotel_id', $hotel->id)->where('table_uuid', $tableUuid)->firstOrFail();

        $session = OrderSession::where('table_id', $table->id)
            ->where('status', SessionStatus::Active)
            ->latest('id')
            ->first();

        $isStale = $session && $session->updated_at->lt(now()->subHours(self::SESSION_TTL_HOURS));

        if (! $session || $isStale) {
            if ($isStale) {
                $session->update(['status' => SessionStatus::Expired]);
            }

            $session = OrderSession::create([
                'hotel_id' => $hotel->id,
                'table_id' => $table->id,
                'member_count' => 0,
                'status' => SessionStatus::Active,
            ]);
        }

        $memberCookieName = $this->memberCookieName($session->id);
        $memberNo = $request->cookie($memberCookieName);

        if (! $memberNo || ! $session->members()->where('member_no', $memberNo)->exists()) {
            $memberNo = $session->member_count + 1;
            $session->members()->create(['member_no' => $memberNo]);
            $session->increment('member_count');

            // Cookie::make() defaults httpOnly to true already.
            Cookie::queue($memberCookieName, (string) $memberNo, 60 * self::SESSION_TTL_HOURS);
        }

        Cookie::queue(self::SESSION_COOKIE, $session->session_token, 60 * self::SESSION_TTL_HOURS);

        return $session;
    }

    public function memberCookieName(int $sessionId): string
    {
        return 'aries_member_'.$sessionId;
    }
}
