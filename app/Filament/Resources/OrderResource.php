<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';
    protected static ?string $navigationLabel = 'الطلبات';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationGroup = 'إدارة المبيعات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('المستخدم')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title')
                    ->label('الدورة')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\TextInput::make('order_number')
                    ->label('رقم الطلب')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                
                Forms\Components\TextInput::make('currency')
                    ->label('العملة')
                    ->default('EGP')
                    ->required()
                    ->maxLength(3),
                
                Forms\Components\Select::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'pending' => 'معلق',
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'expired' => 'منتهي الصلاحية',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\Select::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('تاريخ انتهاء الصلاحية')
                    ->required(),
                
                // Customer details
                Forms\Components\Section::make('تفاصيل العميل')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('اسم العميل')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('customer_email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('رقم الهاتف')
                            ->maxLength(20),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('حالة الطلب')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        'paid' => 'success',
                        'failed' => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    }),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاريخ انتهاء الصلاحية')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'pending' => 'معلق',
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'expired' => 'منتهي الصلاحية',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ]),
                
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->label('الدورة')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
