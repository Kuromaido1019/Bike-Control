<?php

namespace App\Console\Commands;

use App\Models\Access;
use App\Models\Bike;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyDataRelationships extends Command
{
    protected $signature = 'data:verify-relationships
                            {--details : Mostrar detalles de registros problemáticos}
                            {--stats : Mostrar estadísticas avanzadas del sistema}';

    protected $description = 'Verifica la integridad de las relaciones entre modelos y muestra estadísticas';

    public function handle()
    {
        $this->info('🔍 Iniciando verificación de relaciones de datos...');
        $this->newLine();

        // 1. Verificación básica de relaciones
        $this->checkUsersRelationships();
        $this->checkBikesRelationships();
        $this->checkAccessesRelationships();
        $this->checkProfilesRelationships();

        // 2. Estadísticas avanzadas (si se solicita)
        if ($this->option('stats')) {
            $this->newLine();
            $this->showSystemStatistics();
            $this->showActivityAnalysis();
        }

        $this->newLine();
        $this->info('✅ Verificación completada');
    }

    protected function checkUsersRelationships()
    {
        $this->info('👥 Verificando usuarios...');

        $invalidUsers = User::query()
            ->where(function($query) {
                $query->where('role', 'visitante')
                    ->doesntHave('bikes');
            })
            ->orWhere(function($query) {
                $query->where('role', 'guardia')
                    ->doesntHave('accessesAsGuard');
            })
            ->count();

        $this->line("Usuarios con relaciones inconsistentes: {$invalidUsers}");

        if ($this->option('details') && $invalidUsers > 0) {
            $users = User::withCount(['bikes', 'accessesAsGuard'])
                ->having('bikes_count', '=', 0)
                ->orHaving('accessesAsGuard_count', '=', 0)
                ->get();

            $this->table(
                ['ID', 'Nombre', 'Rol', 'Bicicletas', 'Registros'],
                $users->map(function ($user) {
                    return [
                        $user->id,
                        $user->name,
                        $user->role,
                        $user->bikes_count,
                        $user->accessesAsGuard_count
                    ];
                })
            );
        }
    }

    protected function checkBikesRelationships()
    {
        $this->info('🚲 Verificando bicicletas...');

        $orphanBikes = Bike::doesntHave('user')->count();
        $unusedBikes = Bike::doesntHave('accesses')->count();

        $this->line("Bicicletas sin dueño: {$orphanBikes}");
        $this->line("Bicicletas nunca registradas: {$unusedBikes}");

        if ($this->option('details') && ($orphanBikes > 0 || $unusedBikes > 0)) {
            $bikes = Bike::withCount(['user', 'accesses'])
                ->having('user_count', '=', 0)
                ->orHaving('accesses_count', '=', 0)
                ->get();

            $this->table(
                ['ID', 'Marca', 'Color', 'Dueño', 'Registros'],
                $bikes->map(function ($bike) {
                    return [
                        $bike->id,
                        $bike->brand,
                        $bike->color,
                        $bike->user_count ? 'Sí' : 'No',
                        $bike->accesses_count
                    ];
                })
            );
        }
    }

    protected function checkAccessesRelationships()
    {
        $this->info('🔄 Verificando registros de acceso...');

        $invalidAccesses = DB::table('accesses')
            ->leftJoin('users as visitors', 'accesses.user_id', '=', 'visitors.id')
            ->leftJoin('users as guards', 'accesses.guard_id', '=', 'guards.id')
            ->leftJoin('bikes', 'accesses.bike_id', '=', 'bikes.id')
            ->whereNull('visitors.id')
            ->orWhereNull('guards.id')
            ->orWhereNull('bikes.id')
            ->orWhere('visitors.role', '!=', 'visitante')
            ->orWhere('guards.role', '!=', 'guardia')
            ->count();

        $this->line("Registros de acceso inválidos: {$invalidAccesses}");

        if ($this->option('details') && $invalidAccesses > 0) {
            $accesses = Access::with(['user', 'guardUser', 'bike'])
                ->get()
                ->filter(function ($access) {
                    return !$access->user ||
                           !$access->guardUser ||
                           !$access->bike ||
                           $access->user->role !== 'visitante' ||
                           $access->guardUser->role !== 'guardia';
                });

            $this->table(
                ['ID', 'Visitante', 'Guardia', 'Bicicleta', 'Problema'],
                $accesses->map(function ($access) {
                    $problems = [];
                    if (!$access->user) $problems[] = 'Sin visitante';
                    elseif ($access->user->role !== 'visitante') $problems[] = 'Rol visitante inválido';
                    if (!$access->guardUser) $problems[] = 'Sin guardia';
                    elseif ($access->guardUser->role !== 'guardia') $problems[] = 'Rol guardia inválido';
                    if (!$access->bike) $problems[] = 'Sin bicicleta';

                    return [
                        $access->id,
                        $access->user ? $access->user->name : 'N/A',
                        $access->guardUser ? $access->guardUser->name : 'N/A',
                        $access->bike ? $access->bike->brand : 'N/A',
                        implode(', ', $problems)
                    ];
                })
            );
        }
    }

    protected function checkProfilesRelationships()
    {
        $this->info('📝 Verificando perfiles de usuario...');

        $invalidProfiles = Profile::doesntHave('user')->count();
        $visitorsWithoutProfile = User::where('role', 'visitante')
            ->doesntHave('profile')
            ->count();

        $this->line("Perfiles sin usuario: {$invalidProfiles}");
        $this->line("Visitantes sin perfil: {$visitorsWithoutProfile}");

        if ($this->option('details') && ($invalidProfiles > 0 || $visitorsWithoutProfile > 0)) {
            if ($invalidProfiles > 0) {
                $this->info('Perfiles huérfanos:');
                $this->table(
                    ['ID', 'RUT', 'Teléfono'],
                    Profile::doesntHave('user')
                        ->get()
                        ->map(function ($profile) {
                            return [
                                $profile->id,
                                $profile->rut,
                                $profile->phone
                            ];
                        })
                );
            }

            if ($visitorsWithoutProfile > 0) {
                $this->info('Visitantes sin perfil:');
                $this->table(
                    ['ID', 'Nombre', 'Email'],
                    User::where('role', 'visitante')
                        ->doesntHave('profile')
                        ->get()
                        ->map(function ($user) {
                            return [
                                $user->id,
                                $user->name,
                                $user->email
                            ];
                        })
                );
            }
        }
    }

    protected function showSystemStatistics()
    {
        $this->info('📊 Estadísticas generales del sistema:');

        $stats = [
            'Total usuarios' => User::count(),
            'Total visitantes' => User::where('role', 'visitante')->count(),
            'Total guardias' => User::where('role', 'guardia')->count(),
            'Total administradores' => User::where('role', 'admin')->count(),
            'Total bicicletas' => Bike::count(),
            'Total accesos registrados' => Access::count(),
            'Bicicletas actualmente dentro' => Access::whereNull('exit_time')->count(),
            'Tiempo promedio de estadía (min)' => round(Access::whereNotNull('exit_time')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, entrance_time, exit_time)')), 2),
        ];

        $this->table(
            ['Métrica', 'Valor'],
            collect($stats)->map(fn($value, $key) => [$key, $value])
        );
    }

    protected function showActivityAnalysis()
    {
        $this->info('📈 Análisis de actividad:');

        // 1. Usuarios más activos
        $this->info('👤 Top 5 usuarios más activos:');
        $topUsers = User::withCount(['accessesAsVisitor', 'accessesAsGuard'])
            ->orderByDesc('accessesAsVisitor_count')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->role,
                    $user->accessesAsVisitor_count,
                    $user->accessesAsGuard_count,
                    optional($user->accessesAsVisitor()->latest()->first())->created_at?->format('d/m/Y')
                ];
            });

        $this->table(
            ['ID', 'Nombre', 'Rol', 'Accesos', 'Registros', 'Último acceso'],
            $topUsers
        );

        // 2. Bicicletas más utilizadas
        $this->info('🚴 Top 5 bicicletas más utilizadas:');
        $topBikes = Bike::withCount('accesses')
            ->with('user')
            ->orderByDesc('accesses_count')
            ->limit(5)
            ->get()
            ->map(function ($bike) {
                return [
                    $bike->id,
                    $bike->brand,
                    $bike->model,
                    $bike->user->name,
                    $bike->accesses_count,
                    optional($bike->accesses()->latest()->first())->created_at?->format('d/m/Y')
                ];
            });

        $this->table(
            ['ID', 'Marca', 'Modelo', 'Dueño', 'Usos', 'Último uso'],
            $topBikes
        );

        // 3. Actividad reciente
        $this->info('🕒 Actividad de los últimos 7 días:');
        $recentActivity = Access::selectRaw('
                DATE(entrance_time) as fecha,
                COUNT(*) as total,
                SUM(CASE WHEN exit_time IS NULL THEN 1 ELSE 0 END) as activos
            ')
            ->where('entrance_time', '>=', now()->subDays(7))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $this->table(
            ['Fecha', 'Total accesos', 'Bicicletas dentro'],
            $recentActivity->map(function ($day) {
                return [
                    $day->fecha,
                    $day->total,
                    $day->activos
                ];
            })
        );
    }
}
