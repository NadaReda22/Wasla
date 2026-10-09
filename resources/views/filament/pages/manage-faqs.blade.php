@php
    use App\Filament\Resources\FaqResource;

    $isAr = app()->getLocale() === 'ar';
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($this->faqs as $faq)
            <div x-data="{ open: false }"
                 class="bg-white rounded-2xl border border-[#DCE3EB] shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <!-- Card Header -->
                <div class="flex items-center justify-between p-5 cursor-pointer" @click="open = !open">
                    <div class="flex-1 pe-4">
                        <div class="flex items-center space-x-2 rtl:space-x-reverse mb-1">
                            <span class="px-2 py-0.5 text-xs font-bold uppercase bg-[#F5B400]/10 text-[#F5B400] rounded-full">
                                {{ __('admin.columns.order') }} {{ $faq->sort_order }}
                            </span>
                            @if($faq->is_active)
                                <span class="px-2 py-0.5 text-xs font-bold uppercase bg-green-50 text-green-600 rounded-full">{{ __('admin.columns.active') }}</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-bold uppercase bg-red-50 text-red-600 rounded-full">{{ __('admin.list_tours.paused') }}</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-[#0A2540]">{{ $isAr ? ($faq->question_ar ?? $faq->question_en) : $faq->question_en }}</h3>
                        @if(app()->getLocale() !== 'ru' && !empty($faq->question_ru))
                            <h4 class="text-sm text-gray-500 mt-0.5">{{ $faq->question_ru }}</h4>
                        @endif
                    </div>
                    <!-- Chevron icon -->
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-200 flex-shrink-0"
                         :class="open ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <!-- Expandable Content -->
                <div x-show="open" x-collapse>
                    <div class="px-5 pb-5 space-y-4 border-t border-gray-100 pt-4">

                        <!-- Answer (Primary Locale) -->
                        <div>
                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">
                                {{ $isAr ? __('admin.fields.answer_ar') : __('admin.fields.answer_en') }}
                            </h4>
                            <div class="prose prose-sm max-w-none text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                {!! $isAr ? ($faq->answer_ar ?? $faq->answer_en) : $faq->answer_en !!}
                            </div>
                        </div>

                        <!-- Answer (Russian) -->
                        @if(!empty($faq->answer_ru))
                        <div>
                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">
                                {{ __('admin.fields.answer_ru') }}
                            </h4>
                            <div class="prose prose-sm max-w-none text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                {!! $faq->answer_ru !!}
                            </div>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex justify-end space-x-2 rtl:space-x-reverse pt-2 border-t border-gray-50">
                            <!-- Active Toggle -->
                            <x-filament::button
                                wire:click="mountAction('toggleActive', { faq: {{ $faq->id }} })"
                                :color="$faq->is_active ? 'gray' : 'success'"
                                size="sm"
                            >
                                @if($faq->is_active)
                                    <x-heroicon-o-x-circle class="w-4 h-4 me-1" />
                                    {{ __('admin.actions.deactivate') }}
                                @else
                                    <x-heroicon-o-check-circle class="w-4 h-4 me-1" />
                                    {{ __('admin.actions.activate') }}
                                @endif
                            </x-filament::button>

                            <!-- Edit (Filament action, receives the FAQ id as an argument) -->
                            <x-filament::button
                                wire:click="mountAction('edit', { faq: {{ $faq->id }} })"
                                color="warning"
                                size="sm"
                            >
                                <x-heroicon-o-pencil-square class="w-4 h-4 me-1" />
                                {{ __('admin.actions.edit') }}
                            </x-filament::button>

                            <!-- Delete Button -->
                            <x-filament::button
                                wire:click="mountAction('delete', { faq: {{ $faq->id }} })"
                                color="danger"
                                size="sm"
                            >
                                <x-heroicon-o-trash class="w-4 h-4 me-1" />
                                {{ __('admin.actions.delete') }}
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-[#DCE3EB]">
                <x-heroicon-o-question-mark-circle class="w-12 h-12 text-gray-300 mb-4" />
                <p class="text-gray-500">{{ __('admin.list_tours.no_faqs') }}</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>