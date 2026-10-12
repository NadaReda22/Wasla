<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use App\Models\Faq;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class ManageFaqs extends Page
{
    protected static string $resource = FaqResource::class;

    protected static string $view = 'filament.pages.manage-faqs';

    public function getTitle(): string
    {
        return __('admin.nav.faqs');
    }

    /** Used by the Blade view as $this->faqs */
    public function getFaqsProperty(): Collection
    {
        return Faq::orderBy('sort_order')->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Without ->model(), CreateAction does `new $model` on null => "Class name must be a valid object or a string".
            Actions\CreateAction::make()
                ->model(Faq::class)
                ->label(fn () => __('admin.actions.add_faq'))
                ->icon('heroicon-o-plus')
                ->form(fn (Form $form) => FaqResource::form($form))
                ->createAnother(false),
        ];
    }


    public function editAction(): Actions\EditAction
    {
        return Actions\EditAction::make('edit')
            ->model(Faq::class)
            ->record(fn (array $arguments) => Faq::findOrFail($arguments['faq']))
            ->form(fn (Form $form) => FaqResource::form($form))
            ->color('warning');
    }

    public function deleteAction(): Actions\Action
    {
        return Actions\Action::make('delete')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn () => __('admin.actions.delete_faq'))
            ->modalDescription(fn () => __('admin.notifications.delete_faq_desc'))
            ->action(function (array $arguments): void {
                Faq::find($arguments['faq'])?->delete();

                Notification::make()
                    ->title(__('admin.notifications.faq_deleted'))
                    ->success()
                    ->send();
            });
    }

    public function toggleActiveAction(): Actions\Action
    {
        return Actions\Action::make('toggleActive')
            ->action(function (array $arguments): void {
                $faq = Faq::findOrFail($arguments['faq']);
                $faq->update(['is_active' => ! $faq->is_active]);

                Notification::make()
                    ->title(__($faq->is_active ? 'admin.notifications.faq_activated' : 'admin.notifications.faq_deactivated'))
                    ->success()
                    ->send();
            });
    }
}