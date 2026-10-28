<?php

namespace App\Http\Controllers;

use App\Services\SchoolIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolIntegrationController extends Controller
{
    public function __construct(
        protected SchoolIntegrationService $integrationService
    ) {}

    /**
     * Get list of unique available provinces for integration.
     */
    public function provinces(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'provinces' => $this->integrationService->getAvailableProvinces(),
        ]);
    }

    /**
     * Get list of available regencies for integration.
     */
    public function regencies(Request $request): JsonResponse
    {
        $regencies = $this->integrationService->getAvailableRegencies();
        if ($request->filled('province')) {
            $province = trim($request->input('province'));
            $regencies = array_values(array_filter($regencies, fn($r) => strcasecmp($r['province'], $province) === 0));
        }

        return response()->json([
            'status' => true,
            'regencies' => $regencies,
        ]);
    }

    /**
     * Get list of districts (kecamatan) for a specific regency.
     */
    public function districts(Request $request): JsonResponse
    {
        $regencyCode = (string) $request->input('regency_code', '3215');
        $districts = $this->integrationService->getDistrictsByRegency($regencyCode);

        return response()->json([
            'status' => true,
            'regency_code' => $regencyCode,
            'districts' => $districts,
        ]);
    }

    /**
     * Execute integration with Dapodik (Kemdikbud) and EMIS (Kemenag).
     */
    public function integrate(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'regency_code' => ['required', 'string', 'max:10'],
            'district_code' => ['nullable'],
            'district_codes' => ['nullable', 'array'],
            'criteria' => ['required', 'string', 'in:ALL,SMA,SMK,MA'],
            'integration_year' => ['nullable', 'integer', 'min:2000', 'max:2050'],
        ], [
            'regency_code.required' => 'Silakan pilih Kabupaten / Kota target integrasi.',
            'criteria.required' => 'Silakan pilih kriteria jenjang sekolah (SMK, SMA, MA, atau ALL).',
        ]);

        $districtParam = $request->input('district_codes');
        if (empty($districtParam) && $request->filled('district_code')) {
            $districtParam = $request->input('district_code');
        }

        $integrationYear = $request->filled('integration_year')
            ? (int) $request->input('integration_year')
            : (int) date('Y');

        $result = $this->integrationService->integrate(
            $request->input('regency_code'),
            $request->input('criteria', 'ALL'),
            $districtParam,
            $integrationYear
        );

        $toastType = $result['status'] ? 'success' : 'danger';
        session()->flash('toast', [
            'type' => $toastType,
            'message' => $result['message'],
        ]);
        if ($result['status']) {
            session()->flash('integration_summary', $result);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result, $result['status'] ? 200 : 422);
        }

        return redirect()->route('schools.index');
    }
}
