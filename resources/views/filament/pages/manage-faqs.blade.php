@php
    use App\Filament\Resources\FaqResource;
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($this->faqs as $faq)
            <div x-data="{ open: false }"
                 class="bg-white rounded-2xl border border-[#DCE3EB] shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <!-- Card Header -->
                <div class="flex items-center justify-between p-5 cursor-pointer" @click="open = !open">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="px-2 py-0.5 text-xs font-bold uppercase bg-[#F5B400]/10 text-[#F5B400] rounded-full">
                                Order {{ $faq->sort_order }}
                            </span>
                            @if($faq->is_active)
                                <span class="px-2 py-0.5 text-xs font-bold uppercase bg-green-50 text-green-600 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-bold uppercase bg-red-50 text-red-600 rounded-full">Inactive</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-[#0A2540]">{{ $faq->question_en }}</h3>
                    </div>
                    <!-- Chevron icon -->
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-200" 
                         :class="open ? 'rotate-180' : ''" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <!-- Expandable Content -->
                <div x-show="open" x-collapse>
                    <div class="px-5 pb-5 space-y-4 border-t border-gray-100 pt-4">
                        <!-- Answer -->
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Answer</h4>
                            <div class="prose prose-sm max-w-none text-gray-700">
                                {!! $faq->answer_en !!}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-2 pt-2 border-t border-gray-50">
                            <!-- Active Toggle (Filament Action) -->
                            <x-filament::button
                                wire:click="mountAction('toggleActive', { faq: {{ $faq->id }} })"
                                :color="$faq->is_active ? 'gray' : 'success'"
                                size="sm"
                            >
                                @if($faq->is_active)
                                    <x-heroicon-o-x-circle class="w-4 h-4 mr-1" />
                                    Deactivate
                                @else
                                    <x-heroicon-o-check-circle class="w-4 h-4 mr-1" />
                                    Activate
                                @endif
                            </x-filament::button>

                            <!-- Edit Button -->
                            <x-filament::button
                                tag="a"
                                href="{{ FaqResource::getUrl('edit', ['record' => $faq->id]) }}"
                                color="warning"
                                size="sm"
                            >
                                <x-heroicon-o-pencil-square class="w-4 h-4 mr-1" />
                                Edit
                            </x-filament::button>

                            <!-- Delete Button (Filament Action with modal) -->
                            <x-filament::button
                                wire:click="mountAction('delete', { faq: {{ $faq->id }} })"
                                color="danger"
                                size="sm"
                            >
                                <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                Delete
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-[#DCE3EB]">
                <x-heroicon-o-question-mark-circle class="w-12 h-12 text-gray-300 mb-4" />
                <p class="text-gray-500">No FAQs yet. Click "Add FAQ" to create one.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>