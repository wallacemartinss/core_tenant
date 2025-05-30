<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\UserResource\{Pages};
use App\Models\User;
use Filament\Forms\Components\{Section, TextInput};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{ImageColumn, TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'fas-user-plus';

    protected static ?string $navigationGroup = 'Administração';

    protected static ?string $navigationLabel = 'Meus Usuários';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $modelLabelPlural = "Usuários";

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = true;

    public static function form(Form $form): Form
    {
        return $form

            ->schema([

                Section::make('Dados do usuário')
                    ->description('Preencha os dados do usuário, a senha de acesso será gerada automaticamente e enviada para o e-mail do seu usuário.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->prefixIcon('fas-id-card')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->prefixIcon('fas-envelope')
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'E-mail já cadastrado.',
                            ])
                            ->required()
                            ->maxLength(255),
                        PhoneNumber::make('phone')
                            ->mask('(99) 99999-9999')
                            ->required()
                            ->prefixIcon('fas-phone'),

                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->getStateUsing(function ($record) {
                        return $record->getFilamentAvatarUrl();
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable(),

                ToggleColumn::make('is_active')
                    ->label('Ativo')
                    ->sortable()
                    ->alignCenter()
                    ->beforeStateUpdated(function ($record, $state) {
                        $record->update(['is_active' => $state]);

                        if ($state === true) {
                            Notification::make()
                            ->title('Acesso Liberado')
                            ->body("O Acesso do Usuário {$record->name} foi liberado")
                            ->success()
                            ->send();
                        } else {
                            Notification::make()
                            ->title('Acesso Desativado')
                            ->body("O Acesso do Usuário {$record->name} foi Desativado")
                            ->warning()
                            ->send();
                        }

                    }),
                Tables\Columns\IconColumn::make('is_tenant_admin')
                    ->label('Dono do Tenant')
                    ->alignCenter()
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('primary'),
                    EditAction::make()
                        ->color('secondary'),
                    DeleteAction::make(),
                ])
                ->icon('fas-sliders')
                ->color('warning'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
