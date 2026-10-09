<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Database\Seeders\BookingInstructionsSeeder;
use Database\Seeders\LegalPoliciesSeeder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.general_settings');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.settings');
    }

    public static function fleetDefaults(): array
    {
        $rows = [
            ['type' => 'exterior', 'label' => 'Exterior — Main Fleet Van',          'alt' => 'Modern white tourist vehicle',                'image' => null],
            ['type' => 'exterior', 'label' => 'Exterior — Resort Transfer Vehicle', 'alt' => 'Luxury tourist vehicle exterior',             'image' => null],
            ['type' => 'interior', 'label' => 'Interior — Spacious Seats & A/C',    'alt' => 'Interior of air-conditioned tourist vehicle', 'image' => null],
            ['type' => 'interior', 'label' => 'Interior — Clear View Windows',      'alt' => 'Vehicle interior showing panoramic windows',  'image' => null],
            ['type' => 'interior', 'label' => 'Interior — Premium Minivan',         'alt' => 'Modern tourist minibus interior',             'image' => null],
            ['type' => 'exterior', 'label' => 'Exterior — Coastal Route',           'alt' => 'Tourist vehicle driving along coastal road',  'image' => null],
            ['type' => 'interior', 'label' => 'Interior — Premium Minivan',         'alt' => 'Modern tourist minibus interior',             'image' => null],
        ];

        return array_map(fn ($row, $i) => $row + ['slot' => $i], $rows, array_keys($rows));
    }

    public static function fleetDefaultImagePath(int $slot): string
    {
        $files = [1, 2, 3, 6, 5, 4, 7];
        return 'images/bus-showcase/vehicle' . ($files[$slot] ?? 1) . '.png';
    }

    /** Write-only secret field: the saved value is never loaded back into the form. */
    protected static function secretInput(string $name, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)
            ->password()
            ->revealable()                                 
            ->autocomplete('new-password')                 
            ->dehydrated(fn ($state) => filled($state))     
            ->placeholder(fn () => filled(Setting::first()?->{$name})
                ? __('admin.settings_page.secret_saved')
                : __('admin.settings_page.secret_paste'))
            ->helperText(fn () => filled(Setting::first()?->{$name})
                ? __('admin.settings_page.key_saved')
                : __('admin.settings_page.key_not_set'));
    }

    protected static function kashierCredentials(string $mode): Forms\Components\Fieldset
    {
        return Forms\Components\Fieldset::make($mode === 'live' ? __('admin.settings_page.live_credentials') : __('admin.settings_page.test_credentials'))
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make("kashier_{$mode}_merchant_id")->label(fn() => __('admin.settings_page.merchant_id')),
                self::secretInput("kashier_{$mode}_api_key", __('admin.settings_page.api_key')),
                self::secretInput("kashier_{$mode}_webhook_secret", __('admin.settings_page.webhook_secret')),
            ]);
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Section::make(fn () => __('admin.sections.brand_identity') ?? 'Brand Identity')
                        ->description(fn () => __('admin.sections.brand_identity_desc') ?? 'Name, slogan, logo, and page backgrounds')
                        ->icon('heroicon-o-building-office')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpan(2)
                        ->schema([
                            Forms\Components\TextInput::make('brand_name')->label(fn () => __('admin.fields.brand_name') ?? 'Brand Name')->required()->maxLength(255),
                            Forms\Components\TextInput::make('brand_slogan')->label(fn () => __('admin.fields.brand_slogan') ?? 'Slogan')->maxLength(255),
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\FileUpload::make('brand_logo')->label(fn () => __('admin.fields.brand_logo') ?? 'Logo')->image()->directory('settings/brand')->maxSize(10000)
                                    ->saveUploadedFileUsing(function ($file, $get) {
                                        $path = $file->store('settings/brand', 'public');
                                        $fullPath = storage_path('app/public/' . $path);
                                        $manager = new ImageManager(new Driver());
                                        $img = $manager->read($fullPath);
                                        $img->trim();
                                        $img->scaleDown(width: 600);
                                        $img->save($fullPath);
                                        return $path;
                                    }),
                                Forms\Components\FileUpload::make('landing_background')->label(fn () => __('admin.fields.landing_background') ?? 'Landing Background')->image()->directory('settings/landing')->maxSize(10240)
                                    ->saveUploadedFileUsing(function ($file, $get) {
                                        $path = $file->store('settings/landing', 'public');
                                        $fullPath = storage_path('app/public/' . $path);
                                        $manager = new ImageManager(new Driver());
                                        $img = $manager->read($fullPath);
                                        $img->scaleDown(width: 800);
                                        $img->save($fullPath, quality: 80);
                                        return $path;
                                    }),
                                Forms\Components\FileUpload::make('booking_background')->label(fn () => __('admin.fields.booking_background') ?? 'Booking Background')->image()->directory('settings/backgrounds')->maxSize(10240)
                                    ->saveUploadedFileUsing(function ($file, $get) {
                                        $path = $file->store('settings/backgrounds', 'public');
                                        $fullPath = storage_path('app/public/' . $path);
                                        $manager = new ImageManager(new Driver());
                                        $img = $manager->read($fullPath);
                                        $img->scaleDown(width: 1920);
                                        $img->save($fullPath, quality: 80);
                                        return $path;
                                    }),
                            ]),
                        ]),

                    Forms\Components\Section::make(fn () => __('admin.sections.dashboard_identity') ?? 'Dashboard Identity')
                        ->description(fn () => __('admin.sections.dashboard_identity_desc') ?? 'Admin name and avatar shown in the dashboard')
                        ->icon('heroicon-o-user-circle')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\TextInput::make('admin_name')->label(fn () => __('admin.fields.admin_name') ?? 'Admin Display Name')->required()->maxLength(255),
                            Forms\Components\FileUpload::make('admin_image')->label(fn () => __('admin.fields.admin_image') ?? 'Admin Avatar')->image()->directory('settings/admin')->maxSize(10000),
                        ]),

                    Forms\Components\Section::make(fn () => __('admin.sections.landing_pricing') ?? 'Landing Page Pricing')
                        ->description(fn () => __('admin.sections.landing_pricing_desc') ?? 'Minimum tour cost and deposit (in USD)')
                        ->icon('heroicon-o-currency-dollar')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpan(2)
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('minimum_tour_cost_dollar')->label(fn () => __('admin.fields.min_tour_cost') ?? 'Minimum Tour Cost (USD)')->numeric()->prefix('$')->minValue(0)->step(0.01),
                                    Forms\Components\TextInput::make('deposit_amount_dollar')->label(fn () => __('admin.fields.deposit_amount') ?? 'Deposit Amount (USD)')->numeric()->prefix('$')->minValue(0)->step(0.01),
                                ]),
                        ]),

                    Forms\Components\Section::make(fn () => __('admin.sections.announcements') ?? 'Announcements & Offers')
                        ->description(fn () => __('admin.sections.announcements_desc') ?? 'Top offer bar and main announcement card')
                        ->icon('heroicon-o-megaphone')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Textarea::make('offer_bar_text')->label(fn () => __('admin.fields.offer_bar_en') ?? 'Offer Bar Text (English)')->rows(2),
                                Forms\Components\Textarea::make('offer_bar_text_ru')->label(fn () => __('admin.fields.offer_bar_ru') ?? 'Offer Bar Text (Russian)')->rows(2),
                            ]),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Textarea::make('announcement_card_text')->label(fn () => __('admin.fields.announcement_card_en') ?? 'Announcement Card (English)')->rows(3),
                                Forms\Components\Textarea::make('announcement_card_text_ru')->label(fn () => __('admin.fields.announcement_card_ru') ?? 'Announcement Card (Russian)')->rows(3),
                            ]),
                        ]),

                    Forms\Components\Section::make(fn () => __('admin.sections.contact_info') ?? 'Contact Information')
                        ->description(fn () => __('admin.sections.contact_info_desc') ?? 'Email and phone displayed on the landing page')
                        ->icon('heroicon-o-phone')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('contact_email')->label(fn () => __('admin.fields.contact_email') ?? 'Email')->email()->required(),
                                    Forms\Components\TextInput::make('contact_phone')->label(fn () => __('admin.fields.contact_phone') ?? 'Phone')->tel()->required(),
                                ]),
                        ]),

                    Forms\Components\Section::make(fn () => __('admin.settings_page.fleet_showcase'))
                        ->description(fn () => __('admin.settings_page.fleet_showcase_desc'))
                        ->icon('heroicon-o-camera')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('reset_fleet')
                                    ->label(fn () => __('admin.settings_page.reset_fleet'))
                                    ->icon('heroicon-o-arrow-path')
                                    ->color('gray')
                                    ->requiresConfirmation()
                                    ->modalDescription(fn () => __('admin.settings_page.reset_fleet_desc'))
                                    ->action(function (Forms\Set $set) {
                                        $set('fleet_images', self::fleetDefaults());
                                        Notification::make()->title(__('admin.settings_page.defaults_loaded'))->warning()->send();
                                    }),
                            ]),

                            Forms\Components\Repeater::make('fleet_images')
                                ->label(fn () => __('admin.settings_page.vehicle_images'))
                                ->schema([
                                    Forms\Components\Hidden::make('slot'),
                                    Forms\Components\Placeholder::make('default_preview')
                                        ->label(fn () => __('admin.settings_page.default_preview'))
                                        ->content(fn (Forms\Get $get) => new HtmlString('<img src="' . e(asset(self::fleetDefaultImagePath((int) $get('slot')))) . '" class="h-32 rounded-lg object-cover" alt="">'))
                                        ->visible(fn (Forms\Get $get) => empty($get('image')))
                                        ->columnSpanFull(),
                                    Forms\Components\FileUpload::make('image')
                                        ->label(fn () => __('admin.settings_page.image_upload'))
                                        ->image()
                                        ->imageEditor()
                                        ->imageEditorAspectRatios(['4:3'])
                                        ->directory('settings/fleet')
                                        ->maxSize(5120)
                                        ->saveUploadedFileUsing(function ($file, $get) {
                                            $path = $file->store('settings/fleet', 'public');
                                            $fullPath = storage_path('app/public/' . $path);
                                            $manager = new ImageManager(new Driver());
                                            $img = $manager->read($fullPath);
                                            $img->scaleDown(width: 800);
                                            $img->save($fullPath, quality: 80);
                                            return $path;
                                        })
                                        ->live()
                                        ->columnSpanFull(),
                                    Forms\Components\Select::make('type')
                                   ->label(fn () => __('admin.settings_page.type'))
                                        ->options([
                                            'exterior' => __('admin.settings_page.type_exterior'), 
                                            'interior' => __('admin.settings_page.type_interior')
                                        ])->required(),
                                    Forms\Components\TextInput::make('label')->label(fn () => __('admin.settings_page.badge_label'))->required(),
                                    Forms\Components\TextInput::make('alt')->label(fn () => __('admin.settings_page.seo_alt'))->required(),
                                ])
                                ->columns(3)
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->default(self::fleetDefaults()),
                        ]),

                    Forms\Components\Section::make(fn () => __('admin.settings_page.instructions_page'))
                        ->description(fn () => __('admin.settings_page.instructions_page_desc'))
                        ->icon('heroicon-o-document-text')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('reset_instructions')
                                    ->label(fn () => __('admin.settings_page.reset_instructions'))
                                    ->icon('heroicon-o-arrow-path')
                                    ->color('gray')
                                    ->requiresConfirmation()
                                    ->modalDescription(fn () => __('admin.settings_page.reset_instructions_desc'))
                                    ->action(function (Forms\Set $set) {
                                        foreach (BookingInstructionsSeeder::defaults() as $field => $rows) {
                                            $set($field, $rows);
                                        }
                                        Notification::make()->title(__('admin.settings_page.instructions_loaded'))->warning()->send();
                                    }),
                            ]),
                            Forms\Components\Repeater::make('booking_steps')->label(fn () => __('admin.settings_page.booking_steps'))
                                ->schema([
                                    Forms\Components\TextInput::make('number')->label(fn () => __('admin.settings_page.step_number'))->required(),
                                    Forms\Components\TextInput::make('icon')->label(fn () => __('admin.settings_page.emoji_icon'))->required(),
                                    Forms\Components\TextInput::make('title')->label(fn () => __('admin.settings_page.title_en'))->required(),
                                    Forms\Components\TextInput::make('title_ru')->label(fn () => __('admin.settings_page.title_ru')),
                                    Forms\Components\Textarea::make('desc')->label(fn () => __('admin.settings_page.desc_en'))->required()->rows(3),
                                    Forms\Components\Textarea::make('desc_ru')->label(fn () => __('admin.settings_page.desc_ru'))->rows(3),
                                ])
                                ->columns(2)
                                ->itemLabel(fn (array $state): ?string => trim(($state['number'] ?? '') . ' ' . ($state['title'] ?? '')) ?: null)
                                ->collapsed(),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Repeater::make('booking_dos')->label(fn () => __('admin.settings_page.dos'))
                                    ->schema([
                                        Forms\Components\TextInput::make('item')->label(fn () => __('admin.settings_page.rule_en'))->required(),
                                        Forms\Components\TextInput::make('item_ru')->label(fn () => __('admin.settings_page.rule_ru')),
                                    ])
                                    ->columns(2)
                                    ->itemLabel(fn (array $state): ?string => $state['item'] ?? null)
                                    ->collapsed(),

                                Forms\Components\Repeater::make('booking_donts')->label(fn () => __('admin.settings_page.donts'))
                                    ->schema([
                                        Forms\Components\TextInput::make('item')->label(fn () => __('admin.settings_page.rule_en'))->required(),
                                        Forms\Components\TextInput::make('item_ru')->label(fn () => __('admin.settings_page.rule_ru')),
                                    ])
                                    ->columns(2)
                                    ->itemLabel(fn (array $state): ?string => $state['item'] ?? null)
                                    ->collapsed(),
                            ]),

                            Forms\Components\Repeater::make('important_notes')->label(fn () => __('admin.settings_page.important_notes'))
                                ->schema([
                                    Forms\Components\TextInput::make('icon')->label(fn () => __('admin.settings_page.svg_path'))->required()->columnSpanFull(),
                                    Forms\Components\TextInput::make('title')->label(fn () => __('admin.settings_page.title_en'))->required(),
                                    Forms\Components\TextInput::make('title_ru')->label(fn () => __('admin.settings_page.title_ru')),
                                    Forms\Components\Textarea::make('text')->label(fn () => __('admin.settings_page.text_en'))->required()->rows(3),
                                    Forms\Components\Textarea::make('text_ru')->label(fn () => __('admin.settings_page.text_ru'))->rows(3),
                                ])
                                ->columns(2)
                                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                ->collapsed(),
                        ]),

                    Forms\Components\Section::make(fn () => __('admin.settings_page.legal_policies'))
                        ->description(fn () => __('admin.settings_page.legal_policies_desc'))
                        ->icon('heroicon-o-scale')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('reset_policies')
                                    ->label(fn () => __('admin.settings_page.reset_policies'))
                                    ->icon('heroicon-o-arrow-path')
                                    ->color('gray')
                                    ->requiresConfirmation()
                                    ->modalDescription(fn () => __('admin.settings_page.reset_policies_desc'))
                                    ->action(function (Forms\Set $set) {
                                        $set('legal_policies', LegalPoliciesSeeder::defaults());
                                        Notification::make()->title(__('admin.settings_page.policies_loaded'))->warning()->send();
                                    }),
                            ]),

                            Forms\Components\Repeater::make('legal_policies')
                                ->label(fn () => __('admin.settings_page.policy_sections'))
                                ->schema([
                                    Forms\Components\TextInput::make('key')
                                    ->label(fn () => __('admin.settings_page.policy_key'))->disabled()->dehydrated()->columnSpanFull(),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('title_en')->label(fn () => __('admin.settings_page.title_en'))->required(),
                                        Forms\Components\TextInput::make('title_ru')->label(fn () => __('admin.settings_page.title_ru'))->required(),
                                    ]),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\Repeater::make('items_en')
                                            ->label(fn () => __('admin.settings_page.rules_en'))
                                            ->schema([ Forms\Components\TextInput::make('item')->label(fn () => __('admin.settings_page.rule'))->required() ])
                                            ->itemLabel(fn (array $state): ?string => $state['item'] ?? null)
                                            ->collapsed(),
                                        Forms\Components\Repeater::make('items_ru')
                                            ->label(fn () => __('admin.settings_page.rules_ru'))
                                            ->schema([ Forms\Components\TextInput::make('item')->label(fn () => __('admin.settings_page.rule'))->required() ])
                                            ->itemLabel(fn (array $state): ?string => $state['item'] ?? null)
                                            ->collapsed(),
                                    ]),
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? null)
                                ->collapsed()
                        ]),

                    Forms\Components\Section::make(fn () => __('admin.settings_page.integrations'))
                        ->description(fn () => __('admin.settings_page.integrations_desc'))
                        ->icon('heroicon-o-key')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema([

                            Forms\Components\Section::make(fn () => __('admin.settings_page.kashier'))
                                ->schema([
                                    Forms\Components\ToggleButtons::make('kashier_mode')
                                        ->label(fn () => __('admin.settings_page.mode'))
                                        ->options([
                                            'test' => __('admin.settings_page.mode_test'), 
                                            'live' => __('admin.settings_page.mode_live')
                                        ])
                                        ->colors(['test' => 'warning', 'live' => 'success'])
                                        ->inline()
                                        ->default('test')
                                        ->required(),

                                    Forms\Components\Grid::make(2)->schema([
                                        self::kashierCredentials('test'),
                                        self::kashierCredentials('live'),
                                    ]),
                                ]),

                            Forms\Components\Section::make(fn () => __('admin.settings_page.whatsapp'))
                                ->schema([
                                    Forms\Components\Toggle::make('whatsapp_enabled')->label(fn () => __('admin.settings_page.whatsapp_enabled')),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('green_api_host')->label(fn () => __('admin.settings_page.api_host'))->placeholder('https://XXXX.api.greenapi.com')->url(),
                                        Forms\Components\TextInput::make('green_api_instance_id')->label(fn () => __('admin.settings_page.instance_id')),
                                    ]),
                                    self::secretInput('green_api_token', __('admin.settings_page.api_token')),
                                ]),
                        ]),
                ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}