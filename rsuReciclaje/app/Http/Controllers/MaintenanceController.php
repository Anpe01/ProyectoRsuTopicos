<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceRequest;
use App\Http\Requests\MaintenanceScheduleRequest;
use App\Models\Employee;
use App\Models\Maintenance;
use App\Models\MaintenanceDay;
use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    public function index()
    {
        $maintenances = Maintenance::with('schedules.vehicle', 'schedules.responsible')
            ->latest()
            ->get();

        return view('maintenances.index', compact('maintenances'));
    }

    public function store(MaintenanceRequest $request): JsonResponse
    {
        try {
            $maintenance = Maintenance::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento registrado correctamente.',
                'maintenance' => $maintenance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el mantenimiento: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(MaintenanceRequest $request, Maintenance $maintenance): JsonResponse
    {
        try {
            $maintenance->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento actualizado correctamente.',
                'maintenance' => $maintenance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el mantenimiento: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Maintenance $maintenance): JsonResponse
    {
        try {
            DB::transaction(function () use ($maintenance) {
                // Eliminar días generados
                $scheduleIds = $maintenance->schedules()->pluck('id');
                MaintenanceDay::whereIn('maintenance_schedule_id', $scheduleIds)->delete();
                
                // Eliminar horarios
                $maintenance->schedules()->delete();
                
                // Eliminar mantenimiento
                $maintenance->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el mantenimiento: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Métodos para horarios de mantenimiento
    public function getSchedules(Maintenance $maintenance): JsonResponse
    {
        $schedules = $maintenance->schedules()
            ->with('vehicle', 'responsible')
            ->get();

        return response()->json($schedules);
    }

    public function storeSchedule(MaintenanceScheduleRequest $request): JsonResponse
    {
        try {
            // La validación se hace automáticamente por MaintenanceScheduleRequest
            // Si llega aquí, la validación pasó
            $schedule = null;
            DB::transaction(function () use ($request, &$schedule) {
                $data = $request->validated();
                $schedule = MaintenanceSchedule::create($data);

                // Generar días automáticamente
                $this->generateMaintenanceDays($schedule);
            });

            $schedule->load('vehicle', 'responsible');

            return response()->json([
                'success' => true,
                'message' => 'Horario de mantenimiento registrado correctamente.',
                'schedule' => $schedule,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación al crear horario: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Error de validación. Verifique los datos ingresados.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al registrar horario: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el horario: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateSchedule(MaintenanceScheduleRequest $request, MaintenanceSchedule $schedule): JsonResponse
    {
        try {
            // Log para depuración
            \Log::info('Actualizando horario', [
                'schedule_id' => $schedule->id,
                'data' => $request->all()
            ]);
            
            // La validación se hace automáticamente por MaintenanceScheduleRequest
            // Si llega aquí, la validación pasó
            DB::transaction(function () use ($request, $schedule) {
                $data = $request->validated();
                $schedule->update($data);

                // Eliminar días existentes y regenerar
                $schedule->days()->delete();
                $this->generateMaintenanceDays($schedule);
            });

            $schedule->load('vehicle', 'responsible');

            return response()->json([
                'success' => true,
                'message' => 'Horario de mantenimiento actualizado correctamente.',
                'schedule' => $schedule,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación al actualizar horario: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Error de validación. Verifique los datos ingresados.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar horario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el horario: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroySchedule(MaintenanceSchedule $schedule): JsonResponse
    {
        try {
            DB::transaction(function () use ($schedule) {
                // Eliminar días generados
                $schedule->days()->delete();
                // Eliminar horario
                $schedule->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Horario de mantenimiento eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el horario: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Métodos para días de mantenimiento
    public function getScheduleDays(MaintenanceSchedule $schedule): JsonResponse
    {
        $days = $schedule->days()
            ->orderBy('date')
            ->get()
            ->map(function ($day) {
                $imageUrl = null;
                if ($day->image_path) {
                    // Extraer el nombre del archivo del path
                    $filename = basename($day->image_path);
                    // Generar URL usando la ruta específica de Laravel para servir imágenes
                    // Solo generar URL si el filename no está vacío
                    if (!empty($filename) && Storage::disk('public')->exists($day->image_path)) {
                        $imageUrl = route('maintenances.images.serve', ['filename' => $filename]);
                    }
                }
                
                return [
                    'id' => $day->id,
                    'date' => $day->date->format('Y-m-d'), // Formato consistente
                    'observation' => $day->observation,
                    'image_path' => $day->image_path,
                    'image_url' => $imageUrl, // URL completa para acceso
                    'executed' => $day->executed,
                ];
            });

        return response()->json([
            'success' => true,
            'days' => $days,
        ]);
    }

    public function updateDay(Request $request, MaintenanceDay $day): JsonResponse
    {
        $request->validate([
            'observation' => ['nullable', 'string'],
            'executed' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        try {
            $data = [
                'observation' => $request->input('observation'),
                'executed' => $request->boolean('executed', false),
            ];

            // Subir imagen si existe
            if ($request->hasFile('image')) {
                // Eliminar imagen anterior si existe
                if ($day->image_path && Storage::disk('public')->exists($day->image_path)) {
                    Storage::disk('public')->delete($day->image_path);
                }

                $path = $request->file('image')->store('maintenance_images', 'public');
                $data['image_path'] = $path;
            }

            $day->update($data);
            
            // Recargar el modelo para obtener los datos actualizados
            $day->refresh();
            
            // Generar URL de la imagen si existe
            $imageUrl = null;
            if ($day->image_path) {
                $filename = basename($day->image_path);
                // Solo generar URL si el filename no está vacío
                if (!empty($filename) && Storage::disk('public')->exists($day->image_path)) {
                    $imageUrl = route('maintenances.images.serve', ['filename' => $filename]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Día actualizado correctamente.',
                'day' => [
                    'id' => $day->id,
                    'date' => $day->date->format('Y-m-d'),
                    'observation' => $day->observation,
                    'image_path' => $day->image_path,
                    'image_url' => $imageUrl,
                    'executed' => $day->executed,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el día: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyDay(MaintenanceDay $day): JsonResponse
    {
        try {
            // Eliminar imagen si existe
            if ($day->image_path && Storage::disk('public')->exists($day->image_path)) {
                Storage::disk('public')->delete($day->image_path);
            }

            $day->delete();

            return response()->json([
                'success' => true,
                'message' => 'Día eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el día: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Métodos auxiliares
    private function generateMaintenanceDays(MaintenanceSchedule $schedule): void
    {
        $maintenance = $schedule->maintenance;
        $startDate = Carbon::parse($maintenance->start_date);
        $endDate = Carbon::parse($maintenance->end_date);

        // Mapeo de días de la semana en español a número de Carbon
        $dayMap = [
            'Lunes' => Carbon::MONDAY,
            'Martes' => Carbon::TUESDAY,
            'Miércoles' => Carbon::WEDNESDAY,
            'Jueves' => Carbon::THURSDAY,
            'Viernes' => Carbon::FRIDAY,
            'Sábado' => Carbon::SATURDAY,
            'Domingo' => Carbon::SUNDAY,
        ];

        $targetDayOfWeek = $dayMap[$schedule->day_of_week] ?? null;

        if (!$targetDayOfWeek) {
            return;
        }

        // Encontrar el primer día de la semana objetivo dentro del rango
        $currentDate = $startDate->copy();
        
        // Si la fecha de inicio no es el día objetivo, avanzar hasta encontrarlo
        if ($currentDate->dayOfWeek !== $targetDayOfWeek) {
            // Calcular cuántos días faltan para llegar al día objetivo
            $daysToAdd = ($targetDayOfWeek - $currentDate->dayOfWeek + 7) % 7;
            if ($daysToAdd === 0) {
                $daysToAdd = 7; // Si es el mismo día, avanzar una semana
            }
            $currentDate->addDays($daysToAdd);
        }

        // Generar días para cada semana dentro del rango
        while ($currentDate->lte($endDate)) {
            // Solo crear si está dentro del rango
            if ($currentDate->gte($startDate) && $currentDate->lte($endDate)) {
                MaintenanceDay::create([
                    'maintenance_schedule_id' => $schedule->id,
                    'date' => $currentDate->toDateString(),
                    'executed' => false,
                ]);
            }

            $currentDate->addWeek();
        }
    }

    // Métodos AJAX para obtener datos
    public function getVehicles(): JsonResponse
    {
        $vehicles = Vehicle::where('status', 1)
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($vehicles);
    }

    public function getEmployees(): JsonResponse
    {
        $employees = Employee::where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->fullname,
            ];
        }));
    }

    /**
     * Servir imágenes de mantenimiento directamente desde el storage
     */
    public function serveImage(string $filename): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        $path = 'maintenance_images/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Imagen no encontrada');
        }

        $file = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
