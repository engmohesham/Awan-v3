<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'الدروس';
    protected static ?string $modelLabel = 'درس';
    protected static ?string $pluralModelLabel = 'الدروس';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الدرس')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('description')
                    ->label('وصف الدرس')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('video_url')
                    ->label('رابط الفيديو')
                    ->required()
                    ->url()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('attachments')
                    ->label('المرفقات')
                    ->multiple()
                    ->directory('lessons/attachments')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_free')
                    ->label('درس مجاني')
                    ->default(false),
                Forms\Components\Toggle::make('is_published')
                    ->label('منشور')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الدرس')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_free')
                    ->label('مجاني')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('النوع')
                    ->placeholder('الكل')
                    ->trueLabel('مجاني')
                    ->falseLabel('مدفوع'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('منشور')
                    ->falseLabel('غير منشور'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة درس'),
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
            ])
            ->defaultSort('created_at', 'desc');
    }
} 