@extends('layouts.app')

@section('content')
@php
    $faqs = [
        ['q' => 'How much does a ride cost?', 'a' => 'Every route is a flat $4. You pay just $1 as a deposit online, and the remaining $3 in cash on the bus.'],
        ['q' => 'How do I pay the deposit?', 'a' => 'You can pay the $1 deposit online using our secure payment gateway (Visa, Mastercard, or Fawry). For countries with banking restrictions (like Russia, Iran, Syria, etc.), you can pay the full $4 in cash on the bus.'],
        ['q' => 'How early should I book?', 'a' => 'We recommend booking at least 24 hours in advance to guarantee your seat, especially during peak tourist season.'],
        ['q' => 'Can I cancel my booking?', 'a' => 'Yes, you can cancel for free up to 12 hours before your trip. The $1 deposit is non-refundable for cancellations within 12 hours. Contact us via WhatsApp to cancel.'],
        ['q' => 'What if the bus is late?', 'a' => 'Our buses are very punctual, but if there is a delay, we will send you a WhatsApp notification immediately with an update.'],
        ['q' => 'Is cash payment available for everyone?', 'a' => 'Yes, cash payment is available for everyone. It is the default option for countries with banking restrictions like Russia, Iran, North Korea, Syria, Cuba, and Belarus.'],
        ['q' => 'Can I modify my trip time?', 'a' => 'Yes, you can modify the departure time up to 6 hours before the trip by contacting us on WhatsApp.'],
        ['q' => 'Are the buses air-conditioned?', 'a' => 'Yes, all our buses are modern and fully air-conditioned with comfortable reclining seats and ample luggage space.'],
        ['q' => 'Do you provide child seats?', 'a' => 'Yes, we can provide child seats upon request. Please mention this in the special notes field when booking.'],
        ['q' => 'What is the maximum luggage allowed?', 'a' => 'Each passenger can bring one carry-on bag and one checked bag. Extra luggage can be arranged by noting it in the special requests field.'],
        ['q' => 'Do you operate every day?', 'a' => 'Yes, we operate 7 days a week with multiple departure times throughout the day for each route.'],
        ['q' => 'How do I contact customer support?', 'a' => 'You can reach us anytime via WhatsApp by clicking the green button at the bottom left of any page, or by calling +20 100 000 0000.'],
    ];
@endphp

<div class="min-h-screen pt-20 pb-16">
    <section class="gradient-navy text-white py-16">
        <div class="container-max px-4 text-center">
            <span class="badge-gold mb-4 inline-block">FAQ</span>
            <h1 class="text-3xl sm:text-5xl font-display font-bold mb-4">
                Frequently Asked Questions
            </h1>
            <p class="text-white/70 max-w-2xl mx-auto text-lg">
                Got questions? We have answers. Find everything you need to know about booking with Wasla.
            </p>
        </div>
    </section>

    <section class="section-padding bg-white">
        <div class="container-max">
            <div class="max-w-3xl mx-auto space-y-4">
                @foreach($faqs as $faq)
                    <details class="card p-5 group cursor-pointer">
                        <summary class="font-semibold text-navy flex items-center justify-between list-none marker:hidden">
                            {{ $faq['q'] }}
                            <svg class="w-5 h-5 text-primary transition-transform group-open:rotate-180 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="text-navy/60 mt-3 leading-relaxed">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <div class="text-center mt-12">
                <p class="text-navy/60 mb-4">Still have questions?</p>
                <a href="https://wa.me/201000000000" target="_blank" rel="noopener noreferrer" class="btn-turquoise inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
                    </svg>
                    Contact Us on WhatsApp
                </a>
            </div>
        </div>
    </section>
</div>
@endsection