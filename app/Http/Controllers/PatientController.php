<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $patients = Patient::query()
            ->withCount('consultations')
            ->withMax('consultations', 'fecha_consulta')
            ->when($search !== '', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients', 'search'));
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'diseases',
            'consultations' => fn ($query) => $query->latest('fecha_consulta'),
            'consultations.refraction',
        ]);

        return view('patients.show', compact('patient'));
    }
}
