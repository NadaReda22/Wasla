<?php
namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use App\Models\Faq;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class ManageFaqs extends Page
{
    protected static string $resource = FaqResource::class;
    protected static string $view = 'filament.pages.manage-faqs';

    public Collection $faqs;

    public function mount(): void
    {
        $this->faqs = Faq::ordered()->get();
    }

    // ---------- Actions ----------
    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Delete FAQ')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription('Are you sure you want to delete this FAQ?')
            ->action(function (array $arguments) {
                $faq = Faq::findOrFail($arguments['faq']);
                $faq->delete();

                $this->faqs = Faq::ordered()->get();

                Notification::make()
                    ->title('FAQ deleted')
                    ->success()
                    ->send();
            });
    }

    public function toggleActiveAction(): Action
    {
        return Action::make('toggleActive')
            ->label(fn (array $arguments) => Faq::find($arguments['faq'])?->is_active ? 'Deactivate' : 'Activate')
            ->icon(fn (array $arguments) => Faq::find($arguments['faq'])?->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
            ->color(fn (array $arguments) => Faq::find($arguments['faq'])?->is_active ? 'gray' : 'success')
            ->action(function (array $arguments) {
                $faq = Faq::findOrFail($arguments['faq']);
                $faq->update(['is_active' => !$faq->is_active]);

                $this->faqs = Faq::ordered()->get();

                Notification::make()
                    ->title($faq->is_active ? 'FAQ activated' : 'FAQ deactivated')
                    ->success()
                    ->send();
            });
    }

    // ---------- Header ----------
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add FAQ')
                ->icon('heroicon-o-plus')
                ->url(fn () => FaqResource::getUrl('create')),   // <-- this tells it where to go
        ];
    }

    public function getTitle(): string
    {
        return 'FAQs';
    }
}