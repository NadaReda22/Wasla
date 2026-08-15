<?php
namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Forms\Contracts\HasForms;             
use Filament\Forms\Concerns\InteractsWithForms;   
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;        

class ManageSettings extends Page implements HasForms  
{
    use InteractsWithForms;                            

    protected static string $resource = SettingResource::class;
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        // Load the first (and only) settings record – create one if missing
        $settings = Setting::firstOrCreate([]);
        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(SettingResource::getFormSchema())   // reuse the schema
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = Setting::first();
        $settings->update($this->form->getState());

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'General Settings';
    }
}