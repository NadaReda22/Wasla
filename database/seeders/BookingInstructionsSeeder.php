<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class BookingInstructionsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = Setting::firstOrCreate([]);

        $update = [];
        foreach (self::defaults() as $column => $rows) {
            if (empty($settings->{$column})) {
                $update[$column] = $rows;
            }
        }

        if (!empty($update)) {
            $settings->update($update);
        }
    }

    public static function defaults(): array
    {
        return [

            // ─────────────────────────── STEPS ───────────────────────────
            'booking_steps' => [
                [
                    'number' => '01', 'icon' => '📋',
                    'title' => 'Read All Policies First',
                    'title_ru' => 'Сначала прочитайте все правила',
                    'desc' => 'Before booking, read our operational policies. Please note that once the online deposit is paid, it is strictly non-refundable and bookings cannot be canceled.',
                    'desc_ru' => 'Перед бронированием ознакомьтесь с правилами. Обратите внимание, что после оплаты онлайн-депозита он не подлежит возврату, а бронирование не может быть отменено.',
                ],
                [
                    'number' => '02', 'icon' => '🗺️',
                    'title' => 'Choose Your Route & Time',
                    'title_ru' => 'Выберите маршрут и время',
                    'desc' => 'Select your destination and preferred departure time. Check seat availability — popular routes fill fast.',
                    'desc_ru' => 'Выберите направление и удобное время отправления. Проверьте наличие мест — популярные маршруты заполняются быстро.',
                ],
                [
                    'number' => '03', 'icon' => '📍',
                    'title' => 'Select Location From The Map',
                    'title_ru' => 'Выберите место на карте',
                    'desc' => "Pinpoint your exact hotel on the interactive map. If your hotel is within our coverage areas but isn't listed, contact us on WhatsApp. We do not provide service outside the highlighted boundaries.",
                    'desc_ru' => 'Укажите точный отель на карте. Если ваш отель находится в нашей зоне обслуживания, но его нет в списке, напишите нам в WhatsApp. Мы не обслуживаем районы за пределами выделенных границ.',
                ],
                [
                    'number' => '04', 'icon' => '💳',
                    'title' => 'Pay the Online Deposit',
                    'title_ru' => 'Оплатите онлайн-депозит',
                    'desc' => 'Secure your seat by paying the required online deposit via our secure payment gateway. The remaining balance must be paid in cash to the driver.',
                    'desc_ru' => 'Забронируйте место, внеся онлайн-депозит через нашу безопасную систему. Оставшаяся сумма оплачивается водителю наличными.',
                ],
                [
                    'number' => '05', 'icon' => '📱',
                    'title' => 'Receive WhatsApp Confirmation',
                    'title_ru' => 'Получите подтверждение в WhatsApp',
                    'desc' => "You'll get an instant WhatsApp message with your final booking details, the exact pricing breakdown, pickup time, and driver's contact.",
                    'desc_ru' => 'Вы сразу получите сообщение в WhatsApp с деталями бронирования, расчетом стоимости, точным временем посадки и контактом водителя.',
                ],
                [
                    'number' => '06', 'icon' => '⏰',
                    'title' => 'Be Ready 10-15 Minutes Early',
                    'title_ru' => 'Будьте готовы за 10-15 минут',
                    'desc' => 'Buses depart on schedule. Please be at your pickup point 10 to 15 minutes before departure. The driver will wait a maximum of 5 minutes.',
                    'desc_ru' => 'Автобусы отправляются строго по расписанию. Будьте на месте посадки за 10-15 минут до отправления. Водитель ждёт максимум 5 минут.',
                ],
            ],

            // ─────────────────────────── DO ───────────────────────────
            'booking_dos' => [
                ['item' => 'Bring your booking confirmation (screenshot or WhatsApp message)',
                 'item_ru' => 'Возьмите подтверждение бронирования (скриншот или сообщение в WhatsApp)'],
                ['item' => 'Have the remaining balance ready in cash (EGP, USD, or EUR)',
                 'item_ru' => 'Подготовьте оставшуюся сумму наличными (EGP, USD или EUR)'],
                ['item' => 'Arrive at the pickup point 10 to 15 minutes before departure',
                 'item_ru' => 'Приходите на место посадки за 10-15 минут до отправления'],
                ['item' => 'Keep your WhatsApp notifications on for driver updates',
                 'item_ru' => 'Оставьте уведомления WhatsApp включёнными — водитель сообщит об изменениях'],
                ['item' => 'Bring water — the weather can be very hot, especially in summer',
                 'item_ru' => 'Возьмите воду — погода может быть очень жаркой, особенно летом'],
                ['item' => 'Confirm your return trip time with the driver if booking round-trip',
                 'item_ru' => 'Если бронируете поездку туда и обратно, уточните у водителя время обратного рейса'],
            ],

            // ─────────────────────────── DON'T ───────────────────────────
            'booking_donts' => [
                ['item' => "Don't be late — buses depart on schedule and won't wait more than 5 minutes",
                 'item_ru' => 'Не опаздывайте — автобусы отправляются по расписанию и ждут не более 5 минут'],
                ['item' => "Don't behave disrespectfully towards the driver or other passengers",
                 'item_ru' => 'Не проявляйте неуважение к водителю или другим пассажирам'],
                ['item' => "Don't smoke or consume alcohol on the bus",
                 'item_ru' => 'Не курите и не употребляйте алкоголь в автобусе'],
                ['item' => "Don't book without reading the cancellation policy (no refunds)",
                 'item_ru' => 'Не бронируйте, не прочитав правила отмены (без возврата средств)'],
                ['item' => "Don't share your booking confirmation with others — it's personal",
                 'item_ru' => 'Не передавайте подтверждение бронирования другим — оно персональное'],
                ['item' => "Don't negotiate prices — all fares are fixed and transparent",
                 'item_ru' => 'Не торгуйтесь — все тарифы фиксированные и прозрачные'],
            ],

            // ─────────────────────────── IMPORTANT NOTES ───────────────────────────
            'important_notes' => [
                [
                    'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'title' => 'Map Coverage Areas',
                    'title_ru' => 'Зоны обслуживания на карте',
                    'text' => 'We only operate within the active zones highlighted on our map. If your hotel is inside these boundaries but unlisted, message us. Bookings outside these areas are not accepted.',
                    'text_ru' => 'Мы работаем только в активных зонах, выделенных на карте. Если ваш отель находится в этих границах, но его нет в списке, напишите нам. Бронирования за пределами этих зон не принимаются.',
                ],
                [
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'title' => 'Departure Times',
                    'title_ru' => 'Время отправления',
                    'text' => 'All times shown are local time (UTC+3). Buses depart exactly on schedule — no delays for late passengers.',
                    'text_ru' => 'Всё время указано по местному времени (UTC+3). Автобусы отправляются строго по расписанию — опоздавших не ждут.',
                ],
                [
                    'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
                    'title' => 'Pickup Locations',
                    'title_ru' => 'Места посадки',
                    'text' => 'Your exact pickup point is selected via the map and confirmed on WhatsApp. If your hotel is outside the zone, we can arrange a nearby meeting point.',
                    'text_ru' => 'Точное место посадки выбирается на карте и подтверждается в WhatsApp. Если ваш отель находится вне зоны, мы организуем ближайшее место встречи.',
                ],
                [
                    'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
                    'title' => 'Need Help?',
                    'title_ru' => 'Нужна помощь?',
                    'text' => 'Tap the WhatsApp button on your screen to reach our support team before booking if you have any questions.',
                    'text_ru' => 'Нажмите кнопку WhatsApp на экране, чтобы связаться с нашей службой поддержки до бронирования, если у вас есть вопросы.',
                ],
            ],
        ];
    }
}