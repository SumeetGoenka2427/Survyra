<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrCode as QrCodeModel;
use App\Models\Survey;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * No index() action here on purpose - the survey builder's QR Codes tab
 * (admin/surveys/_qr-codes-tab.blade.php, rendered from SurveyController::edit)
 * lists and generates codes inline; a separate listing page would be dead code.
 */
class QrCodeController extends Controller
{
    public function __construct(private readonly QrCodeService $qrCodes)
    {
    }

    public function store(Request $request, Survey $survey): RedirectResponse
    {
        $this->authorize('update', $survey);

        abort_unless($survey->status === 'published', 422, 'Only published surveys can have QR codes.');

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'format' => ['required', Rule::in(['svg', 'pdf'])],
        ]);

        $this->qrCodes->generate($survey, $validated['label'], $validated['format']);

        return back()->with('status', 'QR code generated.');
    }

    public function download(Survey $survey, QrCodeModel $qrCode): StreamedResponse
    {
        $this->authorize('update', $survey);

        abort_if($qrCode->survey_id !== $survey->id, 404);

        return Storage::disk('public')->download($qrCode->file_path, "{$qrCode->label}.{$qrCode->format}");
    }

    public function destroy(Survey $survey, QrCodeModel $qrCode): RedirectResponse
    {
        $this->authorize('update', $survey);

        abort_if($qrCode->survey_id !== $survey->id, 404);

        $this->qrCodes->delete($qrCode);

        return back()->with('status', 'QR code removed.');
    }
}
