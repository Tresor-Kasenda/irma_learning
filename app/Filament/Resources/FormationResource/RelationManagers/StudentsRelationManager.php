<?php

namespace App\Filament\Resources\FormationResource\RelationManagers;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserRoleEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\SelectColumn::make('pivot.status')
                    ->label('Statut')
                    ->options(EnrollmentStatusEnum::class)
                    ->selectablePlaceholder(false),

                Tables\Columns\SelectColumn::make('pivot.payment_status')
                    ->label('Paiement')
                    ->options(EnrollmentPaymentEnum::class)
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('pivot.progress_percentage')
                    ->label('Progression')
                    ->suffix('%')
                    ->alignCenter()
                    ->color(fn($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('pivot.enrollment_date')
                    ->label('Inscrit le')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.last_accessed_at')
                    ->label('Dernier accès')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pivot.status')
                    ->label('Statut')
                    ->options(EnrollmentStatusEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('pivot.payment_status')
                    ->label('Statut de paiement')
                    ->options(EnrollmentPaymentEnum::class)
                    ->multiple(),

                Tables\Filters\Filter::make('completed')
                    ->query(fn(Builder $query) => $query->wherePivot('progress_percentage', '>=', 100))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DetachAction::make()
                        ->label('Se désinscrire')
                        ->icon('heroicon-o-minus')
                        ->color('danger')
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('view_progress')
                        ->label('Voir progression')
                        ->icon('heroicon-o-chart-bar')
                        ->color('info')
                        ->url(fn($record) => route('filament.admin.resources.users.view', $record))
                        ->openUrlInNewTab(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Se désinscrire')
                        ->icon('heroicon-o-minus')
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('pivot.enrollment_date', 'desc');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de l\'inscription')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Étudiant')
                            ->relationship('students', 'name', function ($query) {
                                $query->where('role', UserRoleEnum::STUDENT->value);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(
                                collect(EnrollmentStatusEnum::cases())
                                    ->take(7)
                                    ->mapWithKeys(fn($role) => [$role->value => $role->getLabel()])
                            )
                            ->required()
                            ->default('active'),

                        Forms\Components\Select::make('payment_status')
                            ->label('Statut de paiement')
                            ->options(
                                collect(EnrollmentPaymentEnum::cases())
                                    ->take(7)
                                    ->mapWithKeys(fn($role) => [$role->value => $role->getLabel()])
                            )
                            ->required()
                            ->default('pending'),

                        Forms\Components\TextInput::make('progress_percentage')
                            ->label('Progression (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),

                        Forms\Components\DateTimePicker::make('enrollment_date')
                            ->label('Date d\'inscription')
                            ->default(now())
                            ->required(),
                    ]),
            ]);
    }
}
