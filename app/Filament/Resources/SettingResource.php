<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'General Settings';

    // This static method lets us reuse the form schema in the page
    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Section::make('Brand Identity')
                        ->description('Name, slogan, and logo used on the landing page')
                        ->icon('heroicon-o-building-office')
                        ->collapsible()
                        ->columnSpan(2)
                        ->schema([
                            Forms\Components\TextInput::make('brand_name')
                                ->label('Brand Name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('brand_slogan')
                                ->label('Slogan')
                                ->maxLength(255),
                            Forms\Components\FileUpload::make('brand_logo')
                                ->label('Logo')
                                ->image()
                                ->directory('settings/brand')
                                ->maxSize(10000)
                               ->helperText('Upload a logo with transparent background, landscape orientation, around 600×380px (ratio ~1.6:1). It will be scaled to fit a 200×128px display area.')
                               ->saveUploadedFileUsing(function ($file, $get) {
                                $path = $file->store('settings/brand', 'public');
                                $fullPath = storage_path('app/public/' . $path);

                                $manager = new ImageManager(new Driver());
                                $img = $manager->read($fullPath);
                                $img->trim();
                                $img->scaleDown(width: 600); // cap size, keep aspect ratio, no cropping
                                $img->save($fullPath);

                                return $path;
                            }),
                                                                                ]),
                        Forms\Components\Section::make('Dashboard Identity')
                        ->description('Admin name and avatar shown in the dashboard')
                        ->icon('heroicon-o-user-circle')
                        ->collapsible()
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\TextInput::make('admin_name')
                                ->label('Admin Display Name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\FileUpload::make('admin_image')
                                ->label('Admin Avatar')
                                ->image()
                                ->directory('settings/admin')
                                ->maxSize(10000),
                        ]),

                    Forms\Components\Section::make('Landing Page Pricing')
                        ->description('Minimum tour cost and deposit (in USD)')
                        ->icon('heroicon-o-currency-dollar')
                        ->collapsible()
                        ->columnSpan(2)
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('minimum_tour_cost_dollar')
                                        ->label('Minimum Tour Cost (USD)')
                                        ->numeric()
                                        ->prefix('$')
                                        ->minValue(0)
                                        ->step(0.01),
                                    Forms\Components\TextInput::make('deposit_amount_dollar')
                                        ->label('Deposit Amount (USD)')
                                        ->numeric()
                                        ->prefix('$')
                                        ->minValue(0)
                                        ->step(0.01),
                                ]),
                        ]),

                    Forms\Components\Section::make('Announcements & Offers')
                        ->description('Top offer bar and main announcement card')
                        ->icon('heroicon-o-megaphone')
                        ->collapsible()
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Textarea::make('offer_bar_text')
                                ->label('Offer Bar Text')
                                ->rows(2)
                                ->helperText('Shown at the very top of the landing page.'),
                            Forms\Components\Textarea::make('announcement_card_text')
                                ->label('Announcement Card')
                                ->rows(3)
                                ->helperText('Main announcement visible to all visitors.'),
                        ]),

                    Forms\Components\Section::make('Contact Information')
                        ->description('Email and phone displayed on the landing page')
                        ->icon('heroicon-o-phone')
                        ->collapsible()
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('contact_email')
                                        ->label('Email')
                                        ->email()
                                        ->required(),
                                    Forms\Components\TextInput::make('contact_phone')
                                        ->label('Phone')
                                        ->tel()
                                        ->required(),
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