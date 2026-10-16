<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q', ''));

        $ventas = Venta::query()
            ->with('paciente')
            ->withCount('partidas')
            ->withSum('pagos', 'monto')
            ->when(
                $busqueda !== '',
                fn (Builder $query) => $query->whereHas(
                    'paciente',
                    fn (Builder $pacientes) => $pacientes->where('nombre', 'like', "%{$busqueda}%")
                )
            )
            ->latest('fecha_venta')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('ventas.index', compact('ventas', 'busqueda'));
    }

    public function create(Paciente $paciente): View
    {
        return view('ventas.create', [
            'paciente' => $paciente,
            'fechaVenta' => now()->toDateString(),
        ]);
    }

    public function store(Request $request, Paciente $paciente): RedirectResponse
    {
        $datos = $request->validate([
            'fecha_venta' => ['required', 'date', 'before_or_equal:today'],
            'pago_inicial' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'metodo_pago' => ['nullable', 'string', 'in:efectivo,tarjeta,transferencia,otro'],
            'notas' => ['nullable', 'string', 'max:2000'],
            'productos' => ['required', 'array', 'min:1', 'max:50'],
            'productos.*.descripcion' => ['required', 'string', 'max:255'],
            'productos.*.cantidad' => ['required', 'integer', 'between:1,9999'],
            'productos.*.precio_unitario' => ['required', 'numeric', 'between:0.01,9999999.99'],
        ], [
            'fecha_venta.required' => 'La fecha de compra es obligatoria.',
            'fecha_venta.date' => 'La fecha de compra no es válida.',
            'fecha_venta.before_or_equal' => 'La fecha de compra no puede ser futura.',
            'metodo_pago.in' => 'La forma de pago seleccionada no es válida.',
            'pago_inicial.numeric' => 'El anticipo debe ser un número.',
            'pago_inicial.decimal' => 'El anticipo puede tener hasta dos decimales.',
            'pago_inicial.min' => 'El anticipo no puede ser negativo.',
            'notas.max' => 'Las notas no pueden superar los 2000 caracteres.',
            'productos.required' => 'Agrega al menos un producto a la compra.',
            'productos.min' => 'Agrega al menos un producto a la compra.',
            'productos.max' => 'Una compra no puede tener más de 50 partidas.',
            'productos.*.descripcion.required' => 'La descripción del producto es obligatoria.',
            'productos.*.descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.between' => 'La cantidad debe estar entre 1 y 9999.',
            'productos.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'productos.*.precio_unitario.numeric' => 'El precio debe ser un número.',
            'productos.*.precio_unitario.between' => 'El precio debe estar entre $0.01 y $9,999,999.99.',
        ]);

        $pagoInicial = (int) round((float) ($datos['pago_inicial'] ?? 0) * 100);
        if ($pagoInicial > 0 && empty($datos['metodo_pago'])) {
            throw ValidationException::withMessages([
                'metodo_pago' => 'Selecciona la forma de pago del anticipo.',
            ]);
        }

        $venta = DB::transaction(function () use ($datos, $paciente, $pagoInicial): Venta {
            $partidas = [];
            $totalCentavos = 0;

            foreach ($datos['productos'] as $producto) {
                $precioCentavos = (int) round((float) $producto['precio_unitario'] * 100);
                $subtotalCentavos = $precioCentavos * (int) $producto['cantidad'];
                $totalCentavos += $subtotalCentavos;

                $partidas[] = [
                    'descripcion' => $producto['descripcion'],
                    'cantidad' => (int) $producto['cantidad'],
                    'precio_unitario' => $this->formatearCentavos($precioCentavos),
                    'subtotal' => $this->formatearCentavos($subtotalCentavos),
                ];
            }

            $venta = $paciente->ventas()->create([
                'fecha_venta' => $datos['fecha_venta'],
                'metodo_pago' => $datos['metodo_pago'] ?? 'otro',
                'total' => $this->formatearCentavos($totalCentavos),
                'notas' => $datos['notas'] ?? null,
            ]);

            $venta->partidas()->createMany($partidas);

            if ($pagoInicial > $totalCentavos) {
                throw ValidationException::withMessages([
                    'pago_inicial' => 'El anticipo no puede ser mayor al total de la compra.',
                ]);
            }

            if ($pagoInicial > 0) {
                $venta->pagos()->create([
                    'fecha_pago' => $datos['fecha_venta'],
                    'monto' => $this->formatearCentavos($pagoInicial),
                    'metodo_pago' => $datos['metodo_pago'],
                ]);
            }

            return $venta;
        });

        return redirect()->route('ventas.show', $venta)->with('exito', 'Compra registrada correctamente.');
    }

    public function show(Venta $venta): View
    {
        $venta->load(['paciente', 'partidas', 'pagos' => fn ($query) => $query->orderBy('fecha_pago')->orderBy('id')]);
        $totalCentavos = (int) round((float) $venta->total * 100);
        $pagadoCentavos = (int) round((float) $venta->pagos->sum('monto') * 100);
        $saldoCentavos = max(0, $totalCentavos - $pagadoCentavos);
        $totalFormateado = $this->formatearCentavos($totalCentavos);
        $pagadoFormateado = $this->formatearCentavos($pagadoCentavos);
        $saldoFormateado = $this->formatearCentavos($saldoCentavos);

        return view('ventas.show', compact(
            'venta',
            'totalCentavos',
            'pagadoCentavos',
            'saldoCentavos',
            'totalFormateado',
            'pagadoFormateado',
            'saldoFormateado'
        ));
    }

    public function edit(Venta $venta): View
    {
        $venta->load('partidas');

        return view('ventas.create', [
            'paciente' => $venta->paciente,
            'venta' => $venta,
            'fechaVenta' => $venta->fecha_venta->toDateString(),
        ]);
    }

    public function update(Request $request, Venta $venta): RedirectResponse
    {
        $datos = $request->validate([
            'fecha_venta' => ['required', 'date', 'before_or_equal:today'],
            'notas' => ['nullable', 'string', 'max:2000'],
            'productos' => ['required', 'array', 'min:1', 'max:50'],
            'productos.*.descripcion' => ['required', 'string', 'max:255'],
            'productos.*.cantidad' => ['required', 'integer', 'between:1,9999'],
            'productos.*.precio_unitario' => ['required', 'numeric', 'between:0.01,9999999.99'],
            'abono_monto' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'abono_fecha' => ['nullable', 'date', 'before_or_equal:today'],
            'abono_metodo_pago' => ['nullable', 'string', 'in:efectivo,tarjeta,transferencia,otro'],
            'abono_notas' => ['nullable', 'string', 'max:1000'],
        ], [
            'fecha_venta.required' => 'La fecha de compra es obligatoria.',
            'fecha_venta.date' => 'La fecha de compra no es válida.',
            'fecha_venta.before_or_equal' => 'La fecha de compra no puede ser futura.',
            'notas.max' => 'Las notas no pueden superar los 2000 caracteres.',
            'productos.required' => 'Agrega al menos un producto a la compra.',
            'productos.min' => 'Agrega al menos un producto a la compra.',
            'productos.max' => 'Una compra no puede tener más de 50 partidas.',
            'productos.*.descripcion.required' => 'La descripción del producto es obligatoria.',
            'productos.*.descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.between' => 'La cantidad debe estar entre 1 y 9999.',
            'productos.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'productos.*.precio_unitario.numeric' => 'El precio debe ser un número.',
            'productos.*.precio_unitario.between' => 'El precio debe estar entre $0.01 y $9,999,999.99.',
            'abono_monto.numeric' => 'El importe del abono debe ser un número.',
            'abono_monto.decimal' => 'El importe del abono puede tener hasta dos decimales.',
            'abono_monto.min' => 'El importe del abono no puede ser negativo.',
            'abono_fecha.date' => 'La fecha del abono no es válida.',
            'abono_fecha.before_or_equal' => 'La fecha del abono no puede ser futura.',
            'abono_metodo_pago.in' => 'La forma de pago del abono no es válida.',
            'abono_notas.max' => 'Las notas del abono no pueden superar los 1000 caracteres.',
        ]);

        $abonoCentavos = (int) round((float) ($datos['abono_monto'] ?? 0) * 100);
        if ($abonoCentavos > 0 && empty($datos['abono_metodo_pago'])) {
            throw ValidationException::withMessages([
                'abono_metodo_pago' => 'Selecciona la forma de pago del abono.',
            ]);
        }
        if ($abonoCentavos > 0 && empty($datos['abono_fecha'])) {
            throw ValidationException::withMessages([
                'abono_fecha' => 'Indica la fecha del abono.',
            ]);
        }
        if (
            $abonoCentavos > 0
            && isset($datos['fecha_venta'], $datos['abono_fecha'])
            && $datos['abono_fecha'] < $datos['fecha_venta']
        ) {
            throw ValidationException::withMessages([
                'abono_fecha' => 'La fecha del abono no puede ser anterior a la compra.',
            ]);
        }

        DB::transaction(function () use ($datos, $venta, $abonoCentavos): void {
            $ventaBloqueada = Venta::query()->lockForUpdate()->findOrFail($venta->id);
            $partidas = [];
            $totalCentavos = 0;

            foreach ($datos['productos'] as $producto) {
                $precioCentavos = (int) round((float) $producto['precio_unitario'] * 100);
                $subtotalCentavos = $precioCentavos * (int) $producto['cantidad'];
                $totalCentavos += $subtotalCentavos;

                $partidas[] = [
                    'descripcion' => $producto['descripcion'],
                    'cantidad' => (int) $producto['cantidad'],
                    'precio_unitario' => $this->formatearCentavos($precioCentavos),
                    'subtotal' => $this->formatearCentavos($subtotalCentavos),
                ];
            }

            $pagadoCentavos = (int) round((float) $ventaBloqueada->pagos()->sum('monto') * 100);
            if ($totalCentavos < $pagadoCentavos) {
                throw ValidationException::withMessages([
                    'productos' => 'El total de la compra no puede ser menor a los $'.$this->formatearCentavos($pagadoCentavos).' ya abonados.',
                ]);
            }

            if ($abonoCentavos > $totalCentavos - $pagadoCentavos) {
                throw ValidationException::withMessages([
                    'abono_monto' => 'El abono no puede superar el saldo pendiente de $'.$this->formatearCentavos($totalCentavos - $pagadoCentavos).'.',
                ]);
            }

            if ($ventaBloqueada->pagos()->where('fecha_pago', '<', $datos['fecha_venta'])->exists()) {
                throw ValidationException::withMessages([
                    'fecha_venta' => 'La fecha de compra no puede ser posterior a un anticipo o abono registrado.',
                ]);
            }

            $ventaBloqueada->update([
                'fecha_venta' => $datos['fecha_venta'],
                'total' => $this->formatearCentavos($totalCentavos),
                'notas' => $datos['notas'] ?? null,
            ]);

            $ventaBloqueada->partidas()->delete();
            $ventaBloqueada->partidas()->createMany($partidas);

            if ($abonoCentavos > 0) {
                $ventaBloqueada->pagos()->create([
                    'fecha_pago' => $datos['abono_fecha'],
                    'monto' => $this->formatearCentavos($abonoCentavos),
                    'metodo_pago' => $datos['abono_metodo_pago'],
                    'notas' => $datos['abono_notas'] ?? null,
                ]);
            }
        });

        return redirect()->route('ventas.show', $venta)->with('exito', 'Compra actualizada correctamente.');
    }

    public function storeAbono(Request $request, Venta $venta): RedirectResponse
    {
        $datos = $request->validate([
            'fecha_pago' => ['required', 'date', 'after_or_equal:'.$venta->fecha_venta->toDateString(), 'before_or_equal:today'],
            'monto' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'metodo_pago' => ['required', 'string', 'in:efectivo,tarjeta,transferencia,otro'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ], [
            'fecha_pago.required' => 'La fecha del abono es obligatoria.',
            'fecha_pago.date' => 'La fecha del abono no es válida.',
            'fecha_pago.after_or_equal' => 'La fecha del abono no puede ser anterior a la compra.',
            'fecha_pago.before_or_equal' => 'La fecha del abono no puede ser futura.',
            'monto.required' => 'El importe del abono es obligatorio.',
            'monto.numeric' => 'El importe debe ser un número.',
            'monto.decimal' => 'El importe puede tener hasta dos decimales.',
            'monto.gt' => 'El importe del abono debe ser mayor a cero.',
            'metodo_pago.required' => 'Selecciona la forma de pago.',
            'metodo_pago.in' => 'La forma de pago seleccionada no es válida.',
            'notas.max' => 'Las notas no pueden superar los 1000 caracteres.',
        ]);

        DB::transaction(function () use ($datos, $venta): void {
            $ventaBloqueada = Venta::query()->lockForUpdate()->findOrFail($venta->id);
            $totalCentavos = (int) round((float) $ventaBloqueada->total * 100);
            $pagadoCentavos = (int) round((float) $ventaBloqueada->pagos()->sum('monto') * 100);
            $montoCentavos = (int) round((float) $datos['monto'] * 100);

            if ($montoCentavos > $totalCentavos - $pagadoCentavos) {
                throw ValidationException::withMessages([
                    'monto' => 'El abono no puede superar el saldo pendiente de $'.$this->formatearCentavos($totalCentavos - $pagadoCentavos).'.',
                ]);
            }

            $ventaBloqueada->pagos()->create([
                'fecha_pago' => $datos['fecha_pago'],
                'monto' => $this->formatearCentavos($montoCentavos),
                'metodo_pago' => $datos['metodo_pago'],
                'notas' => $datos['notas'] ?? null,
            ]);
        });

        return redirect()->route('ventas.show', $venta)->with('exito', 'Abono registrado correctamente.');
    }

    private function formatearCentavos(int $centavos): string
    {
        return intdiv($centavos, 100).'.'.str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
