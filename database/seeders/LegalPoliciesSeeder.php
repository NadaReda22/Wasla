<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class LegalPoliciesSeeder extends Seeder
{
    public static function defaults(): array
    {
        return [
            [
                'key' => 'booking',
                'title_en' => 'Booking Policy',
                'title_ru' => 'Бронирование',
                'items_en' => [
                    ['item' => 'All seat reservations are subject to availability.'], 
                    ['item' => 'You must accurately specify your location on the map, your exact hotel name, and provide an active WhatsApp number.'], 
                    ['item' => 'The company is completely not responsible for any incorrect data provided by the passenger.'],
                    ['item' => 'Your final booking confirmation and exact pickup time will be sent to you via a WhatsApp message.'],
                    ['item' => 'Bookings must be made for available hotels or within the specified coverage areas on the map. If a booking is made outside these areas, the company is not responsible for any refund.']
                ],
                'items_ru' => [
                    ['item' => 'Бронирование мест зависит от их наличия.'], 
                    ['item' => 'Вы должны точно указать свое местоположение на карте, точное название отеля и предоставить активный номер WhatsApp.'], 
                    ['item' => 'Компания не несет никакой ответственности за любые неверные данные, предоставленные пассажиром.'],
                    ['item' => 'Окончательное подтверждение бронирования и точное время подачи транспорта будут отправлены вам в сообщении WhatsApp.'],
                    ['item' => 'Бронирование должно осуществляться для доступных отелей или в пределах указанных зон обслуживания на карте. При бронировании за пределами этих зон компания не несет ответственности за возврат средств.']
                ],
            ],
            [
                'key' => 'cancel',
                'title_en' => 'Cancellation & Refunds',
                'title_ru' => 'Отмена и возврат',
                'items_en' => [
                    ['item' => 'The online booking deposit is strictly non-refundable.'], 
                    ['item' => 'If you cancel or miss your ride, the deposit is forfeited.'], 
                    ['item' => 'In the rare event that the company cancels a trip, a full refund will be issued.']
                ],
                'items_ru' => [
                    ['item' => 'Онлайн-депозит за бронирование не подлежит возврату ни при каких условиях.'], 
                    ['item' => 'При отмене бронирования или опоздании депозит аннулируется.'], 
                    ['item' => 'В редком случае отмены рейса со стороны компании, средства возвращаются полностью.']
                ],
            ],
            [
                'key' => 'conduct',
                'title_en' => 'Passenger Conduct',
                'title_ru' => 'Правила поведения',
                'items_en' => [
                    ['item' => 'Smoking and alcohol consumption are strictly prohibited inside the vehicle.'], 
                    ['item' => 'Passengers must maintain respectful behavior towards the driver and other traveling companions.'], 
                    ['item' => 'The driver reserves the right to refuse service for disruptive behavior.']
                ],
                'items_ru' => [
                    ['item' => 'Курение и употребление алкоголя в транспортном средстве строго запрещены.'], 
                    ['item' => 'Пассажиры должны уважительно относиться к водителю и другим попутчикам.'], 
                    ['item' => 'Водитель оставляет за собой право отказать в обслуживании за неподобающее поведение.']
                ],
            ],
            [
                'key' => 'payment',
                'title_en' => 'Payment Terms',
                'title_ru' => 'Оплата',
                'items_en' => [
                    ['item' => 'An online deposit via Kashier is required to secure your seat.'], 
                    ['item' => 'Please note that the deposit amount is not fixed and varies depending on the specific trip.'], 
                    ['item' => 'The remaining balance also varies based on the trip and must be paid in cash (USD, EUR, or EGP) to the driver.'],
                    ['item' => 'All fares are non-negotiable once booked.']
                ],
                'items_ru' => [
                    ['item' => 'Для гарантии места требуется онлайн-депозит через Kashier.'], 
                    ['item' => 'Обратите внимание, что сумма депозита не фиксирована и варьируется в зависимости от конкретной поездки.'], 
                    ['item' => 'Оставшаяся сумма также зависит от поездки и оплачивается водителю наличными (USD, EUR или EGP).'],
                    ['item' => 'Все тарифы не подлежат обсуждению после бронирования.']
                ],
            ],
            [
                'key' => 'delay',
                'title_en' => 'Delays & Punctuality',
                'title_ru' => 'Опоздания',
                'items_en' => [
                    ['item' => 'Vehicles operate on a strict, pre-planned schedule.'], 
                    ['item' => 'Drivers will wait a maximum of 5 minutes past the scheduled pickup time.'], 
                    ['item' => 'We are not responsible for missed tours due to passenger lateness.']
                ],
                'items_ru' => [
                    ['item' => 'Транспорт курсирует по строгому расписанию.'], 
                    ['item' => 'Максимальное время ожидания водителя — 5 минут после назначенного времени.'], 
                    ['item' => 'Мы не несем ответственности, если вы пропустили поездку из-за опоздания.']
                ],
            ],
            [
                'key' => 'privacy',
                'title_en' => 'Privacy Policy',
                'title_ru' => 'Конфиденциальность',
                'items_en' => [
                    ['item' => 'We collect basic data (name, phone, hotel) solely to fulfill your booking.'], 
                    ['item' => 'We do not store credit card details; all payments are secured by Kashier.'], 
                    ['item' => 'Your personal information is never sold or shared with third parties.']
                ],
                'items_ru' => [
                    ['item' => 'Мы собираем данные (имя, телефон, отель) только для оформления бронирования.'], 
                    ['item' => 'Мы не храним данные карт; платежи безопасно обрабатываются системой Kashier.'], 
                    ['item' => 'Ваши личные данные никогда не передаются третьим лицам.']
                ],
            ],
        ];
    }

    public function run(): void
    {
        $setting = Setting::firstOrCreate([]);
        $policies = is_array($setting->legal_policies) ? $setting->legal_policies : [];

        if (empty($policies) || count($policies) !== 6) {
            $setting->update(['legal_policies' => self::defaults()]);
        }
    }
}