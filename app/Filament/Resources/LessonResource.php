<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Filament\Resources\LessonResource\RelationManagers;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Components\VideoThumbnail;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $modelLabel = 'درس';
    protected static ?string $pluralModelLabel = 'الدروس';
    protected static ?string $navigationLabel = 'الدروس';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title')
                    ->label('الدورة')
                    ->required()
                    ->searchable()
                    ->preload(),
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
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\TextInput::make('order')
                    ->label('الترتيب')
                    ->required()
                    ->numeric()
                    ->default(fn (Builder $query) => $query->count() + 1),
                Forms\Components\Toggle::make('is_free')
                    ->label('درس مجاني')
                    ->default(false),
                Forms\Components\Toggle::make('is_published')
                    ->label('منشور')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الدرس')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
                VideoThumbnail::make('video_url')
                    ->label('الفيديو')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('attachments')
                    ->label('المرفقات')
                    ->circular()
                    ->stacked()
                    ->toggleable(),
                Tables\Columns\ToggleColumn::make('is_free')
                    ->label('مجاني')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('منشور')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->label('الدورة')
                    ->placeholder('الكل')
                    ->preload(),
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview')
                        ->label('معاينة')
                        ->icon('heroicon-m-play-circle')
                        ->color('success')
                        ->button()
                        ->size('sm')
                        ->action(function ($record) {
                            return null;
                        }),
                    Tables\Actions\Action::make('edit')
                        ->label('تعديل')
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->url(fn ($record) => route('filament.admin.resources.lessons.edit', $record))
                        ->openUrlInNewTab(false),
                    Tables\Actions\DeleteAction::make()
                        ->label('حذف')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->modalHeading('حذف الدرس')
                        ->modalDescription('هل أنت متأكد من حذف هذا الدرس؟')
                        ->modalSubmitActionLabel('نعم، احذف')
                        ->modalCancelActionLabel('إلغاء'),
                ])
                ->tooltip('الإجراءات')
                ->color('gray')
                ->icon('heroicon-m-ellipsis-horizontal')
                ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
