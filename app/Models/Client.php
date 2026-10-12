<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    protected $guarded = [];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /** Digits and a leading "+" only (drops spaces, dashes, dots, brackets). */
    public static function normalizePhone(?string $phone): string
    {
        return preg_replace('/[^\d+]/', '', (string) $phone);
    }

    /**
     * Create the client, or OVERWRITE the existing row when the phone or the email is already known.
     * Never throws a unique-constraint error for email / whatsapp_number.
     */
    public static function upsertByContact(array $data): self
    {
        $data['whatsapp_number'] = static::normalizePhone($data['whatsapp_number'] ?? '');
        $data['email'] = filled($data['email'] ?? null) ? mb_strtolower(trim($data['email'])) : null;

        try {
            return DB::transaction(fn () => static::upsertOnce($data));
        } catch (UniqueConstraintViolationException $e) {
            // Two requests created the same contact at the same moment: the row exists now, so run once more as an update.
            return DB::transaction(fn () => static::upsertOnce($data));
        }
    }

    private static function upsertOnce(array $data): self
    {
        $phone = $data['whatsapp_number'];
        $email = $data['email'];

        $byPhone = static::findByPhone($phone);
        $byEmail = $email ? static::query()->where('email', $email)->first() : null;

        // The phone number identifies the client; fall back to the email.
        $client = $byPhone ?? $byEmail;

        if (! $client) {
            return static::create($data);
        }

        if ($email) {
            // This email now belongs to the client being updated: release it from any other row
            // (that row keeps its phone and its bookings, it only loses the email).
            static::query()
                ->where('email', $email)
                ->where($client->getKeyName(), '!=', $client->getKey())
                ->update(['email' => null]);
        } else {
            // A blank email on the form must not wipe the email already on file.
            unset($data['email']);
        }

        $client->update($data);

        return $client;
    }

    /** Matches the stored number as typed, or ignoring spaces / dashes / dots / brackets. */
    private static function findByPhone(string $phone): ?self
    {
        if ($phone === '') {
            return null;
        }

        return static::query()->where('whatsapp_number', $phone)->first()
            ?? static::query()
                ->whereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(whatsapp_number, ' ', ''), '-', ''), '.', ''), '(', ''), ')', '') = ?",
                    [$phone]
                )
                ->first();
    }
}