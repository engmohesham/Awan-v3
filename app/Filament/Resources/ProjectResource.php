<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'مشروع';
    protected static ?string $pluralModelLabel = 'المشاريع';
    protected static ?string $navigationLabel = 'المشاريع';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationGroup = 'إدارة الوصول';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('صورة المشروع')
                    ->image()
                    ->imageEditor()
                    ->directory('projects/images')
                    ->visibility('public')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->label('اسم المشروع')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->label('رابط المشروع')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Select::make('platform_type')
                    ->label('نوع المنصة')
                    ->options([
                        'web' => 'موقع ويب',
                        'mobile' => 'تطبيق موبايل',
                        'graphic' => 'تصميم جرافيك',
                        'ai' => 'ذكاء اصطناعي',
                    ])
                    ->required(),
                Forms\Components\Select::make('project_type')
                    ->label('نوع المشروع')
                    ->options([
                        'entertainment' => 'ترفيهي',
                        'commercial' => 'تجاري',
                        'ecommerce' => 'متجر إلكتروني',
                        'educational' => 'تعليمي',
                        'social' => 'اجتماعي',
                        'other' => 'آخر',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('country')
                    ->label('البلد')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('published_at')
                    ->label('تاريخ النشر')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                Forms\Components\Textarea::make('description')
                    ->label('وصف المشروع')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المشروع')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform_type')
                    ->label('نوع المنصة')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'web' => 'موقع ويب',
                        'mobile' => 'تطبيق موبايل',
                        'graphic' => 'تصميم جرافيك',
                        'ai' => 'ذكاء اصطناعي',
                    }),
                Tables\Columns\TextColumn::make('project_type')
                    ->label('نوع المشروع')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entertainment' => 'ترفيهي',
                        'commercial' => 'تجاري',
                        'ecommerce' => 'متجر إلكتروني',
                        'educational' => 'تعليمي',
                        'social' => 'اجتماعي',
                        'other' => 'آخر',
                    }),
                Tables\Columns\TextColumn::make('country')
                    ->label('البلد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform_type')
                    ->label('نوع المنصة')
                    ->options([
                        'web' => 'موقع ويب',
                        'mobile' => 'تطبيق موبايل',
                        'graphic' => 'تصميم جرافيك',
                        'ai' => 'ذكاء اصطناعي',
                    ]),
                Tables\Filters\SelectFilter::make('project_type')
                    ->label('نوع المشروع')
                    ->options([
                        'entertainment' => 'ترفيهي',
                        'commercial' => 'تجاري',
                        'ecommerce' => 'متجر إلكتروني',
                        'educational' => 'تعليمي',
                        'social' => 'اجتماعي',
                        'other' => 'آخر',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
