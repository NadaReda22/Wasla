<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Set to true because any public tourist can submit a booking
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Client details
            'full_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            
            // Booking details
            'tour_id' => 'required|exists:tours,id',
            'tour_departure_id' => 'required|exists:tour_departures,id',
            'tour_pricing_tier_id' => 'required|exists:tour_pricing_tiers,id',
            'hotel_id' => 'required|exists:hotels,id',
            'room_number' => 'nullable|string|max:50',
            'booking_date' => 'required|date|after_or_equal:today',
            'special_requests' => 'nullable|string',
        ];
    }

    /**
     * Custom error messages (Optional)
     */
    public function messages(): array
    {
        return [
            'tour_id.required' => 'Please select a valid tour.',
            'hotel_id.required' => 'Please select a pickup hotel.',
            'booking_date.after_or_equal' => 'The booking date cannot be in the past.',
        ];
    }
}