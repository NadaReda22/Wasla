<?php
namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Database\Seeders\BookingInstructionsSeeder;
use Database\Seeders\LegalPoliciesSeeder;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Arr;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SettingResource::class;
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    private const SECRET_FIELDS = [
        'kashier_test_api_key',
        'kashier_test_webhook_secret',
        'kashier_live_api_key',
        'kashier_live_webhook_secret',
        'green_api_token',
    ];

    public function mount(): void
    {
        $settings = Setting::firstOrCreate([]);

        // 1. Run Seeders to ensure defaults exist in the DB if empty
        (new BookingInstructionsSeeder())->run();
        (new LegalPoliciesSeeder())->run();
        $settings->refresh();

        // 2. Check and fill Fleet Images 
        $fleet = is_array($settings->fleet_images) ? $settings->fleet_images : [];
        if (count($fleet) !== 7) {
            $settings->update(['fleet_images' => SettingResource::fleetDefaults()]);
            $settings->refresh();
        }

        $data = Arr::except($settings->toArray(), self::SECRET_FIELDS);
        $data['fleet_images'] = collect($data['fleet_images'] ?? [])
            ->values()
            ->map(fn ($row, $i) => array_merge($row, ['slot' => $i]))
            ->all();

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(SettingResource::getFormSchema())
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = Setting::first();
        $state = $this->form->getState();

      
        if (($state['kashier_mode'] ?? 'test') === 'live') {
            $missing = collect(['merchant_id', 'api_key', 'webhook_secret'])
                ->filter(fn ($f) => blank($state["kashier_live_{$f}"] ?? null) && blank($settings->{"kashier_live_{$f}"}));

            if ($missing->isNotEmpty()) {
                Notification::make()
                    ->title(__('admin.settings_page.cannot_switch_live') ?? 'Cannot switch to Live mode')
                    ->body(__('admin.settings_page.fill_live_credentials') ?? 'Fill in the Live Merchant ID, API Key and Webhook Secret first.')
                    ->danger()
                    ->send();

                return;
            }
        }

        $settings->update($state);

        foreach (self::SECRET_FIELDS as $field) {
            $this->data[$field] = null;
        }

        Notification::make()
            ->title(__('admin.notifications.settings_saved') ?? 'Settings saved')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return __('admin.nav.general_settings') ?? 'General Settings';
    }
}